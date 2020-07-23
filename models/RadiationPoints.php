<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "radiatopn_points".
 *
 * @property int $id
 * @property string $station
 * @property string $date
 * @property float $lat
 * @property float $lon
 * @property int $h
 * @property int $value
 */
class RadiationPoints extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'radiation_points';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['station', 'date', 'lat', 'lon', 'h', 'value'], 'required'],
            [['date'], 'safe'],
            [['lat', 'lon'], 'number'],
            [['h', 'value'], 'integer'],
            [['station'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'station' => 'Station',
            'date' => 'Date',
            'lat' => 'Lat',
            'lon' => 'Lon',
            'h' => 'H',
            'value' => 'Value',
        ];
    }
}
