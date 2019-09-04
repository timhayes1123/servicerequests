<?php
/**
* Class to handle all aspects of the timesheet Reports.
*
**/
class tsreport {
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
    private $totalMinutesWorked = array();
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

    private function dropDown($ddName, $defaultId, $descriptField, $table, $addBlank = false) {
        $dbConnection = dbconn::getConnectionBuild()->getConnection();

        $ddHTML = '<select id="' . $ddName . '" name="' . $ddName . '" size="1" >';
        $selected = "";

        $sqlStmt = "SELECT id, " . $descriptField . " FROM " . $table . " ORDER BY " . $descriptField . "; ";

        $stmtObj = $dbConnection->prepare($sqlStmt);
        $stmtObj->execute();
        $stmtObj->bind_result($id, $displayValue);
        $rowCount = 1;

        if ($addBlank) {
            $ddHTML .= '<option value="-1" selected="selected">(All)</option>';
            $defaultId = -1;
        }

        while ($stmtObj->fetch()) {
            if (empty($defaultId)) {
                // No default is selected so put the selected attribute in the first row.
                if ($rowCount == 1) {
                    $selected = 'selected="selected"';
                } else {
                    $selected = "";
                }
            } else {
                // A default value was passed in so put the selected attribute in the appropriate row.
                if ($id == $defaultId) {
                    $selected = 'selected="selected"';
                } else {
                    $selected = "";
                }
            }
            $rowCount++;
            $ddHTML .= '<option value="' . $id . '" ' . $selected . '>' . $displayValue . '</option>';
        }


        $ddHTML .= '</select>';
        return $ddHTML;
    }


