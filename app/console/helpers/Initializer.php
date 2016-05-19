<?php

namespace console\helpers;

use Exception;

class Initializer
{
    /**
     * @param $event
     */
    public static function postCreateProject($event)
    {
        $params = $event->getComposer()->getPackage()->getExtra();
        if (isset($params[__METHOD__]) && is_array($params[__METHOD__])) {
            foreach ($params[__METHOD__] as $method => $args) {
                call_user_func_array([__CLASS__, $method], (array) $args);
            }
        }
    }

    /**
     * Set environment.
     *
     * @param string $env environment name.
     * @param array $envsConfig environments configuration.
     * @param string $file .env file path.
     */
    public static function setEnv($env = 'prod', $envsConfig = [], $file = '.env')
    {
        if (empty($envsConfig)) {
            $envsConfig = [
                'dev' => ['YII_ENV' => 'dev', 'YII_DEBUG' => 'true'],
                'prod' => ['YII_ENV' => 'prod', 'YII_DEBUG' => 'false']
            ];
        }
        if (isset($envsConfig[$env])) {
            foreach ($envsConfig[$env] as $name => $value) {
                static::setEnvVar($name, $value, $file);
            }
        }
    }

    /**
     * Set environment variable.
     *
     * @param string $name environment variable name.
     * @param string $value environment variable value.
     * @param string $file .env file path.
     */
    public static function setEnvVar($name, $value, $file = '.env')
    {
        if (is_file($file)) {
            Console::stdout("Setting $name in $file", Console::FG_GREY);
            $content = file_get_contents($file);
            if ($content) {
                $content = preg_replace('/^(' . $name . ')(.+)$/m', '$1 = ' . (string) $value, $content);
                file_put_contents($file, $content);
            }
        } else {
            Console::stderr("File $file not found", Console::FG_RED);
        }
    }

    /**
     * Remove file.
     *
     * @param string $file complete file path to remove.
     * @return bool whether the removing operation succeeded.
     */
    public static function removeFile($file)
    {
        $result = true;
        try {
            Console::stdout("Removing $file", Console::FG_GREY);
            unlink($file);
        } catch (Exception $e) {
            Console::stderr($e->getMessage(), Console::FG_RED);
            $result = false;
        }

        return $result;
    }

    /**
     * Copy file.
     *
     * @param string $source source file path
     * @param string $dest destination file path.
     * @param bool|null $overwrite whether to overwrite destination file if it already exist.
     * @return bool whether the copying operation succeeded.
     */
    public static function copyFile($source, $dest, $overwrite = null)
    {
        if (!is_file($source)) {
            Console::stderr("File $dest skipped ($source not exist)", Console::FG_GREEN);
            return true;
        }
        if (is_file($dest)) {
            if (file_get_contents($source) === file_get_contents($dest)) {
                Console::stdout("File $dest unchanged", Console::FG_GREEN);
                return true;
            }
            Console::stdout("File $dest exist, overwrite? [Yes|No|Quit]", Console::FG_YELLOW);
            $answer = $overwrite === null ? Console::stdin() : $overwrite;
            if (!strncasecmp($answer, 'q', 1) || strncasecmp($answer, 'y', 1) !== 0) {
                Console::stdout("Skipped $dest", Console::FG_GREEN);
                return false;
            }
            file_put_contents($dest, file_get_contents($source));
            Console::stdout("Overwritten $dest", Console::FG_GREEN);
            return true;
        }
        if (!is_dir(dirname($dest))) {
            @mkdir(dirname($dest), 0777, true);
        }
        file_put_contents($dest, file_get_contents($source));
        Console::stdout("Copied $source to $dest", Console::FG_GREEN);

        return true;
    }

    /**
     * Sets the correct permissions for the files and directories listed in the extra section.
     *
     * @param array $paths the paths (keys) and the corresponding permission octal strings (values).
     */
    public static function setPermissions(array $paths)
    {
        foreach ($paths as $path => $permission) {
            if (is_dir($path) || is_file($path)) {
                try {
                    if (chmod($path, octdec($permission))) {
                        Console::stdout("chmod('$path', $permission). Done.", Console::FG_GREEN);
                    };
                } catch (Exception $e) {
                    Console::stderr($e->getMessage(), Console::FG_RED);
                }
            } else {
                Console::stderr('File not found', Console::FG_RED);
            }
        }
    }

    /**
     * Set cookie validation key in .env file.
     *
     * @param array $paths the file paths (keys) and the corresponding patterns to search for (values).
     * @throws Exception
     */
    public static function setCookieValidationKey(array $paths = ['.env' => '<cookie_validation_key>'])
    {
        if (!extension_loaded('openssl')) {
            throw new Exception('The OpenSSL PHP extension is required.');
        }

        foreach ($paths as $file => $pattern) {
            if (is_file($file)) {
                Console::stdout("Generating cookie validation key in $file", Console::FG_GREY);
                $content = file_get_contents($file);
                $content = preg_replace_callback("/$pattern/m", function () {
                    $length = 32;
                    $bytes = openssl_random_pseudo_bytes(32, $crypto_strong);
                    return strtr(substr(base64_encode($bytes), 0, $length), '+/', '_-');
                }, $content);
                file_put_contents($file, $content);
            } else {
                Console::stderr("$file not found", Console::FG_RED);
            }
        }
    }
}
