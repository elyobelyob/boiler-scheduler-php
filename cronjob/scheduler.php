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

    $query = "select unix_timestamp(time) as thermTime, value as thermTemp from emoncms.feeds where name = 'therm_temp' LIMIT 1,1";
    $result = mysql_query($query);
    print_r(mysql_error());
    
    while($row = mysql_fetch_array($result)) {  
        $thermTemp = $row['thermTemp'];
        $thermTime = $row['thermTime'];
        print_r($row).PHP_EOL;

        // time is old, then perhaps out of batteries?
        if ($thermTime < (date()-100) ) {
            break;
        }
    }
}

// Check holiday schedule
$query = 'select * from boiler.configuration where keyword = "holidayFrom" AND UNIX_TIMESTAMP(value) < '.mktime();
echo $query . PHP_EOL;
$result = mysql_query($query);
$rows = mysql_fetch_array($result);
while($row = mysql_fetch_array($result)) {  
    print_r($row).PHP_EOL;
}

if (count($row) > 1) { 
    // ignore schedule and override and set 24 hour to holiday temp
    $temp = $row['temp'];
    } else {

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
    $result = mysql_query($query);
    
    while($row = mysql_fetch_array($result)) {  
        print_r($row).PHP_EOL;
    }

    echo $query.PHP_EOL;

        
    // Override
    // select * from override where date > 1361535136 and (date+length > 1361535136)
    // select UNIX_TIMESTAMP(date) as date, UNIX_TIMESTAMP(date+length) as datelength 
	//     from boiler.override 
	//     where UNIX_TIMESTAMP(date) < 1361547580 and (UNIX_TIMESTAMP(date+length) >
    $query = "SELECT * FROM boiler.override WHERE 
                UNIX_TIMESTAMP(date) < ".mktime()." 
                AND (UNIX_TIMESTAMP(date + INTERVAL length MINUTE) > ".mktime().")";
    $result = mysql_query($query) or die(mysql_error());

    while($row = mysql_fetch_array($result)) {  
        print_r($row).PHP_EOL;
    }

    echo $query.PHP_EOL;

}

$currentTemp = 15;
$temp = 15;

if ($currentTemp < $temp) {
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
} else { 
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