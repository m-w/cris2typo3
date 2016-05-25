<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2016 LME Webteam <admin-web@i5.cs.fau.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

if (!class_exists('tslib_pibase')) require_once(PATH_tslib . 'class.tslib_pibase.php');

require_once(t3lib_extMgm::extPath('cris2typo3').'cris2t3_config.php');
require_once('cris-generic.php');

/**
 * Library for Plugin: 'Publications' of the 'cris2typo3' extension.
 *
 * @author	Felix Lugauer, LME Webteam <admin-web@i5.cs.fau.de>
 * @package	TYPO3
 * @subpackage	cris2typo3
 */
class PubLibCris extends tslib_pibase {
	
	public $parent;
	public $CRISConfig;

	function setParent($parent)
	{
		$this->parent = $parent;
		$this->CRISConfig = new CRISConfig();
	}

	function getUserName() 
	{
		// nerviger Hack, BE_USER ist nur definiert wenn man wirklich im Backend eingeloggt ist, dann wiederum geht aber TSFE nicht
		$uid = $GLOBALS["BE_USER"]->user['uid'];//$GLOBALS[TSFE]->page["perms_userid"];
		if($uid == null) { $uid = $GLOBALS[TSFE]->page["perms_userid"];}
		$where = 'uid='.$uid;
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(	'tx_cris_key',									// SELECT
              											'be_users',     								// FROM
                       									$where,		// WHERE
														'',             								// GROUP BY
                       									'',           	 								// ORDER BY
                      									'' 	       										// LIMIT
								);
								
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		//return 173500;
		//var_dump($row);
		
		if(!$row['tx_cris_key'])
			print "Publications::getUserName() - Error: could not fetch tx_cris_uid!";
		
		return $row['tx_cris_key'];
	}
	
	function getPublicationsAuthor($personKey,$selPubs=FALSE)
	{
		// new cris stuff
		$publ = new CRIS_publications();
		$res = $publ->by_pers_id($personKey);
		
		if($selPubs)
		{
			//$this->selPublications[$personKey] = $res;
			return $res;
		}
		// sort per year
		$formatter = new CRIS_formatter("publyear", $this->CRISConfig->conf['sort_year']);
		return $formatter->execute($res);
	}
	
	function getAnySelectedPublications($uidString)
	{
		$uids = explode(",",$uidString);
		$req = array();
		foreach($uids as $uid)
		{
			$intid = intval($uid);
			if($intid > 2) 
				$req[] = $intid;
		}	
		$publ = new CRIS_publications();
		
		//var_dump($req);
		$res = $publ->by_id($req);
		
		// items are now grouped per ID, ungroup them
		//array_values
		$ret = array();
		foreach($res as $item)
		{
			$ret[] = $item;
		}
		
		return array_values($res);
	}
	function getPublicationsSelected($uids)
	{
		// check if uids are in selPubs...
		$allPubs = $this->getPublicationsAuthor($this->getUserName(),TRUE);
		
		$selPubs = array();
		foreach($allPubs as $pub)
		{
			if( strpos($uids,$pub->ID) !== false)
				$selPubs[] = $pub;
		}
		return $selPubs;	
	}

	
	function getPublicationsYear($year)
	{	
		//$year = "all";
		$filter = array(
			// 2012-2014
			"publyear__ge" => $this->CRISConfig->conf['first_y'],
			"publyear__lt" => $this->CRISConfig->conf['until_y'],
			// request only publications that are created at FAU
			//"FAU Publikation__eq" => "yes",
		);
		
		if ($year == '< 2000' || $year == "older" || $year == "ältere")
			$filter["publyear__lt"] = $this->CRISConfig->conf['old_y'];
		else if( $year == 'all' || $year == 'alle' )
			$filter = null;
		else
		{
			$filter["publyear__ge"] = $year;
			$filter["publyear__lt"] = $year+1;
		}
		// request organisation's publications
		// EAM: 140965, Inf5: 142477
		$publ = new CRIS_publications();
		$res = $publ->by_orga_id($this->CRISConfig->conf['orga_id'], $filter);
		
		// sort per year
		$formatter = new CRIS_formatter("publyear", $this->CRISConfig->conf['sort_year']);
		$data = $formatter->execute($res);
		
		//var_dump($data);	
		return $data;
	}

