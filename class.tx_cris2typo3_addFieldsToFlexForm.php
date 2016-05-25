<?php

require_once(t3lib_extMgm::extPath('cris2typo3').'lib/class.publications.php');

class tx_cris2typo3_addFieldsToFlexForm {
	function addSelectedPublications ($config) {
	  
		// INSERT CODE TO QUERY ALL PUBLICATIONS OF ONE PERSON.
		// REQUIRES PERSONLIB TO FIND CRIS ID of the person
		$pubLib = new PubLibCris;
		$pID = $pubLib->getUserName();

		$allAuthorPubs = $pubLib->getPublicationsAuthor($pID,TRUE);	
		$optionList = array();

		if(is_array($allAuthorPubs))
		foreach($allAuthorPubs as $pub)
		{
			$optionList[] = array(0 => $pub->attributes["cftitle"], 1 => $pub->ID);
		}

		$config['items'] = array_merge($config['items'],$optionList);	
		return $config;
	}
 }
?>
