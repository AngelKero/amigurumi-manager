<?php
/**
 * Centralized In-Memory Configuration Accessor
 * Supports dot notation, e.g. Config::get('auth.jwt_secret')
 */

declare(strict_types=1);

namespace App\Core;

class Config {
    protected static ?array $config = null;

    /**
     * Loads the master configuration file
     */
    public static function load(?string $filePath = null): void {
        $path = $filePath ?? (__DIR__ . '/../config.php');
        if (file_exists($path)) {
            self::$config = require $path;
        } else {
            self::$config = [];
        }
    }

    /**
     * Gets a configuration value by dot-notation key
     */
    public static function get(string $key, mixed $default = null): mixed {
        if (self::$config === null) {
            self::load();
        }

        $segments = explode('.', $key);
        $current = self::$config;

        foreach ($segments as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return $default;
            }
            $current = $current[$segment];
        }

        return $current;
    }

    /**
     * Sets a configuration value at runtime (useful for testing)
     */
    public static function set(string $key, mixed $value): void {
        if (self::$config === null) {
            self::load();
        }

        $segments = explode('.', $key);
        $current = &self::$config;

        foreach ($segments as $segment) {
            if (!isset($current[$segment]) || !is_array($current[$segment])) {
                $current[$segment] = [];
            }
            $current = &$current[$segment];
        }

        $current = $value;
    }

    /**
     * Returns all configuration values
     */
    public static function all(): array {
        if (self::$config === null) {
            self::load();
        }
        return self::$config ?? [];
    }

    /**
     * Clears cached configuration
     */
    public static function reset(): void {
        self::$config = null;
    }
}