	function getPublicationsLatest($cnt)
	{
		/*
		$select  = "*";
		$from    = "u_pub2"; 
		$where   = "`u_pub2`.`type` != 'talk'";
		$orderby = "`u_pub2`.`year` DESC";
		$groupby = "";
		$limit   = $cnt;
		*/
		
		// request organisation's publications
		// EAM: 140965, Inf5: 142477
		$publ = new CRIS_publications();
		$res = $publ->by_orga_id($this->CRISConfig->conf['orga_id']);
		
		// sort per year
		$formatter = new CRIS_formatter("virtualdate", $this->CRISConfig->conf['sort_year']);
		$data = $formatter->execute($res);
		
		$ret = array();
		$count = 0;
		foreach ($data as $cdate => $yearItems) 
		{
			$num = count($yearItems);
			$i = 0;
			do {
				$ret[] = $yearItems[$i++];
				$count++;
				//print $cdate." iter ".$i." curPubs: ".count($ret).": ".$ret[$count-1]->attributes["virtualdate"]."\n";
			} while($i < $num && $count < $cnt);
			if( $count >= $cnt) {break; }		
		}
			
		return $ret;
	}
	
	function getDisclaimer() {
		return '<div class="disclaimer" style="margin-bottom:1em;margin-left:1em;border:1px solid #dd9999;padding:4px;font-size:90%">
			WARNING: All material on this website, including papers, text, figures, and graphics is covered by Copyright &copy; unless otherwise stated.
			You may browse them at your convenience (in the same spirit as you may read a journal or a proceeding article in a public library).
			Retrieving, copying, or distributing these files, however, may violate the copyright protection law.
			We recommend that the user abides international law in accessing this directory.
			</div>';
	}

	function renderPublicationYears($items, $template)
	{
		$content = "";
		
		$cnt = count($items);
		// If SQL query returned no result, there is no publication to render...
		if( $cnt == 0 )
		{
			return $this->parent->pi_getLL("noPublications");
		}
	
		foreach ($items as $currYear => $yearItems) 
		{
			//var_dump($yearItems);
			
			$arrWrappedPubTypeSubpart = array();
			$arrWrappedPubTypeSubpart['###TEMPLATE_PUB_GROUP_TYPE###'] = $this->renderPublicationTypes($yearItems, $template);
				
			$arrYearMarker = array();
			$arrYearMarker["###TEMPLATE_PUB_YEAR###"] = $currYear;
			$arrYearMarker["###TEMPLATE_PUB_NUM###"] = count($yearItems) ." Publications"; //$this->parent->pi_getLL("head_count");
			if(count($yearItems) == 1) { $arrYearMarker["###TEMPLATE_PUB_NUM###"] =  '1 Publication'; }		
			
			$content .= $this->parent->cObj->substituteMarkerArrayCached($this->parent->cObj->getSubpart($template,"###TEMPLATE_PUB_GROUP_YEAR###"), $arrYearMarker, $arrWrappedPubTypeSubpart);		
		}
			
		return $content;
	}

