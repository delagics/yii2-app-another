<?php

namespace console\controllers;

use Yii;
use Exception;
use yii\di\Instance;
use yii\db\Connection;
use yii\helpers\ArrayHelper;
use console\helpers\Console;
use console\helpers\Initializer;
use console\components\Controller;

/**
 * Project initialization commands - provides a list of commands for fast project initialization.
 *
 * @package console\controllers
 * @property Connection $db
 */
class InitController extends Controller
{
    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection to use
     * when applying migrations. Starting from version 2.0.3, this can also be a configuration array
     * for creating the object.
     */
    public $db = 'db';

    /**
     * @var int color used for formatting notable messages.
     */
    public $colorNotable = Console::FG_YELLOW;
    /**
     * @var int color used for formatting success messages.
     */
    public $colorSuccess = Console::FG_GREEN;
    /**
     * @var int color used for formatting error messages.
     */
    public $colorError = Console::FG_RED;
    /**
     * @var int color used for formatting standard messages.
     */
    public $colorDefault = Console::FG_GREY;

    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * It checks the existence of the [[migrationPath]].
     *
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
     * Enironment initialization action.
     * This is just a wrapper for other separate actions.
     *
     * @return int the status of the action execution. 0 means normal, other values mean abnormal.
     */     
    public function actionEnvironment()
    {
        $exitCode = Yii::$app->runAction('init/env-project');
        $exitCode = Yii::$app->runAction('init/env-env') && $exitCode;
        $exitCode = Yii::$app->runAction('init/env-db') && $exitCode;

        return $exitCode;
    }

    /**
     * General initialization action.
     * This is just a wrapper for other separate actions.
     * This one must be called only after 'environment' action is done.
     *
     * @return int the status of the action execution. 0 means normal, other values mean abnormal.
     */
    public function actionInitialize()
    {
        return Yii::$app->runAction('init/migrate-up');
    }

