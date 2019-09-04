window.onload = scriptInit;

function scriptInit() {
    // Keep the hours worked and minutes worked values updated based on the currently entered values.
    if (document.getElementsByClassName("timetotal")) {
        fieldCollection = document.getElementsByClassName("timetotal");
        for (loopIndex = 0; loopIndex < fieldCollection.length; loopIndex++) {
            currentTotal = 0;
            resultArr = fieldCollection[loopIndex].id.split("_");
            empId = resultArr[1];
            markerRows = document.getElementsByClassName("rowmarker");
            for (innerIndex = 0; innerIndex < markerRows.length; innerIndex++) {
                if (markerRows[innerIndex].value == empId) {
                    thisId = markerRows[innerIndex].id;
                    nameArr = thisId.split("_");
                    rowNum = nameArr[1];
                    targetField = "minutesrow_" + rowNum;
                    currentTotal += Number(document.getElementById(targetField).value);
                }
            }

            fieldToUpdate = "rowtotal_" + empId;
            hoursWorked = "" + currentTotal / 60;
            decPos = hoursWorked.indexOf(".");
            if (decPos != -1) {
                hoursWorked = hoursWorked.substr(0, decPos);
            }

            minutesWorked = "" + currentTotal % 60;
            if (minutesWorked.length == 1) {
                minutesWorked = "0" + minutesWorked;
            }
            document.getElementById(fieldToUpdate).value = hoursWorked + ":" + minutesWorked;
        }
    }
}


function isDigit(eventObj) {

    // Return false for all non-digit characters passed in the event.

    var charCode = eventObj.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true;
}

function tsHelpText(eventObj, activateHelp) {
    //document.getElementById("instructionText").innerHTML = eventObj.target.name;
    fieldName = eventObj.target.name;
    instructionText = "";

    if (activateHelp) {
        switch (fieldName) {
            case "jobDate" :
            case "startDate" :
            case "endDate" :        instructionText = "Enter date in the format m/d/yyyy or m/d/yy";
                                    break;
            case "invoiceNbr" :     instructionText = "Enter if an invoice exists. Leave blank for no invoice.";
                                    break;
            case "startTime" :
            case "endTime" :        instructionText = "Enter time in the format hh:mm";
                                    break;
            case "lunch" :          instructionText = "Enter lunch as the number of minutes taken";
                                    break;
        }
    }

    document.getElementById("instructionText").innerHTML = instructionText;
}

function prHelpText(eventObj, activateHelp) {
    //document.getElementById("instructionText").innerHTML = eventObj.target.name;
    fieldName = eventObj.target.name;
    instructionText = "";

    if (activateHelp) {
        switch (fieldName) {
            case "firstName" :
            case "lastName" :
            case "phone" :
            case "email" :          instructionText = "Leave blank if tenant information is the same as customer information.";
                                    break;

        }
    }

    document.getElementById("instructionText").innerHTML = instructionText;
}
