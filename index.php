<?php

## Page Created: 1/30/2015
##
## Primary admin page

include_once '../includes/stdlib.php';
include_once 'includes/adminleftnav.php';
include_once 'includes/cookie.php';
include_once 'includes/loginpage.php';
include_once 'includes/timesheet.php';
include_once 'includes/tsreport.php';
include_once 'includes/customerpage.php';
include_once 'includes/propertypage.php';

$errorTrace = "";
$pageToLoad = "";
$pageToLoadId = 0;
$employeeUserName = "";

if (isset($_GET['page'])) {
    $pageToLoad = $_GET['page'];
}


$cookieObj = new cookie("admin");

if ($pageToLoad === "logout") {
    $cookieObj->logOut();
    header('Location: index.php');
}

# Check for a valid session. Redirect to login if absent/expired.

if ($pageToLoad === "validateLogin") {
    $valid = false;
    $errorTrace = 1;
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $userName = $_POST['username'];
        $password = $_POST['password'];
        $valid = $cookieObj->checkCredentials($userName, $password);
    }

    if ($valid) {
        $pageToLoad = "admin";
    } else {
        $pageToLoad = "login";
    }
} else if (!$cookieObj->validateCookie()) {
    $errorTrace = 2;
    $pageToLoad = "login";
} else {
    $errorTrace = 3;
    if (!$pageToLoad) {
        $pageToLoad = "admin";
    }
}


switch ($pageToLoad) {
    case "customers" :
        $pageToLoadId = "";
        if (isset($_GET['cid'])) {
            $pageToLoadId = $_GET['cid'];
        }
        $pageToLoadId .= "_" . $cookieObj->getEmployeeId();
        $pageObj = new customerpage();
        $pageObj->loadPage($pageToLoadId);
        break;
    case "properties" :
        $pageToLoadId = "";
        if (isset($_GET['pid'])) {
            $pageToLoadId = $_GET['pid'];
        }
        $pageToLoadId .= "_" . $cookieObj->getEmployeeId();
        $pageObj = new propertypage();
        $pageObj->loadPage($pageToLoadId);
        break;
    
    case "timesheets" :
        $pageToLoadId = "";
        if (isset($_GET['tsid'])) {
            $pageToLoadId = $_GET['tsid'];
        }
        $pageToLoadId .= "_" . $cookieObj->getEmployeeId();
        $pageObj = new timesheetpage();
        $pageObj->loadPage($pageToLoadId);
        break;
    case "admin" :
        $pageObj = new loginpage();
        $pageObj->loadPage($cookieObj->getEmployeeId());
        break;
    case "tsreport" :
        if ($cookieObj->validateAdminCookie()) {
            $pageObj = new tsreport();
            $pageObj->loadPage($cookieObj->getEmployeeId());
            break;
        } // fall through if not admin.
    default:
        $pageObj = new loginpage();
        $pageObj->loadPage($pageToLoad);
}


$leftNavObj = new adminleftnav();

ob_clean();
?>
<!doctype html>
<meta charset="utf-8">
<html>
<head>
    <title>Prudent Home Services Admin Page</title>
    <link rel="stylesheet" href="layout_admin.css">
</head>
<body>
    <div id="contentcontainer">    
        <div id="header" class="layout">
<?php
            $userName = $cookieObj->getUserName();
            date_default_timezone_set ("America/Chicago");
            if ($userName) {
                echo '<p class="largefont">Logged in as: <strong>' . $userName . '</strong></p>';
                echo '<p class="largefont">Today\'s Date: ' . date("m/d/Y") . '</p>';
            }
?>
        </div>
        <div id="leftcontent" class="leftnav">
            <?php
                echo $leftNavObj->displayAsHTML($cookieObj->validateAdminCookie());
            ?>
        </div>
        <div id="maincontentpane">
            <?php echo $pageObj->displayAsHTML();?>
        </div>
    </div>
    <script src="scripts/admin.js"></script>
</body>
</html>
