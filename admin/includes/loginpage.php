<?php

/**
* Class to handle all aspects of the login page.
*
**/

class loginpage {
    private $login = true;
    private $employeeName;
    private $employeeUsername;
    private $employeeId;
    private $notifications = array();

    public function __construct() {
    }

    public function __destruct() {
        // clean up here
    }


    public function displayAsText() {
        $outputHtml = "";
        return $outputHtml;
    }

    /**
    * Produce the HTML form for display.
    *
    **/
    public function displayAsHTML() {
        if ($this->login) {
            $outputHtml = <<<EOT
            <form action="index.php?page=validateLogin" method="post">
                <table width="40%">
                    <tr>
                        <td width="50%"><p class="pageedit">User Name:</p></td>
                        <td width="50%"><input type="text" maxlength="25" size="25" name="username" id="username" /></td>
                    </tr>
                    <tr>
                        <td width="50%"><p class="pageedit">Password:</p></td>
                        <td width="50%"><input type="password" maxlength="25" size="25" name="password" id="password" /></td>
                    </tr>
                    <tr>
                        <td width="50%">&nbsp;</td>
                        <td width="50%"><input type="submit" value="Login"></td>
                    </tr>
                </table>
            </form>
EOT;


        } else {
            $outputHtml = <<<EOT
            <p class="pageedit">The following service requests have not been responded to:</p>
            <ul>
EOT;

            foreach ($this->notifications as $key => $requestObj) {
                $outputHtml .= '<li><p class="pageedit"><a href="index.php?page=servicerequests&pageId=' . $requestObj->requestId . '" >';
                $outputHtml .= $requestObj->firstName . " " . $requestObj->lastName . "  " . $requestObj->phone . ", " . $requestObj->email;
                $outputHtml .= '</a></p></li>';
            }
            $outputHtml .= "</li>";
        }
        return $outputHtml;
    }


    public function loadPage($pageId) {
        // pageId is a string that corresponds to the descr field in the page_content table.

        $dbConnection = dbconn::getConnectionBuild()->getConnection();

        if ($pageId === "login") {
            $this->login = true;
        } else {
            $this->login = false;
            $this->employeeId = $pageId;

            $sqlStmt = "SELECT first_name, last_name, username FROM employee WHERE id = ?";

            $stmtObj = $dbConnection->prepare($sqlStmt);
            $stmtObj->bind_param("i", $pageId);
            $stmtObj->execute();
            $stmtObj->bind_result($first_name, $last_name, $username);
            while ($stmtObj->fetch()) {
                $this->employeeName = $first_name . " " . $last_name;
                $this->employeeUsername = $username;
            }

            // Create a list of currently unhandled/unassigned service requests.

            $sqlStmt = "SELECT id, first_name, last_name, phone, email FROM service_request WHERE responder = 0 ORDER BY date_created;";
            $stmtObj = $dbConnection->prepare($sqlStmt);
            $stmtObj->execute();
            $stmtObj->bind_result($request_id, $first_name, $last_name, $phone, $email);
            while ($stmtObj->fetch()) {
                $this->notifications[$request_id] = new request($request_id, $first_name, $last_name, $phone, $email);
            }
        }
    }
}

/**
* Simple class to bundle data.
*
**/
class request {
    public $requestId;
    public $firstName;
    public $lastName;
    public $phone;
    public $email;

    public function __construct($requestId, $firstName, $lastName, $phone, $email) {
        $this->requestId = $requestId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->phone = $phone;
        $this->email = $email;
    }
}
