<?php
/**
 * @link http://www.sav4yk.ru/
 */

namespace app\commands;

use Yii;
use GuzzleHttp\Client;
use app\models\Currency;
use yii\console\ExitCode;
use yii\console\Controller;

class FinanceController extends Controller
{
    /**
     * This command downloads, parse and store data to the database.
     *
     * @return int Exit code
     */
    public function actionIndex($date = '')
    {
        $this->CbrEveryday($date);
        return ExitCode::OK;
    }

    /**
     * This command downloads, parse and store daily data to the database from http://www.cbr.ru/.
     *
     * @return int Exit code
     */
    public function CbrDaily($date = '')
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
                $id = strip_tags($feed->Valute[$i]->attributes()->ID);
                $res2= $client->request('GET', 'http://www.cbr.ru/scripts/XML_dynamic.asp', [
                    'query' => [
                        'date_req1' => date('d/m/Y',strtotime('-30 days')),
                        'date_req2' => date('d/m/Y'),
                        'VAL_NM_RQ' => $id,
                    ],
                    'timeout' => 10,
                ]);
                if ($res2->getStatusCode()==200) {
                    $data = $res2->getBody(true)->getContents();
                    $feed_dynamic = simplexml_load_string($data);
                    $cnt_d = count($feed_dynamic->Record);
                    for($n=0;$n<$cnt_d;$n++) {
                        $currency = Currency::find()->where([
                            'date' => strtotime($feed_dynamic->Record[$n]->attributes()->Date)])->andWhere(['valuteID' => $id])
                            ->one();
                        if(!$currency) {
                            $InsertArray[] = [
                                'valuteID' => strip_tags($feed->Valute[$i]->attributes()->ID),
                                'numCode' => strip_tags($feed->Valute[$i]->NumCode),
                                'сharCodes' => strip_tags($feed->Valute[$i]->CharCode),
                                'name' => strip_tags($feed->Valute[$i]->Name),
                                'value' => strip_tags($feed_dynamic->Record[$n]->Value),
                                'date' => strtotime($feed_dynamic->Record[$n]->attributes()->Date),
                            ];
                        }
                    }
                }

            }
            if(count($InsertArray)>0){
                $columnNameArray=['valuteID','numCode','сharCodes','name','value', 'date'];
                $insertCount = Yii::$app->db->createCommand()
                    ->batchInsert(
                        "currency", $columnNameArray, $InsertArray
                    )
                    ->execute();
                print "--------------------------------\n";
                print "Saved " . $insertCount . " currency\n";
            } else {
                print "--------------------------------\n";
                print "Saved 0 currency\n";
            }
        }
        return ExitCode::OK;
    }

    /**
     * This command downloads, parse and store daily everyday data to the database from http://www.cbr.ru/.
     *
     * @return int Exit code
     */
    public function CbrEveryday($date = '')
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
                $id = strip_tags($feed->Valute[$i]->attributes()->ID);
                $currency = Currency::find()->where([
                    'date' => $date])->andWhere(['valuteID' => $id])
                    ->one();
                if(!$currency) {
                    $InsertArray[] = [
                        'valuteID' => $id,
                        'numCode' => strip_tags($feed->Valute[$i]->NumCode),
                        'сharCodes' => strip_tags($feed->Valute[$i]->CharCode),
                        'name' => strip_tags($feed->Valute[$i]->Name),
                        'value' => strip_tags($feed->Valute[$i]->Value),
                        'date' => $date,
                    ];
                }
            }

            if(count($InsertArray)>0){
                $columnNameArray=['valuteID','numCode','сharCodes','name','value', 'date'];
                $insertCount = Yii::$app->db->createCommand()
                    ->batchInsert(
                        "currency", $columnNameArray, $InsertArray
                    )
                    ->execute();
                print "--------------------------------\n";
                print "Saved " . $insertCount . " currency\n";
            } else {
                print "--------------------------------\n";
                print "Saved 0 currency\n";
            }
        }
        return ExitCode::OK;
    }
}
