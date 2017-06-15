<?php

namespace App\Model;

use App\Token;
use Core\Model;
use PDO;

/**
 * Remembered Login model
 */
class RememberedLogin extends Model
{
    /**
     * Find a remembered login model by the token
     *
     * @param string $token
     *
     * @return mixed Remembered login object if found, false otherwise
     */
    public static function findByToken(string $token)
    {
        $token = new Token($token);
        $tokenHash = $token->getHash();

        $sql = 'SELECT * FROM remembered_logins WHERE token_hash = :tokenHash';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':tokenHash', $tokenHash, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Get the user model associated with this remembered login
     *
     * @return User The User model
     */
    public function getUser(): User
    {
        return User::findById($this->user_id);
    }

    /**
     * See if the remember token has expired or not,
     * based on the current system time
     *
     * @return bool True if token has expired, false otherwise
     */
    public function hasExpired(): bool
    {
        return strtotime($this->expires_at) < time();
    }

    /**
     * Delete this model
     *
     * @return void
     */
    public function delete()
    {
        $sql = 'DELETE FROM remembered_logins WHERE token_hash = :tokenHash';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':tokenHash', $this->token_hash, PDO::PARAM_STR);

        $stmt->execute();
    }
}