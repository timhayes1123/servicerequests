<?php
/**
* Class to handle all aspects of the customer UI.
*
**/

class customerpage {
    private $login = true;

    private $customerId = 0;
    private $employeeId;
    private $firstName = "";
    private $lastName = "";
    private $address = "";
    private $city = "";
    private $state = "";
    private $zip = "";
    private $phone = "";
    private $email = "";
    private $notes = "";
    private $customerList = array();
    private $errorArray;
    private $passedValidation;
    private $fieldArray = ["customerId", "firstName", "lastName", "address", "city", "state", "zip", "phone", "email", "notes"];
    private $statusMessage = "";

    public function __construct() {
        $this->errorArray = [];
        $this->passedValidation = false;
    }

    public function __destruct() {
        // clean up here
    }

    private function setField($fieldName, $fieldValue) {
        switch ($fieldName) {
            case "customerId" :
                $this->customerId = $fieldValue;
                break;
            case "firstName" :
                $this->firstName = $fieldValue;
                break;
            case "lastName" :
                $this->lastName = $fieldValue;
                break;
            case "address" :
                $this->address = $fieldValue;
                break;
            case "city" :
                $this->city = $fieldValue;
                break;
            case "state" :
                $this->state = $fieldValue;
                break;
            case "zip" :
                $this->zip = $fieldValue;
                break;
            case "phone" :
                $this->phone = $fieldValue;
                break;
            case "email" :
                $this->email = $fieldValue;
                break;
            case "notes" :
                $this->notes = $fieldValue;
                break;
        }
    }

    /**
    * Generate the HTML output for error rows.
    *
    * @param string $field1
    * @param string $field2 (optional)
    * @param string $field3 (optional)
    * @return string
    **/
    private function errorRow($field1, $field2 = "", $field3 = "") {
        $outputHTML = "";
        $errorPrinted = false;
        // Nothing is returned if there are no errors for the $fieldx parameters.
        if (array_key_exists($field1, $this->errorArray) || array_key_exists($field2, $this->errorArray) || array_key_exists($field3, $this->errorArray)) {
            // Errors are written in sequence, seperated by a <br> tag.
            $fieldContents = "";
            if (array_key_exists($field1, $this->errorArray)) {
                $fieldContents .= $this->errorArray[$field1];
                $errorPrinted = true;
            }
            if (array_key_exists($field2, $this->errorArray)) {
                if ($errorPrinted) {
                    $fieldContents .= '<br>';
                }
                $errorPrinted = true;
                $fieldContents .= $this->errorArray[$field2];
            }
            if (array_key_exists($field3, $this->errorArray)) {
                if ($errorPrinted) {
                    $fieldContents .= '<br>';
                }
                $errorPrinted = true;
                $fieldContents .= $this->errorArray[$field3];
            }
            // Generate the span tag that formats the error messages.
            $htmlObj = new htmlElement("span", "", "error");
            $htmlObj->setContents($fieldContents);
            $fieldContents = $htmlObj->getHtml();
            // Insert into a field that spans the width of the table.
            $htmlObj = new htmlElement("td");
            $htmlObj->addAttribute("colspan", 4);
            $htmlObj->setContents($fieldContents);
            $rowContents = $htmlObj->getHtml();

            $outputHTML .= "<tr>" . $rowContents . "</tr>";
        }
        return $outputHTML;
    }

    public function displayAsText() {
        $outputHtml = "";
        return $outputHtml;
    }

    /**
    * Generate and return the HTML form for display.
    *
    **/

