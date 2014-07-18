<?php
 /*
  *  status-srv/sks_get_srv_measurement.php
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
  
  /*
   * argv[1] is URL of retrieval check
   */
   	
   	if(!isset($argv[1])) 
  		die("Missing argument");
  		
   /*
   	* Force sleep at random interval to spread load
    */ 
   
    sleep(rand(1,15));
   	
   	$ch = curl_init("{$argv[1]}");
   	curl_setopt($ch, CURLOPT_HEADER, 0); 
   	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   	curl_setopt($ch, CURLOPT_TIMEOUT, 20);
   	if(($ret=curl_exec($ch))===FALSE)
   	{
   	 	curl_close($ch);
   	}
   	else
   	{   
    	curl_close($ch);
    	echo $ret; 
   	}
?>
