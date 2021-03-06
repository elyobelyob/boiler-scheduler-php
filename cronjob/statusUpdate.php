<?php

// Check whether control2drayton set
// otherwise continue ... 

include 'settings.php';

$con = mysql_connect($db,$dbuser,$dbpasswd) or die("Cannot connect mysql".PHP_EOL);

if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }

//Temperature outside check .. if less than 5 degrees .. turn on 30 mins earlier?
//move through a list of preferred temps to get latest
//$listTemp = array('therm_temp','lou_temp', 'bed1_temp', 'out_temp');
$listTemp = array('lou_temp'=>1,'bed1_temp'=>9,'bath_temp'=>23,'loft_temp'=>3,'out_temp'=>2);
$dayNames = array( '','Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );

echo "<b>show temps</b>" . PHP_EOL;
foreach ($listTemp as $key=>$value) {
    // we grab from emoncms
    $currentTemp = (float) str_replace('"','',getEmonTemp($value));
    echo $key. ' -> '. $currentTemp.'c'.PHP_EOL;
}

$schedule = getSchedule();
while($rows = mysql_fetch_assoc($schedule)) {
    	    echo $rows['timeOn'];
    	    echo "- ".$rows['timeOff'];
    	    echo " ".$dayNames[$rows['day']];
    	    echo " ".$rows['heatingTemp']."c";
    	    //echo " waterOn : ".$rows['waterOn'].PHP_EOL;
    	    echo PHP_EOL;
}
            
$holiday = getHoliday();
if( mysql_num_rows($holiday) == 3) {
    while($rows = mysql_fetch_assoc($holiday)) {
        foreach ($rows as $key => $value) {
            echo $key." ".$value.PHP_EOL;
        }
    }
}

$override = getOverride();
while($rows = mysql_fetch_assoc($override)) {
	echo $rows['datestart'];
	echo " -> ";
	echo $rows['dateend'];
	echo " ";
	echo $rows['heatingTemp']."c";
	echo PHP_EOL;
}

echo PHP_EOL;
echo "Finish status update : " . date("d/m/y H.i:s", time()) . PHP_EOL;

// Main Functions
function getEmonTemp($name) {
    global $apikey, $emonserver;
    $c = curl_init("http://".$emonserver."/feed/value.json?apikey=".$apikey."&id=".$name);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

    $html = curl_exec($c);

    if (curl_error($c))
        die(curl_error($c));

    curl_close($c);

    return $html;
}


function getSchedule() {
    echo "<b>todays schedule</b>" . PHP_EOL;
    // Schedule
    $date = (date('N')+1);
    if ($date > 7) {$date = 1;}
    $query = "SELECT    timeOn,
                        timeOff,
                	day,
                	heatingOn,
                	heatingTemp,
                	waterOn
                FROM boiler.schedule WHERE 
                day = ".$date." 
		AND enabled = 1
                ORDER BY day ASC, timeoff ASC
		";
    //echo $query.PHP_EOL;
    $result = mysql_query($query);
    
    return $result;
}

function getHoliday() {
    echo "<b>current/next holiday</b>" . PHP_EOL;
    // Check holiday schedule
    $query = 'SELECT `key`, value FROM boiler.configuration 
                WHERE ((`key` = "holidayFrom" AND value < '.mktime().') 
                    OR (`key` = "holidayTo" AND value > '.mktime().')
                    OR (`key` = "holidayTemp" AND value <> 0))';
    //echo $query . PHP_EOL;
    $result = mysql_query($query);
    
    return $result;
}

function getOverride() {
    echo "<b>current/next override</b>" . PHP_EOL;
    // Override
    // select * from override where date > 1361535136 and (date+duration > 1361535136)
    // select UNIX_TIMESTAMP(date) as date, UNIX_TIMESTAMP(date+duration) as datelength
	//     from boiler.override 
	//     where UNIX_TIMESTAMP(date) < 1361547580 and (UNIX_TIMESTAMP(date+duration) >
    $query = "SELECT    date as datestart,
                        (date + INTERVAL duration MINUTE) as dateend,
                        duration,
                        heatingTemp
                FROM boiler.override WHERE 
                    enabled = 1
                    AND (UNIX_TIMESTAMP(date + INTERVAL duration MINUTE) > ".mktime().")
                    LIMIT 1; ";
    //echo $query.PHP_EOL;
    $result = mysql_query($query) or die(mysql_error());

    return $result;
}

