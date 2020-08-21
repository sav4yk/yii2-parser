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
 * This command downloads data from earthquake.usgs.gov.
 *
 * The received data is parsed and stored in the database.
 *
 * @author Sav4yk <mail@sav4yk.ru>
 */
class SeismicUsgsController extends Controller
{
    /**
     * This command downloads, parse and store data to the database .
     * @return int Exit code
     */
    public function actionIndex()
    {
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
        return ExitCode::OK;
    }

}
