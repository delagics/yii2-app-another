<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Class AppController
 *
 * @package console\controllers
 *
 * @property-read \yii\db\Connection $db
 */
class AppController extends Controller
{
    /**
     * @var string the ID of the action that is used when the action ID is not specified
     * in the request. Defaults to 'set-up'.
     */
    public $defaultAction = 'set-up';

    /**
     * @var \yii\db\Connection application db component.
     */
    private $_db;

    /**
     * Prepare application by running depending modules migrations and other stuff.
     * Deals basically with database but can be also cache, assets and so on.
     *
     * @return integer the status of the action execution. 0 means normal, other values mean abnormal.
     */
    public function actionSetUp()
    {
        /***************************************************************************************************************
         * INPUT AREA
         **************************************************************************************************************/

        $email = $this->prompt('Enter global admin user email:', [
            'default' => ArrayHelper::getValue(Yii::$app->params, 'adminEmail', 'admin@example.com')
        ]);
        $username = $this->prompt('Enter global admin user name:', [
            'default' => ArrayHelper::getValue(Yii::$app->getModule('user')->admins, 0, 'admin')
        ]);
        $password = $this->prompt('Enter global admin user password:', ['default' => '123456']);

        $this->askForInteraction();

        /***************************************************************************************************************
         * Applying `dektrium/yii2-user` and `dektrium/yii2-rbac` migrations, and creating first user.
         **************************************************************************************************************/

        Yii::$app->runAction('migrate/up', [
            'migrationPath' => '@vendor/dektrium/yii2-user/migrations',
            'interactive' => $this->interactive,
        ]);

        Yii::$app->runAction('migrate/up', [
            'migrationPath' => '@yii/rbac/migrations',
            'interactive' => $this->interactive,
        ]);

        // Create and confirm global admin user
        Yii::$app->runAction('user/create', [$email, $username, $password]);
        // Need to wait a while, while user is being inserted into database
        sleep(1);
        Yii::$app->runAction('user/confirm', [$email]);

        /***************************************************************************************************************
         * Clearing all cache
         **************************************************************************************************************/

        Yii::$app->runAction('cache/flush-all');

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Revert changes made by 'set-up' action.
     * Reverts depending modules migrations and other stuff.
     * @return integer the status of the action execution. 0 means normal, other values mean abnormal.
     */
    public function actionTearDown()
    {
        $this->askForInteraction();

        $this->db->createCommand('SET foreign_key_checks = 0;')->execute();

        /***************************************************************************************************************
         * Reverting `dektrium/yii2-user` and `dektrium/yii2-rbac` migrations.
         **************************************************************************************************************/

        Yii::$app->runAction('migrate/down', [
            $this->countMigrations('@yii/rbac/migrations'),
            'migrationPath' => '@yii/rbac/migrations',
            'interactive' => $this->interactive,
        ]);

        Yii::$app->runAction('migrate/down', [
            $this->countMigrations('@vendor/dektrium/yii2-user/migrations'),
            'migrationPath' => '@vendor/dektrium/yii2-user/migrations',
            'interactive' => $this->interactive,
        ]);

        $this->db->createCommand('SET foreign_key_checks = 1;')->execute();

        /***************************************************************************************************************
         * Clearing all cache
         **************************************************************************************************************/

        Yii::$app->runAction('cache/flush-all');

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Delete (reset) found database tables.
     *
     * @return integer the status of the action execution. 0 means normal, other values mean abnormal.
     * @throws \yii\db\Exception
     */
    public function actionDestroyDb()
    {
        $str = $this->ansiFormat('Do you really want to destroy database? (yes|no)', Console::FG_RED);
        $needReset = $this->prompt($str, ['default' => 'yes']);
        $needReset = $needReset == 'y' || $needReset == 'yes' ? true : false;
        $ok = true;

        if ($needReset) {
            $tables = Yii::$app->getDb()->schema->tableNames;
            if ($tables) {
                $this->db->createCommand('SET foreign_key_checks = 0;')->execute();
                foreach ($tables as $table) {
                    $this->stdout("Destroying $table table.", Console::FG_CYAN);
                    $this->db->createCommand()->dropTable($table)->execute();
                }
                $this->db->createCommand('SET foreign_key_checks = 1;')->execute();
                $this->stdout('Successfully destroyed DB', Console::FG_GREEN);
            } else {
                $this->stderr('No tables found', Console::FG_YELLOW);
                $ok = false;
            }
        } else {
            $this->stdout('OK, Application is not touched', Console::FG_YELLOW);
        }
        return $ok ? Controller::EXIT_CODE_NORMAL: Controller::EXIT_CODE_ERROR;
    }

    /**
     * Combines 'destroy-db', 'set-up' and 'insert-demo' actions.
     *
     * @return int
     */
    public function actionReset()
    {
        Yii::$app->runAction('app/destroy-db', [
            'interactive' => false,
        ]);

        Yii::$app->runAction('app/set-up', [
            'interactive' => false,
        ]);

        /*Yii::$app->runAction('app/insert-demo', [
            'interactive' => false,
        ]);*/

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Insert demo data into DB.
     *
     * @throws \yii\db\Exception
     */
    public function actionInsertDemo()
    {
        $this->stdout('Inserting demo data ...', Console::FG_PURPLE);

        // $command = $this->db->createCommand();
        // $command->batchInsert($model::tableName(), $model->attributes(), [
        //     [...]
        //     [...]
        // ])->execute();

        $this->stdout('Inserting demo data finished', Console::FG_PURPLE);
        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Get current Connection.
     *
     * @return \yii\db\Connection
     */
    public function getDb()
    {
        if ($this->_db === null) {
            $this->_db = Yii::$app->getDb();
        }
        return $this->_db;
    }

    /**
     * Formats a string with ANSI codes.
     *
     * @param string $string the string to be formatted
     * @return string
     */
    public function ansiFormat($string)
    {
        if ($this->isColorEnabled()) {
            $args = func_get_args();
            array_shift($args);
            $string = Console::ansiFormat($string, $args);
        }
        return $string;
    }

    /**
     * Prints a string to STDOUT.
     *
     * @param string $string the string to print
     * @return int|boolean Number of bytes printed or false on error
     */
    public function stdout($string)
    {
        if ($this->isColorEnabled()) {
            $args = func_get_args();
            array_shift($args);
            $string = Console::ansiFormat($string, $args);
        }
        return Console::stdout($string . "\n");
    }

    /**
     * Prints a string to STDERR.
     *
     * @param string $string the string to print
     * @return int|boolean Number of bytes printed or false on error
     */
    public function stderr($string)
    {
        if ($this->isColorEnabled(\STDERR)) {
            $args = func_get_args();
            array_shift($args);
            $string = Console::ansiFormat($string, $args);
        }
        return fwrite(\STDERR, $string . "\n");
    }

    /**
     * Count files in migration folder.
     *
     * @param $alias
     * @return integer
     */
    public function countMigrations($alias)
    {
        return count(glob(Yii::getAlias("$alias/m*.php")));
    }

    /**
     * Ask user for interaction.
     *
     * @return void
     */
    public function askForInteraction()
    {
        $str = $this->ansiFormat('Do you want to interact with script? (yes|no)', Console::FG_YELLOW);
        $isInteractive = $this->prompt($str, ['default' => 'no']);
        $this->interactive = $isInteractive == 'no' || $isInteractive == 'n' ? false : true;
    }
}
