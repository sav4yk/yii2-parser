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
        Proxies::deleteAll();
        echo "\nGet proxy from Fate0 start\n";
        $this->proxyFate0();
        echo "\nGet proxy from Socks_proxy start\n";
        $this->proxySocks_proxy();
        echo "\nGet proxy from Xproxy start\n";
        $this->proxyXproxy();
        echo "\nGet proxy from Clarketm start\n";
        $this->proxyTXT('https://raw.githubusercontent.com/clarketm/proxy-list/master/proxy-list-raw.txt');
        echo "\nGet proxy from rootjazz start\n";
        $this->proxyTXT('http://rootjazz.com/proxies/proxies.txt');
        return ExitCode::OK;
    }

    /**
     * Get proxies from https://www.xroxy.com/proxyrss.xml
     */
    public function proxyXproxy()
    {
        $client = new Client();
        $res = $client->request('GET', 'https://www.xroxy.com/proxyrss.xml');
        if ($res->getStatusCode()==200) {
            $data = $res->getBody(true)->getContents();
            $data = str_replace(':','_',$data);
            $feed = simplexml_load_string($data);
            $ping = new Ping("127.0.0.1");
            $ttl = 32;
            $timeout = 1;

            $InsertArray = [];
            for($n=0;$n<count($feed->channel->item);$n++) {
                for ($i = 0; $i < count($feed->channel->item[$n]->prx_proxy); $i++) {
                    echo $feed->channel->item[$n]->prx_proxy[$i]->prx_ip . "\t";
                    $host = $feed->channel->item[$n]->prx_proxy[$i]->prx_ip;
                    $ping = new Ping($host, $ttl, $timeout);
                    $latency = $ping->ping();
                    if ($latency !== false) {
                        print "Latency is " . $latency . " ms - added\n";
                        $found = array_search(strip_tags($host), array_column($InsertArray, 'ip'));
                        if ($found)
                            echo "\n------------" . strip_tags($host). " - " . $found  . "\n";
                        else
                            $InsertArray[]=[
                                'ip'=>strip_tags($host),
                                'port'=>strip_tags($feed->channel->item[$n]->prx_proxy[$i]->prx_port),
                                'type'=>strip_tags($feed->channel->item[$n]->prx_proxy[$i]->prx_type),
                                'isSSL'=>(int)$feed->channel->item[$n]->prx_proxy[$i]->prx_ssl,
                                'check_timestamp'=>(int)$feed->channel->item[$n]->prx_proxy[$i]->prx_check_timestamp,
                                'country_code'=>strip_tags($feed->channel->item[$n]->prx_proxy[$i]->prx_country_code),
                                'latency'=> $latency,
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
    }

    /**
     * Get proxies from txt files
     */
    public function proxyTXT($path = '')
    {
        if ($path=='') {
            return '';
        }
        $client = new Client();
        $res = $client->request('GET', $path);
        if ($res->getStatusCode()==200) {
            $data = $res->getBody(true)->getContents();
            $ping = new Ping("127.0.0.1");
            $ttl = 32;
            $timeout = 1;
            $InsertArray = [];
            foreach(preg_split("/((\r?\n)|(\r\n?))/", $data) as $line){
                echo $line . "\t";
                $host = strstr($line, ':', true);
                $port = strstr($line, ':');
                $ping = new Ping($host, $ttl, $timeout);
                $latency = $ping->ping();
                if ($latency !== false) {
                    print "Latency is " . $latency . " ms - added\n";
                    $found = array_search(strip_tags($host), array_column($InsertArray, 'ip'));
                    if ($found)
                        echo "\n------------" . strip_tags($host). " - " . $found  . "\n";
                    else
                        $InsertArray[]=[
                            'ip'=>strip_tags($host),
                            'port'=>strip_tags($port),
                            'type'=>'',
                            'isSSL'=>0,
                            'check_timestamp'=>0,
                            'country_code'=>0,
                            'latency'=> $latency,
                            'reliability'=>0,
                        ];
                }
                else {
                    print "Host could not be reached - passed.\n";
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
    }

    /**
     * Get proxies from https://socks-proxy.net
     */
    public function proxySocks_proxy()
    {
        $client = new Client();
        $res = $client->request('GET', 'https://socks-proxy.net/');
        if ($res->getStatusCode()==200) {
            $data = $res->getBody(true)->getContents();
            preg_match_all('#<textarea class="form-control" readonly="readonly" (.+?)>(.+?)</textarea>#is', $data, $arr);
            $data = $arr[2][0];
            $ping = new Ping("127.0.0.1");
            $ttl = 32;
            $timeout = 1;
            $InsertArray = [];
            $counter = 0;
            foreach(preg_split("/((\r?\n)|(\r\n?))/", $data) as $line){
               echo $line . "\t";
                if ($counter++ <= 2) {
                    echo "\n";
                    continue;
                }
                $host = strstr($line, ':', true);
                $port = substr(strstr($line, ':'),1);
                $ping = new Ping($host, $ttl, $timeout);
                $latency = $ping->ping();
                if ($latency !== false) {
                    print "Latency is " . $latency . " ms - added\n";
                    $found = array_search(strip_tags($host), array_column($InsertArray, 'ip'));
                    if ($found)
                        echo "\n------------" . strip_tags($host). " - " . $found  . "\n";
                    else
                        $InsertArray[]=[
                            'ip'=>strip_tags($host),
                            'port'=>strip_tags($port),
                            'type'=>'',
                            'isSSL'=>0,
                            'check_timestamp'=>0,
                            'country_code'=>0,
                            'latency'=> $latency,
                            'reliability'=>0,
                        ];
                }
                else {
                    print "Host could not be reached - passed.\n";
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
    }

    /**
     * Get proxies from https://raw.githubusercontent.com/fate0/proxylist/master/proxy.list
     */
    public function proxyFate0()
    {
        $client = new Client();
        $res = $client->request('GET', 'https://raw.githubusercontent.com/fate0/proxylist/master/proxy.list');
        if ($res->getStatusCode()==200) {
            $data = $res->getBody(true)->getContents();
            $ping = new Ping("127.0.0.1");
            $ttl = 32;
            $timeout = 1;
            $InsertArray = [];

            foreach(preg_split("/((\r?\n)|(\r\n?))/", $data) as $line) {
                $feed = json_decode($line);
                if ($feed==NULL) {
                    continue;
                }
                $host = $feed->host;
                $port = $feed->port;
                echo $host . ":" . $port.  "\t";
                $ping = new Ping($host, $ttl, $timeout);
                $latency = $ping->ping();
                if ($latency !== false) {
                    print "Latency is " . $latency . " ms - added\n";
                    $found = array_search(strip_tags($host), array_column($InsertArray, 'ip'));
                    if ($found)
                        echo "\n------------" . strip_tags($host). " - " . $found  . "\n";
                    else
                        $InsertArray[]=[
                            'ip'=>strip_tags($host),
                            'port'=>strip_tags($port),
                            'type'=>strip_tags($feed->type),
                            'isSSL'=>0,
                            'check_timestamp'=>0,
                            'country_code'=>strip_tags($feed->country),
                            'latency'=> $latency,
                            'reliability'=>0,
                        ];
                }
                else {
                    print "Host could not be reached - passed.\n";
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
    }

}
