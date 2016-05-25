
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

// DEFAULT initialization of a module [BEGIN]
if (!isset($MCONF)) {
	require('conf.php');
}

$GLOBALS['LANG']->includeLLFile('EXT:cris2typo3/mod1/locallang.xml');
if (!class_exists('t3lib_scbase')) require_once(PATH_t3lib . 'class.t3lib_scbase.php');

$GLOBALS['BE_USER']->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
// DEFAULT initialization of a module [END]


/**
 * Module 'Cris2Typo3' for the 'cris2typo3' extension.
 *
 * @author	Peter Fischer, Christoph Forman (LME Webteam <admin-web@i5.cs.fau.de>)
 * @package	TYPO3
 * @subpackage	tx_cris2typo3
 */
class  tx_cris2typo3_module1 extends t3lib_SCbase {
	var $pageinfo;

	/**
	 * Initializes the Module
	 * @return	void
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		parent::init();
	}


	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
	 */
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = Array (
			'function' => Array (
				'1' => $LANG->getLL('function1'),
			)
		);
		parent::menuConfig();
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return	[type]		...
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		$this->doc = t3lib_div::makeInstance('mediumDoc');
		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id) || ($GLOBALS["BE_USER"]->user['uid'] && !$this->id))	{

			// Draw the header.
			$this->doc->form='<form action="" method="POST" enctype="multipart/form-data">';

				// JavaScript
			$this->doc->JScode = '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				</script>
			';
			$this->doc->postCode='
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = 0;
				</script>
			';

// 						$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_cs($this->pageinfo['_thePath'],-50);

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			if($BE_USER->user['admin']) {
				$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
				$this->content.=$this->doc->divider(5);
			}
		
			// Render content:
			$this->moduleContent();


			// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
			}

			$this->content.=$this->doc->spacer(10);
		} else {
			// If no access or if ID == zero

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
			$this->content.="no access";
		}
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return	void
	 */
	function printContent()	{

		$this->content.=$this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content
	 *
	 * @return	void
	 */
	function moduleContent()	{
		if(isset($_GET['data']))
		{
		 	$array = array_keys($_GET['data']);
			$table = $array[0];
			$array = array_keys($_GET['data'][$table]);
			$key   = $array[0];
			$array = array_keys($_GET['data'][$table][$key]);
			$field = $array[0];
			$value = $_GET['data'][$table][$key][$field];
			
			$where = "`key` = ".$key;
			$field_values = array($field => $value);
			
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $field_values);
		
		}
		
		if(isset($_GET['move']))
		{
			$array = array_keys($_GET['move']);
			$table = $array[0];
			$array = array_keys($_GET['move'][$table]);
			$key   = $array[0];
			$value = $_GET['move'][$table][$key];
			error_log("move ".$table);
			
			// Update auf Sorting Spalte...
			$where = "`key` = ".$key;
			$fields = array('sorting' => 'sorting +'.$value);
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $fields, array('sorting'));
			$where = "`key` = ".($value+$key);
			$fields = array('sorting' => 'sorting -'.$value);
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $fields, array('sorting'));
		}
		
		if(isset($_GET['edit']))
		{
			$array = array_keys($_GET['edit']);
			$table = $array[0];
			$array = array_keys($_GET['edit'][$table]);
			$key   = $array[0];
			
			$this->editData($table,$key);
			return;
		}
		
		switch((string)$this->MOD_SETTINGS['function'])	{
			case 1:
				$content ="<div align=center><strong>".$GLOBALS["BE_USER"]->user['realName']."</strong> - CRIS Settings</div>";
				$content = '<table>'
					. $this->setCrisID()
					. '<tr><td colspan="2"><input type="submit" value="Save"></td></tr></table>';
 								'<br/> />GET:'. t3lib_utility_Debug::viewArray($_GET).'<br />'.
 								'POST:'. t3lib_utility_Debug::viewArray($_POST).'<br />'.
 								'FILES:'. t3lib_utility_Debug::viewArray($_FILES).'<br />';
				$this->content .= $this->doc->section($GLOBALS["BE_USER"]->user['realName'].'  Settings',$content,0,1);
				break;
		}
	}

	function editData($table, $key)
	{
	 	$query = "SELECT * FROM ".$table." WHERE `key`=".$key;
		$row = $this->performSQLSelectQuery("*",$table,$key,'','','','true');
		
		// Header
		for($i = 0; $i < count($row[0]); $i++) {	
		  	$fieldname = array_keys($row[0]);
		 	if($fieldname[$i] != "key" && $fieldname[$i] != "display" && $fieldname[$i] != "richt" && $fieldname[$i] != "type") {
	 			$out .= "<tr>";
				$out .= '	<td class="c-headLine" nowrap="nowrap"><b>'.$fieldname[$i].'</b></td>';
				$out .= '	<td class="c-headLine" nowrap="nowrap">';
				$out .= '		<input name="data['.$table.']['.$key.']['.$fieldname[$i].']" value="'.$row[0][$fieldname[$i]].'" style="width: 288px;" class="formField2">';
				$out .= "</td></tr>";
			}
		}
		$out .= "</tr>";
		
		
		// Generate Content
		$content='
			<!--
				STANDARD LIST OF "'.$table.'"
			-->
			<table class="typo3-dblist" border="0" cellpadding="0" cellspacing="0">
				<tbody>
				<tr>
					<td class="c-headLineTable" colspan="'.count($row[0]).'" nowrap="nowrap"><b>'.$table.' -> '.$row[0]['key'].'</b></td>
				</tr>
				'.$out.'
				</tbody>
			</table>';
		$this->content.=$this->doc->section($GLOBALS["BE_USER"]->user['realName'].'  Settings',$content,0,1);
	}

	function setCrisID()
	{
		if( !isset($_POST['crisid']) )
		{
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'tx_cris_key',					                          // SELECT
				'be_users',                                               // FROM
				'uid='.$GLOBALS["BE_USER"]->user['uid']                   // WHERE
			);

			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$crisid = $row['tx_cris_key'];

			$content .= '<tr><td>Cris ID:</td>'.
				'<td><input type="text" value="'.$crisid.'" name="crisid"></td></tr>';
		}
		else
		{
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
				'be_users',   						  					  // TABLE
				'uid='.$GLOBALS["BE_USER"]->user['uid'],                  // WHERE
				array('tx_cris_key'=>$_POST['crisid'])                    // FIELDS
			);

			$content = "<tr><td colspan=2>Cris ID successfully changed to: ".$_POST['crisid']."</td></tr>";
		}
		return $content;
	}
	

    function performSQLSelectQuery($select_fields,
									$from_table,
									$where_clause,
									$groupBy='',
									$orderBy='',
									$limit='',
									$output='false')
    {
            $out = array();

            $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select_fields,$from_table,$where_clause,$groupBy,$orderBy,$limit);

            if ($output == 'true')
            {
                    if (!$result)
                    {
                            $message  = 'Invalid query: ' . $GLOBALS['TYPO3_DB']->sql_error() . "\n";
                            $message .= 'Whole query: ' . $query;
                            die($message);
                    }

                    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))
                    {
                            $out[] = $row;
                    }
                    return $out;
            }
    }
                
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cris2typo3/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cris2typo3/mod1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_cris2typo3_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>
