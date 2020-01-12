<?php
/**
 * Simple WHOIS script
 * 
 * @package whois
 * @author     George Kalmoukis <gkalmoukis@vela.gr>
 */


// find_whois_server
// Returns the whois server based on the domain TLD / ccTLD
// You can add more whois servers by expanding the array.
/////////////////////////////////////////////////////////////////////
function find_whois_server($ext)
{
$WHOIS_SERVERS = Array(

    "com" => "whois.verisign-grs.com"


);
	$ext = strtolower($ext);
	if(array_key_exists($ext, $WHOIS_SERVERS))
		return $WHOIS_SERVERS[$ext];
	else return "";
}

/////////////////////////////////////////////////////////////////////
function do_whois($domainname, $server, $port=43)
{
	$output = "Unable to connect to " . $server;
	if(($ns = fsockopen($server,$port)) == true)
	{
		$output = "";
		fputs($ns,"$domainname\r\n");
		while(!feof($ns)) 
			$output .= fgets($ns,128); 
		fclose($ns);
	}
	return $output . "\r\n" . '<hr><p>Simple PHP WHOIS script, copyright (c) <a href="http://www.softnik.com/">Softnik Technologies</a></p>';
}

/////////////////////////////////////////////////////////////////////

function get_registrar_server($string)
{
	$lookfor = array("Registrar WHOIS Server:", "Whois Server:");
	foreach($lookfor as $fstr)
	{
		if(strstr($string, $fstr))
		{
			$string = str_ireplace($fstr, "", $string);
			return trim($string);
		}
	}
	return false;
}

/////////////////////////////////////////////////////////////////////
function domain_whois($domainname, $port=43)
{
	$domainname = trim($domainname);
	$domparts = explode(".", $domainname);
	$count = count($domparts);
	if(!$count)
		return "Invalid domain name";
	$server = find_whois_server($domparts[$count-1]);
	if($server == "")
		return "Don't know the whois server for domain " . $domainname;	
	$lookupname = $domainname;
	if(preg_match("/.(com|.net|.edu)$/i", $domainname))
		$lookupname = "domain " . $domainname;
	$output = do_whois($lookupname, $server);	
	if(preg_match("/.(com|.net|.edu|.cc|.tv|.ws)$/i", $domainname))
	{
		$pieces = explode("\n", $output);
		$count = count($pieces);
		$c = 0;
		for($c = 0; $c < $count; $c++)
		{
			$line = $pieces[$c];
			$registrar_server = get_registrar_server($line);
			if($registrar_server !== false)
				$output = do_whois($domainname, $registrar_server);
		}
	}
	if(!strlen($output))
		$output = "There was error connecting to the whois server [" . $server . "]";
	return $output; 
}

/////////////////////////////////////////////////////////////////////
$domain = "";
$server = "";

if(isset($_GET["domain"]))
	$domain = strip_tags(stripslashes($_GET["domain"]));
if(isset($_GET["server"]))
	$server = strip_tags(stripslashes($_GET["server"]));
	
if($domain != "")
{
	if($server != "")
		echo "<pre>" . do_whois($domain, $server) . "</pre>";
	else
		echo "<pre>" . domain_whois($domain) . "</pre>";
}
else
	echo "Domain Name Not Specified.";
/////////////////////////////////////////////////////////////////////
?>
