<?
 /*
  *  status-srv/gendot.php
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
  
 $dir = "../";
 require("sks.inc.php");
 require("sks-status.inc.php");
 $servers = unserialize(file_get_contents(dirname(__FILE__)."/sks_cache.serialized"));
 $status_collection = unserialize(file_get_contents(dirname(__FILE__)."/sks_cache_status_collection.serialized"));
 $servercolarr = $status_collection->get_servers();
 
 $str = "graph sks {    size=\"500,500\";graph [ranksep=5,ratio=auto,overlap_scaling=3,size=500,pack=9,label = \"\\n\\nSKS Keyserver map by:\\nhttp://sks-keyservers.net\",fontname=Verdana];node [color=lightblue2, style=filled, fontname=Verdana];";
 
 foreach($servers->get_servers() as $server)
 {
    if($server->get_statusok() && (isset($servercolarr[$server->get_hostname()]['last_status']) && $servercolarr[$server->get_hostname()]['last_status']==1))
    {
     $str .= "\"".$server->get_hostname()."\" [color=lightblue2];";
     foreach($server->get_peers() as $peer)
     {
      $p = $servers->get_server_by_name(strtolower($peer));
      if($p)
      {
      	if($p->get_statusok() && (isset($servercolarr[$p->get_hostname()]['last_status']) && $servercolarr[$p->get_hostname()]['last_status']==1))
      		$str .= "\"".$server->get_hostname()."\" -- \"{$p->get_hostname()}\" [color=\"#999999\"];";
      }      
     }
    }
    else
    {
     //$str .= "\"".$server->get_hostname()."\" [color=rosybrown2,shape=box];";
    }
   }
   $str .= "}";
   
   file_put_contents(dirname(__FILE__)."/sks2.dot",$str);   

?>
