<?php
/**
 * @link http://www.sav4yk.ru/
 */

namespace app\commands;

use app\models\Proxies;
use app\models\RadiationPoints;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * This command downloads data from feerc.obninsk.org.
 *
 * The received data is parsed and stored in the database.
 *
 * @author Sav4yk <mail@sav4yk.ru>
 */
class RadiationObninskController extends Controller
{

    /**
     * This command downloads, parse and store data to the database .
     * @return int Exit code
     * @throws \yii\db\Exception
     */
    public function actionIndex()
    {
        $client = new Client();
        $load = false;
        $proxy = Proxies::find()->orderBy('latency')->asArray()->all();
        $proxy_i = 0;
        while ($load == false) {
            try {
                $load = true;
//                echo "http://www.feerc.obninsk.org/remac/reqx.htm?p1=0&p2=1&p3=1&p4=0&p5=" .
//                    ((int)date('d') - 20) . "&p6=" . ((int)date('m') - 1) .
//                    "&p7=" . date('Y') . "&p9=1\n";
                echo $proxy[$proxy_i]["ip"] . ":" . $proxy[$proxy_i]["port"];

                $day = ((int)date('d'));
                $month = (int)date('m');
                $year = date('Y');
                $res = $client->request('GET', 'http://www.feerc.obninsk.org/remac/reqx.htm', [
                    'proxy' => $proxy[$proxy_i]["ip"] . ":" . $proxy[$proxy_i]["port"],
                    'query' => [
                        'p1' => '0',
                        'p2' => '1',                                    //21 -1
                        'p3' => '1',                                    //type(1)
                        'p4' => '0',
                        'p5' => $day,                       //day
                        'p6' => ($month - 1),              //month(-1)
                        'p7' => $year,                       //year
//                        'p8' => '2',
                        'p9' => '1',
//                        'p10' => ((float)$longitude - $radius),        //left longitude
//                        'p11' => ((float)$longitude + $radius),        //right longitude
//                        'p12' => ((float)$latitude + $radius),         //north latitude
//                        'p13' => ((float)$latitude - $radius),          //south latitude
                    ],
                    'timeout' => 10,
                    'read_timeout' => 10,
                    'http_errors' => true
                ]);

                if ($res->getStatusCode() == 200) {
                    $InsertArray = [];
                    echo "\t data loaded,";
                    $rad_points = $res->getBody()->getContents();
                    $rad_points = iconv("windows-1251", "utf-8", $rad_points);
                    if (str_contains($rad_points, "Извините")) {
                        $load = true;
                        echo "\t no data on server\n";
                    }

                    $re_date = '/<center>\r\n<P>\r\n<P>(.*?)<P>\r\n<\/center>/s';
                    $c = preg_match_all($re_date, $rad_points, $date, PREG_SET_ORDER, 0);
                    echo "Дата: " . $date[0][1] . "\n";

                    $re1 = '/<table border=1 cellpadding=3 cellspacing=3>(.*?)<\/table>/s';
                    $c = preg_match_all($re1, $rad_points, $table, PREG_SET_ORDER, 0);

                    $re2 = '/<tr>(.*?)<\/tr>/s';
                    $count = preg_match_all($re2, $table[0][0], $tr, PREG_SET_ORDER, 0);

                    $date = DateTime::createFromFormat('d-m-Y', $day . '-' . $month . '-' . $year)->format('Y-m-d');
                    echo "\t proxy used\n";
                    echo "c=" . $c . "\n";

                    for ($i = 1; $i < $count; $i++) {
                        $re3 = '/<td>(.*?)<\/td>/s';
                        $c = preg_match_all($re3, $tr[$i][0], $td, PREG_SET_ORDER, 0);

                        $rad_point = RadiationPoints::find()->where([
                            'date' => $date,
                            'station' => trim($td[1][1])
                        ])
                            ->one();
                        if (!$rad_point):
                            if ($this->is_in_array($InsertArray, 'station', trim($td[1][1])) == 'yes'):
                                echo 'повтор';
                            else:
                                $InsertArray[] = [
                                    'station' => trim($td[1][1]),
                                    'lon' => (float)$td[2][1],
                                    'lat' => (float)$td[3][1],
                                    'h' => (int)$td[4][1],
                                    'value' => (int)$td[6][1],
                                    'date' => $date,
                                ];
                            endif;
                            echo $date . " " . trim($td[1][1]) . " " . (int)$td[6][1] . "\n";
                        endif;

                    }

                    if (count($InsertArray) > 0) {
                        $columnNameArray = ['station', 'lon', 'lat', 'h', 'value', 'date'];
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
                }
            } catch (GuzzleException $e) {
                $load = false;
                echo "\t connection error\n";
                Proxies::deleteAll(['ip' => $proxy[$proxy_i]["ip"], 'port' => $proxy[$proxy_i]["port"]]);
                $proxy_i++;
                if ($proxy_i > count($proxy) - 1) {
                    print "--------------------------------\n";
                    echo "end proxy list\n";
                    $load = true;
                }
            }
        }
        return ExitCode::OK;
    }

    /**
     * Check exists variable in associative array
     *
     * @param $array
     * @param $key
     * @param $key_value
     * @return string
     */
    function is_in_array($array, $key, $key_value)
    {
        $within_array = 'no';
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $within_array = $this->is_in_array($v, $key, $key_value);
                if ($within_array == 'yes') {
                    break;
                }
            } else {
                if ($v == $key_value && $k == $key) {
                    $within_array = 'yes';
                    break;
                }
            }
        }
        return $within_array;
    }
}
