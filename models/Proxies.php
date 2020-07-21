<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "proxies".
 *
 * @property int $id
 * @property string $ip
 * @property string $port
 * @property string $type
 * @property int $isSSL
 * @property int $check_timestamp
 * @property string $country_code
 * @property int $latency
 * @property int $reliability
 */
class Proxies extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'proxies';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ip', 'port', 'type', 'isSSL', 'check_timestamp', 'country_code', 'latency', 'reliability'], 'required'],
            [['isSSL', 'check_timestamp', 'latency', 'reliability'], 'integer'],
            [['ip', 'port'], 'string', 'max' => 20],
            [['type'], 'string', 'max' => 10],
            [['country_code'], 'string', 'max' => 5],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ip' => 'Ip',
            'port' => 'Port',
            'type' => 'Type',
            'isSSL' => 'Is Ssl',
            'check_timestamp' => 'Check Timestamp',
            'country_code' => 'Country Code',
            'latency' => 'Latency',
            'reliability' => 'Reliability',
        ];
    }
}
