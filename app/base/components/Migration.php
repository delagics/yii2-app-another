<?php

namespace base\components;

/**
 * Class Migration
 *
 * @package base\components
 */
class Migration extends \yii\db\Migration
{
    /**
     * @var string tables encoding name.
     */
    public $encoding = 'utf8mb4';
    /**
     * @var string tables collation name.
     */
    public $collation = 'utf8mb4_unicode_ci';
    /**
     * @var string
     */
    public $engine = 'InnoDB';
    /**
     * @var string options applied to each table.
     */
    public $tableOptions;

    /**
     * Initializes the migration.
     * This method will set [[db]] to be the 'db' application component, if it is `null`.
     */
    public function init()
    {
        parent::init();

        if ($this->db->driverName === 'mysql') {

            switch ($this->encoding) {
                case 'utf8mb4':
                    $size = 191;
                    break;
                default:
                    $size = 255;
            }

            /* @var string MAX_CHAR_LENGTH the number of maximum allowed varchar size. */
            defined('MAX_CHAR_LENGTH') or define('MAX_CHAR_LENGTH', $size);

            $this->tableOptions = 'CHARACTER SET ';
            $this->tableOptions.= $this->encoding;
            $this->tableOptions.= ' COLLATE ';
            $this->tableOptions.= $this->collation;
            $this->tableOptions.= ' ENGINE=';
            $this->tableOptions.= $this->engine;
        }
    }
}
