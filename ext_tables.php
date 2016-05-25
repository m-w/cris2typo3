<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout,select_key';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi3']='layout,select_key';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi4']='layout,select_key';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_any']='layout,select_key';

// Alumni pub not needed
//$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi5']='layout,select_key';
t3lib_extMgm::addPlugin(array('LLL:EXT:cris2typo3/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');
t3lib_extMgm::addPlugin(array('LLL:EXT:cris2typo3/locallang_db.xml:tt_content.list_type_pi2', $_EXTKEY.'_pi2'),'list_type');
t3lib_extMgm::addPlugin(array('LLL:EXT:cris2typo3/locallang_db.xml:tt_content.list_type_pi3', $_EXTKEY.'_pi3'),'list_type');
t3lib_extMgm::addPlugin(array('LLL:EXT:cris2typo3/locallang_db.xml:tt_content.list_type_pi4', $_EXTKEY.'_pi4'),'list_type');
t3lib_extMgm::addPlugin(array('LLL:EXT:cris2typo3/locallang_db.xml:tt_content.list_type_any', $_EXTKEY.'_any'),'list_type');

// Alumni pub not needed
//t3lib_extMgm::addPlugin(array('LLL:EXT:cris2typo3/locallang_db.xml:tt_content.list_type_pi5', $_EXTKEY.'_pi5'),'list_type');

//t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Cris2Typo3");
include_once(t3lib_extMgm::extPath($_EXTKEY).'class.tx_cris2typo3_addFieldsToFlexForm.php');

if (TYPO3_MODE=="BE"){
    $TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_cris2typo3_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'selectedPublications/class.tx_cris2typo3_pi1_wizicon.php';
    $TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_cris2typo3_pi2_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'listPublications/class.tx_cris2typo3_pi2_wizicon.php';
    $TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_cris2typo3_pi3_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'listPersonalPublications/class.tx_cris2typo3_pi3_wizicon.php';
    $TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_cris2typo3_pi4_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'recentPublications/class.tx_cris2typo3_pi4_wizicon.php';
	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_cris2typo3_any_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'listAnyPublications/class.tx_cris2typo3_any_wizicon.php';
	// Alumni pub not needed
    //$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_cris2typo3_pi5_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'listAlumniPublications/class.tx_cris2typo3_pi5_wizicon.php';
}

//Required for flexform
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:'.$_EXTKEY.'/selectedPublications/flexform.xml');

$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_any']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_any', 'FILE:EXT:'.$_EXTKEY.'/listAnyPublications/flexform.xml');

$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi2']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi2', 'FILE:EXT:'.$_EXTKEY.'/flexform.xml');

if (TYPO3_MODE == 'BE')	{
	t3lib_extMgm::addModule('web','txcris2typo3M1','',t3lib_extMgm::extPath($_EXTKEY).'mod1/');
}
?>
