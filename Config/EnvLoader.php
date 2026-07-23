<?php

class EnvLoader
{
    private static $loaded = false;
    private static $env = [];

    public static function load($filePath = null)
    {
        if (self::$loaded) {
            return;
        }

        if ($filePath === null) {
            $filePath = dirname(dirname(__FILE__)) . '/.env';
        }

        if (!file_exists($filePath)) {
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse key=value
            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove quotes if present
                if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
                    (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
                    $value = substr($value, 1, -1);
                }

                self::$env[$key] = $value;
                putenv("$key=$value");
            }
        }

        self::$loaded = true;
    }

    public static function get($key, $default = null)
    {
        return self::$env[$key] ?? getenv($key) ?: $default;
    }

    public static function has($key)
    {
        return isset(self::$env[$key]) || getenv($key) !== false;
    }
}
?>
