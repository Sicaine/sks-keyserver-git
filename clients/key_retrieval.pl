#!/usr/bin/perl
# Script http://code.google.com/p/sks-keyservers-pool/source/browse/trunk/sks-keyservers.net/clients/key_r/jsonetrieval.php
# rewritten in PERL under the same license and disclaimer. :-)

use strict;

use CGI;
use WWW::Curl::Easy 4.14;
use Time::HiRes qw(gettimeofday tv_interval);
use JSON::Any;

#use Dumpvalue; my $dumper = Dumpvalue->new;

my $hostserver = 'bakacsin.ki.iif.hu';
my $port = 11371;

my $q = CGI->new;

print $q->header;

my $keyserver = $q->param('keyserver');
if ($keyserver !~ m/^([a-z][a-z0-9-]*[a-z0-9]\.)+[a-z][a-z0-9-]*[a-z0-9]$/i
 and $keyserver !~ m/^(\d+\.){3}\d+$/
 and $keyserver !~ m/^[0-9a-f:]+$/i) {
	print $q->start_html('Incorrect keyserver'),$q->end_html;
	exit;
}

my $key_id = $q->param('key_id');
if ($key_id !~ m/^0x[0-9a-f]{8,16}$/) {
	print $q->start_html('Incorrect key_id'),$q->end_html;
	exit;
}

my $j = JSON::Any->new;
my %retval = (
	hostserver	=> $hostserver,
	keyserver	=> $keyserver,
	port		=> $port,
	key_id		=> $key_id,
	statusok	=> $j->true,
);


my $curl = WWW::Curl::Easy->new;
$curl->setopt(CURLOPT_TIMEOUT, 10);
$curl->setopt(CURLOPT_URL,
	"http://$keyserver:$port/pks/lookup?op=get&search=$key_id");
my $response_body;
$curl->setopt(CURLOPT_WRITEDATA,\$response_body);

my $starttime = [gettimeofday];
my $retcode = $curl->perform;
my $elapsed = tv_interval ($starttime);

#print "retcode=$retcode elapsed=$elapsed<br>\n";
if ($retcode == 0) {
	my $response_code = $curl->getinfo(CURLINFO_HTTP_CODE);
	$retval{download_time} = $elapsed;
	$retval{download_size} = length($response_body);
}
else {
	$retval{statusok} = $j->false;
}

print $j->objToJson(\%retval);
exit;
