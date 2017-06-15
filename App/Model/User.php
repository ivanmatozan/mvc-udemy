<?php

namespace App\Model;

use App\Token;
use Core\Model;
use PDO;
use App\Mail;
use Core\View;

/**
 * User Model
 */
class User extends Model
{
    /**
     * Error messages
     *
     * @var array
     */
    protected $errors = [];

    /**
     * User constructor.
     *
     * @param array $data Initial property values
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Get errors
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Save the user model with current property values
     *
     * @return bool
     */
    public function save(): bool
    {
        $this->validate();

        if (empty($this->errors)) {
            $passwordHash = password_hash($this->password, PASSWORD_DEFAULT);

            $token = new Token();
            $hashedToken = $token->getHash();
            $this->activationToken = $token->getValue();

            $sql = 'INSERT INTO users (name, email, password_hash, activation_hash)
                VALUES (:name, :email, :password_hash, :activation_hash)';

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':password_hash', $passwordHash, PDO::PARAM_STR);
            $stmt->bindValue(':activation_hash', $hashedToken, PDO::PARAM_STR);

            return $stmt->execute();
        }

        return false;
    }

    /**
     * Validate current property values, adding validation error messages
     * to the errors array property
     *
     * @return void
     */
    protected function validate()
    {
        // Name
        if ($this->name == '') {
            $this->errors[] = 'Name is required';
        }

        // Email
        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[] = 'Invalid email';
        }

        if (static::emailExists($this->email, $this->id ?? null)) {
            $this->errors[] = 'Email already taken';
        }

        // Password
        if (isset($this->password)) {
//            if ($this->password != $this->password_confirm) {
//                $this->errors[] = 'Password must match confirmation';
//            }

            if (strlen($this->password) < 6) {
                $this->errors[] = 'Please enter at least 6 characters for the password';
            }

            if (preg_match('/.*[a-z]+.*/i', $this->password) == 0) {
                $this->errors[] = 'Password needs at least one letter';
            }

            if (preg_match('/.*\d+.*/i', $this->password) == 0) {
                $this->errors[] = 'Password needs at least one number';
            }
        }

    }

    /**
     * See if user record already exists with the specified email
     *
     * @param string $email Email address ot search for
     * @param int $ignoreId Return false anyway if the record found has this ID
     *
     * @return bool
     */
    public static function emailExists(string $email, int $ignoreId = null): bool
    {
        $user = static::findByEmail($email);

        if ($user) {
            if ($user->id != $ignoreId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find a user model by email address
     *
     * @param string $email
     *
     * @return mixed User object if found, false otherwise
     */
    public static function findByEmail(string $email)
    {
        $sql = 'SELECT * FROM users WHERE email = :email';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Find a user model by ID
     *
     * @param int $id The user ID
     *
     * @return mixed User object if found, false otherwise
     */
    public static function findById(int $id)
    {
        $sql = 'SELECT * FROM users WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Authenticate user by email and password
     *
     * @param string $email
     * @param string $password
     *
     * @return mixed User object or false if authentication fails
     */
    public static function authenticate(string $email, string $password)
    {
        $user = static::findByEmail($email);

        if ($user && $user->is_active) {
            if (password_verify($password, $user->password_hash)) {
                return $user;
            }
        }

        return false;
    }

    /**
     * Remember the login by inserting a new unique token into the
     * remembered_logins table for this user record
     *
     * @return bool True if the login was remembered successfully, false otherwise
     */
    public function rememberLogin(): bool
    {
        $token = new Token();
        $hashedToken = $token->getHash();
        $this->rememberToken = $token->getValue();
        $this->expiryTimestamp = time() + 60 * 60 * 24 * 30; // 30 days from now

        $sql = 'INSERT INTO remembered_logins (token_hash, user_id, expires_at) 
                VALUES (:token_hash, :user_id, :expires_at)';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashedToken, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $this->id, PDO::PARAM_INT);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $this->expiryTimestamp), PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Send the password reset instructions to the user specified
     *
     * @param string $email The email address
     *
     * @return void
     */
    public static function sendPasswordReset(string $email)
    {
        $user = static::findByEmail($email);

        if ($user) {
            if ($user->startPasswordReset()) {
                $user->sendPasswordResetEmail();
            }
        }
    }

    /**
     * Start the password reset process by generating a new token expiry
     *
     * @return bool
     */
    protected function startPasswordReset(): bool
    {
        $token = new Token();
        $hashedToken = $token->getHash();
        $this->passwordResetToken = $token->getValue();

        $expiryTimestamp = time() + 60 * 60 * 2; // 2 hours from now

        $sql = 'UPDATE users
                SET password_reset_hash = :token_hash, password_reset_expires_at = :expires_at
                WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashedToken, PDO::PARAM_STR);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $expiryTimestamp), PDO::PARAM_STR);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Send password reset instructions in an email to the user
     *
     * @return void
     */
    protected function sendPasswordResetEmail()
    {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/password/reset/' . $this->passwordResetToken;

        $text = View::getTemplate('Password/reset-email.txt', ['url' => $url]);
        $html = View::getTemplate('Password/reset-email.html.twig', ['url' => $url]);

        Mail::send($this->email, 'Password reset', $text, $html);
    }

    /**
     * Find a User model by password reset token and expiry
     *
     * @param string $token
     *
     * @return mixed User object if found and token hasn't expired, null otherwise
     */
    public static function findByPasswordReset(string $token)
    {
        $token = new Token($token);
        $hashedToken = $token->getHash();

        $sql = 'SELECT * FROM users
                WHERE password_reset_hash = :token_hash';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashedToken, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        $user = $stmt->fetch();

        if ($user) {
            // Check password reset token hasn't expired
            if (strtotime($user->password_reset_expires_at) > time()) {
                return $user;
            }
        }
    }

    /**
     * Reset the password
     *
     * @param string $password
     * @return bool True if the password was updated successfully, false otherwise
     */
    public function resetPassword(string $password): bool
    {
        $this->password = $password;

        $this->validate();

        if (empty($this->getErrors())) {
            $passwordHash = password_hash($this->password, PASSWORD_DEFAULT);

            $sql = 'UPDATE users 
                    SET password_hash = :password_hash,
                    password_reset_hash = NULL,
                    password_reset_expires_at = NULL
                    WHERE id = :id';

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':password_hash', $passwordHash, PDO::PARAM_STR);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

            return $stmt->execute();
        }

        return false;
    }

    /**
     * Send an email to the user containing the activation link
     *
     * @return void
     */
    public function sendActivationEmail()
    {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/signup/activate/' . $this->activationToken;

        $text = View::getTemplate('Signup/activation-email.txt', ['url' => $url]);
        $html = View::getTemplate('Signup/activation-email.html.twig', ['url' => $url]);

        Mail::send($this->email, 'Account activation', $text, $html);
    }

    /**
     * Acivate the user account with specified activation token
     *
     * @param string $value Activation token from URL
     *
     * @return void
     */
    public static function activate(string $value)
    {
        $token = new Token($value);
        $hashedToken = $token->getHash();

        $sql = 'UPDATE users 
                SET is_active = 1,
                    activation_hash = NULL
                WHERE activation_hash = :hashed_token';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':hashed_token', $hashedToken, PDO::PARAM_STR);

        $stmt->execute();
    }

    /**
     * Update the user's profile
     *
     * @param array $data Data from the edit profile form
     *
     * @return bool True if the data was updated, false otherwise
     */
    public function updateProfile(array $data): bool
    {
        $this->name = $data['name'];
        $this->email = $data['email'];

        if ($data['password'] != '') {
            $this->password = $data['password'];
        }

        $this->validate();

        if (empty($this->getErrors())) {
            $sql = 'UPDATE users 
                    SET name = :name,
                        email = :email';

            // Add password if it's set
            if (isset($this->password)) {
                $sql .= ', password_hash = :password_hash';
            }

            $sql .= ' WHERE id = :id';

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

            // Add password if it's set
            if (isset($this->password)) {
                $passwordHash = password_hash($this->password, PASSWORD_DEFAULT);
                $stmt->bindValue(':password_hash', $passwordHash, PDO::PARAM_STR);
            }

            return $stmt->execute();
        }

        return false;
    }
}