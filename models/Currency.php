<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "currency".
 *
 * @property int $id
 * @property string $valuteID
 * @property string $numCode
 * @property string $сharCode
 * @property string $name
 * @property float $value
 * @property int $date
 */
class Currency extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'currency';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['valuteID', 'numCode', 'сharCode', 'name', 'value', 'date'], 'required'],
            [['value'], 'number'],
            [['date'], 'integer'],
            [['valuteID'], 'string', 'max' => 10],
            [['numCode', 'сharCode'], 'string', 'max' => 4],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'valuteID' => 'Valute ID',
            'numCode' => 'Num Code',
            'сharCode' => 'Сhar Code',
            'name' => 'Name',
            'value' => 'Value',
            'date' => 'Date',
        ];
    }
}
