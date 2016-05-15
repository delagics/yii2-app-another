<?php

namespace console\controllers;

use console\helpers\Initializer;
use Yii;
use Exception;
use yii\di\Instance;
use yii\db\Connection;
use yii\helpers\ArrayHelper;
use console\helpers\Console;
use console\components\Controller;

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
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection to use
     * when applying migrations. Starting from version 2.0.3, this can also be a configuration array
     * for creating the object.
     */
    public $db = 'db';

    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * It checks the existence of the [[migrationPath]].
     * @param \yii\base\Action $action the action to be executed.
     * @return boolean whether the action should continue to be executed.
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $this->db = Instance::ensure($this->db, Connection::className());
            return true;
        } else {
            return false;
        }
    }

    /**
     * Set environment depending variables in the .env file.
     *
     * @return int the status of the action execution. 0 means normal, other values mean abnormal.
     * @uses \console\helpers\Initializer::setEnv()
     */
    public function actionEnv()
    {
        $str = Console::ansiFormat('Which environment to use? (DEV|PROD)', [Console::FG_YELLOW]);
        $answer = $this->prompt($str, ['default' => 'PROD']);

        if (!strncasecmp($answer, 'd', 1)) {
            $this->stdout('Setting DEV environment...', Console::FG_GREEN);
            Initializer::setEnv('dev');
        } elseif (!strncasecmp($answer, 'p', 1)) {
            $this->stdout('Setting PROD environment...', Console::FG_GREEN);
            Initializer::setEnv('prod');
        } else {
            $this->stderr('Environment you choose does not exist', Console::FG_RED);

            return Controller::EXIT_CODE_ERROR;
        }

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Prepare application by running depending modules migrations and other stuff.
     * Deals basically with database but can be also cache, assets and so on.
     *
     * @return int the status of the action execution. 0 means normal, other values mean abnormal.
     */
    public function actionUp()
    {
        Yii::$app->runAction('init/env');

        /***************************************************************************************************************
         * Getting input data from console user.
         **************************************************************************************************************/

        $emailStr = Console::ansiFormat('Enter admin user email:', [Console::FG_YELLOW]);
        $email = $this->prompt($emailStr, [
            'default' => env('APP_ADMIN_EMAIL', 'admin@example.com'),
        ]);

        $usernameStr = Console::ansiFormat('Enter admin user name:', [Console::FG_YELLOW]);
        $username = $this->prompt($usernameStr, [
            'default' => ArrayHelper::getValue(explode(',', env('APP_ADMINS', 'admin')), 0),
        ]);

        $passwordStr = Console::ansiFormat('Enter global admin user password:', [Console::FG_YELLOW]);
        $password = $this->prompt($passwordStr, ['default' => '123456']);

        $this->askForInteraction();

        /***************************************************************************************************************
         * Applying application migrations.
         **************************************************************************************************************/

        $migrations = explode(',', env('APP_MIGRATION_LOOKUP', '@console/migrations'));

        try {
            foreach ($migrations as $migrationPath) {
                Yii::$app->runAction('migrate/up', [
                    'migrationPath' => $migrationPath,
                    'interactive' => $this->interactive,
                ]);
            }
        } catch (Exception $e) {
            $this->stdout("Exception: {$e->getMessage()}\n", Console::FG_RED);
            $this->stdout("({$e->getFile()}: {$e->getLine()})\n", Console::FG_RED);
            return Controller::EXIT_CODE_ERROR;
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
        $str = Console::ansiFormat('Do you really want to destroy application database? (yes|no)', [Console::FG_YELLOW]);
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
        $str = Console::ansiFormat('Do you want to interact with this script? (yes|no)', [Console::FG_YELLOW]);
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
}
