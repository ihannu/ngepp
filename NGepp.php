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
