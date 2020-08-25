<?php
/**
 * @link http://www.sav4yk.ru/
 */

namespace app\commands;

use app\models\Earthquakes;
use Yii;
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
        http://www.ceme.gsras.ru/cgi-bin/new/ccd_quake.pl?dat=2020-08-21&l=0
        $res = $client->request('GET', 'http://www.ceme.gsras.ru/cgi-bin/new/ccd_quake.p', [
            'query' => [
                'dat' => date('Y-m-d'),
                'l' => 0
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
            $InsertArray=[];
            if ($c != 1)
            for ($i = 0; $i < $c; $i++) {
                $re4 = '/<td(.*?)>(.*?)<\/td>/s';
                $c = preg_match_all($re4, $tr[$i][0], $td, PREG_SET_ORDER, 0);
                $earthquake = Earthquakes::find()->where([
                    'time_in_source' => (int)strtotime($td[1][2])])
                    ->one();
                if(!$earthquake):
                    if ($td[8][2] != '-')
                        $mag = (float) $td[8][2];
                    else
                        $mag = (float) $td[7][2];
                    $InsertArray[]=[
                        'title' => "M " . $mag . " - " . strip_tags($td[9][2]),
                        'source' => "GSRAS",
                        'mag' => $mag,
                        'time_in_source' => (int)strtotime($td[1][2]),
                        'lon' => (float) $td[3][2],
                        'lat' => (float) $td[2][2],
                    ];

                    echo date("d.m.Y H:i:s", (int)strtotime($td[1][2])) . ' ' .
                        "M " . $mag . " - " . strip_tags($td[9][2]) . ' ' .
                        (float) $td[3][2] . ' ' . (float) $td[2][2] . "\t--> added" . "\n";
                endif;

            }
            if(count($InsertArray)>0){
                $columnNameArray=['title','source','mag','time_in_source','lon','lat'];
                $insertCount = Yii::$app->db->createCommand()
                    ->batchInsert(
                        "earthquakes", $columnNameArray, $InsertArray
                    )
                    ->execute();
                print "--------------------------------\n";
                print "Saved " . $insertCount . " earthquakes\n";
            } else {
                print "--------------------------------\n";
                print "Saved 0 earthquakes\n";
            }
        }
        return ExitCode::OK;
    }



}
