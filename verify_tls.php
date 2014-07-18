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
 $title="Verify SSL/TLS certificate";
 include($dir."inc/header.inc.php");
?>
<h1>Validation of HTTPS connection</h1>
<p><a href="https://sks-keyservers.net">https://sks-keyservers.net</a> uses a certificate signed by Thawte, but for additional security it can also be authenticated through the OpenPGP Web of Trust by using <a href="http://web.monkeysphere.info">Monkeysphere</a></p> 

<h1>Key Verification</h1>
<p>The X.509 certificate signed by Thawte should have a SHA1 key fingerprint of <b>FC 5F B2 F5 E4 24 D3 47 B9 C4 07 32 34 6E 16 1F 91 6B F1 F2</b>. The RSA key information for this certificate is the same as in the OpenPGP key described below, but because the schemes are creating the fingerprint over different information, the fingerprint will not match.</p>
<p>The KeyID of the OpenPGP certificate should be <a href="http://p80.pool.sks-keyservers.net/pks/lookup?op=vindex&amp;search=0xd71fd9994af34f0b">0xd71fd9994af34f0b and can be found in the pool</a>. The fingerprint of the key is <b>878F FB44 5E6E 13A6 4716 3BDC D71F D999 4AF3 4F0B</b> and the key is signed by my personal key <a href="http://p80.pool.sks-keyservers.net/pks/lookup?op=vindex&amp;search=0x0B7F8B60E3EDFAE3">0x0B7F8B60E3EDFAE3</a></p>
<p>&nbsp;</p>
<?
 include($dir."/inc/footer.inc.php");
?>
