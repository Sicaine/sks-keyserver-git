<?
 /*
  *  status-srv/sks-status.inc.php
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
  
	date_default_timezone_set("UTC");
 	include_once("/webs/sks-keyservers.net/status/exclude.inc.php");
 	require("sks-srv.inc.php");

   // Define function to calculate statistics
   // receive array of servers key:server name, value: num of keys
   // return, array
	function distribution_statistics(&$ser)
   	{
   		global $debug; 
   		$return_array = array();
   		
   		// Calculate the mean number of keys in selection
   		$mean_total = 0;
   		$mean_count = 0;
		$max = 0; 
   		   		
   		foreach($ser as $num)
   		{
		   	$mean_total += $num;
		   	$mean_count++;
			if($num > $max)
				$max = $num; 
   		}
   		
   		if($mean_count == 0 )
   			$mean = 0; 
   		else
   			$mean = $mean_total / $mean_count;
   		 
   		if($debug>=1)
   			echo "Mean\t$mean\n";
   		
   		// Get the median
   		sort($ser, SORT_NUMERIC); 
   		
   		if($mean_count % 2 == 0)
   		{
   			$median = (($ser[floor($mean_count/2)] + $ser[ceil($mean_count/2)]) / 2); 
   		}
   		else
   		{
   			$median = $ser[$mean_count/2];
   		}
   		
   		 // Calculate the variance
   		$variance_total = 0;
   		
   		foreach($ser as $num)
   		{
   			$variance_total += pow(((int)$num - $mean), 2);
   		}
   		
   		if($debug>=1)
   		{
	   		echo "Variance total:\t".$variance_total."\n";
	   		echo "Mean count:\t".$mean_count."\n";	
   		}
   		
	   // StdDev
	   	$stddev = sqrt(($variance_total / $mean_count));
   		
   		$return_array['stddev'] = $stddev;
   		$return_array['var'] = $variance_total / $mean_count; 
   		$return_array['mean'] = $mean;
   		$return_array['median'] = $median;
   		$return_array['count'] = $mean_count;
		$return_array['max_keys'] = $max;
   		
   		return $return_array;  		
   	}
   	
    /*
     * Simple function to check if domain name is valid
     * this will not handle more complex patterns, but
     * it fits the servers in the pool at the time of 
     * writing
     * 
     */
    function IsFQDN($domainname)
   	{
   		$return_value = true; 
   		
   		// Check max length of entire string
   		if(strlen($domainname) > 253)
   			$return_value = false; 
   		
   		// Quick validate, needs additional testing
   		$valid_pattern = "/^(?:[a-zA-Z0-9\-]{1,63}+\.)+(?:[a-zA-Z0-9]{2,})$/";
   	 	if(!preg_match($valid_pattern, $domainname))
   	 		$return_value = false; 
   	 	
   	 	// a label is not allowed to start or end with - or . (ok, this is technically untrue, as trailing . is actually required in full FQDN)
   	 	if(substr($domainname, 0, 1) == "." || substr($domainname, 0, 1) == "-")
   	 		$return_value = false; 
   	 		
   	 	if(substr($domainname, strlen($domainname)-1) == "." || substr($domainname, strlen($domainname)-1) == "-")
   	 		$return_value = false;
   	 	
   	 	if(strpos($domainname, ".-") !== false || strpos($domainname, "-.") !== false)
   	 		$return_value = false;  
   	 	
   	 	return $return_value;  
   	}
   	
 class sks_status_collection
 {
  private $servers = array();
  private $srv_pools = array(); 
  private $numkey_history = array(); 
  private $numkey;
  private $mean;
  private $median;
  private $max_keys; 
  private $diff;  
  
  private function set_server_notinclude($server, $reason)
  {
  	if($this->servers[$server]['last_status'] == 1) 
	{
    		$this->servers[$server]['last_status_change'] = time();
    		$this->servers[$server]['last_status'] = 0;
		$this->servers[$server]['last_status_reason'] = "{$reason}";
	}
  }
  
  public function run(&$arr)
  {
   	$this->numkey=0;
   
  	$debug = 0; 
	$numkey=0;
   
   	$num_key_array = array();
   
   	// assign number of keys to an array if not obviously unlinked
   if($debug)
   	echo "Number of servers in V-arr is\t".count($arr)."\n";
   
   foreach($arr->get_servers() as $server)
   {
   	if($debug>=2)
   		echo "+Server:\t{$server->get_hostname()} is being processed\n";
   	
   	if(!$server->get_statusok())
   	{
   		if($debug>=2)
   			echo "!Server\t{$server->get_hostname()}\tSkipped for not status OK:\t{$server->get_statusok()}\n";
   		continue;
   	}
   		
   	
   	if((int)$server->get_numkeys() < 3000000)
   	{
   		if($debug>=2)
   			echo "!Server\t{$server->get_hostname()}\tSkipped for count being.{$server->get_numkeys()}\n";
   		continue; 
   	}
   	
   	$num_key_array[$server->get_hostname()] = $server->get_numkeys();
   	
   	if($debug>=1)
   		echo "+Server added:\t{$server->get_hostname()}\tkeys:{$server->get_numkeys()}\tCount:".count($num_key_array)."\n";
   } // End foreach
   		
   // PASS 1
    $s = distribution_statistics($num_key_array);
   	$numkey = ($s['median'] - 0.5*$s['stddev']);
    $this->max_keys = $s['max_keys'];
	
   	if($debug>=1)
   		echo "Numkey set to\t {$numkey} based on mean - 0.5 *\t{$s['stddev']}\n";
   		
   // PASS2 - Filter out servers more than 0.5 stddev away from median (left side)
   	$lower_bound = $s['median'] - 0.5 * $s['stddev']; 
	$upper_bound = $s['median'] + 0.5 * $s['stddev']; 
   	
   	$num_key_array2 = array();
   	
   	foreach($num_key_array as $server=>$num)
   	{
   		if($num < $lower_bound)
   			continue; 
		
		if($num > $upper_bound)
			continue; 
   			
   		$num_key_array2[$server] = $num;
   	}
   	
   	$s = distribution_statistics($num_key_array2);
   	$lower_diff = 0.5 * $s['stddev'];
   	
   	if($lower_diff < 300)
   		$lower_diff = 300; 
   	
   	$numkey = ($s['mean'] - $lower_diff);
 
   	if($debug>=1)
   		echo "Numkey set to\t {$numkey} based on mean - 0.5 *\t{$s['stddev']}\n";
	
	$this->numkey = $numkey;
	$this->mean = $s['mean'];
	$this->median = $s['median'];
	$this->diff =  $lower_diff;
	
	if(!isset($this->numkey_history[date("Y-m-d")]))
		$this->numkey_history[date("Y-m-d")] = $this->max_keys; 

	// Cleanup the historical data
	if(isset($cleanup_history) && $cleanup_history)
	{
		$data_array = $this->numkey_history;
		$this->numkey_history = array(); 
		
		foreach($data_array as $k=>$v)
		{
			$d = explode(" ", $k);
			if(!isset($this->numkey_history[$d[0]]))
				$this->numkey_history[$d[0]] = $v; 
		}
	} // End cleanup of historical data.
	$add_historical_data = false; 
	if(isset($add_historical_data) && $add_historical_data)
	{
		$this->numkey_history['2014-06-06']=3642031;

	}
		
   if($this->numkey=="0") 
	echo "error, number of minimum keys for servers is not set, i.e. all keyservers will be added to pool even with large diffs";

   // Exclude servers not updated to minimum version requirement of SKS
   $min_req = "1.1.3";

   foreach($arr->get_servers() as $server=>$serverobj)
   {	
    // If no entry for server is found - create one and set added time
    if(!isset($this->servers[$server]))
    {
    	$this->servers[$server] = array();
    	$this->servers[$server]['added']=time();
    }
    
    // If there has been a change to the status, reset timer and update status
    if(isset($this->servers[$server]['last_status']) && $this->servers[$server]['last_status'] != $serverobj->get_statusok())
    {
    	$this->servers[$server]['last_status'] = $serverobj->get_statusok();
   	$this->servers[$server]['last_status_change'] = time();
    }
    
    // Status is not set, but server reports OK. Set last_status
    if(!isset($this->servers[$server]['last_status']) && $serverobj->get_statusok())
    {
    	$this->servers[$server]['last_status'] = $serverobj->get_statusok();
    	$this->servers[$server]['last_status_change'] = time();
    }
    	 
    // Set error message for non-responsive servers
    if($serverobj->get_statusok() != 1) 
    	$this->servers[$server]['last_status_reason'] = "Not responding";
    

    /*
     *  The following block checks various criteria for inclusion. 
     *  In terms of last update reason output, the first detected
     *  issue is reported, i.e. the order of tests matter for the output. 
     */

    // Exclude servers that aren't using a FQDN as hostname
    if(!IsFQDN($serverobj->get_hostname()))
    {
    	$this->set_server_notinclude($serverobj->get_hostname(), "Hostname not FQDN");
    }

    // Exclude servers not properly accepting HTTP/1.1 POST. See 
    // http://lists.gnupg.org/pipermail/gnupg-users/2013-February/046191.html 

    if($serverobj->get_http_post_error())
    {
    	$this->set_server_notinclude($serverobj->get_hostname(), "HTTP/1.1 POST error (417)");
    }

    /* Here we check for minimum compatibility versions for SKS and GnuKS software. If additional
     * software is detected we need to add it to this list. We could add a catch-all for unknown
     * software implementations, but as it is unlikely to see this it would only add overhead, so
     * it is more efficient to do this on an ad-hoc basis if it is detected in the pool. 	
     */
    if(
    	($serverobj->get_software() == "SKS" && (!$serverobj->version_satisfy_min($min_req)))
    	||($serverobj->get_software() == "GnuKS" && (!$serverobj->version_satisfy_min("0.9.2"))) // GnuKS 0.9.2 is based on SKS 1.1.3
    	)
    {
    	$this->set_server_notinclude($serverobj->get_hostname(), "SKS version < {$min_req}");
    }

    // Reverse proxy is a requirement for inclusion in the pool. 
    if(!$serverobj->get_is_reverse_proxy())
    {
	$this->set_server_notinclude($serverobj->get_hostname(), "Not running a reverse proxy");
    }

    // Server is viable for inclusion - Check if it is updated to proper number of keys
    if(isset($this->servers[$server]['last_status']) && $this->servers[$server]['last_status']==1)
    {     
     $diff = ($this->numkey - (int)($serverobj->get_numkeys()));
     if($diff >= 0) //Diff to 0 as numkey is set based on StdDev deviation now
     {
      $this->set_server_notinclude($serverobj->get_hostname(), "Missing keys");
     }
    }
    
     if(ServerIsExcluded($serverobj->get_hostname()))
     {
      $this->set_server_notinclude($serverobj->get_hostname(), "In exclude list");
     }
   } // end foreach
   
   /*
     * 	Calculate SRV weights for the servers
     *  Based on http://kfwebs.com/sks-keyservers-SRV.pdf
     * 
     */
     $run_pools = array(
		"EU",	// Europe
		"NA",	// North America
		"OC"	// Oceania
		);
	 
     foreach($run_pools as $srv_pool)
     {
     	// Initialize a new pool object if none exists
 		if(!isset($this->srv_pools[$srv_pool]))
     		$this->srv_pools[$srv_pool] = new pool_srv($srv_pool);
     	
     	// Run update run on pool
     	$this->srv_pools[$srv_pool]->run($arr, $this);
     }
      
  } // End function Run
  
  public function get_servers()
  {
   return $this->servers;
  }
    
  public function get_pool($pool)
  {
  	return $this->srv_pools[$pool];
  }
  
  public function get_statistics_data()
  {
  	$return_array = array();
  	
  	$return_array['numkeys'] = $this->numkey; 
  	$return_array['mean'] = $this->mean;
  	$return_array['median'] = $this->median;
  	$return_array['diff'] = $this->diff;
	$return_array['max_keys'] = $this->max_keys;
  	
  	return $return_array; 
  }
  
  public function get_numkey_history()
  {
	return $this->numkey_history; 
  }
 }
?>
