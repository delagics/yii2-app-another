<?php

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;

/**
 * Load application environment from .env file
 */
try {
    (new Dotenv(dirname(dirname(dirname(__DIR__)))))->load();
} catch (InvalidPathException $e) {
    // Not exiting application, using default values in env() function.
    //throw new InvalidPathException($e->getMessage());
}

/**
 * Initializing application constants.
 *
 * @var bool YII_DEBUG defines whether the application should be in debug mode or not. Defaults to false.
 * @var string YII_ENV defines in which environment the application is running. Defaults to 'prod', meaning production
 *      environment. You may define this constant in the bootstrap script. The value could be 'prod' (production),
 *      'dev' (development), 'test', 'staging', etc.
 * @var bool YII_ENABLE_ERROR_HANDLER defines whether error handling should be enabled. Defaults to true.
 * @see http://www.yiiframework.com/doc-2.0/guide-structure-entry-scripts.html#defining-constants
 */
defined('YII_DEBUG') or define('YII_DEBUG', env('YII_DEBUG', false));
defined('YII_ENV') or define('YII_ENV', env('YII_ENV', 'prod'));
defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER', env('YII_ENABLE_ERROR_HANDLER', true));