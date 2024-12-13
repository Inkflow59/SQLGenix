<?php
class Logger {
    public function log($message) {
        try {
            $result = file_put_contents('sqlflow_log.txt', date('Y-m-d H:i:s') . " - " . $message . PHP_EOL, FILE_APPEND);
            if ($result === false) {
                throw new Exception("Erreur lors de l'écriture dans le fichier de log.");
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
