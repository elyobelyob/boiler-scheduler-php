<?php

// Check whether control2drayton set
// otherwise continue ... 


$con = mysql_connect("192.168.121.132","dbuser","dbuser123") or die("Cannot connect mysql".PHP_EOL);

if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }

$currentTime = date('m/d/Y h:i:s a');

//Temperature outside check .. if less than 5 degrees .. turn on 30 mins earlier?
//move through a list of preferred temps to get latest
$listTemp = array('therm_temp','lou_temp', 'bed1_temp');

for ($i=0;$i<count($listTemp);$i++) {
    // we grab from emoncms
    getEmonTemp($listTemp[$i]);
}

$data = getHoliday();
if( mysql_num_rows($data) == 3) {
    while($rows = mysql_fetch_array($data)) {  
        print_r($rows).PHP_EOL;
    }
    return $rows; 
}

$schedule = getSchedule();
            
$override = getOverride();


if (count($rows) > 1) { 
    // ignore schedule and override and set 24 hour to holiday temp
    $temp = $row['temp'];
    } else {


}

// NJB TODO
$currentTemp = 15;
$temp = 15;

if ($currentTemp < $temp) {
    heatingOn();
} else { 
    heatingOff();
}

function getEmonTemp($name) {
    $query = "SELECT unix_timestamp(time) AS thermTime, 
                     value AS thermTemp 
                     FROM emoncms.feeds WHERE name = '".$name."' LIMIT 0,1";
    $result = mysql_query($query);
    //echo $query . PHP_EOL;
    //print_r(mysql_error());
    
    while($row = mysql_fetch_array($result)) {  
        $thermTemp = $row['thermTemp'];
        $thermTime = $row['thermTime'];
        //print_r($row).PHP_EOL;

        // time is old, then perhaps out of batteries?
        if ($thermTime < (time()-100) ) {
            return false;
        } else {
            return $thermTemp;
        }
    }
}

function getHoliday() {
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

function getSchedule() {
    // Schedule
    //SELECT * FROM schedule WHERE 
    //          (timeOn < '06:17:00' ) 
    //          AND (timeOff > '06:17:00' ) 
    //          AND day = 5
                    
    $query = "SELECT    hour(timeOn) as hourOn, 
                		minute(timeOn) as minuteOn, 
                		hour(timeOff) as hourOff, 
                		minute(timeOff) as minuteOff  
                FROM boiler.schedule WHERE 
                (timeOn < '".date('G').":".date('i').":00') 
                AND (timeOff > '".date('G').":".date('i').":00') 
                AND day = ".date('N');
    //echo $query.PHP_EOL;
    $result = mysql_query($query);
    
/*
    while($row = mysql_fetch_array($result)) {  
        print_r($row).PHP_EOL;
    }
*/
    return $result;
    
}

function getOverride() {
    // Override
    // select * from override where date > 1361535136 and (date+length > 1361535136)
    // select UNIX_TIMESTAMP(date) as date, UNIX_TIMESTAMP(date+length) as datelength 
	//     from boiler.override 
	//     where UNIX_TIMESTAMP(date) < 1361547580 and (UNIX_TIMESTAMP(date+length) >
    $query = "SELECT    UNIX_TIMESTAMP(date) as dateout, 
                        UNIX_TIMESTAMP(date + INTERVAL length MINUTE) as datelength, 
                        type,
                        date,
                        length 
                FROM boiler.override WHERE 
                    UNIX_TIMESTAMP(date) < ".mktime()." 
                    AND (UNIX_TIMESTAMP(date + INTERVAL length MINUTE) > ".mktime().")";
    //echo $query.PHP_EOL;
    $result = mysql_query($query) or die(mysql_error());

/*
    while($row = mysql_fetch_array($result)) {  
        print_r($row).PHP_EOL;
    }
*/
    return $result;
}

function heatingOn() {
    //heating on
    // call url
    $ch = curl_init();
    // set URL and other appropriate options
    curl_setopt($ch, CURLOPT_URL, 'http://192.168.1.90/heating/heatingon.php');
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1);
    // grab URL and pass it to the browser
    curl_exec($ch);
    // close cURL resource, and free up system resources
    curl_close($ch);
    
}

function heatingOff() {
    //heating on
    // call url
    $ch = curl_init();
    // set URL and other appropriate options
    curl_setopt($ch, CURLOPT_URL, 'http://192.168.1.90/heating/heatingoff.php');
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1);
    // grab URL and pass it to the browser
    curl_exec($ch);
    // close cURL resource, and free up system resources
    curl_close($ch);
    
}