    public function displayAsHTML() {
        // The page parameter of the form action must be assigned a value that corresponds to the switch statement string in index.php

        $outputHtml = <<<EOT
        <form action="index.php?page=customers" method="post">
            <input type="hidden" name="action" value="save" />
            <input type="hidden" name="customerId" value="$this->customerId" />
            <table width="90%"><tr><td><div class="instructions" id="instructionText"></div></td></tr></table>
            <div class="dataentry">
            <table width="90%">
                <tr>
                    <td width="15%"><p class="pageedit">First Name:</p></td>
                    <td width="35%"><input type="text" maxlength="40" size="40" name="firstName" id="firstName" value="$this->firstName"  /></td>
                    <td width="15%"><p class="pageedit">Last Name:</p></td>
                    <td width="35%"><input type="text" maxlength="40" size="40" name="lastName" id="lastName" value="$this->lastName"  /></td>
                </tr>
EOT;

        $outputHtml .= $this->errorRow("firstName", "lastName");
        $outputHtml .= <<<EOT
                <tr>
                    <td width="15%"><p class="pageedit">Phone:</p></td>
                    <td width="35%"><input type="text" maxlength="10" size="10" name="phone" id="phone" value="$this->phone"  /></td>
                    <td width="15%"><p class="pageedit">E-mail:</p></td>
                    <td width="35%"><input type="text" maxlength="40" size="40" name="email" id="email" value="$this->email"  /></td>
                </tr>
EOT;

        $outputHtml .= $this->errorRow("phone", "email");
        $outputHtml .= <<<EOT
                <tr>
                    <td width="15%"><p class="pageedit">Address:</p></td>
                    <td colspan="3"><input type="text" maxlength="255" size="80" name="address" id="address" value="$this->address"  /></td>
                </tr>
EOT;

        $outputHtml .= $this->errorRow("address");
        $stateDDHtml = stateDropDown("state", $this->state);
        $outputHtml .= <<<EOT
                <tr>
                    <td width="15%"><p class="pageedit">City:</p></td>
                    <td width="35%"><input type="text" maxlength="40" size="40" name="city" id="city" value="$this->city"  /></td>
                    <td width="15%"><p class="pageedit">State/Zip:</p></td>
                    <td width="35%">
                        <table>
                            <tr>
                                <td width="50%">
                                    $stateDDHtml
                                </td>
                                <td width="50%">
                                    <input type="text" maxlength="5" size="6" name="zip" id="zip" value="$this->zip" />
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
EOT;


        $outputHtml .= $this->errorRow("city", "state", "zip");
        $outputHtml .= <<<EOT
                <tr>
                    <td><p class="pageedit">Notes:</p></td>
                    <td colspan="3"><textarea name="notes" id="notes" cols="80" rows="2">$this->notes</textarea></td>
                </tr>

                <tr>
                    <td colspan="4"><input type="submit" value="Save" /><a href="index.php?page=customers"><input type="button" value="New" /></a></td>
                </tr>
                <tr>
                    <td colspan="4"><p class="instructions">$this->statusMessage</p></td>
                </tr>
            </table>
            </div>
        </form>
        <br><br>
EOT;


        $outputHtml .= <<<EOT
       <table class="searchresults">
            <tr>
                <td class="searchresultshdr" width="34%"><p class="searchresultsheader"><strong>Name</strong></p></td>
                <td class="searchresultshdr" width="33%"><p class="searchresultsheader"><strong>Phone</strong></p></td>
                <td class="searchresultshdr" width="33%"><p class="searchresultsheader"><strong>E-mail</strong></p></td>
            </tr>
EOT;

        foreach ($this->customerList as $key => $tableRow) {
            $outputHtml .= '<tr>' . $tableRow . '</tr>';
        }

        $outputHtml .= <<<EOT
        </table>
        <form action="index.php?page=customers" method="post">
            <input type="hidden" name="listby" value="name" />
            <p class="pageedit"><strong>Find customer by name</strong></p>
            <p class="pageedit">First name: <input type="text" maxlength="40" size="40" name="searchFName" id="searchFName" /></p>
            <p class="pageedit">Last name: <input type="text" maxlength="40" size="40" name="searchLName" id="searchLName"  /></p>
            <input type="submit" value="Search">
        </form>
EOT;

        return $outputHtml;
    }

    /**
    * This function trims leading/trailing spaces from the input field, strips any HTML tags out, truncates the field
    * to the specified length and escapes the quotes for database insertion.
    *
    * @param string $field  The value entered by the user. Retrieved from the $_POST variables.
    * @param string $fieldName  The name of the field used on the HTML form.
    * @param string $errorFriendlyName  The name of the field in a format that is suitable for using in a notification to the user.
    * @param int $fieldLength  The number of characters allowed in the field. Corresponds to the [invoice] database table.
    * @param bool $isRequired  Flags whether the field is required or not. If false, no error notification is generated for an empty value.
    *
    * @return string
    *
    **/

