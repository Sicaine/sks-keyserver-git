<?
 /*
  *  index.php
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
 include($dir."inc/header.inc.php");
?>
   <p>This website provides services for the <a href="https://bitbucket.org/skskeyserver/sks-keyserver">SKS keyservers</a> used by <a href="http://www.secure-my-email.com">OpenPGP</a>. A pool of keyservers is available at <b>hkp://pool.sks-keyservers.net</b> Information about the other variants of the pool is found <a href="/overview-of-pools.php">in the overview</a>.</p>
   <p>If you wish to contact me feel free to send an email to the user id in the key <a href="http://p80.pool.sks-keyservers.net/pks/lookup?op=vindex&amp;search=0x0B7F8B60E3EDFAE3">0x0B7F8B60E3EDFAE3</a></p>
 
   <h1>The HKP Pool</h1>
   <p>Read about <a href="/overview-of-pools.php">the various pools</a></p>

   <h1>Keyserver statuses</h1>
   <p>Status information about the different keyservers can be found at <a href="/status/">the status pages</a></p>

   <h1>Interact with the keyservers</h1>
   <p>A simple way to interact with the keyservers is available at <a href="/i/">the interaction pages</a></p>
   
   <h1>The number of OpenPGP Keys</h1>
   <p>A chart showing the development in <a href="/status/key_development.php">the number of OpenPGP keys by day</a>. This is the maximum number of keys found on the keyserver at the start of any given day.</p>
<?
 include($dir."/inc/footer.inc.php");
?>