	function renderPublicationTypes($items, $template)
	{
		$content = "";
	
		$language = $GLOBALS["TSFE"]->sys_language_uid;
		if ($language == 0) {
			$description = 'description_en';
		} else {
			$description = 'description';
		}
		
		// Grouping by user-defined list of publication types need this list *sigh*
		// If any value occurs that is not listed here, it will be put at the end.
		//$o = array("book","journal article","conference contribution","article in edited volumes","other");
		$formatter2 = new CRIS_formatter("publication type", $this->CRISConfig->conf['pub_type_grouping']);	
		$allGroupedItems = $formatter2->execute($items);
		
		//var_dump(array_keys($allGroupedItems));
		//var_dump(array_values($allGroupedItems));
		
		foreach ($allGroupedItems as $currGroup => $groupItems) 
		{
			
		//for ($i = 0; $i < count($allGroupedItems); ++$i) {	
			//print "CurrGroup: " .$currGroup;
			//$currGroup = $allGroupedItems[$i];
			
			// Book -> Monograph
			if($currGroup == 'Book') { $currGroup = 'Monograph';}

			// Replace every first letter of a word by an uppercase letter
			$currGroup = ucwords($currGroup);
			//if($currGroup == 'Journal article') { $currGroup = 'Journal Article';}		
			//if($currGroup == 'Conference contribution') { $currGroup = 'Conference Article';}
			
			// Plural if needed
			if($currGroup == 'Article in Edited Volumes') {
				$currGroup = 'Article in Edited Volume';

				if(count($groupItems) > 1) { $currGroup = 'Articles in Edited Volumes'; }
			} else if(count($groupItems) > 1) { $currGroup .="s"; }
			
			$arrGroupMarker["###TEMPLATE_PUB_TYPE###"] = $currGroup;//htmlspecialchars($items[$i][$description]);
			$arrWrappedSubMarker = array();
			$arrWrappedSubMarker['###TEMPLATE_PUB_ITEM###'] = $this->renderPublicationList($groupItems, $template);
			$content .= $this->parent->cObj->substituteMarkerArrayCached($this->parent->cObj->getSubpart($template,"###TEMPLATE_PUB_GROUP_TYPE###"), $arrGroupMarker, $arrWrappedSubMarker);
		}
		
		
		return $content;	
	}

	function renderPublicationList($items, $template)
	{
		$content = "";
		
		for($i=0; $i < count($items); $i++)
		{
			$content .= $this->parent->cObj->substituteMarkerArrayCached($this->parent->cObj->getSubpart($template,"###TEMPLATE_PUB_ITEM###"), $this->renderPublicationEntry($items[$i], $i) );
			//var_dump( $this->parent->cObj->getSubpart($template,"###TEMPLATE_PUB_ITEM###"));
		}
		return $content;
	}
	
	function renderAuthorList($strAuthors)
	{
		//     ["exportauthors"]=>
		// string(43) "Fischer:Peter|Pohl:Thomas|Hornegger:Joachim"
		$authors = explode("|",$strAuthors);
		$ret = "";
		$i = 0;
		foreach($authors as $author)
		{
			if($i > 0) { $ret .= "; ";}
			$name = explode(":",$author);
			$ret .= $name[0].", ".$name[1];
			$i++;
		}
		return $ret;
	}
	
	function renderDate($from,$to="",$pubyear='')
	{
		// German style: dd.mm.year, English: year-mm-dd
		list ($year, $month, $day) = split('[/.-]', $from);
		$del = '.';

		// extract date range
		if($to != '') 
		{			
			list ($tyear, $tmonth, $tday) = split('[/.-]', $to);
			
			// change of month (change of year not considered^)
			if($month != $tmonth)
			{
				$out = $day.$del.$month.$del.'-'.$tday.$del.$tmonth.$del.$year;
			}
			else
			{
				$out = $day.$del.'-'.$tday.$del.$month.$del.$year;
			}
			// OPTIONAL: omit conference year when publication and conf. years match
			if($year == $pubyear)
				$out = substr($out,0,-4);
			
		} else {
			$out = $day.$del.$month.$del.$year;
		}
		
		return $out;
		
		// English style
	}

