<?php
 /*
  *  status-srv/sks_get_peer_data.php
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
  
  if(!isset($argv[1])) exit; 
  
  $host = $argv[1]; //Set host to first argument
  $port=11371; //Manually set port
  
  $debug = false;
  $require_CA = true; 
    
  include_once("/webs/sks-keyservers.net/status/exclude.inc.php");
  require_once('Net/DNS2.php');
    
  if(ServerIsExcluded($host)) 
  	exit; 
  
  /*
   * Force sleep at random interval to spread load
   */ 
   
  sleep(rand(1,15));
  
  function gethostbyname6($host, $try_a = false) {
        // get AAAA record for $host
        // if $try_a is true, if AAAA fails, it tries for A
        // the first match found is returned
        // otherwise returns false

        $dns = gethostbynamel6($host, $try_a);
        if ($dns == false) { return false; }
        else { return $dns[0]; }
    }

    function gethostbynamel6($host, $try_a = false) {
        // get AAAA records for $host,
        // if $try_a is true, if AAAA fails, it tries for A
        // results are returned in an array of ips found matching type
        // otherwise returns false

        $dns6 = dns_get_record($host, DNS_AAAA);
        if ($try_a == true) {
            $dns4 = dns_get_record($host, DNS_A);
            $dns = array_merge($dns4, $dns6);
        }
        else { $dns = $dns6; }
        $ip6 = array();
        $ip4 = array();
        foreach ($dns as $record) {
            if ($record["type"] == "A") {
                $ip4[] = $record["ip"];
            }
            if ($record["type"] == "AAAA") {
                $ip6[] = $record["ipv6"];
            }
        }
        if (count($ip6) < 1) {
            if ($try_a == true) {
                if (count($ip4) < 1) {
                    return false;
                }
                else {
                    return $ip4;
                }
            }
            else {
                return false;
            }
        }
        else {
            return $ip6;
        }
    }
  

  function microtime_float()
  {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
  }
    
   
   $return_array['hostname'] = $host;
   $return_array['called_hostname'] = $host;
   
   $return_array['port'] = $port;
   
   $return_array['statusok'] = true; //Default status OK to true
   
   $timestart = microtime_float(); 
   if($debug)
    echo $host; 
    
   $ch = curl_init("http://$host:$port/pks/lookup?op=stats&options=mr");
   
   /*
    * Force the use of HTTP Host header. In the event a virtual machine
    * setup is used, we want to only include servers configured 
    * to accept the pool hosts. 
    */
   $http_headers = array("Host: pool.sks-keyservers.net");
   
   curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);
   curl_setopt($ch, CURLOPT_HEADER, 1); 
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   /*
    * 	Force IPv4 check here, IPv6 is checked for later
    * 	IPv6 only servers will not be included in the pool as long as this remain in place.
    * 	Require PHP 5.3 or later - see https://bugs.php.net/bug.php?id=47739  
    */
   curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);  
   curl_setopt($ch, CURLOPT_TIMEOUT, 15);
   if(($ret=curl_exec($ch))===FALSE || curl_getinfo($ch, CURLINFO_HTTP_CODE) != "200")
   {
    curl_close($ch);
    $return_array['statusok']=false;
   }
   else
   {   
    if($debug)
     echo curl_error($ch); 
     
    curl_close($ch);
    $return_array['responsetime'] = microtime_float() - $timestart; 
    
    // Check server version in HTTP response
    
    $return_array['http_response_server'] = false; 
    $return_array['http_response_via'] = false; 
    
    preg_match("/Server:\s+(.+)/", $ret, $matches);
    if(isset($matches[1]))
    	$return_array['http_response_server'] = trim($matches[1]); 
    unset($matches);
	
	preg_match("/Via:\s+(.+)/", $ret, $matches);
    if(isset($matches[1]))
    	$return_array['http_response_via'] = true;
    unset($matches);


    // We are done checking server headers. Chomp it off
    $header_split = strpos($ret, "\r\n\r\n");  
    $ret = substr($ret, $header_split); 

    // Set Software default to SKS, newer forks should have explicitly software names set
    $return_array['software'] = "SKS";

    // Check for json response and prosess that
    $json_server_data = json_decode($ret, true); 
    if($json_server_data !== NULL) 
    {
	if($debug) 
	{
		echo "JSON prosessed OK"; 
    		print_r($json_server_data); 
	}

	// workaround check for hockeypuck bug 
        // https://bugs.launchpad.net/hockeypuck/+bug/1313096
	if($json_server_data['hostname'] != "pool.sks-keyservers.net") 
	    	$return_array['hostname'] = $json_server_data['hostname'];

    	@$return_array['server_contact'] = $json_server_data['server_contact'];
        @$return_array['software'] = $json_server_data['software']; 
        @$return_array['version'] = $json_server_data['version']; 
        @$return_array['numkeys'] = $json_server_data['numkeys']; 
	// $return_array['recon_port']
 	// $return_array['peers'][]

    }
    else    // No json response found in request, process using old-style HTML parsing
    {
	    // Set hostname based on server information
	    preg_match("#<tr><td>Hostname:(?:</td>)?<td>([^<]+)(?:</td></tr>)?#", $ret, $matches);
	    if(isset($matches[1]))
	    	$return_array['hostname'] = trim($matches[1]); 
	    unset($matches);
	    
	    
	    // Set server contact server information
	    $return_array['server_contact'] = "";
	    preg_match("#<tr><td>Server contact:(?:</td>)?<td>([^<]+)(?:</td></tr>)?#", $ret, $matches);
	    if(isset($matches[1]))
	    	$return_array['server_contact'] = trim($matches[1]); 
	    unset($matches);
	     
	    //Set recon port based on server information
	    $return_array['recon_port'] = "(null)";
	    preg_match("#<tr><td>Recon port:(?:</td>)?<td>([^<]+)(?:</td></tr>)?#", $ret, $matches);
	    if(isset($matches[1]))
	    	$return_array['recon_port'] = trim($matches[1]); 
	    unset($matches);
	    
	    // Set number of keys
	    preg_match("/Total number of keys:\s+(\d+)/",$ret,$matches);
	    if(isset($matches[1])) 
	    	$return_array['numkeys'] = $matches[1];
	    else
	    	$return_array['numkeys'] = 0;
	    	
	    unset($matches);
	    	    
	    $matches = null; 
	    preg_match("#<tr><td>Software:(?:</td>)?<td>([^<]+)(?:</td></tr>)?#", $ret, $matches);
	    if(isset($matches[1])) 
	    	$return_array['software'] = $matches[1];	
	    unset($matches);
	    
	    // Set version
	    preg_match("/Version:.+?([\d\.\+]+)/",$ret,$matches);
	    if(isset($matches[1])) 
	    	$return_array['version'] = $matches[1];
	    else
	    	$return_array['version'] = 0;
	    	
	    unset($matches);
	    
	    // populate peers
	    $ret = strtr($ret,array("\n"=>""));
	    preg_match("/<h2>Gossip Peers<\/h2><table[^>]*>(.*?)<\/table>/",$ret,$matches);
	    if(isset($matches[1]))
	    {
	    	preg_match_all("/<tr><td>([a-zA-Z0-9\.\-]+)\s+(\d+)/",$matches[1],$matches2);
	    
		    foreach($matches2[1] as $id=>$hosts)
		    {
		     $return_array['peers'][] = strtolower($hosts);
		    }
	    }
	    
	   
	    unset($matches); 
	    unset($matches2);
	    
   }

   // Set host to detected hostname rather than passed peer data
   $host = $return_array['hostname'];
   
   
   // SETUP ipv6
   $blacklistv6 = array();
   // Check IPv6 status
   $return_array['statusipv6ok'] = false;
    
   $ipv6_addy = gethostbyname6($host);
   if(!is_array($ipv6_addy) && $ipv6_addy !== false && !in_array($host, $blacklistv6))
   {
    $sock = socket_create(AF_INET6, SOCK_STREAM, SOL_TCP);
    socket_set_option($sock, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 5, 'usec' => 0)); 
    if(@socket_connect($sock, $ipv6_addy, 11371)) 
    	$return_array['statusipv6ok'] = true;
    	 
    socket_close($sock);
   }
   
   /* 
    * Check POST data with Expect header. (Issue 12)
    */
   
   $return_array['postExpect'] = false;
   
   $http_headers = array("Host: pool.sks-keyservers.net", "Expect: 100-continue");
   $chPE = curl_init("http://$host:$port/pks/add");
   curl_setopt($chPE, CURLOPT_HTTPHEADER, $http_headers);
   curl_setopt($chPE, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
   $key = <<<EOF
-----BEGIN PGP PUBLIC KEY BLOCK-----
-----END PGP PUBLIC KEY BLOCK-----
EOF;
   
   $post_fields = array("keytext" => urlencode($key));
   $post_fields_string = "";
   foreach($post_fields as $key=>$value) 
   { 
    $post_fields_string .= $key.'='.$value.'&'; 
   }
   curl_setopt($chPE, CURLOPT_POST, count($post_fields));
   curl_setopt($chPE, CURLOPT_POSTFIELDS, $post_fields_string);
   curl_setopt($chPE, CURLOPT_RETURNTRANSFER, 1);
   if($ret = curl_exec($chPE) !== FALSE)
   {
   	if(curl_getinfo($chPE, CURLINFO_HTTP_CODE) == 417)
   	  $return_array['postExpect'] = true;  
   	curl_close($chPE); 
   }
   /*
    * Check for port 80
    */
   
   $return_array['port80'] = false;
   
   $http_headers = array("Host: p80.pool.sks-keyservers.net");
   
   $ch80 = curl_init("http://$host:80/pks/lookup?op=stats");
   curl_setopt($ch80, CURLOPT_HTTPHEADER, $http_headers);
   curl_setopt($ch80, CURLOPT_HEADER, 1); 
   curl_setopt($ch80, CURLOPT_RETURNTRANSFER, 1);
   /*
    * 	Force IPv4 check here, IPv6 is checked for later
    * 	IPv6 only servers will not be included in the pool as long as this remain in place.
    * 	Require PHP 5.3 or later - see https://bugs.php.net/bug.php?id=47739  
    */
   curl_setopt($ch80, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);  
   curl_setopt($ch80, CURLOPT_TIMEOUT, 15);
   if(($ret=curl_exec($ch80))===FALSE)
   {
    curl_close($ch80);
    $return_array['port80']=false;
   }
   else
   {
   	if(curl_getinfo($ch80, CURLINFO_HTTP_CODE) == "200" && curl_getinfo($ch80, CURLINFO_SIZE_DOWNLOAD) > 2000)
   		   $return_array['port80'] = true;
    curl_close($ch80);
   }
   
   /*
    * Check for HTTPS/HKPS support. First a DNS lookup for a SRV record
    * is performed. If none is found, try the default port of 443.  
    */
    
    $return_array['has_hkps'] = false;
    $return_array['hkps_port'] = false;  
    
    $resolver = new Net_DNS2_Resolver(array('nameservers' => array('127.0.0.1')));
    $hkps_has_srv = true;
    $hkps_port = 0; 

     
    try
    {
      $resolver_result = $resolver->query('_pgpkey-https._tcp.'.$return_array['hostname'], 'SRV');
    } catch(Net_DNS2_Exception $e) 
    {   
        if($debug)
          echo "::query() failed: ", $e->getMessage(), "\n";      
        
        $hkps_has_srv = false;
    }
    
    if($hkps_has_srv)
    {
       foreach($resolver_result->answer as $SRVrr)
       {
          if($debug)
              printf("port=%d, host=%s\n", $SRVrr->port, $SRVrr->target);

          if($SRVrr->type !== 'SRV') 
          {
            if($debug)
            	echo "Record type not SRV, bugging out\n";       
            
            continue;	
          }
             
          
          if($SRVrr->target != $return_array['hostname']) 
          	{
          		if($debug)
            		echo "Target not matching, bugging out\n";
            	
            	continue;
          	}
          	
          
          if($SRVrr->port < 1 || $SRVrr->port > 65536)
          {
          	  if($debug)
            		echo "Port mismatch, bugging out\n";	
          
          	  continue;
          }
             
          
          $hkps_port = $SRVrr->port; 
       	  break;
       }
    }
    
    if(!$hkps_has_srv)
    	$hkps_port = 443;
   
   if($debug) 
       echo "host at this stage (XX1123) is {$host}\n";
 
   $curl_ip = gethostbyname($host);
   if($curl_ip == $host)
   {
     $return_array['has_hkps']=false;
   }
   else
   {
	   $http_headers = array("Host: hkps.pool.sks-keyservers.net");
	   
	   $chhkps = curl_init("https://hkps.pool.sks-keyservers.net:$hkps_port/pks/lookup?op=stats");
	   curl_setopt($chhkps, CURLOPT_HTTPHEADER, $http_headers);
	   curl_setopt($chhkps, CURLOPT_HEADER, 1); 
	   curl_setopt($chhkps, CURLOPT_RETURNTRANSFER, 1);
	   curl_setopt($chhkps, CURLOPT_CAINFO, '/webs/sks-keyservers.net/sks-keyservers.netCA.pem');
	   curl_setopt($chhkps, CURLOPT_CAPATH, '/dev/null');
	   // CURLOPT_CRLFILE is supported as of PHP 5.5.4 (patch for other 
	   // versions submitted at https://bugs.php.net/bug.php?id=65575 )
	   curl_setopt($chhkps, CURLOPT_CRLFILE, '/webs/sks-keyservers.net/ca/crl.pem'); 
	   /* CURLOPT_RESOLVE require PHP 5.5 or later */
	   curl_setopt($chhkps, CURLOPT_RESOLVE, array("hkps.pool.sks-keyservers.net:{$hkps_port}:{$curl_ip}"));
	   curl_setopt($chhkps, CURLOPT_SSL_VERIFYHOST, 2);
	   curl_setopt($chhkps, CURLOPT_SSL_VERIFYPEER, true);
	   
	   /*
	    * 	Force IPv4 check here, IPv6 is checked for later
	    * 	IPv6 only servers will not be included in the pool as long as this remain in place.
	    * 	Require PHP 5.3 or later - see https://bugs.php.net/bug.php?id=47739  
	    */
	   curl_setopt($chhkps, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);  
	   curl_setopt($chhkps, CURLOPT_TIMEOUT, 15);
	   if(($ret=curl_exec($chhkps))===FALSE)
	   {
	    if($debug)
	    	echo curl_error($chhkps);
	    	 
	    curl_close($chhkps);
	    $return_array['has_hkps']=false;
	   }
	   else
	   {
	    if($debug)
	    	echo curl_error($chhkps);

	    if($debug)
	    	echo "SSL verification result: ".curl_getinfo($chhkps, CURLINFO_SSL_VERIFYRESULT)."\n";
	    	      
	   	if(curl_getinfo($chhkps, CURLINFO_HTTP_CODE) == "200" && curl_getinfo($chhkps, CURLINFO_SIZE_DOWNLOAD) > 2000)
	   	{
	    	$return_array['has_hkps'] = true;
	    	$return_array['hkps_port'] = $hkps_port;
	   	}
	   		
	   curl_close($chhkps);
	   }
   	}

	/*
	 * Check for CVE-2014-3207 
	 */	
	$return_array['cve-2014-3207'] = true;
	$runfile = dirname(__FILE__); 
	$ret = `${runfile}/test_cve-2014-3207.sh $host`;

	if(strpos($ret, "not affected") !== FALSE)
		$return_array['cve-2014-3207'] = false;
   }    
   // Return json encoded data
   $json =  json_encode($return_array);
   echo $json; 
?>
