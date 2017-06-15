<?php

namespace Core;

use PDO;
use App\Config;

/**
 * Class Base Model
 */
abstract class Model
{
    /**
     * Get PDO database connection
     *
     * @return PDO
     */
    protected static function getDB(): PDO
    {
        static $db = null;

        if ($db === null) {

            try {
                $dsn = 'mysql:dbname=' . Config::DB_NAME . ';host=' . Config::DB_HOST . ';charset=utf8';
                $db = new PDO($dsn, Config::DB_USER, Config::DB_PASSWORD);

                // Throw an Exception when an error occurs
               $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (\PDOException $e) {
                echo $e->getMessage();
            }
        }

        return $db;
    }
}