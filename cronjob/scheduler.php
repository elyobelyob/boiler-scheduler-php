<?php

// Check whether control2drayton set
// otherwise continue ... 

include 'settings.php';
$dayNames = array( '','Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );

echo "Start scheduler : " . date("d/m/y H.i:s", time()) . PHP_EOL;

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
        	echo $rows['name']. ' -> '.$rows['thermTemp'].PHP_EOL;
        	if (count($rows) > 0) { break; }
    	}

    }
}

$schedule = getSchedule();
while($rows = mysql_fetch_assoc($schedule)) {
    $heatingStatus = 1;
    $heatingTemp = $rows['heatingTemp'];
    echo $dayNames[$rows['day']]." ".$rows['timeOn']." -> ".$rows['timeOff']." ".$rows['heatingTemp']."c".PHP_EOL;
}
            
$holiday = getHoliday();
if( mysql_num_rows($holiday) == 3) {
    while($rows = mysql_fetch_assoc($holiday)) {
        $heatingStatus = 1;
        $heatingTemp = 8;
        foreach ($rows as $key => $value) {
            echo $key." ".$value.PHP_EOL;
        }
    }
}

$override = getOverride();
while($rows = mysql_fetch_assoc($override)) {
    $heatingStatus = 1;
    $heatingTemp = $rows['heatingTemp'];
        echo $rows['datestart'];
        echo " -> ";
        echo $rows['dateend'].PHP_EOL;
        echo $rows['duration']." ";
        echo $rows['heatingTemp'].PHP_EOL;

}

if ($heatingStatus) {
        checkHeatingTemp();
    } else {
        echo "Heating not requested by any process". PHP_EOL;
    }

setHeating();

echo "Finish scheduler : " . date("d/m/y H.i:s", time()) . PHP_EOL;

// Main Functions

function getEmonTemp($name) {
    echo "<b>checking temps</b>" . PHP_EOL;
    $query = "SELECT name,
                     time,
                     value AS thermTemp 
                     FROM emoncms.feeds WHERE name = '".$name."' LIMIT 0,1";
    //echo $query . PHP_EOL;
    $result = mysql_query($query);
    return $result;    
}

function getSchedule() {
    echo "<b>checking schedule</b>" . PHP_EOL;
    // Schedule
    //SELECT * FROM schedule WHERE 
    //          (timeOn < '06:17:00' ) 
    //          AND (timeOff > '06:17:00' ) 
    //          AND day = 5
    
    $dayOfWeek = (date('N')+1);
    if ($dayOfWeek > 7) { $dayOfWeek = 1; }
    
    $query = "SELECT    timeOn,
                        timeOff,
                		day,
                		heatingOn,
                		heatingTemp,
                		waterOn
                FROM boiler.schedule WHERE 
                enabled = 1 
                AND (timeOn < '".date('G').":".date('i').":00') 
                AND (timeOff > '".date('G').":".date('i').":00') 
                AND day = ".$dayOfWeek;
    //cho $query.PHP_EOL;
    $result = mysql_query($query);
    
/*
    while($row = mysql_fetch_array($result)) {  
        print_r($row).PHP_EOL;
    }
*/
    return $result;
    
}

function getHoliday() {
    echo "<b>checking holiday</b>" . PHP_EOL;
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
    echo "<b>checking override</b>" . PHP_EOL;
    // Override
    // select * from override where date > 1361535136 and (date+length > 1361535136)
    // select UNIX_TIMESTAMP(date) as date, UNIX_TIMESTAMP(date+length) as datelength 
	//     from boiler.override 
	//     where UNIX_TIMESTAMP(date) < 1361547580 and (UNIX_TIMESTAMP(date+length) >
    $query = "SELECT date as datestart,
                    (date + INTERVAL duration MINUTE) as dateend,
                    duration,
                    heatingTemp
                FROM boiler.override WHERE 
                    enabled = 1
                    AND UNIX_TIMESTAMP(date) < ".mktime()." 
                    AND (UNIX_TIMESTAMP(date + INTERVAL duration MINUTE) > ".mktime().")
                    ORDER BY id DESC LIMIT 1; ";
    //echo $query.PHP_EOL;
    $result = mysql_query($query) or die(mysql_error());

/*
    while($row = mysql_fetch_array($result)) {  
	echo $row['datestart'];
	echo " -> ";
	echo $row['dateend'].PHP_EOL;
	echo $row['duration']." ";
	echo $row['heatingTemp'].PHP_EOL;
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
        echo "<b>Switch Heating On</b>" . PHP_EOL;
        $heatingAction = setHeatingOn();
        logLastAction(1);
        echo $heatingAction. PHP_EOL;
    } else {
        echo "<b>Switch Heating Off</b>" . PHP_EOL;
        $heatingAction = setHeatingOff();
        logLastAction(0);
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
    //heating off
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
    //heating control back to backup
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

function logLastAction($status) {

    global $heatingStatus, $heatingTemp, $currentTemp;

    $query = "SELECT * FROM boiler.boiler_history ORDER BY id DESC LIMIT 1";
    $result = mysql_query($query);
    $lastinput = mysql_fetch_array($result);
        
    if ($lastinput['status'] != $status) {
        $query = "INSERT INTO boiler.boiler_history (status,datetime,temp,node) VALUES ($status,NOW(),$currentTemp,'lou_temp')";
        //echo $query . PHP_EOL;
        $result = mysql_query($query);
    }
}