    private function generalFieldValidation($field, $fieldName, $errorFriendlyName, $fieldLength, $isRequired = true) {
        $dbConnection = dbconn::getConnectionBuild()->getConnection();

        $field = strip_tags(trim($field));
        if ($field !== "") {
            $field = $dbConnection->real_escape_string($field);
            if ($fieldLength > 0) {
                $field = substr($field, 0, $fieldLength);
            }
        } else {
            if ($isRequired) {
                $this->errorArray[$fieldName] = $errorFriendlyName . " is required.";
            }
        }

        return $field;
    }

    private function validateFormData() {
        // User data field validation and error message generation.

        $this->firstName = $this->generalFieldValidation($this->firstName, "firstName", "First name", 40);
        $this->lastName = $this->generalFieldValidation($this->lastName, "lastName", "Last name", 40);
        $this->email = $this->generalFieldValidation($this->email, "email", "E-mail", 60);
        $this->address = $this->generalFieldValidation($this->address, "address", "Address", 255);
        $this->city = $this->generalFieldValidation($this->city, "city", "City", 60);
        $this->state = $this->generalFieldValidation($this->state, "state", "State", 2);
        $this->zip = $this->generalFieldValidation($this->zip, "zip", "Zip code", 5);
        $this->phone = $this->generalFieldValidation($this->phone, "phone", "Phone number", 10);
        $this->notes = $this->generalFieldValidation($this->notes, "notes", "Notes", -1, false);
        if ($this->phone !== "") {
            if (!is_numeric($this->phone)) {
                $this->errorArray["phone"] = "Phone number may only contain digits.";
            } else {
                if (strlen($this->phone) < 10) {
                    $this->errorArray["phone"] = "Phone number must contain 10 digits.";
                }
            }
        }
        if ($this->zip !== "") {
            if (!is_numeric($this->zip)) {
                $this->errorArray["zip"] = "Zip code may only contain digits.";
            } else {
                if (strlen($this->zip) !== 5) {
                    $this->errorArray["zip"] = "Five digit zip code required.";
                }
            }
        }
        if ($this->email !== "") {
            if (!preg_match("/^\S+@\S+\.\S+$/", $this->email)) {
                $this->errorArray["email"] = "E-mail address is not valid.";
            }
        }
        if (count($this->errorArray) === 0) {
            $this->passedValidation = true;
        }
    }

    /**
    * Assuming all data validation has passed, insert a new customer record or update an existing one.
    *
    **/

    private function saveData() {

        $dbConnection = dbconn::getConnectionBuild()->getConnection();
        date_default_timezone_set ("America/Chicago");

        if ($this->customerId == 0) {
            // Insert a new row.
            $sqlStmt = "INSERT INTO customer (first_name, last_name, address, city, state, zip, phone, email, ";
            $sqlStmt .= "notes, created_by, last_modified_by, last_modified) ";
            $sqlStmt .= "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ";

            $stmtObj = $dbConnection->prepare($sqlStmt);
            if ($dbConnection->error) {
                echo $dbConnection->error;
                exit();
            }

            $currentDate = date('Y-m-d H:i:s');
            $stmtObj->bind_param("sssssssssiis", $this->firstName, $this->lastName, $this->address, $this->city,
                            $this->state, $this->zip, $this->phone, $this->email, $this->notes, $this->employeeId,
                            $this->employeeId, $currentDate);
            $stmtObj->execute();
            if ($dbConnection->error) {
                echo $dbConnection->error;
                exit();
            }
            $this->customerId = $dbConnection->insert_id;
        } else {
            // Update existing row.
            $sqlStmt = "UPDATE customer SET first_name = ?, last_name = ?, address = ?, city = ?, ";
            $sqlStmt .= "state = ?, zip = ?, phone = ?, email = ?, notes = ?, last_modified_by = ?, last_modified = ? ";
            $sqlStmt .= "WHERE id = ? ";

            $currentDate = date('Y-m-d H:i:s');

            $stmtObj = $dbConnection->prepare($sqlStmt);
            $stmtObj->bind_param("sssssssssisi", $this->firstName, $this->lastName, $this->address, $this->city,
                            $this->state, $this->zip, $this->phone, $this->email, $this->notes, $this->employeeId,
                            $currentDate, $this->employeeId);
            $stmtObj->execute();
        }

        $this->statusMessage = "Save successful: " . date("h:i:s a");

    }


