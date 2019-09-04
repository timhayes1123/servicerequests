<?php
/**
* Class to handle all aspects of the timesheet UI.
*
**/

class timesheetpage {
    private $login = true;

    private $timeSheetId = 0;
    private $employeeId;
    private $propertyId;
    private $jobDescr = "";
    private $startTime = "";
    private $startAmPm = "";
    private $endAmPm = "";
    private $endTime = "";
    private $lunch = 0;
    private $jobDate = "";
    private $errorMessage = "";
    private $invoiceNbr = "";
    private $timeSheetList = array();

    public function __construct() {
    }

    public function __destruct() {
        // clean up here
    }


    public function displayAsText() {
        $outputHtml = "";
        return $outputHtml;
    }

    private function propertyDropDown($ddName, $defaultPropertyId, $addBlank = false) {
        $dbConnection = dbconn::getConnectionBuild()->getConnection();

        $ddHTML = '<select id="' . $ddName . '" name="' . $ddName . '" size="1" >';
        $selected = "";

        $sqlStmt = "SELECT id, address FROM property ORDER BY address; ";

        $stmtObj = $dbConnection->prepare($sqlStmt);
        $stmtObj->execute();
        $stmtObj->bind_result($property_id, $address);
        $rowCount = 1;

        if ($addBlank) {
            $ddHTML .= '<option value="" selected="selected">(None)</option>';
            $defaultPropertyId = -1;
        }

        while ($stmtObj->fetch()) {
            if (empty($defaultPropertyId)) {
                // No default is selected so put the selected attribute in the first row.
                if ($rowCount == 1) {
                    $selected = 'selected="selected"';
                } else {
                    $selected = "";
                }
            } else {
                // A default value was passed in so put the selected attribute in the appropriate row.
                if ($property_id == $defaultPropertyId) {
                    $selected = 'selected="selected"';
                } else {
                    $selected = "";
                }
            }
            $rowCount++;
            $ddHTML .= '<option value="' . $property_id . '" ' . $selected . '>' . $address . '</option>';
        }


        $ddHTML .= '</select>';
        return $ddHTML;
    }


