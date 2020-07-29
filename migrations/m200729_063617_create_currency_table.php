<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%currency}}`.
 */
class m200729_063617_create_currency_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%currency}}', [
            'id' => $this->primaryKey(),
            'valuteID' => $this->string(10)->notNull(),
            'numCode' => $this->string(4)->notNull(),
            'сharCode' => $this->string(4)->notNull(),
            'name' => $this->string(255)->notNull(),
            'value' => $this->float()->notNull(),
            'date' => $this->integer(11)->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%currency}}');
    }
}
