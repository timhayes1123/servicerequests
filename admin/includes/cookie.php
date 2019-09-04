<?php
/**
* Class to handle all interactions with the user cookie.
*
**/

class cookie {
    private $cookieName;
    private $isAdmin = 0;
    private $userId = -1;
    private $sessionId = -1;
    private $employeeId;

    public function __construct($cookieName) {

        $this->cookieName = $cookieName;
        $validCookie = false;

        if (isset($GLOBALS['_COOKIE'][$cookieName])) {
            $this->readCookie($cookieName);
        }
    }

    public function __destruct() {
        // clean up here
    }

    public function getSessionId() {
        return $this->sessionId;
    }

    /**
    * Return the username of the employee given the id stored in the instance variable.
    *
    **/

    public function getUserName() {
        $userName = "";
        if ($this->employeeId) {
            $dbConnection = dbconn::getConnectionBuild()->getConnection();

            $sqlStmt = "SELECT username FROM employee WHERE id = ?";
            $statementObj = $dbConnection->prepare($sqlStmt);
            $statementObj->bind_param("i", $this->employeeId);
            $statementObj->execute();
            $statementObj->bind_result($user_name);
            while ($statementObj->fetch()) {
                $userName = $user_name;
            }
        }
        return $userName;
    }

    public function getEmployeeId() {
        return $this->employeeId;
    }

    /**
    * Get the session id from the user's cookie. Assuming a valid session is found in the table that
    * matches the session id from the cookie, then set the employee id of the cookie object.
    *
    * @param string $cookieName
    **/
    private function readCookie($cookieName) {
        $dbConnection = dbconn::getConnectionBuild()->getConnection();

        // Get rid of old sessions.

        $sqlStmt = "DELETE FROM valid_sessions WHERE ABS(timestampdiff(HOUR, now(), date_created)) > 12;";
        $dbConnection->query($sqlStmt);

        $this->sessionId = $GLOBALS['_COOKIE'][$cookieName];
        $sqlStmt = "SELECT employee_id FROM valid_sessions WHERE session_id = ? ";
        $statementObj = $dbConnection->prepare($sqlStmt);
        $statementObj->bind_param("s", $this->sessionId);
        $statementObj->execute();
        $statementObj->bind_result($employee_id);
        while ($statementObj->fetch()) {
            $this->employeeId = $employee_id;
        }

    }

    public function logOut() {
		### Unset cookie and remove associated sessions from database table.
        if(isset($_COOKIE[$this->cookieName])) {
            unset($_COOKIE[$this->cookieName]);
            setcookie($this->cookieName, "", time()-3600);
        }
        $dbConnection = dbconn::getConnectionBuild()->getConnection();
        $sqlStmt = "DELETE FROM valid_sessions WHERE session_id = ? AND employee_id = ?";
        $statementObj = $dbConnection->prepare($sqlStmt);
        $statementObj->bind_param("si", $this->sessionId, $this->employeeId);
        $statementObj->execute();
    }

    public function validateCookie() {
		### Return boolean value based on whether a valid session is found.
        $rtnVal = false;
        $dbConnection = dbconn::getConnectionBuild()->getConnection();
        // Check to see if there is an entry in the valid_sessions table with the session id in the user's cookie.

        $sqlStmt = "SELECT session_id FROM valid_sessions WHERE session_id = ? ";
        $statementObj = $dbConnection->prepare($sqlStmt);
        $statementObj->bind_param("s", $this->sessionId);
        $statementObj->execute();
        $statementObj->bind_result($returnSessionId);
        while ($statementObj->fetch()) {
            if (is_null($returnSessionId)) {
                $rtnVal = false;
            } else {
                $rtnVal = true;
            }
        }

        return $rtnVal;
    }

     public function validateAdminCookie() {
		 ### Return boolean value based on whether a valid administrator session is found.
        $rtnVal = false;
        $dbConnection = dbconn::getConnectionBuild()->getConnection();
        // Check to see if there is an entry in the valid_sessions table with the session id in the user's cookie.

        $sqlStmt = "SELECT session_id FROM valid_sessions WHERE session_id = ? ";
        $sqlStmt .= "AND is_admin = 1";
        $statementObj = $dbConnection->prepare($sqlStmt);
        $statementObj->bind_param("s", $this->sessionId);
        $statementObj->execute();
        $statementObj->bind_result($returnSessionId);
        while ($statementObj->fetch()) {
            if (is_null($returnSessionId)) {
                $rtnVal = false;
            } else {
                $rtnVal = true;
            }
        }

        return $rtnVal;
    }


    /**
    * Check the username and sha1 encrypted password against the values stored in the database.
    * If the login is successful, generate a user cookie.
    *
    * @param string $userName
    * @param string $password
    *
    **/

    public function checkCredentials($userName, $password) {
        $rtnVal = false;
        $dbConnection = dbconn::getConnectionBuild()->getConnection();

        $userName = strip_tags(trim($userName));
        $userName = $dbConnection->real_escape_string($userName);

        $password = strip_tags(trim($password));
        $password = $dbConnection->real_escape_string($password);
        $password = sha1($password);

        $sqlStmt = "SELECT username, id FROM employee ";
        $sqlStmt .= "WHERE username = ? ";
        $sqlStmt .= "AND user_password = ? ";

        $stmtObj = $dbConnection->prepare($sqlStmt);
        $stmtObj->bind_param("ss", $userName, $password);
        $stmtObj->execute();
        $stmtObj->bind_result($user_name, $employee_id);
        while ($stmtObj->fetch()) {
            if (is_null($user_name)) {
                $rtnVal = false;
            } else {
                $rtnVal = true;
            }
        }
        if ($rtnVal) {
            $sessionInt = microtime() + rand();
            $this->sessionId = sha1($sessionInt);
            $this->employeeId = $employee_id;
            $this->writeCookie($this->cookieName, $this->sessionId);
        }

        return $rtnVal;
    }

    /**
    * Create the user cookie and create a corresponding entry in the valid_sessions database table.
    *
    * @param string $cookieName
    * @param string $sessionId
    **/

    private function writeCookie($cookieName, $sessionId) {
        $dbConnection = dbconn::getConnectionBuild()->getConnection();

        $sqlStmt = "SELECT is_admin FROM employee WHERE id = ? ";
        $stmtObj = $dbConnection->prepare($sqlStmt);
        $stmtObj->bind_param("i", $this->employeeId);
        $stmtObj->execute();
        $stmtObj->bind_result($is_admin);

        while ($stmtObj->fetch()) {
            $this->isAdmin = $is_admin;
        }

        $sqlStmt = "INSERT INTO valid_sessions (session_id, is_admin, employee_id) VALUES (?, ?, ?) ";
        $stmtObj = $dbConnection->prepare($sqlStmt);
        if (!$stmtObj) {
            die($dbConnection->error);
        }
        $stmtObj->bind_param("sii", $sessionId, $this->isAdmin, $this->employeeId);
        $stmtObj->execute();

        setcookie($cookieName, $sessionId, time() + 3600);
    }

}
