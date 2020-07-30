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
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['id']);
        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['valuteID', 'numCode', 'сharCodes', 'name', 'value', 'date'], 'required'],
            [['value'], 'number'],
            [['date'], 'integer'],
            [['valuteID'], 'string', 'max' => 10],
            [['numCode', 'сharCodes'], 'string', 'max' => 4],
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
            'сharCodes' => 'Сhar Code',
            'name' => 'Name',
            'value' => 'Value',
            'date' => 'Date',
        ];
    }
}
