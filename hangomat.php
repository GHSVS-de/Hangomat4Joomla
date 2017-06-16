<?php
/**
 * ################################################################################
 * ###    Hang-o-Mat v. 2.5   ###   Copyright Jan Erdmann @ http://www.je0.de   ###
 * ################################################################################
 * @edit 2016-08-30 for Joomla 3.6.2 by ghsvs.de
 * @version 2017.06.16 (tested with Joomla 3.7.3 beta)
*/
?>
<?php
defined('_JEXEC') or die;

#########################################
########## EINSTELLUNGEN START ##########
/*
$dbprefix string
 Wenn leer wird Joomla-Datenbankprefix verwendet (empfohlen!).

 Seit Version 2016-08-31-2: Leer ist dann mpfohlen, wenn keine
 externe Datenbank verwendet wird. Siehe dafür weitere Einstellungen
 unten.

 Wenn man $dbprefix wechselt, daran denken, dass weitere DB-Tabellen
 erzeugt werden. Eben welche mit dem geänderten Prefix.

$createHangomatTables boolean true|false
 Siehe auch function createHangomatTables.
 Sollen Datenbanktabellen erstellt werden? Existierende werden
 NICHT überschrieben. Empfohlen ist false, wenn Tabellen
 bereits angelegt sind; wegen Performance.

$insertTestData boolean true|false
 Siehe auch function insertTestData.
 Legt Testdaten in Tabellen ab. VORSICHT! Wenn Tabelle bereits
 gefüllt, gibt's Fehlermeldungen. Geht aber nichts kaputt.
*/
$dbprefix = ''; 
$createHangomatTables = true;
$insertTestData = false;

/**
 * @since 2016.08.31-2

 VERWENDEN EINER EXTERNEN DATENBANK.

$useExternalDB boolean true oder false
 HAUPTSCHALTER FÜR Joomla-EXTERNE Datenbank.

 Die danach eingegebenen Verbindungsdaten sollen verwendet
 werden, um eine andere als die Joomladatenbank zu verwenden.

 Es muss sich dabei NICHT um eine Joomladatenbank handeln.

 Beachten Sie, dass
 1) sich die Datenbank auf dem selben "Server"
 befinden muss wie das Joomla, das das Script ausführt.
 Das ist in den allermeisten Fällen gegeben,
 wenn die Fremddatenbank im selben Webseiten-Account angelegt wird
 wie die Joomladatenbank.

 Oder 2) die Datenbank externen Zugang zulassen muss,
 was bei den meisten "normalen" Providern nicht möglich ist.

$host string
 Häufig 'localhost', weicht aber bei paar Providern ab.

$user string
 Datenbankuser für Anmeldung.

$password string
 Datenbankkennwort für Anmeldung.

$database string
 Datenbankname.

$driver string
 Im Normalfall 'mysqli'.
*/
$useExternalDB = false;

// $dbprefix = #Setze oben $dbprefix!!!!!!
$host = 'localhost';
$user = '';
$password = '';
$database = '';
$driver = 'mysqli';

/**
$szge string
 Grafikordner mit Hangomat-Grafiken innerhalb Joomla-Installatio.
 Bsp.: '/images/hangomat/'.
 Beachte: Relative Pfade sind NICHT empfohlen.
*/
$szge = '/images/hangomat/';

/**
$adminpass string
 Hangomat-Administrations-Kennwort. Sollte niemals zugleich DB-Passwort oder
 ähnlich gefährlich sein.
*/
$adminpass = 'abc';

/**
$loeanz integer
 Anzahl der anzuzeigenden Löser-Namen.
*/
$loeanz = 10;

/**
 Diverse weitere.
*/
$moeglicheBuchstaben = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
$counter = 0;
$hmhoch = 0;
########## EINSTELLUNGEN ENDE ##########
########################################

// Init some variables to avoid Warnings and Notices.
$W1 = $W2 = array();
$Text = $Text2 = $Text3 = $hmswort = $col = $Titel = $jetec_Ip = $stwort = '';

// Init some variables/shortcuts.
$heuteTag = date('l');
$session = JFactory::getSession();
$input = JFactory::getApplication()->input;
$formAction = htmlspecialchars(JUri::getInstance()->toString());
$szge = JUri::root(true) . $szge;
$dbprefix = trim($dbprefix) ? trim($dbprefix) : '#__';