    public function displayAsHTML() {
        $propertyDDHtml = $this->propertyDropDown("propertyId", $this->propertyId);
        $outputHtml = <<<EOT
        <form action="index.php?page=timesheets" method="post">
            <input type="hidden" name="action" value="save" />
            <input type="hidden" name="timeSheetId" value="$this->timeSheetId" />
            <table width="90%"><tr><td><div class="instructions" id="instructionText"></div></td></tr></table>
            <div class="dataentry">
            <table width="90%">
                <tr>
                    <td><p class="pageedit"><strong>Date</strong></p></td>
                    <td><p class="pageedit"><strong>Address</strong></p></td>
                    <td><p class="pageedit"><strong>Invoice Number</strong></p></td>
                    <td><p class="pageedit"><strong>Start Time</strong></p></td>
                    <td><p class="pageedit"><strong>End Time</strong></p></td>
                    <td><p class="pageedit"><strong>Lunch</p></strong></td>
                </tr>
                <tr>
                    <td width="10%"><input type="text" maxlength="10" size="10" name="jobDate" id="jobDate" value="$this->jobDate" onfocus="tsHelpText(event, true);" onblur="tsHelpText(event, false);" /></td>
                    <td width="40%">$propertyDDHtml</td>
                    <td width="10%"><input type="text" maxlength="20" size="10" name="invoiceNbr" id="invoiceNbr" value="$this->invoiceNbr" onfocus="tsHelpText(event, true);" onblur="tsHelpText(event, false);" /></td>
                    <td width="15%">
                        <input type="text" maxlength="5" size="5" name="startTime" id="startTime" value="$this->startTime" onfocus="tsHelpText(event, true);" onblur="tsHelpText(event, false);" />
                        <select id="startAmPm" name="startAmPm" size="1">
EOT;
        if ($this->startAmPm == "pm") {
            $outputHtml .= '<option value="am" >am</option>';
            $outputHtml .= '<option value="pm" selected="selected">pm</option>';
        } else {
            $outputHtml .= '<option value="am" selected="selected">am</option>';
            $outputHtml .= '<option value="pm" >pm</option>';
        }

        $outputHtml .= <<<EOT
                        </select>
                    </td>
                    <td width="15%">
                        <input type="text" maxlength="5" size="5" name="endTime" id="endTime" value="$this->endTime" onfocus="tsHelpText(event, true);" onblur="tsHelpText(event, false);" />
                        <select id="endAmPm" name="endAmPm" size="1">
EOT;
        if ($this->endAmPm == "pm") {
            $outputHtml .= '<option value="am" >am</option>';
            $outputHtml .= '<option value="pm" selected="selected">pm</option>';
        } else {
            $outputHtml .= '<option value="am" selected="selected">am</option>';
            $outputHtml .= '<option value="pm" >pm</option>';
        }
        $outputHtml .= <<<EOT
                        </select>
                    </td>
                    <td width="10%"><input type="text" maxlength="3" size="3" name="lunch" id="lunch" onfocus="tsHelpText(event, true);" onblur="tsHelpText(event, false);" onkeypress="return isDigit(event);" value="$this->lunch" /></td>
                </tr>
                <tr>
                    <td><p class="pageedit"><strong>Job:</strong></p></td>
                    <td colspan="5"><textarea name="jobDescr" id="jobDescr" cols="80" rows="2">$this->jobDescr</textarea></td>
                </tr>
                <tr><td colspan="6"><p class="error">$this->errorMessage</p></td></tr>
                <tr>
                    <td colspan="6"><input type="submit" value="Save" /><a href="index.php?page=timesheets"><input type="button" value="New" /></a></td>
                </tr>
            </table>
            </div>
        </form>
        <br><br>
EOT;


        $outputHtml .= <<<EOT
        <table class="searchresults">
            <tr>
                <td class="searchresultshdr" width="5%"> <p class="searchresultsheader"><strong>Date</strong></p></td>
                <td class="searchresultshdr" width="20%"><p class="searchresultsheader"><strong>Address</strong></p></td>
                <td class="searchresultshdr" width="5%"> <p class="searchresultsheader"><strong>Zip</strong></p></td>
                <td class="searchresultshdr" width="25%"><p class="searchresultsheader"><strong>Job Description</strong></p></td>
                <td class="searchresultshdr" width="15%"><p class="searchresultsheader"><strong>Start Time</p></strong></td>
                <td class="searchresultshdr" width="15%"><p class="searchresultsheader"><strong>End Time</strong></p></td>
                <td class="searchresultshdr" width="5%"> <p class="searchresultsheader"><strong>Lunch</strong></p></td>
                <td class="searchresultshdr" width="10%"><p class="searchresultsheader"><strong>Time Worked</strong></p></td>
            </tr>
EOT;

        foreach ($this->timeSheetList as $key => $tableRow) {
            $outputHtml .= '<tr>' . $tableRow . '</tr>';
        }

        $outputHtml .= <<<EOT
        </table>
        <form action="index.php?page=timesheets" method="post">
            <input type="hidden" name="listby" value="date" />
            <p class="pageedit"><strong>Get timesheet listing by date</strong></p>
            <p class="pageedit">Start Date: <input type="text" maxlength="10" size="10" name="startDate" id="startDate" onfocus="tsHelpText(event, true);" onblur="tsHelpText(event, false);" /></p>
            <p class="pageedit">End Date: <input type="text" maxlength="10" size="10" name="endDate" id="endDate" onfocus="tsHelpText(event, true);" onblur="tsHelpText(event, false);" /></p>
            <input type="submit" value="Search">
        </form>
EOT;

        return $outputHtml;
    }

    private function timeFormat($dateTimeStr) {
        $dateArray = explode(" ", $dateTimeStr);
        $time = $dateArray[1];
        $timeArray = explode(":", $time);
        $hrs = $timeArray[0];
        $mins = $timeArray[1];
        $formatArray = array();
        if ($hrs >= 12) {
            $formatArray["ampm"] = "pm";
            if ($hrs > 12) {
                $hrs -= 12;
            }
        } else {
            $formatArray["ampm"] = "am";
        }
        $formatArray["hours"] = $hrs;
        $formatArray["minutes"] = $mins;

        return $formatArray;
    }

