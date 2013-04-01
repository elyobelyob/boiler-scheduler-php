<?php

// script to check that the batteries haven't died

include 'settings.php';
require '../vendor/autoload.php';

$con = mysql_connect($db,$dbuser,$dbpasswd) or die("Cannot connect mysql".PHP_EOL);

if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }

$listTemp = array('lou_temp', 'bed1_temp', 'out_temp');

for ($i=0;$i<count($listTemp);$i++) {
    // we grab from emoncms
    if ($data = getEmonTemp($listTemp[$i])) {

    	while($rows = mysql_fetch_assoc($data)) {
    		$currentTemp = $rows['thermTemp'];
        	echo $rows['name']. ' -> '.$rows['thermTemp'].' -> '.strtotime($rows['time']).' '.strtotime($checktime,time()).PHP_EOL;
        	if (strtotime($rows['time']) < strtotime($checktime,time())) {
            	sendMessage($rows['name'],$prowlapi); 
        	}
    	}

    }
}

function getEmonTemp($name) {
    $query = "SELECT name,
                     time,
                     value AS thermTemp 
                     FROM emoncms.feeds WHERE name = '".$name."' LIMIT 0,1";
    //echo $query . PHP_EOL;
    $result = mysql_query($query);
    return $result;    
}

function sendMessage($powerName,$prowlapi) {
    $oProwl = new \Prowl\Connector();
    
    $oMsg = new \Prowl\Message();

	$oProwl->setFilterCallback(function($sText) {
        return $sText;
    });

    $oProwl->setIsPostRequest(true);
    $oMsg->setPriority(0);
    
    // You can ADD up to 5 api keys
    // This is a Test Key, please use your own.
    $oMsg->addApiKey($prowlapi);
    $oMsg->setEvent('My Event!');
    
    // These are optional:
    $oMsg->setApplication($powerName.' battery dead');
    
    $oResponse = $oProwl->push($oMsg);
    
    if ($oResponse->isError()) {
        print $oResponse->getErrorAsString();
    } else {
        print "Message sent." . PHP_EOL;
        print "You have " . $oResponse->getRemaining() . " Messages left." . PHP_EOL;
        print "Your counter will be resetted on " . date('Y-m-d H:i:s', $oResponse->getResetDate()) . PHP_EOL;
    }    
}