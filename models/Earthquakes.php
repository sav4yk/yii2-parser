<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "earthquakes".
 *
 * @property int $id
 * @property string $title
 * @property string $source
 * @property float $mag
 * @property int $time_in_source
 * @property float $lat
 * @property float $lon
 */
class Earthquakes extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'earthquakes';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'source', 'mag', 'time_in_source', 'lat', 'lon'], 'required'],
            [['mag', 'lat', 'lon'], 'number'],
            [['time_in_source'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['source'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'source' => 'Source',
            'mag' => 'Mag',
            'time_in_source' => 'Time In Source',
            'lat' => 'Lat',
            'lon' => 'Lon',
        ];
    }
}