	function renderPublicationEntry($crisItem, $pubNr=-1)
	{
		$arrMarker = array();
		
		//var_dump($crisItem);
		
		$arrMarker["###TEMPLATE_PUB_ID###"] 		= $crisItem->ID;
		$arrMarker["###TEMPLATE_PUB_TITLE###"] 		= $crisItem->attributes["cftitle"];
		$arrMarker["###TEMPLATE_PUB_AUTHORS###"]	= $this->renderAuthorList($crisItem->attributes["exportauthors"]); //$crisItem->attributes["srcauthors"]	
		
		if($pubNr > -1)
		{
			$arrMarker["###TEMPLATE_PUB_NR###"]		= $pubNr;
			if($pubNr == 0)
			{	
				$arrMarker["###TEMPLATE_PUB_DISPLAY###"] = "";
			} else {
				$arrMarker["###TEMPLATE_PUB_DISPLAY###"] = "style='display:none;'";
			}
		}
		
		// URI available
		if($crisItem->attributes["cfuri"])
		{
			//$arrMarker["###TEMPLATE_PUB_TITLE###"] = $arrMarker["###TEMPLATE_PUB_TITLE###"];
			//$arrMarker["###TEMPLATE_PUB_TITLE1###"]  = $crisItem->attributes["cfuri"];
			$arrMarker["###TEMPLATE_PUB_TITLE###"]  = '<a href='.$crisItem->attributes["cfuri"].'>'.$crisItem->attributes["cftitle"].'</a>';
		}
		
		/*
		if($crisItem->ID == 0)
		{	
			$arrMarker["###TEMPLATE_PUB_DISPLAY###"] = "";
		} else {
 			$arrMarker["###TEMPLATE_PUB_DISPLAY###"] = "style='display:none;'";
		}
		*/
		
		if ($crisItem->attributes["srceditors"])
		{
			$arrMarker["###TEMPLATE_PUB_EDITORIAL###"] = ', <span class="editors">'.$crisItem->attributes["srceditors"].'</span> (Eds.)';
		} else {
			$arrMarker["###TEMPLATE_PUB_EDITORIAL###"] = "";
		}
		
		$pub_Information = "";
		$pub_Information_recent = "";
		switch ($crisItem->attributes["publication type"])
		{
			case 'Conference contribution':		
				if ($crisItem->attributes["conference proceedings title"])
				{
					$pub_Information .= $crisItem->attributes["conference proceedings title"];	
				} else {
					if($crisItem->attributes["book title"])
						$pub_Information .= $crisItem->attributes["book title"];	
				}

				if ($crisItem->attributes["event title"]) 
				{
					if ($crisItem->attributes["event title"] != $pub_Information)
						$pub_Information .= " (".$crisItem->attributes["event title"].')';
					if ($crisItem->attributes["event location"]) 
						$pub_Information .= ", ".$crisItem->attributes["event location"];
					if ($crisItem->attributes["event start date"])  
						$pub_Information .= ", ".$this->renderDate($crisItem->attributes["event start date"],$crisItem->attributes["event end date"],$crisItem->attributes["publyear"]);
				}
				if ($crisItem->attributes["book volume"] && $crisItem->attributes["book volume"] != 'null')      	
					$pub_Information .= ", " . $this->parent->pi_getLL("volume") . " ".$crisItem->attributes["book volume"];
				
				//if ($crisItem->attributes["publisher"])   $pub_Information .= ", ".$crisItem->attributes["publisher"];
				//if ($item['plocation'])   $pub_Information .= ' ('.$item['plocation'].')';
				
				if ($crisItem->attributes["pagesrange"] && $crisItem->attributes["pagesrange"] != '-')  
					$pub_Information .= ", ".$this->parent->pi_getLL("pp")." " .$crisItem->attributes["pagesrange"];
				if ($crisItem->attributes["publyear"])     		$pub_Information .= ", ".$crisItem->attributes["publyear"];
				if ($crisItem->attributes["cfisbn"])        	$pub_Information .= ", ISBN ".$crisItem->attributes["cfisbn"];
				
				//$pub_Information_recent = $item['conference'];				
				break;
				
			case 'Journal article':
				if ($crisItem->attributes["journalname"])     	$pub_Information .= $crisItem->attributes["journalname"];
				if ($crisItem->attributes["book volume"])     	$pub_Information .= ", ".$this->parent->pi_getLL("volume")." ".$crisItem->attributes["book volume"];
				if ($crisItem->attributes["journal issue"] && $crisItem->attributes["journal issue"] != 'null')     
					$pub_Information .= ", ".$this->parent->pi_getLL("number")." ".$crisItem->attributes["journal issue"];
				if ($crisItem->attributes["pagesrange"] && $crisItem->attributes["pagesrange"] != '-')  
					$pub_Information .= ", ".$this->parent->pi_getLL("pp")." " .$crisItem->attributes["pagesrange"];
				if ($crisItem->attributes["publyear"])     		$pub_Information .= ", ".$crisItem->attributes["publyear"];
				
				$pub_Information_recent = $crisItem->attributes["journalname"];
				
				break;
				
			case 'Book':
				if ($crisItem->attributes["publisher"])   $pub_Information = $crisItem->attributes["publisher"];
				if ($crisItem->attributes["cfcitytown"])  $pub_Information .= ", ".$crisItem->attributes["cfcitytown"];
                if ($crisItem->attributes["publyear"])    $pub_Information .= ", ".$crisItem->attributes["publyear"];
				break;
				
			case 'Article in Edited Volumes':
				if ($crisItem->attributes["edited volumes"])   	$pub_Information = $crisItem->attributes["edited volumes"];
				if ($crisItem->attributes["cfseries"])   		$pub_Information .= " - ".$crisItem->attributes["cfseries"];
				if ($crisItem->attributes["serieseditor"])   		$pub_Information .= ", ".$crisItem->attributes["serieseditor"];
				if ($crisItem->attributes["pagesrange"] && $crisItem->attributes["pagesrange"] != '-')  
					$pub_Information .= ", ".$this->parent->pi_getLL("pp")." " .$crisItem->attributes["pagesrange"];
                if ($crisItem->attributes["publyear"])    		$pub_Information .= ", ".$crisItem->attributes["publyear"];
				
				break;
				
			case 'Other':
			
				// check subtype:
				if($crisItem->attributes["publication journal subtype"] && $crisItem->attributes["type other subtype"])
				{	
					if ($crisItem->attributes["type other subtype"])$pub_Information .= '('.$crisItem->attributes["type other subtype"].') ';
					if ($crisItem->attributes["journalname"])     	$pub_Information .= $crisItem->attributes["journalname"];
					if ($crisItem->attributes["book volume"])     	$pub_Information .= ", ".$this->parent->pi_getLL("volume")." ".$crisItem->attributes["book volume"];
					if ($crisItem->attributes["journal issue"])     $pub_Information .= ", ".$this->parent->pi_getLL("number")." ".$crisItem->attributes["journal issue"];
					if ($crisItem->attributes["pagesrange"])  		$pub_Information .= ", ".$this->parent->pi_getLL("pp")." " .$crisItem->attributes["pagesrange"];
					if ($crisItem->attributes["publyear"])     		$pub_Information .= ", ".$crisItem->attributes["publyear"];
				}
				
				break;
			/*	
			case 'techrep':
				if ($item['school'])      $pub_Information .= $item['school'];
				if ($item['address'])     $pub_Information .= ", ".$item['address'];
				if ($item['year'])        $pub_Information .= ", ".$item['year'];
				break;
			
			case 'artmono':
				if ($item['booktitle'])   $pub_Information .= $item['booktitle'];
				if ($item['series'])      $pub_Information .= " ".$item['series'];
				if ($item['publisher'])   $pub_Information .= ", ".$item['publisher'];
				if ($item['plocation'])   $pub_Information .= ' '.$item['plocation'];
				if ($item['year'])        $pub_Information .= ", ".$item['year'];
				if ($item['pages'])       $pub_Information .= ", ".$item['pages'];
				
				$pub_Information_recent = $item['booktitle'];
				
				break;
				
			case 'dayband':
				if ($item['conference'])  $pub_Information .= $item['conference'];
				if ($item['address'])     $pub_Information .= ", ".$item['address'];
				if ($item['volume'])      $pub_Information .= ", ".$this->parent->pi_getLL("volume")." ".$item['volume'];
				if ($item['publisher'])   $pub_Information .= ", ".$item['publisher'];
				if ($item['year'])        $pub_Information .= ", ".$item['year'];
				
				$pub_Information_recent = $item['conference'];
				
				break;	
				
			case 'dayung':
				$pub_dayung = "";
				if ($item['conference'])  $pub_Information .= $item['conference'];
				if ($item['address'])     $pub_Information .= ", ".$item['address'];
				if ($item['volume'])      $pub_Information .= ", ".$this->parent->pi_getLL("volume")." ".$item['volume'];
				if ($item['year'])        $pub_Information .= ", ".$item['year'];
				
				$pub_Information_recent = $item['conference'];
				
				break;
				
			case 'talk':
				$pub_talk = "";
				if ($item['school'])      $pub_Information .= "at ".$item['school'];
				if ($item['conference'])  $pub_Information .= " (".$item['conference'].")";
				if ($item['address'])     $pub_Information .= " in ".$item['address'];
				if ($item['hsyear'])      $pub_Information .= " (".$item['hsyear'].")";
				break;
				
			case 'monogr':
				if ($item['publisher'])   $pub_Information .= $item['publisher'];
				if ($item['plocation'])   $pub_Information .= ' '.$item['plocation'];
                if ($item['year'])        $pub_Information .= ", ".$item['year'];
				break;
				
			case 'schutzr':
				if ($item['number'])      $pub_Information .= " ".$item['number'];
				break;	
			
			case 'hschri':
				if ($item['school'])      $pub_Information .= " ".$item['school']."";
				if ($item['hstype'])      $pub_Information .= ", ".$item['hstype'].".";
				if ($item['year'])        $pub_Information .= ", ".$item['year'];
				if ($item['pages'])       $pub_Information .= ", ".$item['pages']." ".$this->parent->pi_getLL("pages");
				break;	
			*/
			default:
				$pub_Information = "";
		}
		$arrMarker["###TEMPLATE_PUB_CONFERENCE###"] = htmlspecialchars($pub_Information);
	
		$arrMarker["###RECENT_MEDIA###"] = $pub_Information_recent;
	
		$arrMarker['###TMPL_PUB_LINKS###'] = '';
   	
		//if ($item['type'] != 'talk' ) 
    	{
    		$arrMarker['###TMPL_PUB_LINKS###'] .= "(";
			//$arrMarker['###TMPL_PUB_LINKS###'] .= '<a href="http://univis.uni-erlangen.de/prg?search=publications&id='.$crisItem->ID.'&show=bibtex">BiBTeX</a>';
			$arrMarker['###TMPL_PUB_LINKS###'] .= '<a href="http://cris.fau.de/bibtex/publication/'.$arrMarker["###TEMPLATE_PUB_ID###"].'.bib">BiBTeX</a>';
			//$arrMarker['###TMPL_PUB_LINKS###'] .= ', <a href="http://scholar.google.com/scholar?as_occt=title&as_q='.urlencode($crisItem->attributes["cftitle"]).'&num=100">'.$this->parent->pi_getLL("whoCited").'</a>';
			$arrMarker['###TMPL_PUB_LINKS###'] .= ', <a href="http://scholar.google.com/scholar?as_occt=title&as_q='.urlencode($crisItem->attributes["cftitle"]).'&num=100">Cited by</a>';
			if ($crisItem->attributes["doi"]) 
				$arrMarker['###TMPL_PUB_LINKS###'] .= ', <a href="http://dx.doi.org/'.$crisItem->attributes["doi"].'">DOI</a>';
			$arrMarker['###TMPL_PUB_LINKS###'] .= ")";
		}
	
		return $arrMarker;
	}
}
