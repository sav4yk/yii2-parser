<?php
/**
 * @link http://www.sav4yk.ru/
 */

namespace app\commands;

use app\models\Earthquakes;
use app\models\News;
use app\models\Proxies;
use GuzzleHttp\Client;
use JJG\Ping;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;


/**
 * This command downloads proxy list.
 *
 * Received proxies are used in the project.
 *
 * @author Sav4yk <mail@sav4yk.ru>
 */
class ProxyController extends Controller
{
    /**
     * This command downloads, parse and store proxy list to the database .
     * @return int Exit code
     */
    public function actionIndex()
    {
        $client = new Client();
        $res = $client->request('GET', 'https://www.xroxy.com/proxyrss.xml');
        if ($res->getStatusCode()==200) {
            $data = $res->getBody(true)->getContents();
            $data = str_replace(':','_',$data);
            $feed = simplexml_load_string($data);
            //var_dump($feed);
            $ping = new Ping("127.0.0.1");
            $ttl = 32;
            $timeout = 1;
            Proxies::deleteAll();
            for($n=0;$n<count($feed->channel->item);$n++) {
                for ($i = 0; $i < count($feed->channel->item[$n]->prx_proxy); $i++) {
                    echo $feed->channel->item[$n]->prx_proxy[$i]->prx_ip . "\n";
                    $host = $feed->channel->item[$n]->prx_proxy[$i]->prx_ip;
                    $ping = new Ping($host, $ttl, $timeout);
                    $latency = $ping->ping();
                    if ($latency !== false) {
                        print "Latency is " . $latency . " ms - added\n";
                        $InsertArray[]=[
                            'ip'=>strip_tags($host),
                            'port'=>strip_tags($feed->channel->item[$n]->prx_proxy[$i]->prx_port),
                            'type'=>strip_tags($feed->channel->item[$n]->prx_proxy[$i]->prx_type),
                            'isSSL'=>(int)$feed->channel->item[$n]->prx_proxy[$i]->prx_ssl,
                            'check_timestamp'=>(int)$feed->channel->item[$n]->prx_proxy[$i]->prx_check_timestamp,
                            'country_code'=>strip_tags($feed->channel->item[$n]->prx_proxy[$i]->prx_country_code),
                            'latency'=>(int)$feed->channel->item[$n]->prx_proxy[$i]->prx_latency,
                            'reliability'=>(int)$feed->channel->item[$n]->prx_proxy[$i]->prx_reliability,
                        ];

                    }
                    else {
                        print "Host could not be reached - passed.\n";
                    }
                }
            }

            if(count($InsertArray)>0){
                $columnNameArray=['ip','port','type','isSSL','check_timestamp','country_code','latency','reliability'];
                // below line insert all your record and return number of rows inserted
                $insertCount = Yii::$app->db->createCommand()
                    ->batchInsert(
                        "proxies", $columnNameArray, $InsertArray
                    )
                    ->execute();
                print "--------------------------------\n";
                print "Saved " . $insertCount . " proxies\n";
            } else {
                print "--------------------------------\n";
                print "Saved 0 proxies\n";
            }

        }
        return ExitCode::OK;
    }
}
