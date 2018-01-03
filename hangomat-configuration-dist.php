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
$hmconfig['moeglicheBuchstaben'] = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';


#### ???HANGOMAT-TABELLEN MIT TESTDATEN BEFÜLLEN???
#### Wenn man Hangomat das erste mal verwendet, kann das hilfreich sein.
#### !Voreinstellung: false für NEIN!
#### Setzen Sie auf true, wenn Tabellen befüllt werden sollen.
#### Beachten:  Wenn die Tabellen bereits Daten enthalten,
####  gibt's eine Nachricht auf der Seite. Dann hier wieder auf false setzen.
$hmconfig['insertTestData'] = false;


#### ???NICHT DIE JOOMLA-DATENBANK VERWENDEN???
####
#### !HAUPTSCHALTER!
#### Setzen Sie useExternalDB auf true, um eine andere Datenbank zu verwenden.
#### Voreinstellung: false für Joomla-Datenbank verwenden.
$hmconfig['useExternalDB'] = false;
####
#### !VERBINDUNGSDATEN!
#### Geben Sie die Verbindungsdaten für die externe Datenbank ein:
$hmconfig['dbprefix'] = '';
$hmconfig['host'] = 'localhost';
$hmconfig['user'] = '';
$hmconfig['password'] = '';
$hmconfig['database'] = '';
$hmconfig['driver'] = 'mysqli';
