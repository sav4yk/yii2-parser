<?php
/**
 * @link http://www.sav4yk.ru/
 */

namespace app\jobs;

use app\models\News;
use GuzzleHttp\Client;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * This command downloads data from news sites
 *
 * The received data is parsed and stored in the database.
 *
 * @author Sav4yk <mail@sav4yk.ru>
 */
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
        $client = new Client();
        $res = $client->request('GET', $this->url);
        if ($res->getStatusCode()==200) {
            $data = $res->getBody(true)->getContents();
            $data = str_replace("&", "&amp;", $data);
            $feed = simplexml_load_string($data);
            for($i=0;$i<count($feed->channel->item);$i++) {
                $news = News::find()->where([
                    'pubDate' => (int)strtotime($feed->channel->item[$i]->pubDate)])
                    ->one();
                if(!$news) {
                    $news = new News();
                }  else {
                    break;
                }
                $news->channel = strip_tags($feed->channel->title);
                $news->title = strip_tags($feed->channel->item[$i]->title);
                $news->link = strip_tags($feed->channel->item[$i]->link);
                $news->pubDate = (int)strtotime($feed->channel->item[$i]->pubDate);
                $news->category = strip_tags($feed->channel->item[$i]->category);
                $news->description = strip_tags($feed->channel->item[$i]->description);
                $news->save();
            }
        }
    }

}