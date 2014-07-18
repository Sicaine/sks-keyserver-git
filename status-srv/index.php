<?
 /*
  *  status-srv/index.php
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
 include($dir."inc/header.inc.php");
 include_once("exclude.inc.php");
 require("sks.inc.php");
 require("sks-status.inc.php");
 date_default_timezone_set("UTC");
 $servers = unserialize(file_get_contents(dirname(__FILE__)."/sks_cache.serialized"));
 $status_collection = unserialize(file_get_contents(dirname(__FILE__)."/sks_cache_status_collection.serialized"));
 $servercolarr = $status_collection->get_servers();
 $stats = $status_collection->get_statistics_data();
 $pools = array("EU", "NA", "OC");
 
 echo "<p>These statistics were last updated: ".date("Y-m-d H:i",$servers->get_time())." (".date_default_timezone_get().")</p>";
 echo "<table><tr><td>Lower bound of keys:</td><td>".((int)$stats['numkeys'])."</td></tr><tr><td>Mean:</td><td>".((int)$stats['mean'])."</td></tr><tr><td>Median:</td><td>".((int)$stats['median'])."</td></tr><tr><td>Max difference:</td><td>".((int)$stats['diff'])."</td></tr><tr><td>Max keys:</td><td>".(int)$stats['max_keys']."</td></tr></table>";
 echo "<h1>Servers in the pool</h1>";
 echo "<p>Information about the various pools is <a href=\"/overview-of-pools.php\">found here</a><br /></p>"; 
 echo "<table id=\"mainpool\" class=\"list\">\n";
 echo "<thead><tr><th>&nbsp;</th><th>Hostname</th><th>IPv6</th><th>RProx</th><th>Port 80</th><th>hkps</th><th>Software</th><th>&Delta;Keys</th>"; 
 foreach($pools as $p)
 {
 	echo "<th>SRV ({$p})</th>";	
 }
  
 echo "<th>Stats</th><th>Meta</th></tr></thead><tbody>\n";
 $serverarr = $servers->get_servers();
 sort($serverarr);
 $nonrespserver = array();
 
 $mean = ((int)$stats['mean']); 
 
 $c1 = "#fff"; 
 $c2 = "#b4cad8"; 
 $c = 0;
 $counter = 1;
  
  
  
  $pool = array();
  $sum = array();
  
  foreach($pools as $p)
  {
  	$pool[$p] = $status_collection->get_pool($p);
  	$sum[$p] = array_sum($pool[$p]->get_top_servers());	
  }
 
 foreach($serverarr as $server)
 {
  if(!isset($servercolarr[$server->get_hostname()]['last_status']) || $servercolarr[$server->get_hostname()]['last_status']!=1)
  {
   $nonrespserver[] = array(
   		$server->get_hostname(),
   		$server->get_port(),
   		$server->get_ipv6(),
   		$server->get_version(),
   		$server->get_numkeys(),
   		$server->get_software(),
   		$server->get_is_reverse_proxy(),
		$server->get_server_contact(),
		$server->get_is_loadbalanced()
   		);
  }
  else
  {
   if(ServerIsExcluded($server->get_hostname())) continue;
   $cu = (($c==1) ? $c2 : $c1);

   echo "<tr>";
   echo "<td style=\"background-color: {$cu};\">{$counter}</td>";
   echo "<td style=\"background-color: {$cu};\"><a href=\"http://{$server->get_hostname()}:{$server->get_port()}\">{$server->get_hostname()}</a>".(($server->get_server_contact() != ""&& preg_match("/^0x[0-9a-fA-F]{8,40}$/", $server->get_server_contact())) ? "<a title=\"OpenPGP Key: {$server->get_server_contact()}\" style=\"font-weight: bold; color: blue;\" href=\"http://p80.pool.sks-keyservers.net/pks/lookup?op=vindex&amp;search={$server->get_server_contact()}\">[@]</a>" : "")."</td>";
   echo "<td style=\"background-color: ".(($server->get_ipv6() === true) ? "green" : "red")."; margin:none; padding:none;\"></td>";
   echo "<td style=\"background-color: ".(($server->get_is_reverse_proxy()) ? (($server->get_is_loadbalanced()) ? "#336699" : "green") : "red")."; margin:none; padding:none;\"></td>";
   echo "<td style=\"background-color: ".(($server->get_port80()) ? "green" : "red")."; margin:none; padding:none;\"></td>";
   echo "<td style=\"background-color: ".($server->get_has_hkps() && (!$server->get_affected_cve2014_3207()) ? "green" : "red")."; margin:none; padding:none;\"></td>";
   /*
    * Software name and version
    */
   $sw = $server->get_software(); 
   switch($sw)
   {
   	case "SKS": 
   		if($server->version_satisfy_min("1.1.5", 0)) // In subset pool
   			$sw_color = "#334499";
   		else
   			$sw_color = "#336699";
   		break;
   	case "GnuKS":
   		$sw_color = "#993399";
   		break;
   	default:
   		$sw_color = "red";
   }
   echo "<td style=\"background-color: {$sw_color}; color: white; text-align: center;\" title=\"{$sw}\">{$server->get_version()}</td>";
   /*
    * Number of keys
    */
   $nk = ($server->get_numkeys() - $mean);
   if($nk <0)
   {
   	$nk_color = "red";
   	$nk_bgcolor = "#FFFFCC";
   } 
   else
   {
   	$nk_color = "black";
   	$nk_bgcolor = $cu;
   }
   echo "<td style=\"background-color: {$nk_bgcolor}; color: {$nk_color}; text-align: right;\">".number_format($nk)."</td>";
   /*
    * SRV weights for various pools
    */
   foreach($pools as $p)
   {
   		$included = array_key_exists($server->get_hostname(), $pool[$p]->get_top_servers()); 
   		echo "<td style=\"". ($included ? "background-color: green; color: white;" : "background-color: {$cu}" )."; text-align: right;\"".($included ? " title=\"".number_format($pool[$p]->get_srvweight($server->get_hostname()) / $sum[$p] * 100, 2)."%\"" : "").">".number_format($pool[$p]->get_srvweight($server->get_hostname()))."</td>";	
   }
   echo "<td style=\"background-color: {$cu};\"><a href=\"http://{$server->get_hostname()}:{$server->get_port()}/pks/lookup?op=stats\">Stats</a></td>";
   echo "<td style=\"background-color: {$cu};\"><a href=\"info/{$server->get_hostname()}\">Meta</a></td>";
   echo "</tr>\n";
   $c = (($c==1) ? 0 : 1);
   $counter++;
  }
 }
 echo "</tbody></table>\n";
 
 echo "<h1>Servers currently not in the pool</h1>";
 echo "<p>These servers were detected when iterating though keyserver synchronisation peers, but did not qualify for inclusion in the main pool.</p>";
 echo "<table class=\"list\">\n";
 echo "<tr><th>&nbsp;</th><th>Hostname</th><th>IPv6</th><th>RProx</th><th>Version</th><th>&Delta;Keys</th><th>Stats</th><th>Meta</th></tr>\n";

 $c = 0;
 $counter = 1;

 foreach($nonrespserver as $server)
 {
  if(ServerIsExcluded($server[0])) continue;
  $cu = (($c==1) ? $c2 : $c1);
  	echo "<tr><td style=\"background-color: {$cu};\">{$counter}</td>";
	echo "<td style=\"background-color: {$cu};\">".(($server[2]==1) ? "<a href=\"http://{$server[0]}:{$server[1]}/\">{$server[0]}</a>" : $server[0])."".(($server[7] != ""&& preg_match("/^0x[0-9a-fA-F]{8,40}$/", $server[7])) ? "<a title=\"OpenPGP Key: {$server[7]}\" style=\"font-weight: bold; color: blue;\" href=\"http://p80.pool.sks-keyservers.net/pks/lookup?op=vindex&amp;search={$server[7]}\">[@]</a>" : "")."</td>";
	echo "<td style=\"background-color: ".(($server[2]==1) ? "green" : "red").";\"></td>";
	echo "<td style=\"background-color: ".(($server[6]==1) ? (($server[8]==1) ? "#336699" : "green") : "red").";\"></td>";
	/*
    * Software name and version
    */
   $sw = $server[5]; 
   switch($sw)
   {
   	case "SKS": 
   		$sw_color = "#336699";
   		break;
        case "GnuKS":
   		$sw_color = "#993399";
   		break;
        case "hockeypuck": 
		$sw_color = "#999966";
		break;  
   	default:
   		$sw_color = "red";
   }
   echo "<td style=\"background-color: {$sw_color}; color: white; text-align: center;\" title=\"{$sw}\">{$server[3]}</td>";
	echo "<td style=\"background-color: {$cu}; text-align: right;\">".(($server[4] != "") ? number_format($server[4] - $mean) : "")."</td>";
	echo "<td style=\"background-color: {$cu};\"><a href=\"http://{$server[0]}:{$server[1]}/pks/lookup?op=stats\">Stats</a></td>";
  	echo "<td style=\"background-color: {$cu};\"><a href=\"info/{$server[0]}\">Meta</a></td>";
  	echo "</tr>\n";
  $c = (($c==1) ? 0 : 1);
  $counter++;
 }
 echo "</table>";
 include($dir."inc/footer.inc.php");
?>
