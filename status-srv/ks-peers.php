<?
 /*
  *  status-srv/ks-peers.php - Provide membership file for individual keyserver in pool
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
  
 $title = "Membership file";
 $dir = "../";
 include($dir."inc/header.inc.php");
 require("sks.inc.php");
 require("sks-status.inc.php");
 date_default_timezone_set("UTC");
 $servers = unserialize(file_get_contents(dirname(__FILE__)."/sks_cache.serialized"));
 $status_collection = unserialize(file_get_contents(dirname(__FILE__)."/sks_cache_status_collection.serialized"));
 
 echo "<p>These statistics were last updated: ".date("Y-m-d H:i",$servers->get_time())." (".date_default_timezone_get().")</p>";
 $serverarr = $servers->get_servers();
 $servercolarr = $status_collection->get_servers();
 sort($serverarr);
 
 $lookup_server = (IsFQDN($_GET['server'])) ? $_GET['server'] : "keys.kfwebs.net";
 $s = $servers->get_server_by_name($lookup_server); 
 
 $lookup_server2 = ($s !== false) ? $s->get_hostname() : $lookup_server;
 $lookup_server3 = ($s !== false) ? $s->get_called_hostname() : $lookup_server;
 
  
 echo "<pre>";
 echo "# Automatically generated membership file\n";
 echo "# for\t".$lookup_server."\n";
 echo "# from\tsks-keyservers.net\n";
 echo "# Updated: ".date("Y-m-d H:i", $servers->get_time())."(".date_default_timezone_get().")\n";
 echo "\n\n";
 
 foreach($serverarr as $serid=>$server)
 {
  $peers = $server->get_peers();
  if(in_array($lookup_server, $peers)||in_array($lookup_server2, $peers)||in_array($lookup_server3, $peers))
  {
  	$status = "Not good"; 
  	if((isset($servercolarr[$server->get_hostname()]['last_status']) && $servercolarr[$server->get_hostname()]['last_status']==1))
  	{
  		$status = "Good status.".$server->get_software().":". $server->get_version();
  	}
  	elseif((isset($servercolarr[$server->get_hostname()]['last_status']) && $servercolarr[$server->get_hostname()]['last_status']==0 && $server->get_statusok()))
  	{
  		$status = "Not good:".$servercolarr[$server->get_hostname()]['last_status_reason'];
  	}  
    echo $server->get_hostname()."\t".$server->get_recon_port()."\n";
	echo "#".$server->get_server_contact()."\n";
	echo "#".$status."\n\n";
  }	
 }
 echo "</pre>";
 
 include($dir."inc/footer.inc.php");
?>
