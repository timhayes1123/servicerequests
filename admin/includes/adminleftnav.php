<?php
/**
 *
 * Left navigation bar for the administration pages.
 *
**/

class adminleftnav {
    private $navArray;
    public $debug;
    
    public function __construct() {
       
    }

    public function __destruct() {
        // clean up here
    }

    public function displayAsHTML($isAdmin = false) {
		## Add each link to the navigation bar. Must include a page=pagename GET parameter.
		
        $htmlLink = new htmlElement("a", "", "navlink");
        $htmlLink->setContents("Timesheets");
        $htmlLink->addAttribute("href", "index.php?page=timesheets");
        
        $navHTML = $htmlLink->getHtml() . "<br>";
        
        $htmlLink = new htmlElement("a", "", "navlink");
        $htmlLink->setContents("Service Requests");
        $htmlLink->addAttribute("href", "index.php?page=servicerequests");

        $navHTML .= $htmlLink->getHtml() . "<br><hr><br>";
        
        $htmlLink = new htmlElement("a", "", "navlink");
        $htmlLink->setContents("Customers");
        $htmlLink->addAttribute("href", "index.php?page=customers");
        
        $navHTML .= $htmlLink->getHtml() . "<br>";
        
        $htmlLink = new htmlElement("a", "", "navlink");
        $htmlLink->setContents("Properties");
        $htmlLink->addAttribute("href", "index.php?page=properties");
        
        $navHTML .= $htmlLink->getHtml() . "<br>";
        
		### Add additional links for the administrator.
		
        if ($isAdmin) {
            $navHTML .= "<hr><br>";
            $htmlLink = new htmlElement("a", "", "navlink");
            $htmlLink->setContents("Timesheet Reports");
            $htmlLink->addAttribute("href", "index.php?page=tsreport");
            
            $navHTML .= $htmlLink->getHtml() . "<br>";
        }
        
        $htmlLink = new htmlElement("a", "", "navlink");
        $htmlLink->setContents("Logout");
        $htmlLink->addAttribute("href", "index.php?page=logout");
        
        $navHTML .= "<br>";
        $navHTML .= "<hr>";
        $navHTML .= "<br>";
        $navHTML .= $htmlLink->getHtml();

        $outputHTML = $navHTML;
        
        return $outputHTML;
    }

}

