<?
 /*
  *  status-srv/sks-srv.inc.php
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
  
  include_once("/webs/sks-keyservers.net/status/exclude.inc.php");
  
  function event_srvreturn($fd, $flag, $arg)
  {
    $pool_obj = $arg[2];    
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
             $pool_obj->add_data($json, $arg[4]);
            
            pclose($fd);
        }
        
        unset($pool_obj->fh[$arg[3]]);
        event_free($arg[0]); 
  } 
  
 
       class pool_srv
       {
          private $poolname;
          private $server_response_statistics = array(); 
          private $server_srv_weight = array();
          private $events = array();
          private $sever_bandwidth_information = array();  
          public $event_base; 
          public $fh = array(); 
           
           /*
            *     Constructor
            */
            
           public function pool_srv($poolname)
           {
               $this->poolname = $poolname;
           }
           
           /*
            * Function to return SRV weight for a specific server
            */
           public function get_srvweight($server)
           {
               if(isset($this->server_srv_weight[$server]))
                   return $this->server_srv_weight[$server];
               else
                   return 0; 
           }
           
           /*
            * Function to return top online servers by SRV weight
            */
            
           public function get_top_servers($num=10)
           {
               $srv_weights = $this->server_srv_weight;
               arsort($srv_weights);
               $return_array = array();
               $c = 0;
               foreach($srv_weights as $servername=>$weight)
               {
                   if($c > $num)
                       continue; 
                   $return_array[$servername] = $weight; 
                   $c++;
               }
               return $return_array;                     
           }
           
           public function add_data($json, $max_count)
           {
               $rec_json_dec = json_decode($json, true);
               
               if(isset($rec_json_dec['statusok']) && $rec_json_dec['statusok'])
               {
                   $this->server_response_statistics[$rec_json_dec['keyserver']][$rec_json_dec['hostserver']][] = $rec_json_dec['download_time'];    
               }
               else
               {
                   $this->server_response_statistics[$rec_json_dec['keyserver']][$rec_json_dec['hostserver']][] = -1;
               }
               
               /*
                * Clean up non-used data
                */               
                $c = count($this->server_response_statistics[$rec_json_dec['keyserver']][$rec_json_dec['hostserver']]);
                $c2 = 0;
                if($c > $max_count)
                {
                    $delete = $c - $max_count;
                    foreach($this->server_response_statistics[$rec_json_dec['keyserver']][$rec_json_dec['hostserver']] as $k => $v)
                    {
                    	if($c2<$delete)
                        	unset($this->server_response_statistics[$rec_json_dec['keyserver']][$rec_json_dec['hostserver']][$k]);
                        
                        $c2++;
                    }
                }
           }
           
           /*
            *     Calculate SRV weights for the servers
          *     Based on http://kfwebs.com/sks-keyservers-SRV.pdf
            */
           public function run(&$servers, &$server_collection)
           {
               global $debug; 
               $this->event_base = event_base_new();
               $this->update_bandwidth_information();
                    
        /*
         *  Define clients to use for response time measurements
         *  The format is [key] = baseurl 
         *     Full example: 
         *  http://sks-keyservers.net/clients/key_retrieval.php?keyserver=keys2.kfwebs.net&key_id=0x16e0cf8d6b0b9508
         */
         
            $responsetime_measurement_clients = array();
            $key_id = "0x16e0cf8d6b0b9508";
            
            switch($this->poolname)
            {
                case "EU": 
                    $responsetime_measurement_clients = array(
                        'kfwebs.net' => "http://sks-keyservers.net/clients/key_retrieval.php",
                        'oslo.kfwebs.net' => "http://keys2.kfwebs.net/clients/key_retrieval.php",
                        'bakacsin.ki.iif.hu' => "http://keys.niif.hu/cgi-bin/keycheck.pl",
                        'keyserver.ccc-hanau.de' => "http://keyserver.ccc-hanau.de/key-retrieval.pl",
                        'keyserver.stack.nl' => "http://keyserver.stack.nl/key_retrieval.pl"
                    );
                    break; 
                case "NA":
                     $responsetime_measurement_clients = array(
                         'keyserver.fug.com.br' => "http://keyserver.fug.com.br/key_retrieval.php",
                         'sks.primeirospassos.org' => "http://sks.primeirospassos.org/key_retrieval.php",
                         'keyserver.kjsl.org' => "http://keyserver.kjsl.org:81/cgi-bin/key_retrieval.pl" 
                     );
                     
                    break;
                case "OC":
                    $responsetime_measurement_clients = array(
                        'keys.riverwillow.net.au' => "http://keys.riverwillow.net.au/key_retrieval.php",
                        'keys.riverwillow.com.au' => "http://keys.riverwillow.com.au/key_retrieval.php",
                        'mirror.oeg.com.au' => "http://mirror.oeg.com.au/key_retrieval.php"
                    );
                    break;
                case "SA":
                    $responsetime_measurement_clients = array(
                        'www.transminuano.com.br' => "http://www.transminuano.com.br/key_retrieval.php"
                    );
                    break;
            }
       // Clean up data
       foreach($this->server_response_statistics as $e)
       {
        	if(ServerIsExcluded($e))
        	{
        		unset($this->server_response_statistics[$e]);
        		continue;	
        	}
        	
        	foreach($e as $rs => $val)
        	{
        		if(!in_array($rs, $responsetime_measurement_clients) 
        			&& isset($this->server_response_statistics[$e]) 
				&& isset($this->server_response_statistics[$e][$rs]))
        				unset($this->server_response_statistics[$e][$rs]);
        	}
        }
               
        /*
         *    Grab updated data on the servers and add it to 
         *    the store $server_response_statistics
         */
             $max_R = 40; // n in document 
             
             $server_collection_array = $server_collection->get_servers();
             
             foreach($server_collection_array as $servername=>$data)
             {
                   // Only update good servers
                 if(!isset($data['last_status']) || $data['last_status'] != 1)
                     continue;
                 
                 foreach($responsetime_measurement_clients as $client=>$baseurl)
                 {
                     $url = $baseurl."?keyserver={$servername}&key_id={$key_id}";
                    
                     $this->events[$client."-".$servername] = event_new();
                       $this->fh[$client."-".$servername] = popen("/usr/bin/php -f /webs/sks-keyservers.net/status/sks_get_srv_measurement.php \"{$url}\"", "r");
                       event_set($this->events[$client."-".$servername], $this->fh[$client."-".$servername], EV_TIMEOUT | EV_READ | EV_WRITE | EV_PERSIST, "event_srvreturn", array($this->events[$client."-".$servername], $this->event_base, &$this, $client."-".$servername, $max_R));
                       event_base_set($this->events[$client."-".$servername], $this->event_base);
                       event_add($this->events[$client."-".$servername], 35000);
                 } 
             }
             
             echo "Start looping sks-srv loop ({$this->poolname})\n";
             
             event_base_loop($this->event_base);
             
             unset($this->event_base);
             unset($this->events);
             unset($this->fh);
             
             
             
             /*
              * Calculate R for each server and the mean, stddev for the pool. 
              * Do this as a two run pass, excluding those more than 2 stddev away
              */
              
          
              /* Initialize R-array for calculation across servers
               * Data can be found in $this->server_response_statistics
               * Format of array is keyserver > hostserver (monitoring station) > data
               * Note that data is added iteratively, so need to be array_reverse()'d  
               * while performing calculations. 
               */ 
               
              $R_array = array(); 
              
              foreach($this->server_response_statistics as $ks => $rs)
              {
                  // Re-initialize R for each new server
                  $R = 0;
                  $Rweight = 0; 
                  $min_threshold = 0.1; 
                  
                  foreach($responsetime_measurement_clients as $ms=>$data_not_used)
                  {
                      if(!isset($rs[$ms]))
                          continue; 
                          
                      $data = $rs[$ms];
                      array_reverse($data);
                      
                      $n = count($data);
                      $i = 0;  
                      foreach($data as $d)
                      {                          
                          // Check for min threshold, lower than this 
                          // is expected to be localhost
                          // This also skip -1 entries. 
                          if ($d < $min_threshold)
                          {
                              // skip
                          }
                          else
                          {
                              $w = 1 - ($i / $n); 
                              $R = $R + $w * $d; 
                              $Rweight = $Rweight + $w;
                              $i++;     
                          }
                      }    
                  }
                       
                  // Check for division by zero
                  if($Rweight == 0)
                      continue; 
                  $R = $R / $Rweight; 
                  
                  $R_array[$ks] = $R; 
              }
              
              if($debug>=1)
                  print_r($R_array); 
              
              $s = $this->calculate_statistics($R_array);
              
              if($debug>=1)
                  print_r($s);
              
              $R_array2 = array();
              foreach($R_array as $server => $responsetime)
              {
                  if($responsetime < ($s['mean'] - 2 * $s['stddev']))
                      continue;
                      
                  if($responsetime > ($s['mean'] + 2 * $s['stddev']))
                      continue;  
                      
                  $R_array2[$server] = $responsetime; 
              }
              
              $s = $this->calculate_statistics($R_array2);
              
              if($debug>=1)
                  print_r($s);
              
              /*
               * SRV
               * Calculate the actual SRV weights
               * Input the weight for the various servers in $server_srv_weight
               */
               
               // Reset array - We only want online servers included, 
               // and they will be re-added
               $this->server_srv_weight = array(); 
               
                             
              /*
               * Initialize variables
               */
              $alpha = 150; 
              $beta_R = 500; 
              $beta_B = 500; 
              $beta_P = 1; 
              $rho = 100; // Addon for reverse-enabled server
	      $xi = 200; // Addon for load-balanced server
              $y = 2; 
              $R_ceil = 2000; // phi
              
              $individual_server = $servers->get_servers();
              
              foreach($individual_server as $ks=>$ks_obj)
              {
                  // Only Calculate SRV weights for good servers
                  if(!isset($server_collection_array[$ks]['last_status']) 
                     || $server_collection_array[$ks]['last_status'] != 1)
                     continue;                 
                 
                 if(!isset($R_array[$ks]))
                     continue; 
                 
                 $SRV = 0; 
                 
                 // Add alpha
                 $SRV += $alpha; 
                 
                 // Add R based weight
                 $SRV_r = $beta_R * (1 / pow(($R_array[$ks] - 
                                    ($s['mean'] - 2.5 * $s['stddev'])), $y));
                 
                 if($SRV_r > $R_ceil)
                     $SRV_r = $R_ceil; 
                     
                 $SRV += $SRV_r; 
                 
                 // Add bandwidth based weight here
                 $SRV += $beta_B * ($this->get_bandwidth($ks) / 
                                     $this->get_total_bandwidth());
                 
                 // Add weight based on reverse proxy
                 $SRV += $beta_P * ($ks_obj->get_is_reverse_proxy()) * $rho; 

                 // Add weight based on load balanced server
                 $SRV += $beta_P * ($ks_obj->get_is_loadbalanced()) * $xi; 
                 
                 $this->server_srv_weight[$ks] = round($SRV); 
              }
             
             if($debug>=1)
                 print_r($this->server_srv_weight);
              
           } // End function Run
           
       
       /*
        * Function to update bandwidth information
        */
       private function update_bandwidth_information()
       {
           // Add entries. Keysever : Mbit/s upstream
           $this->sever_bandwidth_information = array(
            'keys.kfwebs.net'              => 3,
            'keys2.kfwebs.net'             => 10,
            'keyserver.saol.no-ip.com'     => 10,
            'keyserver.fug.com.br'         => 1000,
            'keyserver.ccc-hanau.de'       => 100,
            'keyserver.kjsl.org'           => 1000,
            'keyserver.layer42.net'        => 1000,
            'keys.niif.hu'                 => 1000,
            'pgpkeys.co.uk'                => 100,
            'pgpkeys.eu'                   => 100,
            'keyserver.veloxis.de'         => 100,
            'keyserver.rainydayz.org'      => 3,
            'keyservers.org'               => 5,
            'keys.riverwillow.net.au'      => 100,
            'pks.aaiedu.hr'                => 1000,
            'sks.spodhuis.org'             => 80,
            'key-server.org'               => 100,
            'keyserver.durcheinandertal.ch'=> 100,
            'thesecuregroup.com'           => 100,
            'ranger.ky9k.org'              => 1000,
            'keyserver.linux.it'           => 100,
            'key.ip6.li'                   => 1000,
            'keys.wuschelpuschel.org'      => 100,
            'zomers.be'                    => 100,
            'keyserver.adamas.ai'          => 100,
            'keyserver.secretresearchfacility.com' => 100,
            'keyserver.gingerbear.net'     => 5,
            'sks.keyservers.net'           => 10,
            'keyserver.borgnet.us'         => 7,
            'keyserver.maze.io'            => 1000,
            'sks.karotte.org'			   => 100,
            'a.keyserver.pki.scientia.net' => 100,
            'pgp.megagod.net'              => 100,
            'keys.internet-sicherheit.de'  => 100,
            'keyserver.undergrid.net'      => 250,
            'sks.mrball.net'               => 20,
	    'sks.undergrid.net'		   => 1000,
	    'pks.ms.mff.cuni.cz'	   => 1000,
	    'pgpkey.org'		   => 1000,
	    'sks.alpha-labs.net'	   => 1000,
	    'keys.alderwick.co.uk'	   => 280,
	    'keys2.alderwick.co.uk'        => 140,
            'pgp.benny-baumann.de'         => 200,
	    'sks-server.randala.com'	   => 100,
	    'keyserver.blupill.com'	   => 350,
	    'keyserver.matteoswelt.de'     => 100,
	    'key.bbs4.us'		   => 1000,
	    'keyserver.stack.nl'	   => 300,
	    'keyserver.mattrude.com'	   => 400,
	    'keyserver.codinginfinity.com' => 100
           );
       }
       
       /*
        * Function to return bandwidth information of a specific server
        */
       public function get_bandwidth($server)
       {
           if(isset($this->sever_bandwidth_information[$server]))
               return $this->sever_bandwidth_information[$server]; 
           else
               return 0; 
       }
       
       /*
        * Function to return sum of total bandwidth information recorded
        */
       public function get_total_bandwidth()
       {
           return array_sum($this->sever_bandwidth_information);
       }    
           /*
     * Function for calculation statistics
     */   
    private function calculate_statistics(&$ser)
       {
           global $debug; 
           $return_array = array();
           
           // Calculate the mean number of keys in selection
           $mean_total = 0;
           $mean_count = 0;
                      
           foreach($ser as $num)
           {
               $mean_total += $num;
               $mean_count++;
           }
           
           if($mean_count == 0 )
           {
           if($debug>=1)
               echo "Mean cound issue:\t$mean_count\n";	
               $mean_count = 1;
               $mean = 1;
           }           
           else
               $mean = $mean_total / $mean_count;
            
           if($debug>=1)
               echo "Mean\t$mean\n";
           
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
           $return_array['count'] = $mean_count;
           
           return $return_array;          
       } // ENd statistics function
       } // End class
       
?>
