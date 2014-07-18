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
  
 require("sks.inc.php");
 require("sks-status.inc.php");
 
 $title = "History of number of OpenPGP keys";
 $dir = "../";
 include($dir."inc/header.inc.php");
 
 $servers = unserialize(file_get_contents(dirname(__FILE__)."/sks_cache_status_collection.serialized"));
 $status_collection = unserialize(file_get_contents(dirname(__FILE__)."/sks_cache_status_collection.serialized"));
 $numkeys = $servers->get_numkey_history();
 $stats = $status_collection->get_statistics_data();
 $mk = (int)$stats['max_keys']; 
 
 ksort($numkeys);
 
 function diff_by_days(&$data, $days=1)
 {
 	$c = count($data);
 	$i = 1;
 	$d = $c - $days;
 	$start = 0;
 	$end = 0;
 	foreach($data as $k=>$v)
 	{
 		if($i==$d)
 			$start = $v;
 		
 		if($i==$c)
 			$end = $v; 
 		
 		$i++;
 	}
 	return ($end - $start);
 }
 
 function val_by_days(&$data, $days=1)
 {
 	$c = count($data);
 	$i = 1;
 	$d = $c - $days;
 	$end = 0;
 	foreach($data as $k=>$v)
 	{
 		if($i==$c)
 			$end = $v; 
 		
 		$i++;
 	}
 	return ($end);
 }
?>
<h2>Statistics:</h2>
<table style="width: 570px;">
<tr><td>Keys added today (partial):</td><td style="text-align: right;"><?=number_format($mk - val_by_days($numkeys, 1));?></td></tr>
<tr><td>Keys added yesterday:</td><td style="text-align: right;"><?=number_format(diff_by_days($numkeys, 1));?></td></tr>
<tr><td>Keys added the past 7 days:</td><td style="text-align: right;"><?=number_format(diff_by_days($numkeys, 7));?></td></tr>
<tr><td>Keys added the past 30 days:</td><td style="text-align: right;"><?=number_format(diff_by_days($numkeys, 30));?></td></tr>
<tr><td>Keys added the past 180 days:</td><td style="text-align: right;"><?=number_format(diff_by_days($numkeys, 180));?></td></tr>
</table>
<p>Below is a chart showing the development in the number of total OpenPGP keys by day.</p>
<img src="generate_key_chart.php" alt="Total number of OpenPGP keys" />
<p>Below is a chart showing the development in the number of  OpenPGP keys added by day.</p>
<img src="generate_key_bar_chart.php" alt="Number of OpenPGP keys added by day" />
<?
 include($dir."inc/footer.inc.php");
?>
