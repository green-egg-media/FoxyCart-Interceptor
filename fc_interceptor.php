<?php

/*
	Title:
		FoxyCart_Interceptor
	
	Description:
		Intercepts the FoxyCart XML Datafeed and hands it off to two or more other URLs for processing.
		This allows a single datafeed to be used by more than one script.
		A response of "foxy" is returned to FoxyCart only if ALL secondary scripts return "foxy" to the interceptor.
		
	Author:
		Lance Johnson of Green Egg Media (http://www.greeneggmedia.com/)
	
	How to Use:
		Step 1: Upload the Interceptor
		#	Upload this file to your server.
		#	This file needs to live somewhere that is directly web-accessible, i.e., it is not CMS-dependant, and
			is not served out by a CMS.
		
		Step 2: Set your URLs
		#	Add each URL to the $urls array.
		
		Step 3: Tell FoxyCart where to send the XML
		#	In your FoxyCart Admin panel, point the XML datafeed to this script.
		
		Step 4: TEST
		#	Test that everything is working correctly.
		#	Test that everything is working correctly.
		#	Test that everything is working correctly. (Yes, test three times!)
	
	IMPORTANT NOTES:
		*	Interceptor does not decrypt your datafeed, it only passes it along to additional scripts, so your
			scripts need to do their own decoding.
		*	FoxyCart expects a response of "foxy" when the datafeed is sent, otherwise it will mark that feed
			as failed. Interceptor will only return "foxy" if all of the subsidiary scripts return "foxy" to
			Interceptor. Otherwise, Interceptor will return the entire response of each subsidiary script.
	
	Version:
		0.1.0 Alpha
*/

/************************************
*		MODIFY THIS SECTION			*
************************************/

// Add each URL to this array
$urls = array(
			'http://someurl.tld/path/to/script1',
			'http://otherurl.tld/path/to/script2');
			
// If there are problems getting the data sent to your scripts correctly, try setting this to TRUE
$ignore_ssl = FALSE;


/************************************
*	DO NOT MODIFY THIS SECTION		*
************************************/

if (isset($_POST["FoxyData"])) {
	
	$responses = array();
	$foxy = "foxy";
	
	foreach($urls as $url) {
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_HEADER, FALSE); // we don't want the full header, just the content
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); // we're just collecting the response, not displaying it
		curl_setopt($ch, CURLOPT_POST, 1); // we're posting data here, people
		curl_setopt($ch, CURLOPT_POSTFIELDS, array("FoxyData" => $_POST["FoxyData"])); // just hand this over, as is
		if($ignore_ssl === TRUE) curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // sometimes there are problem with SSL
		$result = curl_exec($ch);
		curl_close($ch);
		
		if($result != "foxy") {
			$foxy = FALSE;
		}
		$responses[$url] = $result;
		$result = "";
	}
	
	if($foxy === FALSE) {
		$fc_response = "";
		foreach($responses as $k => $v) {
			$fc_response .= $k." responded ".$v;
		}
		echo $fc_response;
		return;
	}
	
	echo $foxy;
	return;

}

echo "Invalid data";

?>