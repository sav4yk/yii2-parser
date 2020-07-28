<?php
/**
 * @link http://www.sav4yk.ru/
 */

namespace app\commands;

use app\models\News;
use GuzzleHttp\Client;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;


/**
 * This command downloads data from news sites
 *
 * The received data is parsed and stored in the database.
 *
 * @author Sav4yk <mail@sav4yk.ru>
 */
class NewsController extends Controller
{
    /**
     * This command downloads, parse and store data to the database .
     * @return int Exit code
     */
    public function actionIndex()
    {
        $client = new Client();
        $res = $client->request('GET', 'https://tproger.ru/feed/');
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
        return ExitCode::OK;
    }
}