    /**
     * Initialize project.
     *
     * @return int the status of the action execution. 0 means normal, other values mean abnormal.
     */
    public function actionEnvProject()
    {
        $str = Console::ansiFormat('Do you want to initialize(reinitialize) project?', [$this->colorNotable]);
        $confirm = $this->confirm($str, false);

        if ($confirm) {
            $this->stdout('Initializing project...', $this->colorDefault);

            $this->stdout('Copying .env file from .env.example template', $this->colorDefault);
            Initializer::copyFile(".env.example", ".env", null);

            $this->stdout('Setting folders permissions', $this->colorDefault);
            Initializer::setPermissions([
                'app/back/runtime' => "0777",
                'app/front/runtime' => "0777",
                'app/console/runtime' => "0777",
                'public/admin/assets' => "0777",
                'public/assets' => "0777",
                'public/storage' => "0777",
                'yii' => "0755",
            ]);

            $this->stdout('Setting cookie validation keys', $this->colorDefault);
            Initializer::setCookieValidationKey([".env" => "<cookie_validation_key>"]);
        } else {
            $this->stdout('OK, project is not touched', $this->colorSuccess);

            return Controller::EXIT_CODE_ERROR;
        }

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Set environment depending variables.
     *
     * @return int the status of the action execution. 0 means normal, other values mean abnormal.
     * @uses \console\helpers\Initializer::setEnv()
     */
    public function actionEnvEnv()
    {
        $str = Console::ansiFormat('Which environment to use? (DEV|PROD)', [$this->colorNotable]);
        $answer = $this->prompt($str, ['default' => 'PROD']);

        if (!strncasecmp($answer, 'd', 1)) {
            $this->stdout('Setting DEV environment...', $this->colorDefault);
            Initializer::setEnv('dev');
        } elseif (!strncasecmp($answer, 'p', 1)) {
            $this->stdout('Setting PROD environment...', $this->colorDefault);
            Initializer::setEnv('prod');
        } else {
            $this->stderr('Environment you choose does not exist', $this->colorError);

            return Controller::EXIT_CODE_ERROR;
        }

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Set database variables in the .env file.
     *
     * @return int the status of the action execution. 0 means normal, other values mean abnormal.
     * @uses \console\helpers\Initializer::setEnvVar()
     */
    public function actionEnvDb()
    {
        $dbHostPrompt = Console::ansiFormat('Enter database host name: ', [$this->colorNotable]);
        $dbHost = $this->prompt($dbHostPrompt, ['default' => env('DB_HOST', 'localhost')]);

        $dbPortPrompt = Console::ansiFormat('Enter database host name: ', [$this->colorNotable]);
        $dbPort = $this->prompt($dbPortPrompt, ['default' => env('DB_PORT', '3306')]);

        $dbNamePrompt = Console::ansiFormat('Enter database name: ', [$this->colorNotable]);
        $dbName = $this->prompt($dbNamePrompt, ['default' => env('DB_NAME', 'app_db')]);

        $dbUserPrompt = Console::ansiFormat('Enter database user name: ', [$this->colorNotable]);
        $dbUser = $this->prompt($dbUserPrompt, ['default' => env('DB_USERNAME', 'root')]);

        $dbPassPrompt = Console::ansiFormat('Enter database user password: ', [$this->colorNotable]);
        $dbPass = $this->prompt($dbPassPrompt, ['default' => env('DB_PASSWORD', '')]);

        $dbCharsetPrompt = Console::ansiFormat('Enter database charset name: ', [$this->colorNotable]);
        $dbCharset = $this->prompt($dbCharsetPrompt, ['default' => env('DB_CHARSET', 'utf8')]);

        $this->stdout('Setting database...', $this->colorDefault);
        Initializer::setEnvVar('DB_HOST', $dbHost);
        Initializer::setEnvVar('DB_PORT', $dbPort);
        Initializer::setEnvVar('DB_NAME', $dbName);
        Initializer::setEnvVar('DB_USERNAME', $dbUser);
        Initializer::setEnvVar('DB_PASSWORD', $dbPass);
        Initializer::setEnvVar('DB_CHARSET', $dbCharset);

        $this->db->username = $dbUser;
        $this->db->password = $dbPass;
        $this->db->charset = $dbCharset;
        $this->db->dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName";

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Prepare application by running depending modules migrations.
     * Deals basically with database but can be also cache, assets and so on.
     *
     * @return int the status of the action execution. 0 means normal, other values mean abnormal.
     */
    public function actionMigrateUp()
    {
        /***************************************************************************************************************
         * Getting input data from console user.
         **************************************************************************************************************/

        $emailStr = Console::ansiFormat('Enter admin user email:', [$this->colorNotable]);
        $email = $this->prompt($emailStr, [
            'default' => env('APP_ADMIN_EMAIL', 'admin@example.com'),
        ]);

        $usernameStr = Console::ansiFormat('Enter admin user name:', [$this->colorNotable]);
        $username = $this->prompt($usernameStr, [
            'default' => ArrayHelper::getValue(explode(',', env('APP_ADMINS', 'admin')), 0),
        ]);

        $passwordStr = Console::ansiFormat('Enter global admin user password:', [$this->colorNotable]);
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
            $this->stdout("Exception: {$e->getMessage()}\n", $this->colorError);
            $this->stdout("({$e->getFile()}: {$e->getLine()})\n", $this->colorError);

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
     * Revert depending modules migrations.
     * Revert changes made by 'migrate-up' action.
     * This method probably will not work if there are holes in migration history.
     *
     * @return int the status of the action execution. 0 means normal, other values mean abnormal.
     */
    public function actionMigrateDown()
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
        $str = Console::ansiFormat('Do you really want to destroy application database?', [$this->colorNotable]);
        $confirm = $this->confirm($str, false);

        if ($confirm) {
            $tables = Yii::$app->getDb()->schema->tableNames;
            if ($tables) {
                $this->db->createCommand('SET foreign_key_checks = 0;')->execute();
                foreach ($tables as $table) {
                    $this->stdout("Destroying $table table", $this->colorDefault);
                    $this->db->createCommand()->dropTable($table)->execute();
                }
                $this->db->createCommand('SET foreign_key_checks = 1;')->execute();
                $this->stdout('Successfully destroyed DB', $this->colorSuccess);
            } else {
                $this->stderr('No tables found', $this->colorError);
            }
        } else {
            $this->stdout('OK, Application database is not touched', $this->colorSuccess);
        }

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Run 'destroy-db', 'migrate-up' and 'insert-demo' actions without user interaction.
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
        $this->stdout('Inserting demo data...', $this->colorDefault);

        // $command = $this->db->createCommand();
        // $command->batchInsert($model::tableName(), $model->attributes(), [
        //     [...]
        //     [...]
        // ])->execute();

        $this->stdout('Inserting demo data finished', $this->colorDefault);

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Ask user for interaction.
     *
     * @return void
     */
    public function askForInteraction()
    {
        $str = Console::ansiFormat('Do you want to interact with this script?', [$this->colorNotable]);
        $this->interactive = $this->confirm($str, true);
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
