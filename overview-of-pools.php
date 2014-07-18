<?
 /*
  *  overview-of-pools.php
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
  
 $title = "Overview of the pools";
 $dir = "./";
 include($dir."inc/header.inc.php");
?>
<h1><?=$title;?></h1>
<p>The servers that are included in the pool responded during the last update, are updated to the required minimum version of the software and is synching with the rest of the network to update the keys and only includes servers running a reverse proxy rather than exposing sks directly to the clients.</p>
<p>hkp defaults to port 11371, and the same pool can be accessed using e.g. http://pool.sks-keyservers.net:11371</p>

<h2 id="pool_main">pool.sks-keyservers.net</h2>
<p>The primary pool. This includes both A (ipv4) and AAAA (ipv6) records based on a random selection of included servers</p>

<h2 id="pool_eu">eu.pool.sks-keyservers.net</h2>
<p>European pool. This includes A (ipv4), AAAA (ipv6) and SRV records based on the performance timing expressed in the SRV weights.</p>

<h3>Service (SRV) Records</h3>
<p>The pools <b>_pgpkey-http._tcp.eu.pool.sks-keyservers.net</b> contains <a href="http://en.wikipedia.org/wiki/SRV_record">DNS Service (SRV)</a> records with weights as found in the <a href="/status/">status list</a>.
For a description of how the weights are calculated, please see <a href="http://kfwebs.com/sks-keyservers-SRV.pdf">this PDF document</a></p>

<h2 id="pool_na">na.pool.sks-keyservers.net</h2>
<p>North american pool. This includes A (ipv4), AAAA (ipv6) and SRV records based on the performance timing expressed in the SRV weights.</p>

<h3>Service (SRV) Records</h3>
<p>The pools <b>_pgpkey-http._tcp.na.pool.sks-keyservers.net</b> contains <a href="http://en.wikipedia.org/wiki/SRV_record">DNS Service (SRV)</a> records with weights as found in the <a href="/status/">status list</a>.
For a description of how the weights are calculated, please see <a href="http://kfwebs.com/sks-keyservers-SRV.pdf">this PDF document</a></p>

<h2 id="pool_oc">oc.pool.sks-keyservers.net</h2>
<p>Preliminary Oceania pool. Note, this pool currently does not have enough measuring clients to be considered stable. This includes A (ipv4), AAAA (ipv6) and SRV records based on the performance timing expressed in the SRV weights.</p>

<h3>Service (SRV) Records</h3>
<p>The pools <b>_pgpkey-http._tcp.oc.pool.sks-keyservers.net</b> contains <a href="http://en.wikipedia.org/wiki/SRV_record">DNS Service (SRV)</a> records with weights as found in the <a href="/status/">status list</a>.
For a description of how the weights are calculated, please see <a href="http://kfwebs.com/sks-keyservers-SRV.pdf">this PDF document</a></p>

<h2 id="pool_ipv6">ipv6.pool.sks-keyservers.net</h2>
<p>IPv6 enabled servers are included with AAAA records in the main pool, and an IPv6-only pool is available at <b>ipv6.pool.sks-keyservers.net</b></p>

<h2 id="pool_ipv4">ipv4.pool.sks-keyservers.net</h2>
<p>Similarily an IPv4 only pool is available at <b>ipv4.pool.sks-keyservers.net</b> if anyone for some reason (broken IPv6) should have difficulties</p>

<h2 id="pool_subset">subset.pool.sks-keyservers.net</h2>
<p>This is a subset of the pool: At the moment it only includes servers updated to version <a href="http://lists.nongnu.org/archive/html/sks-devel/2014-05/msg00026.html">1.1.5</a>. This pool support Elliptic Curve public keys as described in <a href="http://tools.ietf.org/rfc/rfc6637.txt">RFC6637</a> and add a CORS header to web server responses to allow JavaScript code to interact with keyservers.</p>

<h2 id="pool_ha">ha.pool.sks-keyservers.net</h2>
<p>This is a high-availibility subset of the pool. As the main pool <a href="http://lists.nongnu.org/archive/html/sks-devel/2013-10/msg00048.html">require all servers to be behind a reverse proxy</a>, this subpool doesn't currently provide any additional functionality. The HA name is reserved for future use related to clustered servers (currently marked with blue indicator for reverse proxy in <a href="/status/">the status pages</a>)</p>

<h2 id="pool_p80">p80.pool.sks-keyservers.net</h2>
<p>This is a pool containing only servers available on port 80 (needs to be used as hkp://p80.pool.sks-keyservers.net:80)</p>

<h2 id="pool_hkps">hkps.pool.sks-keyservers.net</h2>
<p>This is a pool containing only servers available using hkps. Regular A and AAAA and SRV records are included for port 443 servers, and a lookup is performed for _pgpkey-https._tcp on the individual servers to determine if a hkps enabled service is listening on another port, in which case this is included as a SRV record.</p><p>This pool only include servers that have been certified by the sks-keyservers.net CA, of which the certificate can be found at <a href="https://sks-keyservers.net/sks-keyservers.netCA.pem">https://sks-keyservers.net/sks-keyservers.netCA.pem</a>[<a href="https://sks-keyservers.net/sks-keyservers.netCA.pem.asc">OpenPGP signature</a>][<a href="https://sks-keyservers.net/ca/crl.pem">CRL</a>]. This can be used by using the following parameters in gpg.conf:</p>
<p><pre>~/.gnupg/gpg.conf:
  keyserver hkps://hkps.pool.sks-keyservers.net
  keyserver-options ca-cert-file=/path/to/CA/sks-keyservers.netCA.pem</pre></p>
<p>Keyserver operators wanting to be included in this pool will have to send an OpenPGP signed message containing a CSR to a UserID of <a href="http://pool.sks-keyservers.net:11371/pks/lookup?op=vindex&amp;search=0x0B7F8B60E3EDFAE3">0x0B7F8B60E3EDFAE3</a>.</p>

<?
 include($dir."/inc/footer.inc.php");
?>
