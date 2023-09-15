<?php

namespace Core;

use ErrorException;

/**
 * Error and exception handler
 */
class Error
{
    /**
     * Error handler. Convert all errors to Exceptions by throwing an ErrorException.
     */
    public static function errorHandler(int $level, string $message, string $file, int $line)
    {
        throw new ErrorException($message, 0, $level, $file, $line);
    }

    /**
     * Exception handler.
     */
    public static function exceptionHandler($exception)
    {
        // Code is 404 (not found) or 500 (general error)
        $code = $exception->getCode();
        if ($code != 404) {
            $code = 500;
        }
        http_response_code($code);

        if ($_ENV['APP_LIVE_DEBUG']) {
            echo "<h1>Fatal error</h1>";
            echo "<p>Uncaught exception: '" . get_class($exception) . "'</p>";
            echo "<p>Message: '" . $exception->getMessage() . "'</p>";
            echo "<p>Stack trace:<pre>" . $exception->getTraceAsString() . "</pre></p>";
            echo "<p>Thrown in '" . $exception->getFile() . "' on line " . $exception->getLine() . "</p>";
        } else {
            $log = '../Storage/Logs/' . date('Y-m-d') . '.txt';
            ini_set('error_log', $log);

            $message = "Uncaught exception: '" . get_class($exception) . "'";
            $message .= " with message '" . $exception->getMessage() . "'";
            $message .= "\nStack trace: " . $exception->getTraceAsString();
            $message .= "\nThrown in '" . $exception->getFile() . "' on line " . $exception->getLine();

            self::SystemFailure($message,true,$_ENV['MAIL_ERRORS']);
        }
    }

    /**
     * Write line to log file
     */
    static function WriteLogLine(string $strLogLine, string $strLogFileName):void
    {
        $resLogFile = fopen($strLogFileName, 'a');
        fwrite($resLogFile, $strLogLine . "\r\n");
        fclose($resLogFile);
    }

    /**
     * Report system failures to administrator(s)
     */
    static function SystemFailure (string $strErrorMessage, bool $boolLogged = true, bool $boolMail = false):void
    {
        $strLogLine = '[' . date('d-m-Y @ H:i') . ' on ' . $_SERVER["REQUEST_URI"] . '] ' . $strErrorMessage;

        if ($boolLogged){
            self::WriteLogLine($strLogLine, '../Storage/Logs/failure.log');
        }else{
            die('Er is een interne fout opgetreden. Een ogenblik geduld a.u.b.');
        }
    }

}
