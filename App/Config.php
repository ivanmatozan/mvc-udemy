<?php

namespace App;

/**
 * Application configuration
 */
class Config
{
    /**
     * Database host
     *
     * @var string
     */
    const DB_HOST = 'localhost';

    /**
     * Database name
     *
     * @var string
     */
    const DB_NAME = 'mvc_udemy';

    /**
     * Database user
     *
     * @var string
     */
    const DB_USER = 'root';

    /**
     * Database password
     *
     * @var string
     */
    const DB_PASSWORD = '';

    /**
     * Show or hide error messages on screen
     *
     * @var bool
     */
    const SHOW_ERRORS = true;

    /**
     * Secret key for hashing
     *
     * @var string
     */
    const SECRET_KEY = '';

    /**
     * Mailgun API key
     *
     * @var string
     */
    const MAILGUN_API_KEY = '';

    /**
     * Mailgun domain
     *
     * @var string
     */
    const MAILGUN_DOMAIN = '';
}