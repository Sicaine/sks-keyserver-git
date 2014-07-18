<?
 /*
  *  contact.php
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
  
 $dir = "./";
 $title="Contact";
 include($dir."inc/header.inc.php");
?>
<p>If you want to contact me, use the email information found in the OpenPGP key 
<a href="http://pool.sks-keyservers.net:11371/pks/lookup?op=get&amp;search=0x0B7F8B60E3EDFAE3">0x0B7F8B60E3EDFAE3</a></p>

<p>sks-keyservers.net is released under the GNU General Public License v3 and the sourcecode is available 
at <a href="https://code.google.com/p/sks-keyservers-pool/">code.google.com</a> along with an issue tracker
 and possible wiki page for discussion about possible improvements to this service.</p>
 
<?
 include($dir."/inc/footer.inc.php");
?>
