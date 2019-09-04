<?php
class dbconn {
	#### Database connection object
    private static $connObj;
    private $db;

    public static function getConnectionBuild() {
        if (!self::$connObj) {
            self::$connObj = new dbconn();
        }
        return self::$connObj;
    }

    public function getConnection() {
        if (!$this->db) {
            $this->db = new mysqli("localhost","****","****","phs");
        }
        return $this->db;
    }
}
