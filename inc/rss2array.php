<?
 /*
  *
  * RSS parsing functions and HTML grabber
  * Courtesy of KF Webs
  * http://www.kfwebs.net
  * 
  * Feel free to use at your own discresion.
  * Links back to the website is encouraged.
  *
  * Example: 
  * $rss_array = rss2array("http://www.kfwebs.net/news/rss.xml");
  * 
  * Changelog:
  * Version 0.1 (2006-04-14)
  *  - First public
  *
  */
  
 function dechunk_data($data)
 {
  $darr = explode("\r\n",$data);
  $dcount = count($darr);
  $dcont = "";
  for($i=0;$i<$dcount;$i++)
  {
   if(hexdec($darr[$i])==strlen($darr[$i+1]))
   {
    $dcont.=$darr[$i+1];
    $i++;
   }
  }
  return $dcont;
 }
 
 function html_get($url,$port=80,$timeout=15)
 {
  if(preg_match("/^http:\/\/([^\/]+)(.+)$/",$url,$matches))
  {
   $host=$matches[1];
   $file=$matches[2];
   $data="";
   $err = "";
   
   // Open socket
   $fp = fsockopen($host,$port,$errno,$errstr,$timeout);
   if(!$fp) $err .= "$errstr ($errno)\n";
   
   $http_request = "GET {$file} HTTP/1.1\r\n";
   $http_request .= "Host: {$host}\r\n";
   $http_request .= "User-Agent: kfwebs.net HTTP Grabber\r\n";
   $http_request .= "Connection: close\r\n\r\n";
   
   fwrite($fp,$http_request);
   $timeout = time() + $timeout;
   while(!feof($fp) && time() < $timeout)
   {
    $data .= fgets($fp,4096);
   }
   fclose($fp);
   $head="";
   $cont="";
   $pos=0;
   while(!preg_match("/\r?\n\r?\n$/",$head))
   {
    $head.=$data{$pos++};
   }
   $cont=substr($data,$pos);
   if(strpos($head,"HTTP/1.1 200 OK")!==false && strpos($head,"Transfer-Encoding: chunked")!==false) return dechunk_data($cont);
   elseif(strpos($head,"HTTP/1.1 200 OK")!==false && strpos($head,"Transfer-Encoding: chunked")===false) return $cont;
   else return "ERROR";
  }
  else
  {
   return "invalid url";
  }
 }
  
 function startElement($parser, $name, $attrs)
 {
  global $internal_data;
  switch($name)
  {
   case 'CHANNEL': 
    $internal_data['in_channel'] = true; 
   break;
   case 'ITEM': 
    $internal_data['in_item'] = true;
    $internal_data['items'][$internal_data['item_counter']] = array();
   break;
  }
  $internal_data['cur_name'] = $name;
 }
 
 function endElement($parser, $name)
 {
  global $internal_data;
  $internal_data['cur_name'] = "";
  switch($name)
  {
   case 'CHANNEL': 
    $internal_data['in_channel'] = false; 
   break;
   case 'ITEM': 
    $internal_data['in_item'] = false; 
    $internal_data['item_counter']++;
   break;
  }
 }
 
 function characterData($parser, $data)
 {
  global $internal_data;
  if($internal_data['in_channel']===true && $internal_data['in_item']!==true)
  {
   switch($internal_data['cur_name'])
   {
    case 'TITLE': $internal_data['rss_info']['title'] = $data; break;
    case 'LINK': $internal_data['rss_info']['link'] = $data; break;
   }
  }
  elseif($internal_data['in_channel']===true && $internal_data['in_item']===true)
  {
   switch($internal_data['cur_name'])
   {
    case 'TITLE': $internal_data['items'][$internal_data['item_counter']]['title'] = htmlentities($data); break;
    case 'LINK': $internal_data['items'][$internal_data['item_counter']]['link'] = trim($data); break;
    case 'DESCRIPTION': $internal_data['items'][$internal_data['item_counter']]['desc'] = htmlentities($data); break;
    case 'PUBDATE': $internal_data['items'][$internal_data['item_counter']]['date'] = htmlentities($data); break;
   }
  }
 }
 
 function rss2array($url)
 {
  global $internal_data;
  $internal_data = array();
  $internal_data['item_counter']=0;
  
  $xml_parser = xml_parser_create("UTF-8");
  xml_set_element_handler($xml_parser, "startElement", "endElement");
  xml_set_character_data_handler($xml_parser, "characterData");
  xml_parse($xml_parser,trim(html_get($url)));
  xml_parser_free($xml_parser);
  
  return $internal_data;
 }
?>