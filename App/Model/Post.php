<?php

namespace App\Model;

use Core\Model;
use PDO;

/**
 * Class Post
 */
class Post extends Model
{
    /**
     * Get all post as an associative array
     *
     * @return array
     */
    public static function getAll()
    {
        try {
            $db = self::getDB();

            $stmt = $db->query('SELECT id, title, content FROM posts ORDER BY created_at');
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $results;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }
}