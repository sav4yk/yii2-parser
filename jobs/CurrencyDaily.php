<?php
/**
 * @link http://www.sav4yk.ru/
 */

namespace app\jobs;

use Yii;
use GuzzleHttp\Client;
use yii\base\BaseObject;
use app\models\Currency;
use yii\queue\JobInterface;
use GuzzleHttp\Exception\GuzzleException;

class CurrencyDaily extends BaseObject implements JobInterface
{
    /**
     * @var int $date set request date
     */
    public $date;

    /**
     * This command downloads, parse and store data to the database .
     * @throws GuzzleException
     */
    public function execute($queue)
    {
        echo "\nGet daily data " . $this->date . " start\n";
       $this->CbrDaily($this->date);

    }

    /**
     * This command downloads, parse and store daily data to the database from http://www.cbr.ru/.
     *
     * @return int Exit code
     */
    public function CbrDaily($date = '14/07/2020')
    {
        $client = new Client();
        $res = $client->request('GET', 'http://www.cbr.ru/scripts/XML_daily.asp', [
            'query' => [
                'date_req' => $date,
            ],
            'timeout' => 10,
        ]);
        if ($res->getStatusCode()==200) {
            $data = $res->getBody(true)->getContents();
            $feed = simplexml_load_string($data);
            $InsertArray=[];
            $cnt = count($feed->Valute);
            $date =  date( strtotime($feed->attributes()->Date));
            for($i=0;$i<$cnt;$i++) {
                $currency = Currency::find()->where([
                    'date' => $date])
                    ->one();
                if(!$currency) {
                    echo $date . " " . strip_tags($feed->Valute[$i]->Name) . "\n";
                    $InsertArray[]=[
                        'valuteID' => strip_tags($feed->Valute[$i]->attributes()->ID),
                        'numCode' => strip_tags($feed->Valute[$i]->NumCode),
                        'сharCode' => strip_tags($feed->Valute[$i]->CharCode),
                        'name' => strip_tags($feed->Valute[$i]->Name),
                        'value' => strip_tags($feed->Valute[$i]->Value),
                        'date' => $date,
                    ];
                }
            }
            if(count($InsertArray)>0){
                $columnNameArray=['valuteID','numCode','сharCode','name','value', 'date'];
                $insertCount = Yii::$app->db->createCommand()
                    ->batchInsert(
                        "currency", $columnNameArray, $InsertArray
                    )
                    ->execute();
                print "--------------------------------\n";
                print "Saved " . $insertCount . " currency\n";
            } else {
                print "--------------------------------\n";
                print "Saved 0 news\n";
            }
        }
    }
}
