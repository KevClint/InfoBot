<?php
class EnvLoader {
    public static function load($path) {
        if (!file_exists($path)) return false;

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Skip comments
            if (empty(trim($line)) || strpos(trim($line), '#') === 0) continue;

            // Parse key=value
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value, "\"'\n\r "); // Remove quotes & whitespace

                // Set environment variable
                putenv("$name=$value");
                $_ENV[$name] = $value;
            }
        }
        return true;
    }

    public static function get($key, $default = null) {
        $value = getenv($key);
        return $value !== false ? trim($value) : $default;
    }
}
?>
