<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "news".
 *
 * @property int $id
 * @property string $channel
 * @property string $title
 * @property string $link
 * @property int $pubDate
 * @property string $category
 * @property string $description
 */
class News extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'news';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['channel', 'title', 'link', 'pubDate', 'category', 'description'], 'required'],
            [['pubDate'], 'integer'],
            [['description','title'], 'string'],
            [['channel', 'link', 'category'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'channel' => 'Channel',
            'title' => 'Title',
            'link' => 'Link',
            'pubDate' => 'Pub Date',
            'category' => 'Category',
            'description' => 'Description',
        ];
    }

    public function getDateText()
    {
        return date('d.m.Y', $this->pubDate);
    }

}