// Read POST datas from forms.
$hmcook = $input->post->get('hmcook', '', 'string');
$hangowort = trim($input->post->get('hangowort', '', 'string'));
$Hangodel = (int) $input->post->get('Hangodel');
$Loesungswort = trim($input->post->get('Loesungswort', '', 'string'));
$Buchstabe = $input->post->get('Buchstabe', '');
$hm_name = trim($input->post->get('hm_name', '', 'string'));
$hm_mail = trim($input->post->get('hm_mail', '', 'string'));
$hm_url = trim($input->post->get('hm_url', '', 'string'));
$sessionKey = 'hangomat_hmcook.'.$module->id;

// Get IP of user.
if (isset($_SERVER['REMOTE_ADDR']))
{
 $jetec_Ip = $_SERVER['REMOTE_ADDR'];
}
elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
{
 $jetec_Ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
elseif (isset($_SERVER['HTTP_CLIENT_IP']))
{
 $jetec_Ip = $_SERVER['HTTP_CLIENT_IP'];
}

// Check if admin is logged in and write into session.
if (!empty($hmcook) && $hmcook === $adminpass)
{
 $session->set($sessionKey, 1);
}
elseif ($hmcook == 'Logout')
{
 $session->set($sessionKey, null);
}
$loggedIn = $session->get($sessionKey, null);

// Create Login/Logout input for admin.
$jehmtt = '<p><input type="button" ';
if ($loggedIn)
{
	$jehmtt .= 'value="Adminlogout" onclick="jehmlogin(0)">';
}
else
{
	$jehmtt .= 'value="Adminlogin" onclick="jehmlogin(1)">';
}
$jehmtt .= '</p>';

// Load CSS and JS in page HEAD. See function addCSSJS below for changes.
addCSSJS($formAction);

// Init db query variable
if ($useExternalDB)
{
 $options = array(
  'driver' => $driver,
  'host' => $host,
  'user' => $user, 
  'password' => $password,
  'database' => $database,
  'prefix' => $dbprefix
 );
 $db = JDatabaseDriver::getInstance($options);
}
else
{
 $db = JFactory::getDbo();
}
$query = $db->getQuery(true);

// Create db tables.
if ($createHangomatTables)
{
 createHangomatTables($dbprefix, $db);
}

// Fill db tables with test datas.
if ($insertTestData)
{
 insertTestData($dbprefix, $db);
}

// Prepare table names (shortcuts). Add prefix and quote names.
$hangomat = $db->qn($dbprefix . 'hangomat');
$hangomat_ip = $db->qn($dbprefix . 'hangomat_ip');
$hangomat_liste = $db->qn($dbprefix . 'hangomat_liste');
?>
<div class="div4whole-hangomat">

<?

## START Administrator Panel.
if ($loggedIn)
{
 // Admin has entered new word. Save in db!
 if ($hangowort && preg_match('/^[a-zA-Z ]+$/', $hangowort))
 {
  $hangowort = preg_replace('/\s\s+/', ' ', strtoupper($hangowort));
  for ($a = 0; $a < strlen($hangowort); $a++)
  {
   if (substr($hangowort, $a, 1) != ' ')
   {
    $stwort .= '_';
   }
   else
   {
    $stwort .= ' ';
   }
  }
  $columns = array('Wort', 'SWort',);
  $values =  array($db->q($hangowort), $db->q($stwort));
  $query->clear()->insert($hangomat)
   ->columns($db->qn($columns))->values(implode(',', $values));
  $db->setQuery($query)->execute();
 } // end Admin has entered new word. Save in db!
 
 // Delete db entry?
 if ($Hangodel)
 {
  $query->clear()->select('MIN(' . $db->qn('Id') . ')')->from($hangomat);
  $db->setQuery($query);
  $min = (int) $db->loadResult();
  if ($min === $Hangodel)
  {
   $query->clear()->delete($hangomat_ip);
   
   $db->setQuery($query)->execute();
  }

  $query->clear()->delete($hangomat)->where($db->qn('Id') . '=' . $db->q($Hangodel));
  
  $db->setQuery($query)->execute();
 } // end Delete.
 
 ### START Adminbereich Worte.
 $query->clear()->select('*')->from($hangomat)->order($db->qn('Id') . ' ASC');
 $db->setQuery($query);
 $worte = $db->loadObjectList();

 $textCollector = array('<div class="div4admin">');
 $textCollector[] = '<h4>Worte verwalten</h4>';
 if ($worte)
 {
  $textCollector[] = '<ul class="list-striped">';
  foreach ($worte as $i => $wort)
  {
   $textCollector[] = '<li>';
   $textCollector[] = '<a href="javascript:jehmloesch(\'' . $wort->Id . '\')">löschen</a> ';
   $textCollector[] = '' . $wort->Wort . ($i == 0 ? ' (Aktuelles Wort)' : '') . '';
   $textCollector[] = '</li>';
  }
		$textCollector[] = '</ul>';
 }
 else
 {
  $textCollector[] = '<p>Keine Worte in Datenbank gefunden.</p>';
 }
 
 $textCollector[] = '<h5>Neues Wort anlegen</h5>';
 $textCollector[] = '<p><input type="text" name="hangowort" value=""><br />';
 $textCollector[] = '<input type="submit" value="schreiben" name="hangwort"></p>';
 $textCollector[] = '</div><!--/div4admin-->';
 $Text = implode("\n", $textCollector); 
 echo '<form action="' . $formAction . '" method="post" name="hango">';
 echo '<input type="hidden" value="" name="Hangodel" />';
 echo $Text . $jehmtt;
	echo '</form>';
 echo '<script>document.forms.hango.onkeypress = stopRKey;</script>';
}
## ENDE Administrator Panel.


// START ANZEIGE FÜR BESUCHER
// Die Datenbank enthält vom Administrator vorbereitete Worte (s.o.).
// Dabei ist das mit der niedrigsten Id der aktuelle Wort-Datensatz.
$Text = '';
$query->clear()->select('*')->from($hangomat)->order($db->qn('Id') . ' ASC');
$db->setQuery($query);
$ergebnis = $db->loadAssocList();

// Aktueller Wort-Datensatz.
if(($dat = array_shift($ergebnis)))
{

 // Tagwechsel? Dann setze abgestimmte Buchstaben, die korrekt etc.
 if ($dat['Tag'] != $heuteTag)
 {
  for ($a = 0; $a < strlen($moeglicheBuchstaben); $a++)
  {
   if ($dat[$moeglicheBuchstaben[$a]] > $hmhoch)
   {
    $hmhoch = $dat[$moeglicheBuchstaben[$a]];
    $hmmaxx = $moeglicheBuchstaben[$a];
   }
   
   // Absolut gar keine Ahnung, was das soll. Zufallsberechnung.
   if ($dat[$moeglicheBuchstaben[$a]] == $hmhoch)
   {
    srand((double) microtime() * time());
    $hm = rand(1, 2);
    if ($hm == 1)
    {
     $hmmaxx = $moeglicheBuchstaben[$a];
     $hmhoch = $dat[$moeglicheBuchstaben[$a]];
    }
   }
  } // end for
  
  // $dat['Wort'] ist das komplette, zu findende Lösungswort.
  for ($a = 0; $a < strlen($dat['Wort']); $a++)
  {
   if ($dat['Wort'][$a] == $hmmaxx)
   {
    
    // $dat['SWort'] ist das zu findende Lösungswort mit Unterstrich-Platzhaltern.
    $dat['SWort'][$a] = $dat['Wort'][$a];
   }
   
   // $dat['Buchstaben'] sind die für Tipps noch zur Verfügung stehenden Buchstaben.
   $hmbuch = str_replace($hmmaxx, '', $dat['Buchstaben']);
  }
  
  $query->clear()->update($hangomat)
   ->where($db->qn('Id') . '=' . $db->q($dat['Id']))
   ->set($db->qn('Tag') . ' = ' . $db->q($heuteTag))
   ->set($db->qn('Anzahl') . ' = ' . $db->q($dat['Anzahl'] + 1));

  for ($a = 0; $a < strlen($moeglicheBuchstaben); $a++)
  {
   $query->set($db->qn($moeglicheBuchstaben[$a]) . ' = 0');
  }
  
  if ($hmhoch != 0)
  {
   $query->set($db->qn('Buchstaben') . ' = ' . $db->q($hmbuch))
   ->set($db->qn('SWort') . ' = ' . $db->q($dat['SWort']))
   ->set($db->qn('Last') . ' = ' . $db->q($hmmaxx));
  }
  $db->setQuery($query)->execute();
  
  $query->clear()->delete($hangomat_ip);
  
  $db->setQuery($query)->execute();
 } // end Tagwechsel?

 // Lösungsversuch durch Worteingabe?
 if ($Loesungswort && !$Buchstabe)
 {
  $query->clear()->select('*')->from($hangomat_ip)
  ->where($db->qn('Ip') . '=' . $db->q($jetec_Ip))
  ->where($db->qn('Try') . '= 1')
  ;
  $db->setQuery($query);
  
  if ($db->loadResult())
  {
   $Text = '<p class="alert alert-error alerter">Du hast heute schon versucht zu lösen. Versuche es morgen noch einmal.<br><br><a class="btn" href="' . $formAction . '">zurück</a></p>';
  }
  else
  {
   // Solved. 
   if (strtoupper($Loesungswort) == $dat['Wort'])
   {
    // Winning user has entered already name, mail...
    if ($hm_name)
    {
     $hm_name = htmlentities($hm_name, ENT_QUOTES, 'UTF-8');
     $hm_url = htmlentities($hm_url, ENT_QUOTES, 'UTF-8');
     $hm_name = addslashes($hm_name);
     $hm_url = addslashes($hm_url);
     $hm_anzahl = $dat['Anzahl'];

     if($hm_mail && !JMailHelper::isEmailAddress($hm_mail))
     {
      $Text='<p class="alert alert-error alerter">Die angegebene Mailadresse ist falsch.<br><br><a class="btn" href="' . $formAction . '">zurück</a></p>';
     }

     if ($hm_url && !preg_match("/^[a-zA-Z0-9-_.:\/]+$/", $hm_url))
     {
      $Text='<p class="alert alert-success alerter">Bitte nur "A-Z", "0-9" und ":/-_." bei "Homepage" benutzen.<br><br><a class="btn" href="' . $formAction . '">zurück</a></p>';
     }

     if (substr($hm_url, 0, 4) != 'http')
     {
      $hm_url = 'http://' . $hm_url;
     }
     
     // All user datas are correctly entered. So update.
     if (!$Text)
     {
      // Delete now outdated entry and IPs.
      $query->clear()->delete($hangomat)
      ->where($db->qn('Id') . '=' . $db->q($dat['Id']));
      
      $db->setQuery($query)->execute();

      $query->clear()->delete($hangomat_ip);

      $db->setQuery($query)->execute();
      
      // Update next entry. Remember last correct answer...
      if (($dat = array_shift($ergebnis)))
      {
       $query->clear()->update($hangomat)
        ->where($db->qn('Id') . '=' . $db->q($dat['Id']))
        ->set($db->qn('LWort') . ' = ' . $db->q($Loesungswort))
        ->set($db->qn('Tag') . ' = ' . $db->q($heuteTag));
       $db->setQuery($query)->execute();
      }
      
      // Save datas of winner.
      $columns = array(
       'Zeit',
       'Name',
       'HP',
       'Mail',
       'Wort',
       'Anzahl',
      );
      $values = array(
       $db->q(time()),
       $db->q($hm_name),
       $db->q($hm_url),
       $db->q($hm_mail),
       $db->q($Loesungswort),
       $db->q($hm_anzahl),
      );
      $query->clear()->insert($hangomat_liste)
       ->columns($db->qn($columns))->values(implode(',', $values));
      $db->setQuery($query)->execute();
      
      // $dat['LWort'] ist das zuletzt fertig gelöste Wort.
      $dat['LWort'] = $Loesungswort;
      
      $Text='<p class="alert alert-success alerter">Du wurdest in die Liste eingetragen.<br><br><a class="btn" href="' . $formAction . '">zurück</a></p>';
     } // end if (!$Text)
    }
    // Winning user has not yet entered name, mail... Show a form for these datas.
    else
    {
     $textCollector = array();
     $textCollector[] = '<p class="alert alert-success alerter">Die Antwort ist Richtig!<br />Das gesuchte Wort ist "<strong>' . $dat['Wort'] . '</strong>".<br /><br />
<strong>Du kannst dich in die Liste der Sieger eintragen!</strong></p>';
     
     $textCollector[] = '<p>Name:<br /><input type="text" name="hm_name" maxlength=50></p>';
     $textCollector[] = '<p>Homepage:<br /><input type="url" name="hm_url" maxlength=255></p>';
     $textCollector[] = '<p>E-Mail:<br /><input type="email" name="hm_mail" maxlength=50></p>';
     $textCollector[] = '<input type="hidden" name="Loesungswort" value="' . $dat['Wort'] . '">';
     $textCollector[] = '<p><input type="submit" value="Eintragen"></p>';
     $Text = implode("\n", $textCollector);
    }
   }
   // User tried it but incorrect answer. Block IP for today.
   else
   {
    
    $Text = '<p class="alert alert-error alerter">Leider ist der eingegeben Begriff falsch!<br />
Versuche es morgen noch einmal!<br><br><a href="' . $formAction . '" class="btn">zurück</a></p>';

    $query->clear()->delete($hangomat_ip)
    ->where($db->qn('Ip') . '=' . $db->q($jetec_Ip));
    $db->setQuery($query)->execute();
    
    $columns = array('Try', 'Ip');
    $values =  array(1, $db->q($jetec_Ip));
    
    $query->clear()->insert($hangomat_ip)
     ->columns($db->qn($columns))->values(implode(',', $values));

    $db->setQuery($query)->execute();
   }
  }
  echo '<form method="post" action="' . $formAction . '" name="loesen">';
  echo $Text . '</form>';
  echo '<script>document.forms.loesen.onkeypress = stopRKey;</script>';
 }
 // end Lösungsversuch durch Worteingabe?


 // Voting für einzelnen Buchstaben?
 $vote = 9;

 // Single letter selected.
 if ($Buchstabe && (preg_match ("/^[A-Z]+$/", $Buchstabe)))
 {
  // Selected Buchstabe allowed?
  $query->clear()->select($db->qn('Id'))->from($hangomat)
   ->where($db->qn('Buchstaben') . ' NOT LIKE ' . $db->q('%' . $Buchstabe . '%'))
   ->where($db->qn('Id') . '=' . $db->q($dat['Id']));
  $db->setQuery($query);

  if ($db->loadResult())  
  {
   $Text = 'Der Buchstabe "<b>' . $Buchstabe . '</b>" steht nicht mehr zur Auswahl.<br><a href="' . $formAction . '">zur&uuml;ck</a>';
  }
  
  $query->clear()->select('*')->from($hangomat_ip)
   ->where($db->qn('Ip') . ' = ' . $db->q($jetec_Ip));
  $db->setQuery($query);
  $ergebnis1 = $db->loadAssocList();
  
  if (($dat1 = array_shift($ergebnis1)))
  {
   $vote = $dat1['Vote'];
  }
  
  // User may not vote for a letter.
  if ($vote == 1)
  {
   $Text = '<p class="alert alert-error alerter">Du hast heute bereits für den Buchstaben "<b>' . $dat1['Buch'] . '</b>" gevotet!<br />
Versuche es morgen noch einmal!<br><br><a href="' . $formAction . '" class="btn">zurück</a></p>';
  }
  
  // User may vote for a letter.
  if (!$Text)
  {
   // $dat[$Buchstabe]++;
   if ($vote == 0)
   {
    $query->clear()->update($hangomat_ip)
     ->where($db->qn('Ip') . '=' . $db->q($jetec_Ip))
     ->set($db->qn('Vote') . ' = 1')
     ->set($db->qn('Buch') . ' = ' . $db->q($Buchstabe));
    $db->setQuery($query)->execute();
   }
   elseif ($vote == 9)
   {
    $columns = array('Vote', 'Ip', 'Buch');
    $values =  array(1, $db->q($jetec_Ip), $db->q($Buchstabe));
    $query->clear()->insert($hangomat_ip)
     ->columns($db->qn($columns))->values(implode(',', $values));
    $db->setQuery($query)->execute();
   }
   if (!strstr($dat['SWort'], $Buchstabe))
   {
    $query->clear()->update($hangomat)
     ->where($db->qn('Id') . '=' . $db->q($dat['Id']))
     ->set($db->qn($Buchstabe) . ' = ' . $db->q(($dat[$Buchstabe] + 1)));
    $db->setQuery($query)->execute();
   }
  } // end // User may vote for a letter.
 } // end Voting für einzelnen Buchstaben?



 // Anzeige bei Erstbesuch.
 if (!$Text)
 {
  
  // Datensätze der angelegten Worte auslesen.
  $query->clear()->select('*')->from($hangomat)->order($db->qn('Id') . ' ASC');
  $db->setQuery($query);
  $ergebnis = $db->loadAssocList();
  
  // Datensatz $dat mit niedrigster Id ist aktuelles.
  if(($dat = array_shift($ergebnis)))
  {
   #### START Zu lösendes Wort (mit Unterstrichen) ausgeben.
   $textCollector = array('<div class="div4zu-loesendes-wort">');
   $textCollector[] = '<h4>Löse dieses Wort!</h4>';
   $textCollector[] = '<p class="aktuellesWort">';
			$wortlaenge = strlen($dat['SWort']);
   for ($a=0; $a < $wortlaenge; $a++)
   {
    $textCollector[] = '&nbsp;' . $dat['SWort'][$a];
   }
   $textCollector[] = '</p>';
   $textCollector[] = '<p class="description">';
			$textCollector[] = 'Das gesuchte Wort hat ' . $wortlaenge . ' Buchstaben.';
   $textCollector[] = '<br />Das Wort läuft seit ' . $dat['Anzahl'];
   $textCollector[] = ' Tag' . ($dat['Anzahl'] != 1 ? 'en.' : '.');
   if ($dat['Last'])
   {
    // $textCollector[] = '<br />Letzter ausgewerteter Buchstabe: ' . $dat['Last'];
   }
   if ($dat['LWort'])
   {
    $textCollector[] = '<br />Letzter gelöster Begriff: ' . $dat['LWort'] . '.';
   }
   $textCollector[] = '</p>';
   $textCollector[] = '</div><!--/div4zu-loesendes-wort-->';
   $Text .= implode("\n", $textCollector);
   #### ENDE Zu lösendes Wort (mit Unterstrichen) ausgeben.

   #### START Buchstabenfelder A-Z.
   $textCollector = array('<div class="div4buchstaben">');
   $textCollector[] = '<h5>Wähle entweder einen Buchstaben...</h5>';
   
   // Alle grundlegend möglichen Buchstaben durchlaufen.
   for ($a=0; $a < strlen($moeglicheBuchstaben); $a++)
   {
    
    // Bspw. [A] => 2. $dat['A']. Also die heute schon gemachten Tipps/Klicks
    // für den Buchstaben A, die erst am Folgetag ausgewertet werden.
    if ($dat[$moeglicheBuchstaben[$a]] > 0)
    {
     // Wie oft wurde der Buchstabe heute schon getippt?
     $W1[$a] = $dat[$moeglicheBuchstaben[$a]];
     // Der Buchstabe selbst.
     $W2[$a] = $moeglicheBuchstaben[$a];
    }
    
    $textCollector[] = '<p class="p4letter">';
    
    // Für Auswahl gesperrter Buchstabe oder nicht.
    if ($dat['Buchstaben'][$counter] == $moeglicheBuchstaben[$a])
    {
     $textCollector[] = '<input type="submit" value="' . $moeglicheBuchstaben[$a] . '" name="Buchstabe">';
     $counter++;
    }
    else
    {
     $textCollector[] = '<span>' . $moeglicheBuchstaben[$a] . '</span>';
    }
    $textCollector[] = '</p><!--/p4letter-->';
   }
   $textCollector[] = '</div><!--/div4buchstaben-->';
   $Text .= implode("\n", $textCollector);
   #### ENDE Buchstabenfelder A-Z.
   
   #### START Heute getippte Buchstaben ausgeben.
   $textCollector = array('<div class="div4stimmen-heute">');
   $textCollector[] = '<h6>Heute schon getippte Buchstaben</h6>';
   if ($W1 && $W2)
   {
    array_multisort($W1, SORT_NUMERIC, SORT_DESC, $W2);

    for ($a=0; $a < count($W1); $a++)
    {
     $textCollector[] = '<p>';
     $textCollector[] = '<span class="span4letter">' . $W2[$a] . '</span>';
     $textCollector[] = '<span class="span4votes">' . $W1[$a] . 'x</span>';
     $textCollector[] = '</p>';
    }
   }
   else
   {
    $textCollector[] = '<div>Heute hat noch niemand getippt.' . '</div>';
   }
   $textCollector[] = '</div><!--/div4stimmen-heute-->';
   $Text .= implode("\n", $textCollector);
   #### ENDE Heute getippte Buchstaben ausgeben.
   
   
   #### START Lösungswort direkt eingeben.
   $textCollector = array('<div class="div4loesungswort-eingabe">');
   $textCollector[] = '<h5>..oder löse das ganze Wort!</h5>';
   $textCollector[] = '<p>';
   $textCollector[] = '<input type="text" name="Loesungswort"><br />';
   $textCollector[] = '<input type="submit" value="Lösen">';
   $textCollector[] = '</p>';
   $textCollector[] = '</div><!--/div4loesungswort-eingabe-->';
   $Text .= implode("\n", $textCollector);
   #### ENDE Lösungswort direkt eingeben.
   
   
   #### START Die letzten X Löser anzeigen.
   $query->clear()->select('*')->from($hangomat_liste)->order($db->qn('Id') . ' DESC');
   $db->setQuery($query, 0, $loeanz);
   $loeser = $db->loadObjectList();
   
   $textCollector = array('<div class="div4letzte-loeser">');
   $textCollector[] = '<h5>Die letzten ' . $loeanz . ' Sieger</h5>';
   if ($loeser)
   {
    $textCollector[] = '<ul class="list-striped">';
    foreach ($loeser as $i => $sieger)
    {
     $textCollector[] = '<li>';
     $textCollector[] = '<span class="siegerName">' . $sieger->Name . '</span>';
     $textCollector[] = '<br />löste <span class="siegerName">' . $sieger->Wort . '</span>';
     $textCollector[] = '<br /> nach <span class="siegerName">' . $sieger->Anzahl . '</span> Tag' . ($sieger->Anzahl > 1 ? 'en' : '');
     $textCollector[] = ' am ' . date('d.m.Y', $sieger->Zeit) . '.';
     $textCollector[] = '</li>';
    }
    $textCollector[] = '</ul>';
   }
   else
   {
    $textCollector[] = '<p>Keine in Datenbank gefunden.</p>';
   }
   $textCollector[] = '</div><!--/div4letzte-loeser-->';
   $Text .= implode("\n", $textCollector);
   #### ENDE Die letzten X Löser anzeigen.

  }
 }
}
else
{
 $Text = "Kein Wort in der Datenbank.<br><br>" . $jehmtt;
}

if (!$Loesungswort)
{
 echo '<form method="post" action="' . $formAction . '" name="hmform"><input type="hidden" value="" name="hmcook">';
 echo $Text;
 

 echo '<p>' . $jehmtt . '</p>';
 
 echo '</form>';
 echo '<script>document.forms.hmform.onkeypress = stopRKey;</script>';
}
?>

</div><!--/div4whole-hangomat-->
<?php

/**

*/
function createHangomatTables($dbprefix, $db)
{
 $sql = array();
 $sql[] = "CREATE TABLE IF NOT EXISTS `" . $dbprefix . "hangomat` (
 `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `Buchstaben` varchar(26) NOT NULL DEFAULT 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
 `Wort` varchar(255) NOT NULL DEFAULT '',
 `SWort` varchar(255) NOT NULL DEFAULT '',
 `Last` char(1) NOT NULL DEFAULT '',
 `Tag` varchar(15) NOT NULL DEFAULT '',
 `Anzahl` int(10) NOT NULL DEFAULT '0',
 `LWort` varchar(200) NOT NULL DEFAULT '',
 `A` int(10) NOT NULL DEFAULT '0',
 `B` int(10) NOT NULL DEFAULT '0',
 `C` int(10) NOT NULL DEFAULT '0',
 `D` int(10) NOT NULL DEFAULT '0',
 `E` int(10) NOT NULL DEFAULT '0',
 `F` int(10) NOT NULL DEFAULT '0',
 `G` int(10) NOT NULL DEFAULT '0',
 `H` int(10) NOT NULL DEFAULT '0',
 `I` int(10) NOT NULL DEFAULT '0',
 `J` int(10) NOT NULL DEFAULT '0',
 `K` int(10) NOT NULL DEFAULT '0',
 `L` int(10) NOT NULL DEFAULT '0',
 `M` int(10) NOT NULL DEFAULT '0',
 `N` int(10) NOT NULL DEFAULT '0',
 `O` int(10) NOT NULL DEFAULT '0',
 `P` int(10) NOT NULL DEFAULT '0',
 `Q` int(10) NOT NULL DEFAULT '0',
 `R` int(10) NOT NULL DEFAULT '0',
 `S` int(10) NOT NULL DEFAULT '0',
 `T` int(10) NOT NULL DEFAULT '0',
 `U` int(10) NOT NULL DEFAULT '0',
 `V` int(10) NOT NULL DEFAULT '0',
 `W` int(10) NOT NULL DEFAULT '0',
 `X` int(10) NOT NULL DEFAULT '0',
 `Y` int(10) NOT NULL DEFAULT '0',
 `Z` int(10) NOT NULL DEFAULT '0',
 PRIMARY KEY  (`Id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
 
 $sql[] = "CREATE TABLE IF NOT EXISTS `" . $dbprefix . "hangomat_ip` (
 `Vote` int(10) NOT NULL DEFAULT '0',
 `Try` int(10) NOT NULL DEFAULT '0',
 `Ip` varchar(20) NOT NULL DEFAULT '',
 `Buch` char(1) NOT NULL DEFAULT ''
 )ENGINE=InnoDB DEFAULT CHARSET=utf8;";
 
 $sql[] = "CREATE TABLE IF NOT EXISTS `" . $dbprefix . "hangomat_liste`
 (
 `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `Zeit` int(15) NOT NULL default '0',
 `Name` varchar(50) NOT NULL default '',
 `HP` varchar(255) NOT NULL default '',
 `Mail` varchar(255) NOT NULL default '',
 `Wort` varchar(255) NOT NULL default '',
 `Anzahl` int(10) NOT NULL default '0',
 PRIMARY KEY  (`Id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
 
 foreach ($sql as $query)
 {
  
  $db->setQuery($query);
  try
  {
   $db->execute();
  }
  catch (Exception $e)
  {
   echo JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br />';
   return false;
  }
 }
 return true;
}

/**

*/
function insertTestData($dbprefix, $db)
{
 $sql = array();
 $sql[] = "INSERT INTO `" . $dbprefix . "hangomat_liste` (
 `Id`, `Zeit`, `Name`, `HP`, `Mail`, `Wort`, `Anzahl`) VALUES
 (1, 1160295791, 'test', 'http://www.test.de', 'test@gmx.de', 'ERDBEERE', 1),
 (2, 1160317415, 'tester', 'http://', '', 'TESTER', 1),
 (3, 1161605334, 'Karina', 'http://', '', 'HOHENFELS', 9),
 (5, 1162486233, 'Tobias Schmidt', 'http://', '', 'KALVARIENBERG', 7),
 (6, 1162724253, 'Sandy Johnston', 'http://', '', 'FORELLENBACH', 4),
 (7, 1162903900, 'Sandy J.', 'http://', '', 'TRUPPENUEBUNGSPLATZ', 2),
 (8, 1163074091, 'Sandy J.', 'http://', '', 'TUCHERHAUS', 3),
 (9, 1163247057, 'Sandy J.', 'http://', '', 'OBERPFALZ', 2),
 (10, 1163416206, 'Sandy J.', 'http://', '', 'ALBRECHT VON HOHENFELS', 3);";
 
 $sql[] = "INSERT INTO `" . $dbprefix . "hangomat_ip` (`Vote`, `Try`, `Ip`, `Buch`) VALUES
(1, 0, '91.115.139.220', 'G');";

 $sql[] = "INSERT INTO `" . $dbprefix . "hangomat` (`Id`, `Buchstaben`, `Wort`, `SWort`, `Last`, `Tag`, `Anzahl`, `LWort`, `A`, `B`, `C`, `D`, `E`, `F`, `G`, `H`, `I`, `J`, `K`, `L`, `M`, `N`, `O`, `P`, `Q`, `R`, `S`, `T`, `U`, `V`, `W`, `X`, `Y`, `Z`) VALUES
(467, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'STELLWERK', '_________', '', '', 0, '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(466, 'ABCDFGHJKLMOPQSUVWXYZ', 'BEFLAGGUNG', '_E______N_', 'N', 'Saturday', 5, 'BABYLEICHT', 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(468, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'LUFTRAUM', '________', '', '', 0, '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(469, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'SCHNAPSIDEE', '___________', '', '', 0, '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(470, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'KAMPFJET', '________', '', '', 0, '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);";
 
 foreach ($sql as $query)
 {
  $db->setQuery($query);
  try
  {
   $db->execute();
  }
  catch (Exception $e)
  {
   echo JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br />';
   return false;
  }
 }
 return true;
}

function addCSSJS($formAction)
{
 $doc = JFactory::getDocument();
 $css = '
.div4whole-hangomat{
	width: 100%;
	text-align: center;
}
.alerter{
 font-size:1.2em;
 font-weight:bold;
}
.p4letter{display:inline-block;}
.p4letter input, .p4letter span{
 display:block;
 line-height: 24px;
 font-size: 16px;
 width: 28px;
 padding:0;
 margin:0;
 border: 2px solid gray;
}
.p4letter span{
 border-color: red;
 color: white;
}
.p4letter input{
 border-color: green;
}
.div4stimmen-heute p{
 display:inline-block;
 line-height: 24px;
 font-size: 16px;
 min-width: 28px;
 padding:0;
 margin:0;
 border: 2px solid gray;
}
.div4stimmen-heute span{
 display: block;
 text-align: center;
}
span.span4letter{
 fon-weight: bold;
 background-color: gray;
 color:white;
}
.siegerName{
 font-weight: bold;
 font-size: 1.2em;
 color: green;
}
.siegerWort{
 color: green;
}
.div4letzte-loeser li{
 text-align:center;
}
.aktuellesWort{
 font-weight: bold;
 font-size: 1.5em;
}
 ';
 $doc->addStyleDeclaration($css);
 
 $js = '
 function jehmloesch(id)
 {
  var box = confirm("Wirklich löschen?");
  if (box == true)
  {
   document.forms.hango.elements.Hangodel.value = id;
   document.forms.hango.submit();
  }
 }
 
 function jehmlogin(mto)
 {
  if (mto == 1)
  {
   jehmlog = prompt("Bitte das Adminpasswort eingeben");
   if (jehmlog != "" && jehmlog != null)
   {
    document.forms.hmform.elements.hmcook.value = jehmlog;
   }
  }
  else
  {
   document.forms.hmform.elements.hmcook.value = "Logout";
  }
  document.forms.hmform.submit();
 }
 function stopRKey(evt) {
   var evt = (evt) ? evt : ((event) ? event : null);
   var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
   if ((evt.keyCode == 13) && (node.type=="text"))  {return false;}
 }
 ';
 $doc->addScriptDeclaration($js);
}
