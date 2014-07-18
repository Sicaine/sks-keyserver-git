<?php
 /*
  *  client/key_retrieval.php: A PHP client for key retrieval measurement
  *  Copyright (C) 2006, 2007, 2008, 2009, 2010, 2011, 2012  Kristian Fiskerstrand
  *  
  *  This file is part of SKS Keyserver Pool (http://sks-keyservers.net)
  *  
  *  The Author can be reached by electronic mail at kristian.fiskerstrand@kfwebs.net 
  *  Communication using OpenPGP is preferred - a copy of the public key 0x6b0b9508 
  *  is available in all the common keyservers or in x-hkp://pool.sks-keyservers.net
  *  
  *  This program is free software: you can redistribute it and/or modify
  *  it under the terms of the GNU General Public License as published by
  *  the Free Software Foundation, either version 3 of the License, or
  *  (at your option) any later version.
  *
  *  This program is distributed in the hope that it will be useful,
  *  but WITHOUT ANY WARRANTY; without even the implied warranty of
  *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  *  GNU General Public License for more details.
  *
  *  You should have received a copy of the GNU General Public License
  *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
  */
  
  	function microtime_float()
  	{
    	list($usec, $sec) = explode(" ", microtime());
    	return ((float)$usec + (float)$sec);
  	}
  	
  	/*
  	 * Set HTTP header for plain text output since we are not
  	 * doing HTML (which is PHP's default).
  	 */
  	header('Content-Type: text/plain; charset=UTF-8');
    
  	/*
  	 * Define the name of the server performing the query
  	 * This will be used as key for the data collection array 
  	 */
  	$hostserver = $_SERVER['SERVER_NAME'];
    
   	$keyserver = $_GET['keyserver'];
   	
   	// Key id e.g. in the format 0x16E0CF8D6B0B9508
   	$key_id = $_GET['key_id'];
   	if(!preg_match("/^0x[0-9a-f]{8,16}$/", $key_id))
   		die("Wrong key ID format");
   
   	// Statically define port 
   	$port = 11371; 
   
   	// Define and initialize return array
   	$return_array = array();
   	$return_array['hostserver'] = $hostserver;
   	$return_array['keyserver'] = $keyserver;
   	$return_array['port'] = $port;
   	$return_array['key_id'] = $key_id; 
   	$return_array['statusok'] = true; 
   
   	$timestart = microtime_float(); 
   	$ch = curl_init("http://$keyserver:$port/pks/lookup?op=get&search=".$key_id);
   	curl_setopt($ch, CURLOPT_HEADER, 0); 
   	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   	/*
   	 * 	Force IPv4 check here
   	 * 	Require PHP 5.3 or later - see https://bugs.php.net/bug.php?id=47739  
   	 */
   	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);  
   	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
   	if(($ret=curl_exec($ch))===FALSE)
   	{
   	 curl_close($ch);
   	 $return_array['statusok'] = false;
   	}
   	else
   	{   
   	 curl_close($ch);
   	 $return_array['download_time'] = microtime_float() - $timestart; 
   	 $return_array['download_size'] = strlen($ret);
   	}
   	    
   	// Return json encoded data
   	$json =  json_encode($return_array);
   	echo $json; 
?>
