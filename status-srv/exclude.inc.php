<?
 /*
  *  status-srv/exclude.php
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
  
 function ServerIsExcluded($hostname)
 {
    $exclude = 
	array(
		"localhost", 
		"delta.keyserver.ws",
		"pool.sks-keyservers.net",
		"ipv4.pool.sks-keyservers.net",
		"ipv6.pool.sks-keyservers.net",
		"test.surfnet.nl", // improper (no DNS record) hostname in sksconf for pgp.surfnet.nl
		"nemesis.key-servers.de",
		"key-server.nl",
		"key-server.org",
		"keys.bz",
		"keys.keysigning.org",
		"keys.kfwebs.net",
		"keys.n3npq.net",
		"keys.nayr.net",
		"keys.slc.biz",
		"keys.sugarlabs.org",
		"keys.syn.co.uk",
		"keyserver.cais.rnp.br",
		"keyserver.fug.com.br",
		"keyserver.maze.io",
		"keyserver.mozilla.org",
		"keyserver.novomundo.com.br",
		"keyserver.pramberger.at",
		"keyserver.uberslacks.com",
		"keyserver.veloxis.de",
		"keyservers.org",
		"ks.rainydayz.org",
		"liberty.antagonism.org",
		"node0.mainframe.cx",
		"odin.stueve.us",
		"pgp.acm.jhu.edu",
		"pgp.webtru.st",
		"pks.mtholyoke.edu",
		"redundant.dyndns.org",
		"research.nxfifteen.me.uk",
		"sks.betabuild.net",
		"sks.dgrphone.net",
		/*
		 * Automatically generate exclude list 
		 * from  php debug_generate_exclude.php.php
		 * follows. Checked with 
		 *   for i in `php debug_check_excluded_servers.php `; \ 
		 *   do echo "--------------------------"; \	  	
		 *   php sks_get_peer_data.php $i | grep "statusok\":true" \
		 *  | grep -v "version\":0"; done
		 */
		 "sks1.webtru.st",
		 "sks2.webtru.st",
		 "users.webtru.st",
			"alpha.keyserver.ws",
			"barnstable.jbj.org",
			"basket",
			"booboo",
			"cryptochan.org",
			"cybernude.org",
			"dionysus.ugcs.caltech.edu",
			"druzhnaya.canonical.com",
			"envirobeast",
			"esd0.colliertech.org",
			"gpg-keyserver.de",
			"gpg.debian.unam.mx",
			"gpg.kalkulators.org",
			"honeynet.homelinux.com",
			"horse",
			"key-server.de",
			"key.sodrk.ru",
			"key.space.chaos-disciple.org",
			"keys.cardboard.net",
			"keys.christensenplace.us",
			"keys.pmman.com",
			"keys.rpm5.org",
			"keys3.kfwebs.net",
			"keys4.kfwebs.net",
			"keys5.kfwebs.net",
			"keyserver.argoss.nl",
			"keyserver.colliertech.org",
			"keyserver.dotorg.org",
			"keyserver.duf.hu",
			"keyserver.fabbione.net",
			"keyserver.fishysnax.com",
			"keyserver.fryxell.ru",
			"keyserver.ftbfs.org",
			"keyserver.ganneff.de",
			"keyserver.gurski.org",
			"keyserver.maluska.de",
			"keyserver.metroholografix.org",
			"keyserver.mine.nu",
			"keyserver.mitaka-g.net",
			"keyserver.myriapolis.net",
			"keyserver.nijkamp.net",
			"keyserver.noreply.org",
			"keyserver.northernstandard.us.com",
			"keyserver.nuclearwombats.net",
			"keyserver.nyfnet.net",
			"keyserver.progman.us",
			"keyserver.rootbash.com",
			"keyserver.sparcs.net",
			"keyserver.straderdynamics.com",
			"keyserver.unixbyte.com",
			"keyserver.vescudero.net",
			"keyserver.ws",
			"kim.kim-minh.com",
			"ky-server.org",
			"linux-geeks.de",
			"nellfenwick",
			"nisamox.fciencias.unam.mx",
			"odin.hq-visitech.com",
			"oppie.homelinux.com",
			"pgp.codelabs.ru",
			"pgp.gabrix.ath.cx",
			"pgp.skewed.de",
			"pgp.srv.ualberta.ca",
			"pgp.teanfordhouse.com",
			"pgp.treefish.org",
			"pgp.ugcs.caltech.edu",
			"pgp.wernherus.de",
			"pgpkeys.logintas.ch",
			"pki.colliertech.org",
			"pubdmz01.phx.mozilla.com",
			"research.nxfifteen.com",
			"s328158944.onlinehome.fr",
			"sks-server.siu.edu",
			"sks.5coluna.com",
			"sks.buanzo.org",
			"sks.dnsalias.net",
			"sks.hezmatt.org",
			"sks.keyserver.ca",
			"sks.matroxsolutions.com",
			"sks.mit.edu",
			"sks.nanofortnight.org",
			"sks.teanfordhouse.com",
			"sksbackup.local",
			"sodrk.ru",
			"thermos",
			"users.nayr.net",
			"wellfleet.jbj.org",
			"yogi",
			"barbadine.canonical.com",
			"bhs.dan.me.uk",
			"envirobeast.stueve.us",
			"esperanza.canonical.com",
			"icechest",
			"keys.jbj.org",
			"reg.goeswhere.com", //disabled as per sks-devel announcement
			"carbon.ipv6.jhcloos.org",
			"fau.xxx",
			"funboard.dtdns.net",
			"keys.wuschelpuschel.org",
			"keyserver.fr",
			"keyserver.linux.it",
			"orion.stueve.us",
			"thesecuregroup.com",
			"www.mainframe.cx",
			"zomers.be"
	);
	
 	$returnValue = false; 
 	
 	//Server name is found in exclude list
 	if(in_array($hostname, $exclude)) 
 		$returnValue = true;

	//Ignore IP-only matches
	if(preg_match("/^\d+\.\d+\.\d+\.\d+$/", $hostname))
		$returnValue = true; 
 	 	
 	return $returnValue; 
 }
 
?>
