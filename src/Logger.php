<?php
/**
 * Class Logger
 * 
 * Class responsible for logging messages to a text file.
 * Messages are timestamped and appended to the end of the file.
 */
class Logger {
    /**
     * Logs a message to the log file
     * 
     * @param string $message The message to log
     * @throws Exception If writing to the file fails
     * @return void
     */
    public function log($message) {
        try {
            $result = file_put_contents('sqlflow_log.txt', date('Y-m-d H:i:s') . " - " . $message . PHP_EOL, FILE_APPEND);
            if ($result === false) {
                throw new Exception("Error writing to log file.");
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