    public function displayAsHTML() {
        $propertyDDHtml = $this->dropDown("propertyId", "", "address", "property", true);
        $employeeDDHtml = $this->dropDown("employeeId", "", "username", "employee", true);
        $outputHtml = <<<EOT
        <form action="index.php?page=tsreport" method="post">
            <input type="hidden" name="action" value="retrieve" />
            <table width="90%"><tr><td><div class="instructions" id="instructionText"></div></td></tr></table>
            <div class="dataentry">
            <table width="90%">
                <tr>
                    <td><p class="pageedit"><strong>Start Date</strong></p></td>
                    <td><p class="pageedit"><strong>End Date</strong></p></td>
                    <td><p class="pageedit"><strong>Employee</strong></p></td>
                    <td><p class="pageedit"><strong>Property</strong></p></td>
                </tr>
                <tr>
                    <td width="25%"><input type="text" maxlength="10" size="10" name="startDate" id="startDate" value="" onfocus="tsHelpText(event, true);" onblur="tsHelpText(event, false);" /></td>
                    <td width="25%"><input type="text" maxlength="10" size="10" name="endDate" id="endDate" value="" onfocus="tsHelpText(event, true);" onblur="tsHelpText(event, false);" /></td>
                    <td width="25%">$employeeDDHtml</td>
                    <td width="25%">$propertyDDHtml</td>
                </tr>
                <tr>
                    <td colspan="4"><input type="submit" value="Get Report" /></td>
                </tr>
            </table>
            </div>
        </form>
        <br><br>
EOT;


        $outputHtml .= <<<EOT
        <table class="searchresults">
            <tr>
                <td class="searchresultshdr" width="10%"><p class="searchresultsheader"><strong>Name</strong></p></td>
                <td class="searchresultshdr" width="5%"> <p class="searchresultsheader"><strong>Date</strong></p></td>
                <td class="searchresultshdr" width="15%"><p class="searchresultsheader"><strong>Address</strong></p></td>
                <td class="searchresultshdr" width="5%"> <p class="searchresultsheader"><strong>Zip</strong></p></td>
                <td class="searchresultshdr" width="20%"><p class="searchresultsheader"><strong>Job Description</strong></p></td>
                <td class="searchresultshdr" width="15%"><p class="searchresultsheader"><strong>Start Time</p></strong></td>
                <td class="searchresultshdr" width="15%"><p class="searchresultsheader"><strong>End Time</strong></p></td>
                <td class="searchresultshdr" width="5%"> <p class="searchresultsheader"><strong>Lunch</strong></p></td>
                <td class="searchresultshdr" width="10%"><p class="searchresultsheader"><strong>Time Worked</strong></p></td>
            </tr>
EOT;

        $thisEmpId = -1;
        foreach ($this->timeSheetList as $key => $tableRow) {
            $dataPos = strpos($tableRow, "reportrow_");
            $dataStr = substr($tableRow, $dataPos, 30);
            $pattern = '/value="\d"/';
            preg_match($pattern, $dataStr, $matches);

            $strArr = explode("=", $matches[0]);
            $dataStr = $strArr[1];
            $empId = substr($dataStr, 1, strlen($dataStr - 2));

            if ($thisEmpId != $empId && $thisEmpId != -1) {
                $outputHtml .= '<tr><td colspan="8">&nbsp;</td><td><input class="timetotal" type="text" id="rowtotal_' . $thisEmpId . ' value="" /></td></tr>';
                $thisEmpId = $empId;
            } else if ($thisEmpId != $empId) {
                $thisEmpId = $empId;
            }

            $outputHtml .= '<tr>' . $tableRow . '</tr>';
        }

        if ($thisEmpId != -1) {
            $outputHtml .= '<tr><td colspan="8">&nbsp;</td><td><input class="timetotal" type="text" id="rowtotal_' . $thisEmpId . ' value="" /></td></tr>';
            $outputHtml .= '<tr><td colspan="9"></td></tr>';
        }

        $outputHtml .= "</table>";

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


    private function slashDate($thisDate) {
        $dateArray = explode(" ", $thisDate);
        $hyphenFormat = $dateArray[0];
        $dateArray = explode("-", $hyphenFormat);
        return $dateArray[1] . "/" . $dateArray[2] . "/" . $dateArray[0];
    }


    public function loadPage($pageId) {

        $this->employeeId = $pageId;

        $dbConnection = dbconn::getConnectionBuild()->getConnection();

        if (isset($_POST['action'])) {
            if ($_POST['action'] == "retrieve") {

                $startDate = "";
                $endDate = "";
                $dateCriteria = false;
                $propertyCriteria = false;
                $employeeCriteria = false;

                if (isset($_POST['startDate'])) {
                    $startDate = $_POST['startDate'];
                }
                if (isset($_POST['endDate'])) {
                    $endDate = $_POST['endDate'];
                }

                if ($startDate && !$endDate) {
                    $endDate = $startDate;
                }

                if (!$startDate && $endDate) {
                    $startDate = $endDate;
                }

                if ($startDate && $endDate) {
                    $startDate = $this->parseDate($startDate);
                    $endDate = $this->parseDate($endDate);
                }


                if ($startDate || $endDate) {
                    $dateCriteria = true;
                }

                if (isset($_POST['propertyId'])) {
                    $propertyId = $_POST['propertyId'];
                    if ($propertyId != -1) {
                        $propertyCriteria = true;
                    }
                }

                if (isset($_POST['employeeId'])) {
                    $employeeId = $_POST['employeeId'];
                    if ($employeeId != -1) {
                        $employeeCriteria = true;
                    }
                }

                if ($dateCriteria) {
                    $startDate .= " 00:00:00";
                    $endDate .= " 23:59:59";
                }

                $sqlStmt = "SELECT ts.id, ts.date_worked, ts.property_id, p.address, p.zip, ts.job_description, ts.start_time, ts.end_time, ";
                $sqlStmt .= " ts.lunch, TIMESTAMPDIFF(MINUTE, ts.start_time, ts.end_time), ts.employee_id, e.first_name, e.last_name ";
                $sqlStmt .= "FROM time_sheet ts, property p, employee e ";

                $sqlStmt .= "WHERE ts.property_id = p.id ";
                $sqlStmt .= "AND ts.employee_id = e.id ";

                if ($dateCriteria) {
                    $sqlStmt .= "AND ts.date_worked BETWEEN '" . $startDate . "' AND '" . $endDate . "' ";
                }

                if ($employeeCriteria) {
                    $sqlStmt .= "AND ts.employee_id = " . $employeeId . " ";
                }

                if ($propertyCriteria) {
                    $sqlStmt .= "AND ts.property_id = " . $propertyId . " ";
                }

                $sqlStmt .= "ORDER BY ts.employee_id, ts.date_worked, p.address";

                // echo $sqlStmt;
                $stmtObj = $dbConnection->prepare($sqlStmt);

                $stmtObj->execute();
                $stmtObj->bind_result($timesheet_id, $date_worked, $property_id, $property_address, $property_zip, $job_descr, $start_time, $end_time, $lunch, $minutes_worked, $employee_id, $f_name, $l_name);

                $rowCount = 0;


                while ($stmtObj->fetch()) {

                    if ($rowCount++ % 2 == 0) {
                        $thisClass = "searchresults";
                    } else {
                        $thisClass = "altsearchresults";
                    }

                    $listingHtml = '<td class="' . $thisClass . '">' . $l_name . ', ' . $f_name . '</td>';
                    $listingHtml .= '<td class="' . $thisClass . '"><input type="hidden" class="rowmarker" id="reportrow_' . $rowCount . '" value="' . $employee_id . '" >';
                    $listingHtml .= $this->slashDate($date_worked) . '</td>';
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
                    $listingHtml .= '<input type="hidden" id="minutesrow_' . $rowCount . '" value="' . $minutes_worked . '">';
                    $listingHtml .= $minutesWorked . '</td>';

                    $this->timeSheetList[$timesheet_id] = $listingHtml;
                }
            }
        }
    }
}
?>
