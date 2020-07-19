<?php
/**
 * @link http://www.sav4yk.ru/
 */

namespace app\commands;

use app\models\RadiationPoints;
use yii\console\Controller;
use yii\console\ExitCode;
use GuzzleHttp\Client;

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
     * @param string $latitude user latitude coordinate.
     * @param string $longitude user longitude coordinate.
     * @param int $radius control radius.
     * @return int Exit code
     */
    public function actionIndex($latitude = '44.600246', $longitude = '33.530273', $radius = 5)
    {

        $client = new Client();
        $res = $client->request('GET', 'http://www.feerc.obninsk.org/remac/reqx.htm', [
            'query' => [
                'p1' => '0',
                'p2' => '1',                                    //2,1
                'p3' => '1',                                    //type(1)
                'p4' => '0',
                'p5' => date('d'),                       //day
                'p6' => ((int)date('m')-1),              //month(-1)
                'p7' => date('Y'),                       //year
                'p8' => '2',
                'p9' => '1',
                'p10' => ((float) $longitude - $radius),        //left longitude
                'p11' => ((float) $longitude + $radius),        //right longitude
                'p12' => ((float) $latitude + $radius),         //north latitude
                'p13' =>((float) $latitude - $radius),          //south latitude
                ]
        ]);
        if ($res->getStatusCode()==200) {
            $rad_points = $res->getBody()->getContents();
            $rad_points = iconv("windows-1251","utf-8",$rad_points);

            $re1 = '/<table border=1 cellpadding=3 cellspacing=3>(.*?)<\/table>/s';
            $c = preg_match_all($re1, $rad_points, $table, PREG_SET_ORDER, 0);

            $re2 = '/<tr>(.*?)<\/tr>/s';
           $c = preg_match_all($re2, $table[0][0], $tr, PREG_SET_ORDER, 0);

           $date = date('Y-m-d');

            for ($i=1;$i<=$c;$i++) {
               $re3 = '/<td>(.*?)<\/td>/s';
               $c = preg_match_all($re3, $tr[$i][0], $td, PREG_SET_ORDER, 0);

                $rad_point = RadiationPoints::find()->where([
                    'date' => $date, 'station' =>trim($td[1][1])])
                    ->one();
                if(!$rad_point):
                    $rad_point = new RadiationPoints();
                endif;
                $rad_point->station = trim($td[1][1]);
                $rad_point->lon = (float)$td[2][1];
                $rad_point->lat =(float)$td[3][1];
                $rad_point->h = (int)$td[4][1];
                $rad_point->value = (int)$td[6][1];
                $rad_point->date = $date;
                $rad_point->save();
            }
        }
        return ExitCode::OK;
    }
}
