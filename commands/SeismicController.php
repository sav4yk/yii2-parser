<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use GuzzleHttp\Client;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class SeismicController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex($latitude = '44.600246', $longitude = '33.530273', $radius = 5)
    {
        $client = new Client();
        $res = $client->request('GET', 'https://earthquake.usgs.gov/fdsnws/event/1/query.geojson', [
            'query' => [
                'starttime' => '2019-06-25',
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
            for ($i=0; $i<$count; $i++){
                $mil = $earthquakes->features[$i]->properties->time;
                $seconds = $mil / 1000;

                echo $earthquakes->features[$i]->properties->title . ' ' . $earthquakes->features[$i]->properties->mag .
                ' ' . date("d.m.Y H:i:s", $seconds) . ' ' . $earthquakes->features[$i]->geometry->coordinates[0]
                    . ' ' . $earthquakes->features[$i]->geometry->coordinates[1] . ' ' .
                    $earthquakes->features[$i]->geometry->coordinates[2] . ' ' . "\n";
            }
        }
        return ExitCode::OK;
    }
}
