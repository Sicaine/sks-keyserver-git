<?
 /*
  *  status-srv/eu.php: EU Pool
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
   * Note, this file require jpgraph to be installed in "jpgraph" folder 
   * within status-srv
   */
  
 require("sks.inc.php");
 require("sks-status.inc.php");
 header("Content-type: text/plain");
 $servers = unserialize(file_get_contents(dirname(__FILE__)."/sks_cache_status_collection.serialized"));
 
 $numkeys = $servers->get_numkey_history();
 ksort($numkeys);
 
 $darr = array();
 $karr = array();
 
 $c = 799;
 $d = count($numkeys) - $c; 
 $i = 0;
 $tmpvar = 0;
  
 foreach($numkeys as $k=>$v)
 {
	if($v < 2000000) 
		continue; 
	
	$i++;
	$diff = $v - $tmpvar; 
	$tmpvar = $v;
	
	if($diff > 2000000)
		continue;
		
	if($i<$d) 
		continue;
		
	$darr[] = $k; 
	$karr[] = $diff; 
 }
 
require("jpgraph/src/jpgraph.php");
require("jpgraph/src/jpgraph_bar.php");
 
// Width and height of the graph
$width = 600; $height = 300;
 
// Create a graph instance
$graph = new Graph($width,$height);
// Create the linear plot
$barplot=new BarPlot($karr);
 
// Add the plot to the graph
$barplot->SetFillColor('blue');
$graph->Add($barplot);

$graph->SetScale('intlin');
$graph->SetMargin(70, 20, 10, 80);
// Specify what scale we want to use,

// Setup a title for the graph
$graph->title->Set('OpenPGP Keys added pr day');
 
// Setup Y-axis title
$graph->xaxis->SetTickLabels($darr);
$graph->xaxis->scale->ticks->SupressLast();  
$graph->xaxis->SetLabelAngle(45);
 
// Display the graph
$graph->Stroke();
 
?>
