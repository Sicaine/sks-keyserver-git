<?
 /*
  *  status-srv/ks-status-json.php - Provide MR status for individual keyserver in pool
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
  
 $title = "Status pages";
 $dir = "../";
 require("sks.inc.php");
 require("sks-status.inc.php");
 date_default_timezone_set("UTC");
 $servers = unserialize(file_get_contents(dirname(__FILE__)."/sks_cache.serialized"));
 $status_collection = unserialize(file_get_contents(dirname(__FILE__)."/sks_cache_status_collection.serialized"));
 
 header("Content-Type: application/json");
 
 $return_array['Last_Update'] = date("Y-m-d H:i",$servers->get_time())." (".date_default_timezone_get().")";
 $serverarr = $servers->get_servers();
 $servercolarr = $status_collection->get_servers();
 $stats = $status_collection->get_statistics_data();
 sort($serverarr);
 
 $pools = array("EU", "NA", "OC", "SA");
 
 $numkey=0;
 
 $server = $servers->get_server_by_name(strtolower($_GET['server']));
   	
 
 if($server !== false)
 {
 	$return_array['Hostname'] = $server->get_hostname(); 
 	$return_array['Software'] = $server->get_software();
 	$return_array['Version'] = $server->get_version();
 	$return_array['Server_contact'] = $server->get_server_contact();
 	$return_array['IPv6'] = (($server->get_ipv6()==1) ? true : false);
 	$return_array['Port80'] = (($server->get_port80()==1) ? true : false);
 	$return_array['Keys'] = $server->get_numkeys(); 
 	$return_array['KeyDiff'] = (int)($server->get_numkeys() - $stats['mean']);
 	$return_array['HKPS'] = (bool)($server->get_has_hkps());
 	$return_array['HKPS_port'] = (int)($server->get_hkps_port());
 	$return_array['ReverseProxy'] = (bool)(	$server->get_is_reverse_proxy());  
 	$return_array['LoadBalanced'] = (bool)(	$server->get_is_loadbalanced());
	$return_array['CVE-2014-3207-vulnerable'] = (bool)($server->get_affected_cve2014_3207());

 	foreach($pools as $p)
 	{
 		$return_array['SRV_'.$p] = $status_collection->get_pool($p)->get_srvweight($server->get_hostname()); 
 	}
 	$return_array['Upstream'] = $status_collection->get_pool("EU")->get_bandwidth($server->get_hostname());
 	$return_array['Last_status'] = (isset($servercolarr[$server->get_hostname()]['last_status']) && $servercolarr[$server->get_hostname()]['last_status']==1) ? "OK" : "Not OK";
 	$return_array['Last_status_reason'] = $servercolarr[$server->get_hostname()]['last_status_reason'];
 	$return_array['Last_status_change'] = ((isset($servercolarr[$server->get_hostname()]['last_status_change'])) ? date("Y-m-d H:i", $servercolarr[$server->get_hostname()]['last_status_change']): "N/A");; 
 
    $peers = $server->get_peers();
    sort($peers);
    
    $return_array['Peers'] = array();
      
    foreach($peers as $pks)
    {
     $pks = strtolower($pks);
     $s = $servers->get_server_by_name($pks);
     if($s !== false)
     	$pks = $s->get_hostname();
     
     $peer = array();
     $peer['hostname'] = $pks; 
     $peer['last_status'] = ((isset($servercolarr[$pks]['last_status']) && $servercolarr[$pks]['last_status']==1) ? true: false); 
     $peer['last_status_change'] = (isset($servercolarr[$pks]['last_status_change']) ? date("Y-m-d", $servercolarr[$pks]['last_status_change']) : "N/A"); 
     $peer['software'] = (($s !== false) ? $s->get_software() : "");
     $peer['version'] = (($s !== false) ? $s->get_version() : ""); 
     $peer['numkeys'] = (($s !== false) ? $s->get_numkeys() : ""); 
     
     $return_array['Peers'][] = $peer;
    }   
 }
 else
 {
  $return_array['no_data'] = true; 
 }
 
 echo json_encode($return_array);
?>
