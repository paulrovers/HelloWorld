<?php

namespace core;

class Error
{

    /**
     * Error handler. Convert all errors to Exceptions by throwing an ErrorException.
     *
     * @param int $level  Error level
     * @param string $message  Error message
     * @param string $file  Filename the error was raised in
     * @param int $line  Line number in the file
     *
     * @return void
     */
    public static function errorHandler(int $level, string $message, string $file, int $line):void
    {
        if (error_reporting() !== 0) {  // to keep the @ operator working
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Exception handler.
     *
     * @param Exception $exception  The exception
     *
     * @return void
     */
    public static function exceptionHandler($exception):void
    {
        // Code is 404 (not found) or 500 (general error)
        $code = $exception->getCode();
        if ($code != 404) {
            $code = 500;
        }
        http_response_code($code);

        if ($_ENV['APP_LIVE_DEBUG'] == 1) {
            echo "<h1>Fatal error</h1>";
            echo "<p>Uncaught exception: '" . get_class($exception) . "'</p>";
            echo "<p>Message: '" . $exception->getMessage() . "'</p>";
            echo "<p>Stack trace:<pre>" . $exception->getTraceAsString() . "</pre></p>";
            echo "<p>Thrown in '" . $exception->getFile() . "' on line " . $exception->getLine() . "</p>";
        } else {
            $log = $_ENV['APP_PATH']. '/storage/logs/' . date('Y-m-d') . '.log';
            ini_set('error_log', $log);

            $message = "Uncaught exception: '" . get_class($exception) . "'";
            $message .= " with message '" . $exception->getMessage() . "'";
            $message .= "\nStack trace: " . $exception->getTraceAsString();
            $message .= "\nThrown in '" . $exception->getFile() . "' on line " . $exception->getLine();

            self::SystemFailure($message,true,false);
        }
    }

    // User error
    static $strError;

    /**
     * Write line to log file
     * @param string The line to write
     * @param string The file to write to
     * @returns boolean
     */
    static function WriteLogLine(string $strLogLine, string $strLogFileName):bool
    {
        $resLogFile = fopen($strLogFileName, 'a');
        fwrite($resLogFile, $strLogLine . "\r\n");
        fclose($resLogFile);
    }

    /**
     * Show a nice error to user
     * @param string
     * @return void
     */
    static function UserException ()
    {
        die('An error caused the application to quit.');
    }

    /**
     * Report system failures to administrator(s)
     * @param string Error message
     * @param boolean Should it be logged, or just mailed? (optional)
     * @return boolean
     */
    static function SystemFailure (string $strErrorMessage, bool $boolLogged = true, bool $boolMail = false):bool
    {
        $strLogLine = '[' . date('d-m-Y @ H:i') . ' on ' . $_SERVER["REQUEST_URI"] . '] ' . $strErrorMessage;

        if ($boolLogged)
            self::WriteLogLine($strLogLine, $_ENV['APP_PATH'].'/storage/logs/failure.log');

        self::UserException();
    }

}
