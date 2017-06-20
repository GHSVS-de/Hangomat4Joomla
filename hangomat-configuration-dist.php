<?php
defined('_JEXEC') or die;

### Seit Version 2017.06.16 ###
### Vorlage für Konfigurationsdatei für Hangomat.
### Siehe https://github.com/GHSVS-de/Hangomat4Joomla/wiki/Konfigurationsdatei-f%C3%BCr-hangomat.php
?>
<?php

#### ADMINISTRATOR-KENNWORT FUER HANGOMAT.
#### Sollte niemals zugleich DB-Passwort oder ähnlich gefährlich sein.
#### Wenn Sie leer lassen, ist keine Hangomat-Administration möglich!
#### Darf kein Leerzeichen am Anfang oder Ende enthalten.
$hmconfig['adminpass'] = '';


#### ???WIE VIELE LETZTE GEWINNER SOLLEN MAXIMAL ANGEZEIGT WERDEN???
#### !Voreinstellung: 10 !
#### Wenn Sie auf 0 setzen, werden keine Gewinner angezeigt.
$hmconfig['loeanz'] = 10;


#### ???ERLAUBTE BUCHSTABEN???
#### Beachten: Das ist aus dem Original-Hangomat so übernommen.
#### Ich habe nie getestet, was für Auswirkungen es hat, wenn
####  man hier was ändert.
$hmconfig['moeglicheBuchstaben'] = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';


#### ???HANGOMAT-TABELLEN ANLEGEN, WENN SIE NOCH NICHT EXISTIEREN???
#### !Voreinstellung: true für JA!
#### Kann dauerhaft auf true bleiben.
#### Setzen Sie auf false für NEIN.
$hmconfig['createHangomatTables'] = true;


#### ???HANGOMAT-TABELLEN MIT TESTDATEN BEFÜLLEN???
#### Wenn man Hangomat das erste mal verwendet, kann das hilfreich sein.
#### !Voreinstellung: false für NEIN!
#### Setzen Sie auf true, wenn Tabellen befüllt werden sollen.
#### Beachten:  Wenn die Tabellen bereits Daten enthalten,
####  gibt's Fehlermeldungen auf der Seite. Dann hier wieder auf false setzen.
$hmconfig['insertTestData'] = false;
