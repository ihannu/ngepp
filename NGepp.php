/******************* Written by Hannu Internet Corp. and DomainKing.NG ********************/
/******************* Websites: http://www.hannu.co/ , http://www.domainking.ng/ **********************/

# GET EPP Code of the Domain
function COCCAepp_GetEPPCode($params) {
	# Grab variables
	$sld = $params["sld"];
	$tld = $params["tld"];
	$domain = "$sld.$tld";

	# Get client instance
	try {
		$client = _COCCAepp_Client();

		# Get Domain Information for EPP Code
		$result = $client->request($xml = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
   <epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
     <command>
       <info>
         <domain:info
          xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">
           <domain:name hosts="all">'.$domain.'</domain:name>
         </domain:info>
       </info>
       <clTRID>'.mt_rand().mt_rand().'</clTRID>
     </command>
   </epp>
');

# Parse XML result
		$doc = new DOMDocument();
    $doc->loadXML($result);
		logModuleCall('COCCAepp', 'GetEPPCode', $xml, $result);

		# Pull off status
		$coderes = $doc->getElementsByTagName('result')->item(0)->getAttribute('code');
		$msg = $doc->getElementsByTagName('msg')->item(0)->nodeValue;
    $authcode = $doc->getElementsByTagName('pw')->item(0)->nodeValue;

		$values["eppcode"] = $authcode;

                
    # Check the result is ok
		if(!eppSuccess($coderes)) {
			$values["error"] = "GetEPPCode/domain-info($domain): Code ($coderes) $msg";
		return $values;
		} 
    
}
catch (Exception $e) {
		$values["error"] = 'GetEPPCode/EPP: '.$e->getMessage();
		return $values;
	} 

	return $values;
}


//Get Registrar Lock Status

function COCCAepp_GetRegistrarLock($params) {
	# Grab variables
	$sld = $params["sld"];
	$tld = $params["tld"];
        $domain=$sld . "." . $tld;

// What is the current domain status?
# Grab list of current nameservers
 try {
 $client = _COCCAepp_Client();
        $request = $client->request($xml='<?xml version="1.0" encoding="UTF-8" standalone="no"?>
   <epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
     <command>
       <info>
         <domain:info
          xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">
           <domain:name hosts="all">'.$domain.'</domain:name>
         </domain:info>
       </info>
       <clTRID>'.mt_rand().mt_rand().'</clTRID>
     </command>
   </epp>
');

        # Parse XML result
        $doc= new DOMDocument();
        $doc->loadXML($request);
        $statusarray = $doc->getElementsByTagName("status");
				$currentstatus = array();
				foreach ($statusarray as $nn) {
					$currentstatus[] = $nn->getAttribute("s");
				}
			}
			catch (Exception $e) {
				$values["error"] = $e->getMessage();
				return $values;
			}
			

	# Get lock status
	if (array_key_exists(array_search("clientDeleteProhibited", $currentstatus), $currentstatus) == 1 || array_key_exists(array_search("clientTransferProhibited", $currentstatus), $currentstatus) == 1 || array_key_exists(array_search("clientUpdateProhibited", $currentstatus), $currentstatus) == 1) {
				$lockstatus = "locked";
			}
			else {
				$lockstatus = "unlocked";
			}
			return $lockstatus;
		}

# Save Registrar Lock
function COCCAepp_SaveRegistrarLock($params) {
	# Grab variables
	$sld = $params["sld"];
	$tld = $params["tld"];
	$domain = "$sld.$tld";
        $lockenabled=$params["lockenabled"];
        $lockmsg="Lock Enabled:" . $lockenabled . "Domain:" . $domain;
        
 	if ($lockenabled == "locked") {
 	COCCAepp_LockDomain($domain);
 	}
        else{
 	COCCAepp_UnlockDomain($domain);
   	}
}

# Lock Domain
function COCCAepp_LockDomain($params) {

$domain=$params;
$lockmsg="Domain:" . $domain;
try {
		if (!isset($client)) {
			$client = _COCCAepp_Client();
		}

		# Lock Domain
		//First lock the less restrictive locks
		$request = $client->request($xml = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <command>
    <update>
      <domain:update xmlns:domain="urn:ietf:params:xml:ns:domain-1.0" xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd">
        <domain:name>'.$domain.'</domain:name>
        <domain:add>
          <domain:status s="clientDeleteProhibited"/>
          <domain:status s="clientTransferProhibited"/>         
        </domain:add>
      </domain:update>
    </update>
    <clTRID>'.mt_rand().mt_rand().'</clTRID>
  </command>
</epp>
');
       
    # Parse XML result		
	$doc= new DOMDocument();
	$doc->loadXML($request);
        logModuleCall('COCCAepp', 'Lock-Delete-Transfer', $xml, $request);


	$coderes = $doc->getElementsByTagName('result')->item(0)->getAttribute('code');
	$msg = $doc->getElementsByTagName('msg')->item(0)->nodeValue;

	# Check result
	if(!eppSuccess($coderes)) {
		$values["error"] = "Lock Domain($domain): Code (".$coderes.") ".$msg;
		return $values;
	}
} 

catch (Exception $e) {
		$values["error"] = 'Domain Lock/EPP: '.$e->getMessage();
		return $values;
	}

	return $values;
}

# Unlock Domain
function COCCAepp_UnlockDomain($params) {
# Grab variables
        $domain=$params;
try {
		if (!isset($client)) {
			$client = _COCCAepp_Client();
		}

 $request = $client->request($xml = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <command>
    <update>
      <domain:update xmlns:domain="urn:ietf:params:xml:ns:domain-1.0" xsi:schemaLocation="urn:ietf:params:xml:ns:domain-1.0 domain-1.0.xsd">
        <domain:name>'.$domain.'</domain:name>
        <domain:rem>          
          <domain:status s="clientDeleteProhibited"/>
          <domain:status s="clientTransferProhibited"/>
        </domain:rem>
      </domain:update>
    </update>
    <clTRID>'.mt_rand().mt_rand().'</clTRID>
  </command>
</epp>
');
	# Parse XML result		
	$doc= new DOMDocument();
	$doc->loadXML($request);
    logModuleCall('COCCAepp', 'Domain UnLock', $xml, $request);

	$coderes = $doc->getElementsByTagName('result')->item(0)->getAttribute('code');
	$msg = $doc->getElementsByTagName('msg')->item(0)->nodeValue;
	# Check result
	if(!eppSuccess($coderes)) {
		$values["error"] = "Domain Unlock($domain): Code (".$coderes.") ".$msg;
		return $values;
	}
} catch (Exception $e) {
		$values["error"] = 'Domain UnLock/EPP: '.$e->getMessage();
		return $values;
	}

	return $values;
}

/******************* Written by Hannu Internet Corp. and DomainKing.NG ********************/
/******************* Websites: http://www.hannu.co/ , http://www.domainking.ng/ **********************/
