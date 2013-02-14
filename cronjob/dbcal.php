<?php

$con = mysql_connect("localhost","dbuser","dbuser123");
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }

$currentTime = date();

//Temperature outside check .. if less than 5 degrees .. turn on 30 mins earlier?
//move through a list of preferred temps to get latest
$listTemp = array('therm_temp','lou_temp', 'bed1_temp');

for ($i=0;$i<count($listTemp);$i++) {
    mysql_select_db("emoncms", $con);
    $query = mysql_query("select unix_timestamp(time) as thermTime, value as thermTemp from feeds where name = 'therm_temp' LIMIT 1,1");
    
    while($row = mysql_fetch_array($result)) {  
        $thermTemp = $row['thermTemp'];
        $thermTime = $row['thermTime'];

        // time is old, then perhaps out of batteries?
        if !($thermTime > (date()-100) ) {
            break;
        }
    }
}

// Check holiday schedule
$query = mysql_query('select * from configuration where holidayFrom < '.$currentTime.' and holidayTo > '.$currentTime);
$rows = mysql_fetch_array($result)
if (count($rows) > 1) { 
    // ignore schedule and override and set 24 hour to holiday temp
    $temp = $row['temp'];
    } else {

    // Schedule
    $schedule = mysql_query('select * from schedule where date = '.$currentTime);
    
    day = date('N')
    hourOn = date('G')
    minuteOn = date('i')
    hourOff = date('G')
    minuteOff = date('i')
    
    // Override
    $override = mysql_query('select * from override where date = '.$currentTime);
}

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