    private function setField($fieldName, $fieldValue) {
        switch ($fieldName) {
            case "jobDate" :
                $this->jobDate = $fieldValue;
                break;
            case "propertyId" :
                $this->propertyId = $fieldValue;
                break;
            case "startTime" :
                $this->startTime = $fieldValue;
                break;
            case "endTime" :
                $this->endTime = $fieldValue;
                break;
            case "jobDescr" :
                $this->jobDescr = $fieldValue;
                break;
            case "invoiceNbr" :
                $this->invoiceNbr = $fieldValue;
                break;
        }
    }

    private function parseTime($thisTime, $thisDate, $amPm) {
        $pos = strpos($thisTime, ":");
        if ($pos === false) {
            $thisTime .= ":00";
        }
        $timeArray = explode(":", $thisTime);
        if (count($timeArray) == 2) {
            $hours = $timeArray[0];
            $mins = $timeArray[1];
            if (is_numeric($hours) && is_numeric($mins)) {
                if ($hours >= 1 && $hours <= 12 && $mins >= 0 && $mins <= 59) {
                    if ($amPm == "pm" && $hours != 12) {
                        $hours += 12;
                    }
                    return $thisDate . " " . $hours . ":" . $mins . ":" . "00";
                }
            }
        }
        return "";
    }

    private function parseDate($thisDate) {
        $dateArray = explode("/", $thisDate);
        if (count($dateArray) == 3) {
            $month = $dateArray[0];
            $day = $dateArray[1];
            $year = $dateArray[2];
            if (is_numeric($month) && is_numeric($day) && is_numeric($year)) {
                if ($year < 100) {
                    $year = $year + 2000;
                }
                $monthValid = false;
                $dayValid = false;
                if ($month >= 1 && $month <= 12) {
                    $monthValid = true;
                }
                if ($day >= 1 && $day <= 31) {
                    if ($day <= 29) {
                        $dayValid = true;
                    } else {
                        if ($month != 2) {
                            if ($day == 31) {
                                if ($month == 1 || $month == 3 || $month == 5 || $month == 7 || $month == 8 || $month == 10 || $month == 12) {
                                    $dayValid = true;
                                }
                            } else {
                                $dayValid = true;
                            }
                        }
                    }
                }

                if ($dayValid && $monthValid) {
                    return $year . "-" . $month . "-" . $day;
                }
            }
        }

        return "";
    }

    private function saveData() {

        $dbConnection = dbconn::getConnectionBuild()->getConnection();

        $fieldArray["jobDate"] = "date";
        $fieldArray["propertyId"] = "address";
        $fieldArray["startTime"] = "start time";
        $fieldArray["endTime"] = "end time";
        $fieldArray["jobDescr"] = "job description";
        $fieldArray["invoiceNbr"] = "invoice number";

        if (isset($_POST['jobDate']) && isset($_POST['propertyId']) && isset($_POST['startTime']) && isset($_POST['endTime']) && isset($_POST['jobDescr'])) {
            $this->timeSheetId = $_POST['timeSheetId'];

            foreach ($fieldArray as $fieldName => $label) {
                $this->setField($fieldName, $_POST[$fieldName]);
            }

            if (isset($_POST['lunch'])) {
                $this->lunch = $_POST['lunch'];
            } else {
                $this->lunch = 0;
            }

            $jobDate = $this->parseDate($this->jobDate);
            if ($jobDate) {
                $startTime = $this->parseTime($this->startTime, $jobDate, $_POST['startAmPm']);
                $endTime = $this->parseTime($this->endTime, $jobDate, $_POST['endAmPm']);
            }

            if ($jobDate && $startTime && $endTime) {
                if ($this->timeSheetId == 0) {
                    // Insert a new row.
                    $sqlStmt = "INSERT INTO time_sheet (employee_id, property_id, ";
                    $sqlStmt .= "job_description, start_time, end_time, lunch, date_worked, invoice_nbr) ";
                    $sqlStmt .= "VALUES (?, ?, ?, ?, ?, ?, ?, ?) ";

                    $stmtObj = $dbConnection->prepare($sqlStmt);
                    if ($dbConnection->error) {
                        echo $dbConnection->error;
                        exit();
                    }
                    $stmtObj->bind_param("iisssiss", $this->employeeId, $this->propertyId, $this->jobDescr, $startTime, $endTime, $this->lunch, $jobDate, $this->invoiceNbr);
                    $stmtObj->execute();
                    if ($dbConnection->error) {
                        echo $dbConnection->error;
                        exit();
                    }
                    $this->timeSheetId = $dbConnection->insert_id;
                } else {
                    // Update existing row.
                    $sqlStmt = "UPDATE time_sheet SET property_id = ?, job_description = ?, start_time = ?, end_time = ?, lunch = ?, date_worked = ?, invoice_nbr = ? ";
                    $sqlStmt .= "WHERE id = ? AND employee_id = ? ";

                    $stmtObj = $dbConnection->prepare($sqlStmt);
                    $stmtObj->bind_param("issssssii", $this->propertyId, $this->jobDescr, $startTime, $endTime, $this->lunch, $jobDate, $this->invoiceNbr, $this->timeSheetId, $this->employeeId);
                    $stmtObj->execute();
                }
                date_default_timezone_set ("America/Chicago");
                $this->errorMessage = "Save successful: " . date("h:i:s a");
            } else {
                $this->errorMessage = "Unable to save. Date and/or time field contain invalid values.";
            }
        } else {
            $errorArray = array();
            foreach ($fieldArray as $fieldName => $label) {
                if (isset($_POST[$fieldName])) {
                    $this->setField($fieldName, $_POST[$fieldName]);
                } else {
                    $errorArray[] = "date";
                }
            }

            $this->errorMessage = "Unable to save. Field(s) ";
            foreach ($errorArray as $errorField) {
                $this->errorMessage .= $errorField . ", ";
            }
            $this->errorMessage .= "must contain valid data.";
        }
    }

