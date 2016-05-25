# cris2typo3
Typo3 Extension zum Einbinden von Daten aus dem CRIS Forschungsportal der FAU (vgl. [univis2web](https://code.google.com/archive/p/univis2typo3/))

Das Univis der FAU wird schrittweise durch neuere Services ersetzt. Darunter das bereits lauffähige CRIS Forschungsportal, welches die Bereitstellung von Publikation ersetzt. Andere Informationsdienste zum Abruf von Lehrveranstaltungen oder Personen sind noch nicht implementiert. Daher kann auf univis2web noch nicht verzichtet werden, was einen Parallelbetrieb von cris2typo3 erforderlich macht. Dies scheint zur Zeit die favorisierte Lösung zu sein. 

LME Webteam <admin-web@i5.cs.fau.de>  (www5.cs.fau.de)

###Features
Alle publikationsbasierten Module aus univis2typo3 sind auch in "cris2typo3" verfügbar:
Ausgewählte Publikationen
Auflistung der Publikationen
Auflistung der Publikationen (Mitarbeiterseite)^ [3]
Neueste Publikationen        [4]
Gemischte Publikationen*  



###Änderungen gegenüber univis2web:
--
- Einige Daten wurden bisher nicht in CRIS importiert und können daher nicht mehr angezeigt werden: 
2015 (203 Publications, Talks and Patents) --> 2015 (64 Publications)
  - Talks []
  - Patents []
  - Dissertationen []

- Links auf Mitarbeiterwebseiten fehlen in der Autorenliste, Feld für Webseiten v. FAU Mitarbeitern

- Datumsangaben bei Konferenzen verbessert

- Gruppierung der Publikationen nach Jahr, dann nach Typen: bequem per CRIS_FORMATTER:
		$o = array("book","journal article","conference contribution","article in edited volumes","other");
		$formatter2 = new CRIS_formatter("publication type", $o);	
		

####Weiteres

- ( CRIS formatter kann nicht gleichzeitig nach Jahr und PubType sortieren bzw. gruppieren ): gelöst durch sequentielle Aufrufe...

- Idee für neues CRIS Plugin: Beliebige PubIDs um Projektbeschreibungen bzw. personenuebergreifende Publikationslisten zu erstellen [x]

- Config Änderungen in ext_tables, evtl. auch in locallang etc. werden aktiv nachdem das Plugin deaktiviert und wieder aktiviert wurde
	--> oder vermutlich durch System cache loeschen: http://www.schwarzer.de/blog/extension-cache-typo3-6-2-komplett-loeschen/

- ext_tables.php: p1/p2 entries were switched
//Required for flexform
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi2']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi2', 'FILE:EXT:'.$_EXTKEY.'/flexform.xml');
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:'.$_EXTKEY.'/selectedPublications/flexform.xml');
