<?
 /*
  *  status-srv/ks-status.php - Provide status for individual keyserver in pool
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
 require("sks.inc.php");
 require("sks-status.inc.php");
 date_default_timezone_set("UTC");
 $servers = unserialize(file_get_contents(dirname(__FILE__)."/sks_cache.serialized"));
 $status_collection = unserialize(file_get_contents(dirname(__FILE__)."/sks_cache_status_collection.serialized"));
 
 echo "<p>These statistics were last updated: ".date("Y-m-d H:i",$servers->get_time())." (".date_default_timezone_get().")</p>";
 $serverarr = $servers->get_servers();
 $servercolarr = $status_collection->get_servers();
 $stats = $status_collection->get_statistics_data();
 sort($serverarr);
 
 $pools = array("EU", "NA", "OC", "SA");
 
 $numkey=0;
 
 $server = $servers->get_server_by_name(strtolower($_GET['server']));
       
 
 if($server !== false)
 {
  ?>
   <h1>Status for <?=$server->get_hostname();?></h1>
   <table class="list">
    <tr>
     <td>Software</td>
     <td><?=$server->get_software();?></td>
    </tr>
    <tr>
     <td>Version</td>
     <td><?=$server->get_version();?></td>
    </tr>
    <tr>
     <td>Server contact</td>
     <td><?=($server->get_server_contact() != ""&& preg_match("/^0x[0-9a-fA-F]{8,40}$/", $server->get_server_contact())) ? "<a href=\"http://p80.pool.sks-keyservers.net/pks/lookup?op=vindex&amp;search={$server->get_server_contact()}\">".$server->get_server_contact()."</a>" : "Not provided in status page";?></td>
    </tr>
    <tr>
     <td>IPv6</td>
     <td><?=(($server->get_ipv6()==1) ? "Yes" : "No");?></td>
    </tr>
     <tr>
     <td>Port 80</td>
     <td><?=(($server->get_port80()==1) ? "Yes" : "No");?></td>
    </tr>
    <tr>
     <td>HKPS</td>
     <td><?=(($server->get_has_hkps()==1) ? "Yes ({$server->get_hkps_port()})" : "No");?></td>
    </tr>
    <tr>
     <td>Keys</td>
     <td><?=number_format($server->get_numkeys());?></td>
    </tr>
    <tr>
     <td>Key diff</td>
     <td><?=number_format((int)($server->get_numkeys() - $stats['mean']));?></td>
    </tr>
    <tr>
     <td>Vulnerable to CVE-2014-3207</td>
     <td><?=(($server->get_affected_cve2014_3207()) ? "<span style=\"color: red;\">Yes</span>" : "No");?></td>
    </tr>
    <?
    foreach($pools as $p)
    {?>
     <tr>
     <td>SRV Weight (<?=$p;?>)</td>
     <td><?=$status_collection->get_pool($p)->get_srvweight($server->get_hostname());?></td>
    </tr>
    <?}?>
    <tr>
     <td>Upstream bandwidth</td>
     <td><?=$status_collection->get_pool("EU")->get_bandwidth($server->get_hostname());?> Mbit/s *</td>
    </tr>
    <tr>
     <td>HTTP Server response</td>
     <td><?=$server->get_http_response_server();?><?=($server->get_has_http_response_via() ? " (has Via header)": "");?> <?=($server->get_is_loadbalanced() ? " (loadbalanced)" : "");?></td>
    </tr>
    <tr>
     <td>Status page</td>
     <td><a href="http://<?=$server->get_hostname();?>:11371/pks/lookup?op=stats">Status</a></td>
    </tr>
    <tr>
     <td>Latest status</td>
     <td><?=(isset($servercolarr[$server->get_hostname()]['last_status']) && $servercolarr[$server->get_hostname()]['last_status']==1) ? "OK" : "Not OK";?></td>
    </tr>
    <?
     if(isset($servercolarr[$server->get_hostname()]['last_status']) && $servercolarr[$server->get_hostname()]['last_status']!=1)
     {
      echo "<tr><td>Reason</td><td>{$servercolarr[$server->get_hostname()]['last_status_reason']}</td></tr>";
     }
    ?>
    <tr>
     <td>Last status change</td>
     <td><?=((isset($servercolarr[$server->get_hostname()]['last_status_change'])) ? date("Y-m-d H:i", $servercolarr[$server->get_hostname()]['last_status_change']): "N/A");?></td>
    </tr>
    <tr>
     <td>Membership file</td>
     <td><a href="/status/membership/<?=$server->get_hostname();?>">See reference membership file</a></td>
    </tr>
   </table>
   <p>* This information has to be manually reported to Kristian Fiskerstrand (OpenPGP key <a href="http://pool.sks-keyservers.net:11371/pks/lookup?op=vindex&amp;search=0x0B7F8B60E3EDFAE3">0x0B7F8B60E3EDFAE3</a>)</p>
   <p>The information for individual servers is also available in a machine readable format <a href="/status/ks-status-json.php?server=<?=$server->get_hostname();?>">(json)</a></p>
   <h1>Recorded peers</h1>
   <table class="list">
   <tr>
    <th>Name</th>
    <th>Latest status</th>
    <th>Latest status changed</th>
    <th>Software</th>
    <th>Version</th>
    <th>Number of keys</th>
    <th>Cross-peered</th>
   </tr>
   <?
    $peers = $server->get_peers();
    sort($peers);
      
    foreach($peers as $pks)
    {
     $pks = strtolower($pks);
     $s = $servers->get_server_by_name($pks);
     if($s !== false)
         $pks = $s->get_hostname();
         
     echo "<tr>";
     echo "<td><a href=\"/status/info/{$pks}\">".$pks."</a></td>";
     echo "<td>".((isset($servercolarr[$pks]['last_status']) && $servercolarr[$pks]['last_status']==1) ? "OK": "Not OK")."</td>";
     echo "<td>".(isset($servercolarr[$pks]['last_status_change']) ? date("Y-m-d", $servercolarr[$pks]['last_status_change']) : "not set")."</td>";
     echo "<td>".(($s !== false) ? $s->get_software() : "")."</td>";
     echo "<td>".(($s !== false) ? $s->get_version() : "")."</td>";
     echo "<td>".(($s !== false) ? number_format($s->get_numkeys()) : "")."</td>";
     echo "<td>";
     
     if(($s !== false) && $s->get_statusok()==1)
     {
      $f=0;
      foreach($s->get_peers() as $v)
      {
          $r = $servers->get_server_by_name($v);
          if($r !== false && (
              $server->get_hostname() == $r->get_hostname()
              || $server->get_called_hostname() == $r->get_hostname()
              || $server->get_hostname() == $r->get_hostname()
              || $server->get_hostname() == $r->get_called_hostname()
              ))
        {
         $f=1;
         break;
        }
       }
      if($f==1) echo "OK";
      else echo "Not OK";
     }
     else
     {
      echo "Unknown";
     }
     echo "</td>";
     echo "</tr>";
    }
   ?>
   </table>
  <?  
  
  
 }
 else
 {
  echo "<p>No data found for keyserver</p>";
 }
 
 include($dir."inc/footer.inc.php");
?>
