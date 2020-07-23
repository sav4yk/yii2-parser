<?php
/**
 * @link http://www.sav4yk.ru/
 */

namespace app\jobs;

use Yii;
use app\models\News;
use GuzzleHttp\Client;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class Rssnews extends BaseObject implements JobInterface
{
    /**
     * @var string $url set request rss url
     */
    public $url;

    /**
     * This command downloads, parse and store data to the database .
     */
    public function execute($queue)
    {
        echo "\nGet news from " . $this->url . " start\n";
        $client = new Client();
        //https://tproger.ru/feed/
        //https://tproger.ru/feed/
        $res = $client->request('GET', $this->url);
        if ($res->getStatusCode()==200) {
            $data = $res->getBody(true)->getContents();
            $data = str_replace("&", "&amp;", $data);
            $feed = simplexml_load_string($data);
            $InsertArray=[];
            for($i=0;$i<count($feed->channel->item);$i++) {
                $news = News::find()->where([
                    'pubDate' => (int)strtotime($feed->channel->item[$i]->pubDate)])
                    ->one();
                if(!$news) {
                    echo date("d.m.Y H:i:s",(int)strtotime($feed->channel->item[$i]->pubDate)) . " " .
                        strip_tags($feed->channel->title) . " " . strip_tags($feed->channel->item[$i]->title) . "\n";
                    $InsertArray[]=[
                        'channel' => strip_tags($feed->channel->title),
                        'title' => strip_tags($feed->channel->item[$i]->title),
                        'link' => strip_tags($feed->channel->item[$i]->link),
                        'pubDate' => (int)strtotime($feed->channel->item[$i]->pubDate),
                        'category' => strip_tags($feed->channel->item[$i]->category),
                        'description' => strip_tags($feed->channel->item[$i]->description),
                    ];
                }
            }
            if(count($InsertArray)>0){
                $columnNameArray=['channel','title','link','pubDate','category','description'];
                $insertCount = Yii::$app->db->createCommand()
                    ->batchInsert(
                        "news", $columnNameArray, $InsertArray
                    )
                    ->execute();
                print "--------------------------------\n";
                print "Saved " . $insertCount . " news\n";
            } else {
                print "--------------------------------\n";
                print "Saved 0 news\n";
            }
        }
    }

}