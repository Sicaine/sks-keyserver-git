<?
 /*
  *  status-srv/sks.inc.php
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
  error_reporting(E_ALL);
  
  date_default_timezone_set("UTC");
  function event_serverreturn($fd, $flag, $arg)
  {
    $serverobj = $arg[2];    
      if($fd !== false)
     {
         $json = "";
        
             while(($buf = fgets($fd, 1024)) !== false)
             {
                 $json .= $buf; 
             }
             
             if (!feof($fd)) 
             {
                echo "Error: popen never ended\n";
            }
            
            $sadd = new sks_peer($serverobj, null, $json);
             $serverobj->add_server($sadd);
            
            pclose($fd);
        }
        
        unset($serverobj->fh[$sadd->get_hostname()]);
        event_free($arg[0]); 
  } 
    
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
 
 class sks_servercollection
 {
  private $servers = array();
  private $servers_queue = array();
  public $created;
  private $events = array(); 
  public $event_base; 
  public $fh = array(); 
  
  public function sks_servercollection()
  {
   $this->created = time();
   $this->event_base = event_base_new(); 
  }
  
  // Queue handling
  
  public function add_server_to_queue($server)
  {
   if($this->do_add($server) && !isset($this->events[$server]))
   {
           $this->servers_queue[$server] = true; 
               
           $this->events[$server] = event_new();
           $this->fh[$server] = popen("/usr/bin/php -f /webs/sks-keyservers.net/status/sks_get_peer_data.php {$server}", "r");
           event_set($this->events[$server], $this->fh[$server], EV_TIMEOUT | EV_READ | EV_WRITE | EV_PERSIST, "event_serverreturn", array($this->events[$server], $this->event_base, &$this, $this->created));
           event_base_set($this->events[$server], $this->event_base);
           event_add($this->events[$server], 50000);
       }
  }
  
  public function get_queue_count()
  {
      return count($this->fh);
  }
     
  public function add_server($obj)
  {
   if($obj != null && $obj->get_hostname() != "")
   {     
       if(!isset($this->servers[$obj->get_hostname()]))
           $this->servers[$obj->get_hostname()] = $obj;
   }
  }
  
  public function get_servers()
  {
   return $this->servers;
  }
  
  public function include_ipv6()
  {
      $should_include_ipv6 = true;
    $count_ipv6 = 0; 
    $kfwebs_included = false; 
          
    foreach($this->servers as $server)
    {
        if($server->get_ipv6())
            $count_ipv6++; 
        
        if($server->get_hostname() == "keys.kfwebs.net")
            $kfwebs_included = true; 
    }
    
    if($kfwebs_included && $count_ipv6 == 1)
        return false; 
    else
        return true; 
  }
  
  public function get_server_by_name($name)
  {
      foreach($this->servers as $id=>$server)
      {
          if($server->get_hostname() == $name || $server->get_called_hostname() == $name)
              return $this->servers[$id];
      }
      return false; 
  }
  
  public function get_time()
  {
   return $this->created;
  }
  
  public function do_add($n)
  {
   if(isset($this->servers[$n]) || isset($this->servers_queue[$n])) 
           return false;
   else 
           return true;
  }
 }
 
 class sks_peer
 {
  private $hostname;
  private $called_hostname;
  private $server_contact;
  private $port;
  private $recon_port;
  private $numkeys;
  private $software;
  private $version;
  private $statusok = true;
  private $statusipv6ok = false;
  private $port80 = false;
  private $has_hkps = false; 
  private $hkps_port = 0; 
  private $statusfaultreason;
  private $peers = array();
  private $debug = 0;
  private $responsetime = -1;
  private $http_response_server;
  private $http_response_via;
  private $http_post_expect_error = false;
  private $affected_cve2014_3207 = true; 
  
  public function get_numkeys() { return $this->numkeys; }
  public function get_software() { return $this->software; }
  public function get_version() { return $this->version; }
  public function get_hostname() { return $this->hostname; }
  public function get_server_contact() { return strtr(strtoupper($this->server_contact), array("X" => "x")); }
  public function get_called_hostname() { return $this->called_hostname; }
  public function get_port() { return $this->port; }
  public function get_recon_port() { return $this->recon_port; }
  public function get_peers() {return $this->peers;}
  public function get_statusok() {return $this->statusok;}
  public function get_serversarr() {return $this->servers;}
  public function get_ipv6() {return $this->statusipv6ok;}
  public function get_has_hkps() {return $this->has_hkps;}
  public function get_hkps_port() {return $this->hkps_port;}
  public function get_port80() {return $this->port80;}
  public function get_responsetime() {return $this->responsetime;}
  public function get_http_response_server(){return $this->http_response_server;}
  public function get_has_http_response_via(){return $this->http_response_via;}
  public function get_is_loadbalanced(){return $this->is_loadbalanced();}
  public function get_is_reverse_proxy() {return $this->is_accepted_server_response();}
  public function get_http_post_error(){return $this->http_post_expect_error;}
  public function get_affected_cve2014_3207(){return $this->affected_cve2014_3207;}
  public function version_satisfy_min($min_version, $development = 0)
  {
      /*
       * Convert version string into array with
       * (major, minor, release)
       */
       $min_version_tuple = preg_split("/\./", $min_version);
       preg_match("/(\d+\.\d+\.\d+)/", $this->version, $matches);
       if(isset($matches[1]))
           $version_tuple = preg_split("/\./", $matches[1]);
       else
           $version_tuple = preg_split("/\./", $this->version);
       
       // Check major
       if((int)($version_tuple[0]) > (int)($min_version_tuple[0]))
           return true; 
       
       // Check minor
       if(
           ((int)($version_tuple[0]) == (int)($min_version_tuple[0])) 
           && ((int)($version_tuple[1]) > (int)($min_version_tuple[1]))
           )
           return true; 
       
       // Check release
       if(
           ((int)($version_tuple[0]) == (int)($min_version_tuple[0])) 
           && ((int)($version_tuple[1]) == (int)($min_version_tuple[1]))
           && ((int)($version_tuple[2]) >= (int)($min_version_tuple[2]))
           && ($development ? substr($this->version, -1) == "+" : true)
           )
           return true;
       
       // If not true by now, return false
           return false; 
  }
  
  public function sks_get_peer_data()
  {
      // Open handle
   $fp = popen("/usr/bin/php -f /webs/sks-keyservers.net/status/sks_get_peer_data.php {$this->hostname}", "r");
   
   // Read back handle
   $fp_rb = "";
   
   while(($buf = fgets($fp, 1024)) !== false)
   {
         $fp_rb .= $buf; 
   } 
   
   pclose($fp);
   return $fp_rb;
  }
  
  public function sks_peer(&$servers, $hostname=false, $jinp=false)
  {
   if($jinp===false)
   {
       $this->hostname = $hostname; 
       $jinp = $this->sks_get_peer_data(); 
   }
     
   // Convert back from json
   $fp_rb_dec = json_decode($jinp, true);
   
   $this->hostname = strtolower($fp_rb_dec['hostname']);
   $this->called_hostname = strtolower($fp_rb_dec['called_hostname']);
   
   $this->port = $fp_rb_dec['port'];
   
   $this->statusok = $fp_rb_dec['statusok'];
   if($this->statusok)
   {
     $this->responsetime = $fp_rb_dec['responsetime'];
     $this->http_response_server = $fp_rb_dec['http_response_server'];
     $this->http_response_via = $fp_rb_dec['http_response_via'];
     $this->numkeys = $fp_rb_dec['numkeys'];
     $this->software = $fp_rb_dec['software'];
     $this->version = $fp_rb_dec['version'];
     $this->server_contact = $fp_rb_dec['server_contact'];
     $this->recon_port = $fp_rb_dec['recon_port'];
     $this->http_post_expect_error = $fp_rb_dec['postExpect'];     
    
     if(isset($fp_rb_dec['peers']) && is_array($fp_rb_dec['peers']))
        $this->peers = $fp_rb_dec['peers'];
     else
        $this->peers = array(); 
    
     $this->statusipv6ok = $fp_rb_dec['statusipv6ok'];
     $this->port80 = $fp_rb_dec['port80'];
     $this->has_hkps = $fp_rb_dec['has_hkps'];
     $this->hkps_port = $fp_rb_dec['hkps_port'];
     $this->affected_cve2014_3207 = $fp_rb_dec['cve-2014-3207'];
   }
   
   if(is_array($this->peers) && count($this->peers) > 0)
   {
           foreach($this->peers as $peer)
           {
               $servers->add_server_to_queue($peer);    
           }
   }
       
   } // End function sks_peer

   private function is_loadbalanced()
   {
   	$loadbalanced = array(
		"keys2.kfwebs.net",
		"sks.undergrid.net",
		"sks.fidocon.de", 
		"keyserver.searchy.nl",
		"keyserver.codinginfinity.com"
		); 
	return $this->is_accepted_server_response() && in_array($this->hostname, $loadbalanced); 
   }
   
   private function is_accepted_server_response()
   {
    $is_accepted_server = false;
    
    // Check if Server: header contain a valid response
    $accepted_http_server_list = array("nginx", "apache");
    foreach($accepted_http_server_list as $revprox)
    {
          if(strstr(strtolower($this->http_response_server), $revprox) !== false) 
              $is_accepted_server = true; 
      }
    
    // Check if Via: header is set, presume proxy if it is
    if($this->http_response_via)
        $is_accepted_server = true; 
        
      return     $is_accepted_server;    
   } // end is_accepted_server_response
  
 } // End class
 
 class sks_stats
 {
  public function sks_stats()
  {
   $servers = new sks_servercollection;
   $sadd = new sks_peer($servers, "keys2.kfwebs.net");
   $servers->add_server($sadd);
   
   if(count($servers->get_servers())<3)
   {
    $sadd = new sks_peer($servers, "sks-peer.spodhuis.org");
    $servers->add_server($sadd);
   }
   
   if(count($servers->get_servers())<3)
   {
    $sadd = new sks_peer($servers, "keyserver.ccc-hanau.de");
    $servers->add_server($sadd);
   }

   if(count($servers->get_servers())<3)
   {
    $sadd = new sks_peer($servers, "keyserver.gingerbear.net");
    $servers->add_server($sadd);
   }

   if(count($servers->get_servers())<3)
   {
    $sadd = new sks_peer($servers, "sks.undergrid.net");
    $servers->add_server($sadd);
   }
   
   echo "Done adding primaries\n";
   event_base_loop($servers->event_base);
   
   echo "Done looping\n";
   echo ""; 
   
   if(!file_exists(dirname(__FILE__)."/sks_cache_status_collection.serialized"))
           $status_collection = new sks_status_collection();
   else
           $status_collection = unserialize(file_get_contents(dirname(__FILE__)."/sks_cache_status_collection.serialized"));
           
   $status_collection->run($servers);
   $nk = $status_collection->get_statistics_data(); 
   echo "Numkey set to:\t".$nk['numkeys']; 
   
   file_put_contents(dirname(__FILE__)."/sks_cache.serialized",serialize($servers));
   file_put_contents(dirname(__FILE__)."/sks_cache_status_collection.serialized",serialize($status_collection));
  }
 }
?>
