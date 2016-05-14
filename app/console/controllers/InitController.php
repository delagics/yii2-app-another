<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use console\helpers\Console;

/**
 * Class InitController
 *
 * @package console\controllers
 * @property-read \yii\db\Connection $db
 */
class InitController extends Controller
{
    /**
     * @var string the ID of the action that is used when the action ID is not specified
     * in the request. Defaults to 'up'.
     */
    public $defaultAction = 'up';

    /**
     * @var \yii\db\Connection application db component.
     */
    private $_db;

    /**
     * Prepare application by running depending modules migrations and other stuff.
     * Deals basically with database but can be also cache, assets and so on.
     *
     * @return int the status of the action execution. 0 means normal, other values mean abnormal.
     */
    public function actionUp()
    {
        /***************************************************************************************************************
         * Getting input data from console user.
         **************************************************************************************************************/

        $email = $this->prompt('Enter global admin user email:', [
            'default' => ArrayHelper::getValue(Yii::$app->params, 'adminEmail', 'admin@example.com'),
        ]);
        $username = $this->prompt('Enter global admin user name:', [
            'default' => ArrayHelper::getValue(Yii::$app->getModule('user')->admins, 0, 'admin'),
        ]);
        $password = $this->prompt('Enter global admin user password:', ['default' => '123456']);

        $this->askForInteraction();

        /***************************************************************************************************************
         * Applying application migrations.
         **************************************************************************************************************/

        $migrations = explode(',', env('APP_MIGRATION_LOOKUP', '@console/migrations'));

        foreach ($migrations as $migrationPath) {
            Yii::$app->runAction('migrate/up', [
                'migrationPath' => $migrationPath,
                'interactive' => $this->interactive,
            ]);
        }

        /***************************************************************************************************************
         * Creating and confirming account of the first user.
         **************************************************************************************************************/

        Yii::$app->runAction('user/create', [$email, $username, $password]);

        // Have to wait a bit until the user is inserted into the database.
        sleep(1);

        Yii::$app->runAction('user/confirm', [$email]);

        /***************************************************************************************************************
         * Clearing all cache.
         **************************************************************************************************************/

        Yii::$app->runAction('cache/flush-all');

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Revert changes made by 'up' action.
     * Reverts depending modules migrations and other stuff.
     * This method probably will not work if there is holes in migration history.
     *
     * @return int the status of the action execution. 0 means normal, other values mean abnormal.
     */
    public function actionDown()
    {
        $this->askForInteraction();

        $this->db->createCommand('SET foreign_key_checks = 0;')->execute();

        /***************************************************************************************************************
         * Reverting application migrations.
         **************************************************************************************************************/

        $migrations = explode(',', env('APP_MIGRATION_LOOKUP', '@console/migrations'));

        // Migrations should be reverted in reversed order.
        $migrations = array_reverse($migrations);

        foreach ($migrations as $migrationPath) {
            $count = $this->countMigrations($migrationPath);
            if ($count > 0) {
                Yii::$app->runAction('migrate/down', [
                    $count,
                    'migrationPath' => $migrationPath,
                    'interactive' => $this->interactive,
                ]);
            }
        }

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
     * @return int the status of the action execution. 0 means normal, other values mean abnormal.
     * @throws \yii\db\Exception
     */
    public function actionDestroyDb()
    {
        $str = Console::ansiFormat('Do you really want to destroy application database? (yes|no)', Console::FG_RED);
        $destroy = $this->prompt($str, ['default' => 'no']);
        $destroy = $destroy == 'y' || $destroy == 'yes' ? true : false;

        if ($destroy) {
            $tables = Yii::$app->getDb()->schema->tableNames;
            if ($tables) {
                $this->db->createCommand('SET foreign_key_checks = 0;')->execute();
                foreach ($tables as $table) {
                    $this->stdout("Destroying $table table.", Console::FG_CYAN);
                    $this->db->createCommand()->dropTable($table)->execute();
                }
                $this->db->createCommand('SET foreign_key_checks = 1;')->execute();
                $this->stdout('Successfully destroyed DB.', Console::FG_GREEN);
            } else {
                $this->stderr('No tables found.', Console::FG_YELLOW);
            }
        } else {
            $this->stdout('OK, Application database is not touched.', Console::FG_YELLOW);
        }

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Runs 'destroy-db', 'up' and 'insert-demo' actions without user interaction.
     *
     * @return int
     */
    public function actionReset()
    {
        Yii::$app->runAction('init/destroy-db', [
            'interactive' => false,
        ]);

        /*Yii::$app->runAction('init/down', [
            'interactive' => false,
        ]);*/

        Yii::$app->runAction('init/up', [
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
     * @todo use Faker.
     * @throws \yii\db\Exception
     */
    public function actionInsertDemo()
    {
        $this->stdout('Inserting demo data...', Console::FG_PURPLE);

        // $command = $this->db->createCommand();
        // $command->batchInsert($model::tableName(), $model->attributes(), [
        //     [...]
        //     [...]
        // ])->execute();

        $this->stdout('Inserting demo data finished.', Console::FG_PURPLE);

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Ask user for interaction.
     *
     * @return void
     */
    public function askForInteraction()
    {
        $str = Console::ansiFormat('Do you want to interact with this script? (yes|no)', Console::FG_YELLOW);
        $isInteractive = $this->prompt($str, ['default' => 'no']);
        $this->interactive = $isInteractive == 'no' || $isInteractive == 'n' ? false : true;
    }

    /**
     * Count files in migration folder.
     *
     * @param $alias
     * @return int
     */
    public function countMigrations($alias)
    {
        return count(glob(Yii::getAlias("$alias/m*.php")));
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
}