    private function slashDate($thisDate) {
        $dateArray = explode(" ", $thisDate);
        $hyphenFormat = $dateArray[0];
        $dateArray = explode("-", $hyphenFormat);
        return $dateArray[1] . "/" . $dateArray[2] . "/" . $dateArray[0];
    }


    public function loadPage($pageId) {
        // pageId is a string in the form of timesheetId_employeeId. timesheetId might be blank.
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

        if (isset($_POST['action'])) {
            if ($_POST['action'] == "save") {
                $this->saveData();
                if ($this->timeSheetId) {
                    $pageId = $this->timeSheetId;
                }
            }
        }

        if ($pageId != "") {
            // Load a specific timesheet.
            $sqlStmt = "SELECT id, property_id, job_description, date_worked, start_time, end_time, lunch, invoice_nbr ";
            $sqlStmt .= "FROM time_sheet ";
            $sqlStmt .= "WHERE employee_id = ? ";
            $sqlStmt .= "AND id = ? ";

            $stmtObj = $dbConnection->prepare($sqlStmt);
            $stmtObj->bind_param("ii", $employeeId, $pageId);
            $stmtObj->execute();
            $stmtObj->bind_result($timesheet_id, $property_id, $job_descr, $date_worked, $start_time, $end_time, $lunch, $invoice_nbr);
            while ($stmtObj->fetch()) {
                $this->timeSheetId = $pageId;
                $this->propertyId = $property_id;
                $this->jobDescr = $job_descr;
                if ($start_time) {
                    $timeArray = $this->timeFormat($start_time);
                    $this->startTime = $timeArray["hours"] . ":" . $timeArray["minutes"];
                    $this->startAmPm = $timeArray["ampm"];
                } else {
                    $this->startTime = "";
                    $this->startAmPm = "am";
                }

                if ($end_time) {
                    $timeArray = $this->timeFormat($end_time);
                    $this->endTime = $timeArray["hours"] . ":" . $timeArray["minutes"];
                    $this->endAmPm = $timeArray["ampm"];
                } else {
                    $this->endTime = "";
                    $this->endAmPm = "am";
                }


                $this->lunch = $lunch;
                if ($date_worked) {
                    $this->jobDate = $this->slashDate($date_worked);
                } else {
                    $this->jobDate = "";
                }

                $this->invoiceNbr = $invoice_nbr;
            }

        }

        $sqlStmt = "";

        if (isset($_POST['listby'])) {
            $startDate = "";
            $endDate = "";

            if (isset($_POST['startDate'])) {
                $startDate = $_POST['startDate'];
            }
            if (isset($_POST['endDate'])) {
                $endDate = $_POST['endDate'];
            }
            if ($startDate && $endDate) {
                $startDate = $this->parseDate($startDate);
                $endDate = $this->parseDate($endDate);
            }

            if ($startDate && $endDate) {
                $startDate .= " 00:00:00";
                $endDate .= " 23:59:59";

                $sqlStmt = "SELECT ts.id, ts.date_worked, ts.property_id, p.address, p.zip, ts.job_description, ts.start_time, ts.end_time, ts.lunch, TIMESTAMPDIFF(MINUTE, ts.start_time, ts.end_time) ";
                $sqlStmt .= "FROM time_sheet ts, property p ";
                $sqlStmt .= "WHERE ts.employee_id = ? ";
                $sqlStmt .= "AND ts.property_id = p.id ";
                $sqlStmt .= "AND ts.date_worked BETWEEN ? AND ? ";
                $sqlStmt .= "ORDER BY ts.date_worked";

                $stmtObj = $dbConnection->prepare($sqlStmt);
                $stmtObj->bind_param("iss", $employeeId, $startDate, $endDate);
            }

        }

        // List available timesheets.

        if (!$sqlStmt) {
            $sqlStmt = "SELECT ts.id, ts.date_worked, ts.property_id, p.address, p.zip, ts.job_description, ts.start_time, ts.end_time, ts.lunch, TIMESTAMPDIFF(MINUTE, ts.start_time, ts.end_time) ";
            $sqlStmt .= "FROM time_sheet ts, property p ";
            $sqlStmt .= "WHERE ts.employee_id = ? ";
            $sqlStmt .= "AND ts.property_id = p.id ";
            $sqlStmt .= "ORDER BY date_worked DESC LIMIT 15 ";

            $stmtObj = $dbConnection->prepare($sqlStmt);
            $stmtObj->bind_param("i", $employeeId);
        }


        $stmtObj->execute();
        $stmtObj->bind_result($timesheet_id, $date_worked, $property_id, $property_address, $property_zip, $job_descr, $start_time, $end_time, $lunch, $minutes_worked);

        $rowCount = 0;
        while ($stmtObj->fetch()) {
            if ($rowCount++ % 2 == 0) {
                $thisClass = "searchresults";
            } else {
                $thisClass = "altsearchresults";
            }
            $listingHtml = '<td class="' . $thisClass . '"><a href="index.php?page=timesheets&tsid=' . $timesheet_id . '_' . $employeeId . '">';
            $listingHtml .= $this->slashDate($date_worked) . '</a></td>';
            $listingHtml .= '<td class="' . $thisClass . '">' . $property_address . '</td>';
            $listingHtml .= '<td class="' . $thisClass . '">' . $property_zip . '</td>';
            $listingHtml .= '<td class="' . $thisClass . '">' . $job_descr . '</td>';
            $startTimeArr = $this->timeFormat($start_time);
            $endTimeArr = $this->timeFormat($end_time);
            $listingHtml .= '<td class="' . $thisClass . '">' . $startTimeArr["hours"] . ':' . $startTimeArr["minutes"] . ' ' . $startTimeArr["ampm"] . '</td>';
            $listingHtml .= '<td class="' . $thisClass . '">' . $endTimeArr["hours"] . ':' . $endTimeArr["minutes"] . ' ' . $endTimeArr["ampm"] . '</td>';
            $listingHtml .= '<td class="' . $thisClass . '">' . $lunch . '</td>';
            $minutes_worked -= $lunch;
            $hoursWorked = $minutes_worked / 60;
            $hoursWorked = (int) $hoursWorked;
            $minutesWorked = $minutes_worked % 60;

            $listingHtml .= '<td class="' . $thisClass . '">' . $hoursWorked . ":";
            if (strlen($minutesWorked) == 1) {
                $listingHtml .= "0";
            }
            $listingHtml .= $minutesWorked . '</td>';

            $this->timeSheetList[$timesheet_id] = $listingHtml;
        }
    }
}
?>
