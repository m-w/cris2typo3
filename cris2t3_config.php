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

/**
 * Plugin 'Cris2Typo3' for the 'cris2typo3' extension.
 *
 * @author	Felix Lugauer, Peter Fischer, Christoph Forman (LME Webteam <admin-web@i5.cs.fau.de>)
 * @package	TYPO3
 * @subpackage	tx_cris2typo3
 */

class CRISConfig
{
	public $conf = array();
	
	function __construct()
	{
		// general
		$this->conf['orga_id'] 		= 142477;		// CRIS id of the chair
		$this->conf['sort_year'] 	= SORT_DESC;	// Sorting of publications
		// groups of publications --> summary per year per group will be shown
		$this->conf['pub_type_grouping'] = array("book","journal article","conference contribution","article in edited volumes","other");
		
		// All publications plugins - year config
		$this->conf['first_y'] 		= 1970;
		$this->conf['until_y'] 		= 2015;
		// configuring the T3 page with the name "older" shows all pubs until 'old_y' year summarized in one page
		$this->conf['old_y'] 		= 2000;	
		
		// Could also be used to change types of listed publications, names of their categories etc. (so far most is hard coded in lib/class.publications and flexform/template and language files)
		
		// TODO convience functions to alter rendering of e.g., author and date format via
		// function renderAuthorList($strAuthors)
		// function renderDate($from,$to="",$pubyear='')
	}
}

?>