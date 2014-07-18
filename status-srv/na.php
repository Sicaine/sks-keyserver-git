<?
 /*
  *  status-srv/na.php: North American pool
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
 
 sleep(rand(1,10));
 $servers = unserialize(file_get_contents(dirname(__FILE__)."/sks_cache.serialized"));
 $serverarr = $servers->get_servers();
 
 $status_collection = unserialize(file_get_contents(dirname(__FILE__)."/sks_cache_status_collection.serialized"));
 $servercolarr = $status_collection->get_servers();
 $pool = $status_collection->get_pool("NA"); 
 
 $top_servers = $pool->get_top_servers();
 $include_ipv6 = $servers->include_ipv6(); 
 
 foreach($top_servers as $server=>$weight)
 {
  	$ip=gethostbyname($server);
  	// gethostbyname returns the unmodified hostname upon failure,
    // so continue if this is encountered
    if($ip == $server)
     continue; 
     
  	// Correct for own host
   if($ip=="10.1.1.3")
    $ip = "37.44.179.12";
   // End correct for own host
 
  	if($serverarr[$server]->get_ipv6() !== false) 
		$ip6=gethostbyname6($server);
  	else
		$ip6="";
  

   	echo  "_pgpkey-http._tcp.na.pool SRV 0 $weight 11371 ".$server.".\n";
   	echo  "na.pool A ".$ip."\n";

   	if($serverarr[$server]->get_ipv6() !== false && $include_ipv6 && $ip6 != "") 
   	{
		echo "na.pool AAAA ".$ip6."\n";
   	}
 }
?>
