<?
 /*
  *  status-srv/get_zonedata.php
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
 
  function event_datareturn($fd, $flags, $arg)
  {
  	if($fd)
  	{
  		while(($buf = fgets($fd, 1024)) !== false)
 		{
			echo $buf;  
 		}
  	}
  	
  	event_free($arg[0]);
  }
  
  
  $arr = array("ip", "ip6","eu", "ip-v1","ha","na", "oc", "sa", "p80", "hkps");
  
  $event_base = event_base_new(); 
  $events = array();
  $fh = array();
  
  foreach($arr as $a)
  {
	$events[$a] = event_new();
    $fh[$a] = popen("/usr/bin/php -f /webs/sks-keyservers.net/status/{$a}.php", "r");
  	event_set($events[$a], $fh[$a], EV_TIMEOUT | EV_READ | EV_WRITE | EV_PERSIST, "event_datareturn", array($events[$a], $event_base));
  	event_base_set($events[$a], $event_base);
  	event_add($events[$a], 45000);
  }
  
  event_base_loop($event_base);

?>
