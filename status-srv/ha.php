<?
 /*
  *  status-srv/ha.php: High-availibility pool - Reverse proxy
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
  
 require("sks.inc.php");
 require("sks-status.inc.php");
 
 header("Content-type: text/plain");
 $servers = unserialize(file_get_contents(dirname(__FILE__)."/sks_cache.serialized"));
 $serverarr = $servers->get_servers();
 
 $status_collection = unserialize(file_get_contents(dirname(__FILE__)."/sks_cache_status_collection.serialized"));
 $servercolarr = $status_collection->get_servers();
 
 sort($serverarr);
 
 $array_of_all_online_servers = array();
 $array_of_selected_servers = array(); 
 
 // Server selection
 foreach($serverarr as $server)
 {
 	if(!(isset($servercolarr[$server->get_hostname()]['last_status']) && $servercolarr[$server->get_hostname()]['last_status']==1)) continue;  
 	if(!$server->get_is_reverse_proxy()) continue;
    $array_of_all_online_servers[] = array($server->get_hostname(), $server->get_ipv6());
 }
 
 // Select only 10 random servers
 if(count($array_of_all_online_servers)>10)
 {
  	$server_keys = array_rand($array_of_all_online_servers,10);
  	foreach($server_keys as $id)
  	{
  		$array_of_selected_servers[] = $array_of_all_online_servers[$id];
  	}
 }
 else
 {
 	$array_of_selected_servers = $array_of_all_online_servers; 
 }
 
 // Get IPs and print zone
 
 $include_ipv6 = $servers->include_ipv6();
 
 foreach($array_of_selected_servers as $server)
 {
	  $ip=gethostbyname($server[0]);
	  
	  // Correct for own host
      if($ip=="10.1.1.3")
        $ip = "37.44.179.12";
      // End correct for own host
	  
	  $pattern = "/^\d+\.\d+\.\d+\.\d+$/";
	  if(!preg_match($pattern, $ip)) continue;  
	  echo "ha.pool A ".$ip."\n";
	  
	  if($server[1] && $include_ipv6)
	  {
	  		$ip=gethostbyname6($server[0]);
			if(!is_array($ip))
	  			echo "ha.pool AAAA ".$ip."\n";
	  }
 }
?>
