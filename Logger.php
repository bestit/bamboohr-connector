<?php

declare(strict_types=1);

namespace BambooHRConnector;

use DateTime;
use DateTimeZone;
use Exception;

/**
 * Class Logger
 *
 * @package BambooHRConnector
 * @author Georgi Damyanov <georgi.damyanov@bestit-online.at>
 */
class Logger
{
    /**
     * TMP file containing errors of the day
     *
     * @var $tmp_error_log string
     */
    private $tmp_error_log;

    /**
     * The global error log file, which contains all the errors
     *
     * @var $global_error_log string
     */
    private $global_error_log;

    /**
     * Logger constructor.
     */
    public function __construct()
    {
        $this->tmp_error_log = './log/tmp_error.txt';
        $this->global_error_log = './log/global_error.txt';
    }

    /**
     * Function writeErrorToFile
     *
     * - Get current date
     * - Write the error message in the tmp_error_log file
     *
     * @param string $message The message which will be save in the log file
     *
     * @return bool Returns true if data successfully written
     * @throws Exception
     */
    public function writeErrorToFile($message)
    {
        static $linebreak = false;

        $date = new DateTime();
        $date->setTimezone(new DateTimeZone('Europe/Berlin'));
        $date = $date->format('Y-m-d H:i:sP');

        if (!$linebreak) {
            $lineSeparator = <<< EOT
--------------------------------------------------------------------------------------------------------------------\n
EOT;
            error_log($lineSeparator, 3, $this->tmp_error_log);
            $linebreak = true;
        }
        error_log($date.' - '.$message."\n", 3, $this->tmp_error_log);
        return true;
    }

    /**
     * Function transferDailyLogToGlobal
     *
     * The tmp_error_log is appended to the globa_error.txt and cleared for the next day
     *
     * @return void
     */
    public function transferDailyLogToGlobal(): void
    {
        $this->createLogDirectory();
        $this->createLogFiles();

        if (file_exists($this->tmp_error_log) && filesize($this->tmp_error_log) !== 0) {
            file_put_contents($this->global_error_log, file_get_contents($this->tmp_error_log), FILE_APPEND | LOCK_EX);
            file_put_contents($this->tmp_error_log, '');
        }
    }

    /**
     * Create log directory if it does not exist
     *
     * @return void
     */
    private function createLogDirectory(): void
    {
        if (!file_exists('./log')) {
            mkdir('./log', 0755, true);
        }
    }

    /**
     * Create the global_error and tmp_error files if they don't exist
     *
     * @return void
     */
    private function createLogFiles(): void
    {
        if (!is_file($this->global_error_log)) {
            fopen($this->global_error_log, 'w');
        }

        if (!is_file($this->tmp_error_log)) {
            fopen($this->tmp_error_log, 'w');
        }
    }
}
