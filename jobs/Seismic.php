<?php
/**
 * @link http://www.sav4yk.ru/
 */

namespace app\jobs;

use app\models\Earthquakes;
use GuzzleHttp\Client;
use yii\base\BaseObject;
use yii\queue\JobInterface;

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
     * This command downloads, parse and store data to the database .
     */
    public function execute($queue)
    {
        $client = new Client();
        $res = $client->request('GET', 'https://earthquake.usgs.gov/fdsnws/event/1/query.geojson', [
            'query' => [
                'starttime' => date('Y-m-d', strtotime("-12 months")),
                'endtime' => date('Y-m-d'),
                'maxlatitude' => ((float) $this->latitude + $this->radius),
                'minlatitude' => ((float) $this->latitude - $this->radius),
                'maxlongitude' => ((float) $this->longitude + $this->radius),
                'minlongitude' => ((float) $this->longitude - $this->radius),
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
    }

}