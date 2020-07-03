<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\Earthquakes;
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
     * @param string $latitude user latitude coordinate.
     * @param string $longitude user longitude coordinate.
     * @param int $radius control radius.
     * @return int Exit code
     */
    public function actionIndex($latitude = '44.600246', $longitude = '33.530273', $radius = 5)
    {
        $client = new Client();
        $res = $client->request('GET', 'https://earthquake.usgs.gov/fdsnws/event/1/query.geojson', [
            'query' => [
                'starttime' => date('Y-m-d', strtotime("-12 months")),
                'endtime' => date('Y-m-d'),
                'maxlatitude' => ((float) $latitude + $radius),
                'minlatitude' => ((float) $latitude - $radius),
                'maxlongitude' => ((float) $longitude + $radius),
                'minlongitude' => ((float) $longitude - $radius),
                'minmagnitude' => '0',
                'orderby' => 'time',
                ]
        ]);
        if ($res->getStatusCode()==200) {
            $earthquakes = json_decode($res->getBody());
            $count = $earthquakes->metadata->count;
            if ($count>20) $count = 20;
            for ($i=0; $i<$count; $i++){
                $mil = $earthquakes->features[$i]->properties->time;
                $seconds = $mil / 1000;

                echo $earthquakes->features[$i]->properties->title . ' ' . $earthquakes->features[$i]->properties->mag .
                ' ' . date("d.m.Y H:i:s", $seconds) . ' ' . $earthquakes->features[$i]->geometry->coordinates[0]
                    . ' ' . $earthquakes->features[$i]->geometry->coordinates[1] . ' ' . "\n";

                $earthquake = Earthquakes::find()->where([
                    'time_in_source' => (int)$seconds])
                    ->one();
                if(!$earthquake):
                    $earthquake = new Earthquakes();
                endif;
                $earthquake->title = $earthquakes->features[$i]->properties->title;
                $earthquake->mag = (float) $earthquakes->features[$i]->properties->mag;
                $earthquake->time_in_source = (int)$seconds;
                $earthquake->lon = (float) $earthquakes->features[$i]->geometry->coordinates[0];
                $earthquake->lat = (float) $earthquakes->features[$i]->geometry->coordinates[1];
                $earthquake->save();

            }
        }
        return ExitCode::OK;
    }



}
