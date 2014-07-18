<?
 /*
  *  i/index.php
  *  Copyright (C) 2006, 2007, 2008, 2009, 2010, 2011  Kristian Fiskerstrand
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
  
 $title = "Interact with the keyservers";
 $dir = "../";
 include($dir."inc/header.inc.php");
?>
  <h1>OpenPGP Public Key Server Commands</h1>
  <p>Welcome to the keyserver interaction page for the <i>pool.<a href="http://sks-keyservers.net">sks-keyservers.net</a></i> round-robin. Interactions will be performed over a TLS/SSL enabled connection using the HKPS pool. For information about this pool, including the root certificate used to sign keyserver certificates, <a href="/overview-of-pools.php#pool_hkps">read more on the overview pages</a></p>

    <h2>
	      <a href="#extract">Extract a Key from the Server</a>
		</h2>
		  <h2>
			    <a href="#submit">Submit a Key to the Server</a>
			      </h2>
<br /><br /><br />
				  <h2 class="head">

					    <a id="extract" name="extract">Extracting a Key</a>
					      </h2>
						<p>Here is how to extract a key:</p>
						  <ol>
							      <li>
								    <p>Select either the &quot;Index&quot; or &quot;Verbose Index&quot; check box. The &quot;Verbose Index&quot; option also displays all signatures on displayed keys.</p>

									</li>
									    <li>
										  <p>Type ID you want to search for in the &quot;Search String&quot; box.</p>
										      </li>
											  <li>
												<p>Press the &quot;Do the search!&quot; key.</p>

												    </li>
													<li>
													      <p>The server will return a (verbose) list of keys on the server matching the given ID. (The ID can be any valid argument to a pgp -kv(v) command. If you want to look up a key by its hexadecimal KeyID, remember to prefix the ID with &quot;0x&quot; .)</p>
														  </li>
														      <li>
															    <p>The returned index will have hypertext links for every KeyID, and every bracket-delimited identifier (i.e. &lt;

															    <a href="https://hkps.pool.sks-keyservers.net/pks/lookup?op=get&amp;exact=on&amp;search=kf@kfwebs.net">kf (at) kfwebs.net</a>
																	 &gt;). Clicking on the hypertext link will display an ASCII-armored version of the listed public key.</p>
																	     </li>
																	       </ol>
																			   <form action="https://hkps.pool.sks-keyservers.net/pks/lookup" method="get">
																				       <p>Index:
																				           <input type="radio" name="op" value="index" />

																					        Verbose Index:
																						    <input type="radio" name="op" value="vindex" checked="checked" />
																						        </p>
																							    <p>Search String:
																							        <input name="search" size="40" />
																								    </p>
																								        <p>
																									    <input type="checkbox" name="fingerprint" />
																									         Show OpenPGP &quot;fingerprints&quot; for keys</p>

																										     <p>
																										         <input type="checkbox" name="exact" />
																											      Only return exact matches</p>
																											          <p>
																												      <input type="reset" value="Reset" />

																												          <input type="submit" value="Do the search!" />
																													      </p>

																													        </form>

																														    <p><strong>Extract caveats:</strong></p>

																														      <ul>
																															          <li>
																																        <p>Currently, hypertext links are only generated for the KeyID and for text found between matching brackets. (It&#39;s a common convention to put your e-mail address inside brackets somewhere in the key ID string.)</p>
																																	    </li>

																																	        <li>

																																		      <p>The search engine is not the same as that used by the gpg(1) or pgp(1) programs. It will return information for all keys which contain all the words in the search string. A &quot;word&quot; in this context is a string of consecutive alphabetic characters. For example, in the string &quot;user@example.com&quot;, the words are &quot;user&quot;, &quot;example&quot;, and &quot;com&quot;.</p>

																																		            <p>This means that some keys you might not expect will be returned. If there was a key in the database for &quot;User &lt;example@foo.com&gt;&quot;, this would be returned for by the above query. If you don&#39;t want to see all these extra matches, you can select &quot;Only return exact matches&quot;, and only keys containing the specified search string will be returned.</p>

																																			          <p>This algorithm does
																																				        <em>not</em>
																																					       match partial words in any case. So, if you are used to specifying only part of a long name, this will no longer work.</p>

																																					           </li>
																																						     </ul>
																																						         <h2 class="head">
																																								   <a id="submit" name="submit">Submitting a new key to the server</a>

																																								     </h2>
																																								       <p>Here is how to add a key to the server&#39;s keyring:</p>
																																								         <ol>

																																										     <li>
																																										           <p>Cut-and-paste an ASCII-armored version of your public key into the text box.</p>
																																											       </li>
																																											           <li>

																																												         <p>Press &quot;Submit&quot;.</p>
																																													     </li>

																																													       </ol>
																																													         <p>That is it! The keyserver will process your request immediately. If you like, you can check that your key exists using the
																																														   <a href="#extract">extract</a>
																																														      procedure above.</p>

																																															  <form action="https://hkps.pool.sks-keyservers.net/pks/add" method="post">
																																																      <p>Enter ASCII-armored PGP key here:</p>
																																																          <p>

																																																	      <textarea name="keytext" rows="20" cols="66"></textarea>
																																																	          </p>
																																																		      <p>
																																																		          <input type="reset" value="Reset" />

																																																			      <input type="submit" value="Submit this key to the keyserver!" />
																																																			          </p>
																																																				    </form>
	  
          
  </div></div>

<?
 include($dir."inc/footer.inc.php");
?>
