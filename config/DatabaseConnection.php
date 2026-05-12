<?php

declare(strict_types=1);

class DatabaseConnection
{
    private static ?mysqli $conn = null;

    public static function connection(): mysqli
    {
        if (self::$conn instanceof mysqli) {
            return self::$conn;
        }

        $config = require app_path('config/database.php');

        if (isset($config['connection']) && $config['connection'] instanceof mysqli) {
            self::$conn = $config['connection'];
            return self::$conn;
        }

        if (!self::$conn) {
            die('Database connection failed: ' . mysqli_connect_error());
        }

        return self::$conn;
    }
}