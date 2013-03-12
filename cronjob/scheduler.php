<?php

// Check whether control2drayton set
// otherwise continue ... 

include 'settings.php';

echo "Start scheduler : " . date("d/m/y H.i:s", time()) . PHP_EOL;

// get contents of a file into a string
$filename = realpath(dirname(__FILE__) . "/control2drayton.txt");
$handle = fopen($filename, "r");
$contents = fread($handle, filesize($filename));
fclose($handle);

if ($contents == 1) {
    echo "Control back to Drayton" . PHP_EOL;
    control2drayton();
    exit;
}

$con = mysql_connect($db,$dbuser,$dbpasswd) or die("Cannot connect mysql".PHP_EOL);

if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }

// initially set heating off
$heatingStatus = 0;
$heatingTemp = 8;

//Temperature outside check .. if less than 5 degrees .. turn on 30 mins earlier?
//move through a list of preferred temps to get latest
//$listTemp = array('therm_temp','lou_temp', 'bed1_temp', 'out_temp');
$listTemp = array('lou_temp');

for ($i=0;$i<count($listTemp);$i++) {
    // we grab from emoncms
    if ($data = getEmonTemp($listTemp[$i])) {

    	while($rows = mysql_fetch_assoc($data)) {
    	    $currentTemp = $rows['thermTemp'];
        	print_r($rows).PHP_EOL;
        	if (count($rows) > 0) { break; }
    	}

    }
}

$schedule = getSchedule();
while($rows = mysql_fetch_assoc($schedule)) {
    $heatingStatus = 1;
    $heatingTemp = $rows['heatingTemp'];
    print_r($rows).PHP_EOL;
}
            
$holiday = getHoliday();
if( mysql_num_rows($holiday) == 3) {
    while($rows = mysql_fetch_assoc($holiday)) {
        $heatingStatus = 1;
        $heatingTemp = 8;
        print_r($rows).PHP_EOL;
    }
}

$override = getOverride();
while($rows = mysql_fetch_assoc($override)) {
    $heatingStatus = 1;
    $heatingTemp = $rows['heatingTemp'];
    print_r($rows).PHP_EOL;
}

if ($heatingStatus) {
        checkHeatingTemp();
    } else {
        echo "Heating not requested by any process". PHP_EOL;
    }

setHeating();

echo "Finish scheduler : " . date("d/m/y H.i:s", time()) . PHP_EOL;

/*
if (count($rows) > 1) { 
    // ignore schedule and override and set 24 hour to holiday temp
    $temp = $row['temp'];
    } else {

}
*/

// Main Functions

function getEmonTemp($name) {
    echo "checking temps" . PHP_EOL;
    $query = "SELECT name,
                     unix_timestamp(time) AS thermTime,
                     time,
                     value AS thermTemp 
                     FROM emoncms.feeds WHERE name = '".$name."' LIMIT 0,1";
    //echo $query . PHP_EOL;
    $result = mysql_query($query);
    //echo mysql_num_rows($result) . PHP_EOL;
    return $result;    
}

function getSchedule() {
    echo "checking schedule" . PHP_EOL;
    // Schedule
    //SELECT * FROM schedule WHERE 
    //          (timeOn < '06:17:00' ) 
    //          AND (timeOff > '06:17:00' ) 
    //          AND day = 5
    $date = (date('N')+1);
    if ($date > 7) {$date = 1;}
    $query = "SELECT    hour(timeOn) as hourOn, 
                		minute(timeOn) as minuteOn, 
                		hour(timeOff) as hourOff, 
                		minute(timeOff) as minuteOff,
                		day,
                		heatingOn,
                		heatingTemp,
                		waterOn
                FROM boiler.schedule WHERE 
                (timeOn < '".date('G').":".date('i').":00') 
                AND (timeOff > '".date('G').":".date('i').":00') 
                AND day = ".$date;  
    //echo $query.PHP_EOL;
    $result = mysql_query($query);
    
/*
    while($row = mysql_fetch_array($result)) {  
        print_r($row).PHP_EOL;
    }
*/
    return $result;
    
}

