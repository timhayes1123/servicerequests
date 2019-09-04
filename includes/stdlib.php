<?php

## Page Created: 9/28/2014
##
## Utility functions and the global database connection are created here.

require_once 'dbconn.php';

// Simple function to include a class file if it's missing. (Requires classname be equal to filename).
function __autoload($class) {
    include "$class.php";
}

// Different database connection info for dev and production environments.
$debugMode = false;


$dbConnection = dbconn::getConnectionBuild()->getConnection();

// Check connection
if ($dbConnection->connect_errno) {
    echo "Failed to connect to MySQL: " . $dbConnection->connect_error;
    exit();
}

/**
 *
 * Definition of htmlElement class. Class to help keep code free of chunks of HTML.
 *
 * tag: HTML Element tag.
 * attribArray: An associative array of attributes belonging to the element that will be written in the form of attribname="value". The attribute name
 *      is the key of the array.
 * contents: The string that goes between the opening and closing tags.
 * additionalString: For those rare random strings that don't fit the attribname="value" pattern but need to be included in the tag definition.
 *
 * METHODS
 * setContents(string): Sets the value of $this->contents to the string parameter.
 * addString(string): concatenates the string parameter to the value of additionalString. A leading and trailing space is added.
 * addAttribute(string, string): Adds the two parameters, attributename and attributevalue, to the attribArray.
 * string getHtml(): Returns the fully assembled HTML string.
**/

class htmlElement {
    private $tag;
    private $attribArray;
    private $contents;
    private $additionalString;

    public function __construct($tag, $id = "", $class = "") {
        $this->tag = $tag;
        $this->contents = '';
        $this->additionalString = "";
        $this->attribArray = [];
        if ($id) {
            $this->attribArray["id"] = $id;
        }
        if ($class) {
            $this->attribArray["class"] = $class;
        }
    }

    public function setContents($contents) {
        $this->contents = $contents;
    }

    public function addString($extraString) {
        $this->additionalString .= " " . $extraString . " ";
    }

    public function addAttribute($attribName, $attribValue) {
        $this->attribArray[$attribName] = $attribValue;
    }

    public function getHtml() {
        // Tags that don't use a closing tag are added to the noCloseArray. Their tag will end with a /> instead of </tagname>.
        $noCloseArray = array("img", "input");

        $outputHTML = '<' . $this->tag . ' ';
        foreach ($this->attribArray as $attrib => $value) {
            $outputHTML .= $attrib . '="' . $value . '" ';
        }
        if (in_array($this->tag, $noCloseArray)) {
            $outputHTML .= $this->additionalString . ' />';
        } else {
            $outputHTML .= $this->additionalString . ' >';
            $outputHTML .= $this->contents;
            $outputHTML .= '</' . $this->tag . '>';
        }
        return $outputHTML;
    }
}

/**
* Build an HTML select box populated with state names/abbreviations.
*
* @param string $ddName
* @param string $defaultState
* @param string $attribute
* @param string $attrValue
* @return string
**/

function stateDropDown($ddName, $defaultState, $attribute = "", $attrValue = "") {
    // create an HTML drop down select box for state abbreviations.
    $dbConnection = dbconn::getConnectionBuild()->getConnection();

    $ddHTML = '<select id="' . $ddName . '" name="' . $ddName . '" size="1" ';
    if ($attribute && $attrValue) {
        $ddHTML .= $attribute . '="' .$attrValue . '">' ;
    }
    $ddHTML .= '">';

    $resultSet = $dbConnection->query("SELECT abbr, state FROM states;");
    $rowCount = 1;

    while($row = $resultSet->fetch_assoc()) {
        if (empty($defaultState)) {
            // No default is selected so put the selected attribute in the first row.
            if ($rowCount == 1) {
                $selected = 'selected="selected"';
            } else {
                $selected = "";
            }
        } else {
            // A default value was passed in so put the selected attribute in the appropriate row.
            if ($row['abbr'] == $defaultState) {
                $selected = 'selected="selected"';
            } else {
                $selected = "";
            }
        }
        $rowCount++;
        $ddHTML .= '<option value="' . $row['abbr'] . '" ' . $selected . '>' . $row['state'] . '</option>';
    }

    $ddHTML .= '</select>';
    return $ddHTML;
} // End function stateDropDown()

/**
* Take a 10 digit string and return it in friendly format for display.
*
* @param string $phone
* @return string
**/
function displayPhone($phone) {
    if (strlen($phone) == 10) {
        return '(' . substr($phone, 0, 3) . ') ' . substr($phone, 3, 3) . '-' . substr($phone, 6);
    } else {
        return $phone;
    }
}