    public function loadPage($pageId) {
        // pageId is a string in the form of customerId_employeeId. customerId might be blank.
        if (substr($pageId, 0, 1) == "_") {
            $employeeId = substr($pageId, 1);
            $pageId = "";
        } else {
            $idArray = explode("_", $pageId);
            $pageId = $idArray[0];
            $employeeId = $idArray[1];
        }

        $this->employeeId = $employeeId;

        $dbConnection = dbconn::getConnectionBuild()->getConnection();

        if ($pageId != "") {
            // Load a specific customer.
            $sqlStmt = "SELECT id, first_name, last_name, address, city, state, zip, phone, email, notes ";
            $sqlStmt .= "FROM customer ";
            $sqlStmt .= "WHERE id = ? ";

            $stmtObj = $dbConnection->prepare($sqlStmt);
            $stmtObj->bind_param("i", $pageId);
            $stmtObj->execute();
            $stmtObj->bind_result($customer_id, $first_name, $last_name, $address, $city, $state, $zip, $phone, $email, $notes);
            while ($stmtObj->fetch()) {
                $this->customerId = $customer_id;
                $this->firstName = $first_name;
                $this->lastName = $last_name;
                $this->address = $address;
                $this->city = $city;
                $this->state = $state;
                $this->zip = $zip;
                $this->phone = $phone;
                $this->email = $email;
                $this->notes = $notes;
            }
        } else {

            foreach ($this->fieldArray as $fieldName) {
                if (isset($_POST[$fieldName])) {
                    $this->setField($fieldName, $_POST[$fieldName]);
                }
            }

        }

        if (isset($_POST['action'])) {
            if ($_POST['action'] == "save") {
                $this->validateFormData();
                if ($this->passedValidation) {
                    $this->saveData();
                }
            }
        }

        $sqlStmt = "";

        if (isset($_POST['listby'])) {
            $searchFName = "";
            $searchLName = "";
            if (isset($_POST['searchFName'])) {
                $searchFName = $_POST['searchFName'];
                if ($searchFName != '') {
                    $searchFName = '%' . $searchFName . '%';
                }
            }
            if (isset($_POST['searchLName'])) {
                $searchLName = $_POST['searchLName'];
                if ($searchLName != '') {
                    $searchLName = '%' . $searchLName . '%';
                }
            }

            if ($searchFName || $searchLName) {

                $bindParam = "";
                $sqlStmt = "SELECT id, first_name, last_name, phone, email ";
                $sqlStmt .= "FROM customer ";
                $sqlStmt .= "WHERE 1 = 1 ";
                if ($searchFName) {
                    $sqlStmt .= "AND first_name LIKE ? ";
                    $bindParam .= "s";
                }

                if ($searchLName) {
                    $sqlStmt .= "AND last_name LIKE ? ";
                    $bindParam .= "s";
                }
                $sqlStmt .= "ORDER BY last_name";

                $stmtObj = $dbConnection->prepare($sqlStmt);
                if (strlen($bindParam) == 1) {
                    $searchStr = $searchFName ? $searchFName : $searchLName;
                    $stmtObj->bind_param($bindParam, $searchStr);
                } else {
                    $stmtObj->bind_param($bindParam, $searchFName, $searchLName);
                }
            }

        }

        // List customers

        if (!$sqlStmt) {
            $sqlStmt = "SELECT id, first_name, last_name, phone, email ";
            $sqlStmt .= "FROM customer ";
            $sqlStmt .= "ORDER BY last_name LIMIT 15 ";

            $stmtObj = $dbConnection->prepare($sqlStmt);
        }


        $stmtObj->execute();
        $stmtObj->bind_result($customer_id, $first_name, $last_name, $phone, $email);
        $rowCount = 0;
        while ($stmtObj->fetch()) {

            if ($rowCount++ % 2 == 0) {
                $thisClass = "searchresults";
            } else {
                $thisClass = "altsearchresults";
            }

            $listingHtml = '<td class="' . $thisClass . '"><a href="index.php?page=customers&cid=' . $customer_id . '_' . $employeeId . '">';
            $listingHtml .= $last_name . ', ' . $first_name . '</a></td>';
            $listingHtml .= '<td class="' . $thisClass . '">' . displayPhone($phone) . '</td>';
            $listingHtml .= '<td class="' . $thisClass . '">' . $email . '</td>';

            $this->customerList[$customer_id] = $listingHtml;
        }
    }
}
?>
