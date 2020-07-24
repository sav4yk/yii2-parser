<?php
/**
 * @link http://www.sav4yk.ru/
 */

namespace app\jobs;

use Yii;
use GuzzleHttp\Client;
use app\models\Proxies;
use yii\base\BaseObject;
use yii\db\Exception;
use yii\queue\JobInterface;
use app\models\RadiationPoints;

class Radiation extends BaseObject implements JobInterface
{
    /**
     * @param string $latitude user latitude coordinate.
     * @param string $longitude user longitude coordinate.
     * @param int $radius control radius.
     */
    public $latitude;
    public $longitude;
    public $radius;

    /**
     * This command downloads, parse and store radiation data from feerc.obninsk.org to the database.
     * @param $queue
     * @throws Exception
     */
    public function execute($queue)
    {
        echo "\nObninsk start\n";
        $client = new Client();
        $load=false;
        $proxy = Proxies::find()->orderBy('latency')->asArray()->all();
        $proxy_i=0;
        while ($load==false) {
            try {
                $load = true;
                echo $proxy[$proxy_i]["ip"] . ":" . $proxy[$proxy_i]["port"];
                $res = $client->request('GET', 'http://www.feerc.obninsk.org/remac/reqx.htm', [
                    'proxy' => $proxy[$proxy_i]["ip"] . ":" . $proxy[$proxy_i]["port"],
                    'query' => [
                        'p1' => '0',
                        'p2' => '1',                                    //21 -1
                        'p3' => '1',                                    //type(1)
                        'p4' => '0',
                        'p5' => date('d'),                       //day
                        'p6' => ((int)date('m') - 1),              //month(-1)
                        'p7' => date('Y'),                       //year
                        'p8' => '2',
                        'p9' => '1',
                        'p10' => ((float)$this->longitude - $this->radius),        //left longitude
                        'p11' => ((float)$this->longitude + $this->radius),        //right longitude
                        'p12' => ((float)$this->latitude + $this->radius),         //north latitude
                        'p13' => ((float)$this->latitude - $this->radius),          //south latitude
                    ],
                    'timeout' => 10,
                    'http_errors' => true
                ]);

                if ($res->getStatusCode() == 200) {
                    echo "\t data loaded,";
                    $rad_points = $res->getBody()->getContents();
                    $rad_points = iconv("windows-1251", "utf-8", $rad_points);
                    if (str_contains($rad_points,"Извините")) {
                        $load = true;
                        echo "\t no data on server\n";
                    }
                    $re1 = '/<table border=1 cellpadding=3 cellspacing=3>(.*?)<\/table>/s';
                    $c = preg_match_all($re1, $rad_points, $table, PREG_SET_ORDER, 0);

                    $re2 = '/<tr>(.*?)<\/tr>/s';
                    $c = preg_match_all($re2, $table[0][0], $tr, PREG_SET_ORDER, 0);

                    $date = date('Y-m-d');
                    echo "\t proxy used and deleted\n";
                    for ($i = 1; $i <= $c; $i++) {
                        $re3 = '/<td>(.*?)<\/td>/s';
                        $c = preg_match_all($re3, $tr[$i][0], $td, PREG_SET_ORDER, 0);

                        $rad_point = RadiationPoints::find()->where([
                            'date' => $date, 'station' => trim($td[1][1])])
                            ->one();
                        if (!$rad_point):
                            echo $date . " " . trim($td[1][1]) . " " . (int)$td[6][1] . "\n";
                            $InsertArray[]=[
                                'station'=> trim($td[1][1]),
                                'lon'=>(float)$td[2][1],
                                'lat'=>(float)$td[3][1],
                                'h'=>(int)$td[4][1],
                                'value'=>(int)$td[6][1],
                                'date'=>$date,
                            ];
                        endif;
                    }

                    if(count($InsertArray)>0){
                        $columnNameArray=['station','lon','lat','h','value','date'];
                        $insertCount = Yii::$app->db->createCommand()
                            ->batchInsert(
                                "radiation_points", $columnNameArray, $InsertArray
                            )
                            ->execute();
                        print "--------------------------------\n";
                        print "Saved " . $insertCount . " radiation points\n";
                    } else {
                        print "--------------------------------\n";
                        print "Saved 0 radiation points\n";
                    }
                    Proxies::deleteAll(['ip' => $proxy[$proxy_i]["ip"], 'port' => $proxy[$proxy_i]["port"]]);
                }
            } catch (\GuzzleHttp\Exception\GuzzleException $e) {
                $load = false;
                echo "\t connection error\n";
                $proxy_i++;
                if ($proxy_i>count($proxy)-1) {
                    print "--------------------------------\n";
                    echo "end proxy list\n";
                    $load = true;
                }
            }
        }
    }

}