function getHoliday() {
    echo "checking holiday" . PHP_EOL;
    // Check holiday schedule
    $query = 'SELECT `key`, value FROM boiler.configuration 
                WHERE ((`key` = "holidayFrom" AND value < '.mktime().') 
                    OR (`key` = "holidayTo" AND value > '.mktime().')
                    OR (`key` = "holidayTemp" AND value <> 0))';
    //echo $query . PHP_EOL;
    $result = mysql_query($query);
    
/*
    if( mysql_num_rows($result) == 3) {
        while($rows = mysql_fetch_array($result)) {  
            print_r($rows).PHP_EOL;
        }
        return $rows; 
    }
*/
    return $result; 

}

function getOverride() {
    echo "checking override" . PHP_EOL;
    // Override
    // select * from override where date > 1361535136 and (date+length > 1361535136)
    // select UNIX_TIMESTAMP(date) as date, UNIX_TIMESTAMP(date+length) as datelength 
	//     from boiler.override 
	//     where UNIX_TIMESTAMP(date) < 1361547580 and (UNIX_TIMESTAMP(date+length) >
    $query = "SELECT    date as datestart,
                        (date + INTERVAL length MINUTE) as dateend,
                        length,
			heatingTemp
                FROM boiler.override WHERE 
                    enabled = 1
                    AND UNIX_TIMESTAMP(date) < ".mktime()." 
                    AND (UNIX_TIMESTAMP(date + INTERVAL length MINUTE) > ".mktime().")
                    ORDER BY id DESC LIMIT 1; ";
    //echo $query.PHP_EOL;
    $result = mysql_query($query) or die(mysql_error());

/*
    while($row = mysql_fetch_array($result)) {  
        print_r($row).PHP_EOL;
    }
*/
    return $result;
}

function checkHeatingTemp() {
    // check whether current temp < target temp    
    // NJB TODO
    global $heatingStatus, $heatingTemp, $currentTemp;
    
    if ($currentTemp < $heatingTemp) {
        echo $currentTemp . " less than ".$heatingTemp." - switch on" . PHP_EOL;
        $heatingStatus = 1;
    } else { 
        echo $currentTemp . " more than or equal to ".$heatingTemp." - switch off" . PHP_EOL;
        $heatingStatus = 0;
    }
}

function setHeating() {
    global $heatingStatus;
    if ($heatingStatus) {
        echo "Switch Heating On" . PHP_EOL;
        $heatingAction = setHeatingOn();
        echo $heatingAction. PHP_EOL;
    } else {
        echo "Switch Heating Off" . PHP_EOL;
        $heatingAction = setHeatingOff();
        echo $heatingAction. PHP_EOL;
    }
}

function setHeatingOn() {
    global $heatingPi;
    //heating on
    // call url
    $ch = curl_init();
    // set URL and other appropriate options
    curl_setopt($ch, CURLOPT_URL, 'http://'.$heatingPi.'/heating/heatingon.php');
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    // grab URL and pass it to the browser
    curl_exec($ch);
    // close cURL resource, and free up system resources
    curl_close($ch);
    
}

function setHeatingOff() {
    global $heatingPi;
    //heating on
    // call url
    $ch = curl_init();
    // set URL and other appropriate options
    curl_setopt($ch, CURLOPT_URL, 'http://'.$heatingPi.'/heating/heatingoff.php');
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);    
    // grab URL and pass it to the browser
    curl_exec($ch);
    // close cURL resource, and free up system resources
    curl_close($ch);
    
}

function control2drayton() {
    global $heatingPi;
    //heating on
    // call url
    $ch = curl_init();
    // set URL and other appropriate options
    curl_setopt($ch, CURLOPT_URL, 'http://'.$heatingPi.'/heating/control2drayton.php');
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    // grab URL and pass it to the browser
    curl_exec($ch);
    // close cURL resource, and free up system resources
    curl_close($ch);
    
}


