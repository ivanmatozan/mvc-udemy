<?php

namespace Core;

use App\Config;

/**
 * Error and exception handler
 */
class Error
{
    /**
     * Error handler. Converts all errors to Exceptions by throwing an ErrorException
     *
     * @param int $level Error level
     * @param string $message Error message
     * @param string $file Filename the error was raised in
     * @param int $line Line number in the file
     *
     * @throws \ErrorException
     *
     * @return void
     */
    public static function errorHandler(int $level, string $message, string $file, int $line)
    {
        if (error_reporting() !== 0) { // to keep @ operator working
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Exception handler
     *
     * @param \Exception $exception
     *
     * @return void
     */
    public static function exceptionHandler(\Exception $exception)
    {
        // Code is 404 (not found) or 500 (general error)
        $code = $exception->getCode();
        if ($code != 404) {
            $code = 500;
        }
        http_response_code($code);

        if (Config::SHOW_ERRORS) {
            echo "<h1>Fatal error</h1>";
            echo "<p>Uncaught exception: '" . get_class($exception) . "'</p>";
            echo "<p>Message: '" . $exception->getMessage() . "'</p>";
            echo "<p>Stack trace:<pre>" . $exception->getTraceAsString() . "</pre>";
            echo "<p>Thrown in '" . $exception->getFile() . "' on line " . $exception->getLine() . "</p>";
        } else {
            $log = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
            $log .= date('Y-m-d') . '.txt';
            ini_set('error_log', $log);

            $message = "Uncaught exception: '" . get_class($exception) . "'";
            $message .= " with message: '" . $exception->getMessage() . "'" . PHP_EOL;
            $message .= "Stack trace: " . $exception->getTraceAsString() . PHP_EOL;
            $message .= "Thrown in '" . $exception->getFile() . "' on line " . $exception->getLine();

            error_log($message);
            View::renderTemplate("$code.html");
        }
    }
}