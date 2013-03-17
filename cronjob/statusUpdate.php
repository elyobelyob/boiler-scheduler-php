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
$listTemp = array('lou_temp','bed1_temp','bath_temp','loft_temp','out_temp',);

echo "<b>show temps</b>" . PHP_EOL;
for ($i=0;$i<count($listTemp);$i++) {
    // we grab from emoncms
    if ($data = getEmonTemp($listTemp[$i])) {

    	while($rows = mysql_fetch_assoc($data)) {
    	    echo $rows['name']. ' -> '. $rows['thermTemp'].'c'.PHP_EOL;
    	}

    }
}

$schedule = getSchedule();
while($rows = mysql_fetch_assoc($schedule)) {
    	    echo "timeOn : ".$rows['timeOn'].PHP_EOL;
    	    echo "timeOff : ".$rows['timeOff'].PHP_EOL;
    	    echo "day : ".$rows['day'].PHP_EOL;
    	    echo "heatingOn : ".$rows['heatingOn'].PHP_EOL;
    	    echo "heatingTemp : ".$rows['heatingTemp'].PHP_EOL;
    	    echo "waterOn : ".$rows['waterOn'].PHP_EOL;
}
            
$holiday = getHoliday();
if( mysql_num_rows($holiday) == 3) {
    while($rows = mysql_fetch_assoc($holiday)) {
        print_r($rows).PHP_EOL;
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
    $query = "SELECT name,
                     time,
                     value AS thermTemp 
                     FROM emoncms.feeds WHERE name = '".$name."' LIMIT 0,1";
    //echo $query . PHP_EOL;
    $result = mysql_query($query);
    //echo mysql_num_rows($result) . PHP_EOL;
    return $result;    
}

function getSchedule() {
    echo "<b>current/next schedule</b>" . PHP_EOL;
    // Schedule
    //SELECT * FROM schedule WHERE 
    //          (timeOn < '06:17:00' ) 
    //          AND (timeOff > '06:17:00' ) 
    //          AND day = 5
    $date = (date('N')+1);
    if ($date > 7) {$date = 1;}
    $query = "SELECT    timeOn,
                        timeOff,
                	day,
                	heatingOn,
                	heatingTemp,
                	waterOn
                FROM boiler.schedule WHERE 
                (timeOff > CONVERT('".date('G').":".date('i').":00', TIME) 
                AND day = ".$date.") 
                OR day > ".$date." 
                ORDER BY day ASC, timeoff ASC
                LIMIT 0,1";  
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
    echo "<b>current/next holiday</b>" . PHP_EOL;
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
    echo "<b>current/next override</b>" . PHP_EOL;
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
                    AND (UNIX_TIMESTAMP(date + INTERVAL length MINUTE) > ".mktime().")
                    LIMIT 1; ";
    //echo $query.PHP_EOL;
    $result = mysql_query($query) or die(mysql_error());

/*
    while($row = mysql_fetch_array($result)) {  
        print_r($row).PHP_EOL;
    }
*/
    return $result;
}





