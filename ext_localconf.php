<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_cris2typo3_pi1 = < plugin.tx_cris2typo3_pi1.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'selectedPublications/class.tx_cris2typo3_pi1.php','_pi1','list_type',0);
t3lib_extMgm::addPItoST43($_EXTKEY,'listPublications/class.tx_cris2typo3_pi2.php','_pi2','list_type',0);
t3lib_extMgm::addPItoST43($_EXTKEY,'listPersonalPublications/class.tx_cris2typo3_pi3.php','_pi3','list_type',0);
t3lib_extMgm::addPItoST43($_EXTKEY,'recentPublications/class.tx_cris2typo3_pi4.php','_pi4','list_type',0);
t3lib_extMgm::addPItoST43($_EXTKEY,'listAnyPublications/class.tx_cris2typo3_any.php','_any','list_type',0);
//t3lib_extMgm::addPItoST43($_EXTKEY,'listAlumniPublications/class.tx_cris2typo3_pi5.php','_pi5','list_type',0);

?>
