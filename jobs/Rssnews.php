<?php
/**
 * @link http://www.sav4yk.ru/
 */

namespace app\jobs;

use GuzzleHttp\Exception\GuzzleException;
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
     * @throws GuzzleException
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
            $InsertCategoryArray=[];
            $news_id = 0;
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
                        'description' => strip_tags($feed->channel->item[$i]->description),
                    ];
                    $news_id++;
                    foreach ($feed->channel->item[$i]->category as $category) {
                        $InsertCategoryArray[]=[
                            'news_id' => $news_id,
                            'category' => strip_tags($category),
                        ];
                    }
                }
            }
            if(count($InsertArray)>0){
                $columnNameArray=['channel','title','link','pubDate','description'];
                $insertCount = Yii::$app->db->createCommand()
                    ->batchInsert(
                        "news", $columnNameArray, $InsertArray
                    )
                    ->execute();
                print "--------------------------------\n";
                print "Saved " . $insertCount . " news\n";
                $id = Yii::$app->db->createCommand('SELECT max(id) FROM news')
                    ->queryScalar();
                print "news_id=" .$news_id . "\n";
                foreach ($InsertCategoryArray as &$cat) {
                    $cat['news_id'] = $id - $insertCount + $cat['news_id'];
                }
                $insertCount = Yii::$app->db->createCommand()
                    ->batchInsert(
                        "category", ['news_id','category'], $InsertCategoryArray
                    )
                    ->execute();
                print "Saved " . $insertCount . " news categories\n";
            } else {
                print "--------------------------------\n";
                print "Saved 0 news\n";
            }
        }
    }

}
