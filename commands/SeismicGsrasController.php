<?php
/**
 * @link http://www.sav4yk.ru/
 */

namespace app\commands;

use app\models\Earthquakes;
use yii\console\Controller;
use yii\console\ExitCode;
use GuzzleHttp\Client;

/**
 * This command downloads data from ceme.gsras.ru.
 *
 * The received data is parsed and stored in the database.
 *
 * @author Sav4yk <mail@sav4yk.ru>
 */
class SeismicGsrasController extends Controller
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
        $res = $client->request('GET', 'http://www.ceme.gsras.ru/cgi-bin/new/mapCustom.pl', [
            'query' => [
                'lat' => ((float) $latitude),
                'lon' => ((float) $longitude),
                'rad' => $radius*100,
                'num' => '10'
                ]
        ]);
        if ($res->getStatusCode()==200) {
            $earthquakes = $res->getBody();
            $earthquakes = iconv("windows-1251", "utf-8", $earthquakes);
            $re1 = '/<table align=center border=0 cellspacing=2 cellpadding=2 id="tableQuakes" class="sortable zstripes">(.*?)<\/table>/s';
            $c = preg_match_all($re1, $earthquakes, $table, PREG_SET_ORDER, 0);
            $re2 = '/<tbody>(.*?)<\/tbody>/s';
            $c = preg_match_all($re2, $table[0][0], $table, PREG_SET_ORDER, 0);


            $re3 = '/<tr(.*?)>(.*?)<\/tr>/s';
            $c = preg_match_all($re3, $table[0][0], $tr, PREG_SET_ORDER, 0);
            if ($c != 1)
            for ($i = 0; $i < $c; $i++) {
                $re4 = '/<td(.*?)>(.*?)<\/td>/s';
                $c = preg_match_all($re4, $tr[$i][0], $td, PREG_SET_ORDER, 0);
                $added = '';
                $earthquake = Earthquakes::find()->where([
                    'time_in_source' => (int)strtotime($td[1][2])])
                    ->one();
                if(!$earthquake):
                    $earthquake = new Earthquakes();
                    $added = "\t--> added";
                endif;
                if ($td[8][2] != '-')
                    $mag = (float) $td[8][2];
                else
                    $mag = (float) $td[7][2];
                $earthquake->title = "M " . $mag . " - " . strip_tags($td[9][2]);
                $earthquake->source = "GSRAS";
                $earthquake->mag = $mag;
                $earthquake->time_in_source = (int)strtotime($td[1][2]);
                $earthquake->lon = (float) $td[3][2];
                $earthquake->lat = (float) $td[2][2];
                $earthquake->save();

                echo $earthquake->title . ' ' . $earthquake->mag .
                    ' ' . date("d.m.Y H:i:s", $earthquake->time_in_source) . ' ' . $earthquake->lon
                    . ' ' . $earthquake->lat . $added . "\n";
            }

        }
        return ExitCode::OK;
    }



}
