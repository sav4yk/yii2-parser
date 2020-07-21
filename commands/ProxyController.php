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

                        $proxy = new Proxies();
                        $proxy->ip = strip_tags($host);
                        $proxy->port = strip_tags($feed->channel->item[$n]->prx_proxy[$i]->prx_port);
                        $proxy->type = strip_tags($feed->channel->item[$n]->prx_proxy[$i]->prx_type);
                        $proxy->isSSL = (int)$feed->channel->item[$n]->prx_proxy[$i]->prx_ssl;
                        $proxy->check_timestamp = (int)$feed->channel->item[$n]->prx_proxy[$i]->prx_check_timestamp;
                        $proxy->country_code = strip_tags($feed->channel->item[$n]->prx_proxy[$i]->prx_country_code);
                        $proxy->latency = (int)$feed->channel->item[$n]->prx_proxy[$i]->prx_latency;
                        $proxy->reliability = (int)$feed->channel->item[$n]->prx_proxy[$i]->prx_reliability;
                        $proxy->save();
                    }
                    else {
                        print "Host could not be reached - passed.\n";
                    }
                }
            }
        }
        return ExitCode::OK;
    }
}
