<?php

namespace App;

/**
 * Unique random tokens
 */
class Token
{
    /**
     * The token value
     *
     * @var array
     */
    protected $token;

    /**
     * Token constructor. Create a new random token
     *
     * @param string $tokenValue
     */
    public function __construct($tokenValue = null)
    {
        if ($tokenValue) {
            $this->token = $tokenValue;
        } else {
            $this->token = bin2hex(random_bytes(16)); // 16 bytes = 128 bits = 32 hex characters
        }
    }

    /**
     * Get the token value
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->token;
    }

    /**
     * Get the hashed token value
     *
     * @return string
     */
    public function getHash(): string
    {
        return hash_hmac('sha256', $this->getValue(), Config::SECRET_KEY); // sha256 = 64 chars
    }
}