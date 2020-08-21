<?php
/**
 * @link http://www.sav4yk.ru/
 */

namespace app\jobs;

use Yii;
use GuzzleHttp\Client;
use yii\db\Exception;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use app\models\Earthquakes;
use GuzzleHttp\Exception\GuzzleException;

class Seismic extends BaseObject implements JobInterface
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
     * This command run two seismic data-load functions.
     */
    public function execute($queue)
    {
        $this->Usgs($this->latitude,$this->longitude,$this->radius);
        $this->Gsras($this->latitude,$this->longitude,$this->radius);
    }

    /**
     * This command downloads, parse and store seismic data from earthquake.usgs.gov to the database.
     * @throws GuzzleException
     * @throws Exception
     */
    public function Usgs()
    {
        echo "\nUSGS start\n";
        $client = new Client();
        echo "now = " . date('Y-m-d H:i:s') . "\n";
        $res = $client->request('GET', 'https://earthquake.usgs.gov/fdsnws/event/1/query.geojson', [
            'query' => [
                'starttime' => date('Y-m-d H:i:s', strtotime('-6 hour', time())),
                'endtime' => date('Y-m-d H:i:s',strtotime('-4 hour', time())),
                'minmagnitude' => '0',
                'orderby' => 'time',
            ]
        ]);
        echo "https://earthquake.usgs.gov/fdsnws/event/1/query.geojson?starttime=" .
            date('Y-m-d H:i:s', strtotime('-6 hour', time())) .
            '&endtime=' . date('Y-m-d H:i:s',strtotime('-4 hour', time())) .
            "&minmagnitude=0&orderby=time\n";
        if ($res->getStatusCode()==200) {
            $earthquakes = json_decode($res->getBody());
            $count = $earthquakes->metadata->count;
            $InsertArray=[];
            for ($i=0; $i<$count; $i++){
                $mil = $earthquakes->features[$i]->properties->time;
                $seconds = $mil / 1000;
                $earthquake = Earthquakes::find()->where([
                    'time_in_source' => (int)$seconds])
                    ->one();
                if(!$earthquake):
                    $InsertArray[]=[
                        'title' => $earthquakes->features[$i]->properties->title,
                        'source' => "USGS",
                        'mag' => (float) $earthquakes->features[$i]->properties->mag,
                        'time_in_source' => (int)$seconds,
                        'lon' => (float) $earthquakes->features[$i]->geometry->coordinates[0],
                        'lat' => (float) $earthquakes->features[$i]->geometry->coordinates[1],
                    ];

                    echo date("d.m.Y H:i:s", $seconds) . ' ' . $earthquakes->features[$i]->properties->title . ' ' .
                        $earthquakes->features[$i]->geometry->coordinates[0] . ' ' .
                        $earthquakes->features[$i]->geometry->coordinates[1] . "\t--> added" . "\n";
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
    }

    /**
     * This command downloads, parse and store seismic data from ceme.gsras.ru to the database.
     * @param string $latitude user latitude coordinate.
     * @param string $longitude user longitude coordinate.
     * @param int $radius control radius.
     * @throws GuzzleException
     * @throws Exception
     */
    public function Gsras($latitude = '44.600246', $longitude = '33.530273', $radius = 3)
    {
        echo "\nGSRAS start\n";
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
                echo "--------------------------------\n";
                echo "Saved " . $insertCount . " new earthquakes\n";
            } else {
                echo "--------------------------------\n";
                echo "Saved 0 earthquakes\n";
            }
        }
    }
}