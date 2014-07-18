<?
 /*
  *  inc/header.inc.php
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
  
?><!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
 <head>
  <title>SKS Keyservers<?=((isset($title)) ? ": ".$title : "");?></title>
  <link rel="stylesheet" type="text/css" href="/style.css" title="default" />
  <script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-240896-6']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
 </head>
 <body>
  <div id="header"><a style="display:block;margin-top:20px;margin-left:250px;color: white;" href="/">sks-keyservers.net</a></div>
  <div id="menu">&nbsp;
  <a href="/">Index</a> |
  <a href="/status/">Status pages</a> |
  <a href="/overview-of-pools.php">Overview of pools</a> |
  <a href="/i/">Interact with the keyservers</a> |
  <a href="/verify_tls.php">HTTPS Verification</a> |
  <a href="/status/key_development.php">#Key development</a> |
  <a href="/contact.php">Contact</a> |
  </div>
  <div id="main">
  <div id="main2">
  <br />
  <?
   $banner = rand(1,10);
   switch($banner)
   {
   	 case 1: 
   	  echo "<p style=\"border: 2px solid blue; margin: 2px; padding: 2px;\">The book <a href=\"http://www.amazon.com/Sending-Emails-introduction-OpenPGP-security/dp/1468153544/ref=ntt_at_ep_dpt_1\"><b>Sending Emails - The Safe Way: An introduction to OpenPGP security</b></a> is available in both Amazon Kindle and Paperback format </p>";
   	  break;
     case 2:
   	  echo "<p style=\"border: 2px solid blue; margin: 2px; padding: 2px;\">Kristian's twitter account: <a href=\"https://twitter.com/krifisk\">@krifisk</a> </p>";
   	  break;
     case 3:
      echo <<<EOF
      <div style="border: 2px solid blue; margin: 2px; padding: 2px;">Do you want to support hosting and the future development of this service? Donations can be made using PayPal: <br /> 
       <div><b><a href="http://www.kfwebs.net/donations.php">Donation list</a></b></div>
<form style="display:inline;margin:none;padding:none;" action="https://www.paypal.com/cgi-bin/webscr" method="post"><div><b>USD</b>: 
<input type="hidden" name="cmd" value="_xclick" />
<input type="hidden" name="business" value="kf@kfwebs.net" />
<input type="hidden" name="undefined_quantity" value="0" />
<input type="hidden" name="item_name" value="KF Webs - sks-keyservers.net donation" />
<input type="text" name="amount" value="10.00" />
<input type="hidden" name="no_shipping" value="2" />
<input type="hidden" name="no_note" value="1" />
<input type="hidden" name="currency_code" value="USD" />
<input type="hidden" name="bn" value="PP-BuyNowBF" />
<input type="hidden" name="on0" value="public" />
<br />
List:<input style="border:0px;" type="radio" name="os0" value="1" checked="checked" />Don't list:<input style="border:none;" type="radio" name="os0" value="0" />
<input type="hidden" name="notify_url" value="http://www.kfwebs.net/ipn.php" />
<input type="hidden" name="return" value="http://www.kfwebs.net" />
<input type="hidden" name="cancel_return" value="http://www.kfwebs.net" />
<input type="submit" value="Make a donation" />
</div></form>
<br />
      </div>
EOF;
     break;
     case 5:
     case 6:
     case 7:
     case 8:?>
     <div style="border: 2px solid blue; margin: 2px; padding: 2px;">Latest articles from Kristian's personal blog (<a href="http://blog.sumptuouscapital.com/feed/">RSS</a>)<br />
            <?
             include($dir."/inc/rss2array.php");
             $proc_xml = rss2array("http://www.kfwebs.net/tmp/procrastinating-student.xml");
             $c = (count($proc_xml['items']) > 3) ? 3 : count($proc_xml['items']);
             echo "<ul>";
             for($i=0;$i<$c;$i++)
             {
              echo "<li><a href=\"{$proc_xml['items'][$i]['link']}\">".date("Y-m-d",strtotime($proc_xml['items'][$i]['date'])).": {$proc_xml['items'][$i]['title']}</a></li>";
             }
             echo "</ul></div>";
     break; 
   	 default: 
		echo "<p style=\"border: 2px solid blue; margin: 2px; padding: 2px;\">Kristian's twitter account: <a href=\"https://twitter.com/krifisk\">@krifisk</a> </p>";
   }
?>
