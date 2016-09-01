<?php
/**
 * ################################################################################
 * ###    Hang-o-Mat v. 2.5   ###   Copyright Jan Erdmann @ http://www.je0.de   ###
 * ################################################################################
 * @edit 2016-08-30 for Joomla 3.6.2 by ghsvs.de
 * @version 2016.08.31-2
*/
?>
<?php
defined('_JEXEC') or die;

#########################################
########## EINSTELLUNGEN START ##########

/**
$debug boolean true|false
 Siehe function DebugQuery.
 "Debug"-Ausgabe von (bisher) wenigen $query-Zeilen. Siehe im Code:
 if ($debug) DebugQuery($query, __LINE__, $debug_exit);
 Sollte gesetzt werden, bevor die DB-Abfrage abläuft.

$debug_exit boolean true|false
 Unterbricht nach jeweils ausgegebener Debug-Zeile den Code.
*/
$debug = false;
$debug_exit = false;

/**
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
$adminpass = '';

/**
$loeanz integer
 Anzahl der anzuzeigenden Löser-Namen.
*/
$loeanz = 10;

/**
 Diverse weitere.
*/
$hmallbuch = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
$hmcount = 0;
$hmhoch = 0;
$HEADER = '<table cellspacing=1 cellpadding=2 class="hmtabelle">';
$FOOTER = '</table>';
########## EINSTELLUNGEN ENDE ##########
########################################

// Init some variables to avoid Warnings and Notices.
$W1 = $W2 = array();
$Text = $Text2 = $Text3 = $hmswort = $col = $Titel = $jetec_Ip = $stwort = '';

// Init some variables/shortcuts.
$heuteTag = date('l');
$hmlaenge = strlen($hmallbuch);
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

// Create Login/Logout input for admin.
$jehmtt = '<input type="button" class="hminput" ';
if ($loggedIn)
{
 $jehmtt .= 'value="Adminlogout" onclick="jehmlogin(0)">';
}
else
{
 $jehmtt .= 'value="Adminlogin" onclick="jehmlogin(1)">';
}
?>
<center>
<?
// Admin action?
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
   if ($debug) DebugQuery($query, __LINE__, $debug_exit);
   $db->setQuery($query)->execute();
  }

  $query->clear()->delete($hangomat)->where($db->qn('Id') . '=' . $db->q($Hangodel));
  if ($debug) DebugQuery($query, __LINE__, $debug_exit);
  $db->setQuery($query)->execute();
 } // end Delete.
 
 $reihen = '&nbsp;&nbsp;&nbsp;(Aktuelles Wort)';
 $Text = '<tr class="hmheader"><td align="center">Neues Wort</td></tr>';

 $query->clear()->select('*')->from($hangomat)->order($db->qn('Id') . ' ASC');
 $db->setQuery($query);
 $ergebnis = $db->loadAssocList();
 
 foreach ($ergebnis as $dat)
 {
  $col = ($col == 'hmfarbe1' ? 'hmfarbe2' : 'hmfarbe1');
  $Text .= '<tr class="' . $col . '"><td><a href="javascript:jehmloesch(\'' . $dat['Id'] . '\')"><img src="' . $szge .'muell.gif" alt="l&ouml;schen" border=0></a>&nbsp;&nbsp;&nbsp;' . $dat['Wort'] . $reihen . '</td></tr>';
  // ????
  if ($reihen != '')
  {
   $reihen = '';
  }
 }
 $Text .= '<tr class="hmfarbe1"><td><input type="text" name="hangowort" value="" style="width:200px;" class="hminput">&nbsp;<input type="submit" value="schreiben" name="hangwort" class="hminput"></td></tr>';
 
 // Output admin form.
 echo '<form action="' . $formAction . '" method="post" name="hango">';
 echo '<input type="hidden" value="" name="Hangodel" />';
 echo $HEADER . $Text . $FOOTER . '</form>';
} // end Admin action?

$Text = '';
$query->clear()->select('*')->from($hangomat)->order($db->qn('Id') . ' ASC');
$db->setQuery($query);
$ergebnis = $db->loadAssocList();

if(($dat = array_shift($ergebnis)))
{
 // Tagwechsel? Dann setze abgestimmte Buchstaben, die korrekt etc.
 if ($dat['Tag'] != $heuteTag)
 {
  for ($a = 0; $a < $hmlaenge; $a++)
  {
   if ($dat[$hmallbuch[$a]] > $hmhoch)
   {
    $hmhoch = $dat[$hmallbuch[$a]];
    $hmmaxx = $hmallbuch[$a];
   }
   if ($dat[$hmallbuch[$a]] == $hmhoch)
   {
    srand((double) microtime() * time());
    $hm = rand(1, 2);
    if ($hm == 1)
    {
     $hmmaxx = $hmallbuch[$a];
     $hmhoch = $dat[$hmallbuch[$a]];
    }
   }
  } // end for

  for ($a = 0; $a < strlen($dat['Wort']); $a++)
  {
   if ($dat['Wort'][$a] == $hmmaxx)
   {
    $dat['SWort'][$a] = $dat['Wort'][$a];
   }
   $hmbuch = str_replace($hmmaxx, '', $dat['Buchstaben']);
  }
  
  $query->clear()->update($hangomat)
   ->where($db->qn('Id') . '=' . $db->q($dat['Id']))
   ->set($db->qn('Tag') . ' = ' . $db->q($heuteTag))
   ->set($db->qn('Anzahl') . ' = ' . $db->q($dat['Anzahl'] + 1));

  for ($a = 0; $a < $hmlaenge; $a++)
  {
   $query->set($db->qn($hmallbuch[$a]) . ' = 0');
  }
  
  if ($hmhoch != 0)
  {
   $query->set($db->qn('Buchstaben') . ' = ' . $db->q($hmbuch))
   ->set($db->qn('SWort') . ' = ' . $db->q($dat['SWort']))
   ->set($db->qn('Last') . ' = ' . $db->q($hmmaxx));
  }
  $db->setQuery($query)->execute();
  
  $query->clear()->delete($hangomat_ip);
  if ($debug) DebugQuery($query, __LINE__, $debug_exit);
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
   $Text = '<tr class="hmfarbe1"><td colspan=2 align="center">Du hast heute schon versucht zu l&ouml;sen, versuche es morgen noch einmal.<br><a href="' . $formAction . '">zur&uuml;ck</a></td></tr>';
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
     #$hm_mail = htmlentities($hm_mail, ENT_QUOTES, 'UTF-8');
     $hm_url = htmlentities($hm_url, ENT_QUOTES, 'UTF-8');
     $hm_name = addslashes($hm_name);
     #$hm_mail = addslashes($hm_mail);
     $hm_url = addslashes($hm_url);
     $hm_anzahl = $dat['Anzahl'];

     if($hm_mail && !JMailHelper::isEmailAddress($hm_mail))
     {
      $Text = '<tr class="hmfarbe1"><td align="center">Die angegebene Mailadresse ist falsch.<br><a href="' . $formAction . '">zur&uuml;ck</a></td></tr>';
     }

     if ($hm_url && !preg_match("/^[a-zA-Z0-9-_.:\/]+$/", $hm_url))
     {
      $Text = '<tr class="hmfarbe1"><td align="center">Bitte nur "A-Z", "0-9" und ":/-_." bei "Homepage" benutzen.<br><a href="' . $formAction . '">zur&uuml;ck</a></td></tr>';
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
      if ($debug) DebugQuery($query, __LINE__, $debug_exit);
      $db->setQuery($query)->execute();

      $query->clear()->delete($hangomat_ip);
      if ($debug) DebugQuery($query, __LINE__, $debug_exit);
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
      
      $dat['LWort'] = $Loesungswort;
      
      $Text='<tr class="hmfarbe1"><td colspan=2 align="center">Du wurdest in die Liste eingetragen.<br><a href="' . $formAction . '">zur&uuml;ck</a></td></tr>';
     } // end if (!$Text)
    }
    // Winning user has not yet entered name, mail... Show a form for these datas.
    else
    {
     $Text = array();
     $Text[] = '<tr class="hmfarbe1"><td colspan=2>Die Antwort ist Richtig, das gesuchte Wort ist "<b>' . $dat['Wort'] . '</b>".<br>Du kannst dich in die Liste eintragen.</td></tr>';
     $Text[] = '<tr class="hmfarbe2"><td align="right">Name:&nbsp;</td><td><input type="text" name="hm_name" style="width:300;" maxlength=50 class="hminput"></td></tr>';
     $Text[] = '<tr class="hmfarbe2"><td align="right">Homepage:&nbsp;</td><td><input type="url" name="hm_url" style="width:300;" maxlength=255 class="hminput"></td></tr>';
     $Text[] = '<tr class="hmfarbe2"><td align="right">E-Mail:&nbsp;</td><td><input type="email" name="hm_mail" style="width:300;" maxlength=50 class="hminput"></td></tr>';
     $Text[] = '<tr class="hmfarbe1"><td align="center" colspan=2>';
     $Text[] = '<input type="hidden" name="Loesungswort" value="' . $dat['Wort'] . '">';
     $Text[] = '<input type="submit" value="&nbsp;Eintragen&nbsp;" class="hminput"></td></tr>';
     $Text = implode('', $Text);
    }
   }
   // User tried it but incorrect answer. Block IP for today.
   else
   {
    $Text = '<tr class="hmfarbe1"><td colspan=2 align="center">Leider ist der eingegeben Begriff falsch, versuche es morgen noch einmal.<br><a href="' . $formAction . '">zur&uuml;ck</a></td></tr>';

    $query->clear()->delete($hangomat_ip)
    ->where($db->qn('Ip') . '=' . $db->q($jetec_Ip));
    if ($debug) DebugQuery($query, __LINE__, $debug_exit);
    $db->setQuery($query)->execute();
    
    $columns = array('Try', 'Ip');
    $values =  array(1, $db->q($jetec_Ip));
    
    $query->clear()->insert($hangomat_ip)
     ->columns($db->qn($columns))->values(implode(',', $values));

    $db->setQuery($query)->execute();
   }
  }
  echo '<form method="post" action="' . $formAction . '" name="loesen">' . $HEADER;
  echo '<tr class="hmheader"><td align="center" colspan=2>' . $Titel . '</td></tr>
' . $Text . $FOOTER . '</form>';
 } // end Lösungsversuch durch Worteingabe?

 // Voting für einzelnen Buchstaben?
 $vote = 9;
 // Single letter selected.
 if ($Buchstabe && (preg_match ("/^[A-Z]+$/", $Buchstabe)))
 {
  // Selected Buchstabe allowed?
  $query->clear()->select($db->qn('Id'))->from($hangomat)
   ->where($db->qn('Buchstaben') . ' NOT LIKE ' . $db->q('%' . $Buchstabe . '%'))
   ->where($db->qn('Id') . '=' . $db->q($dat['Id']));
  if ($debug) DebugQuery($query, __LINE__, $debug_exit);
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
   $Text = 'Du hast Heute bereits f&uuml;r den Buchstaben "<b>' . $dat1['Buch'] . '</b>" gevotet, versuche es Morgen noch einmal.<br><a href="' . $formAction . '">zur&uuml;ck</a>';
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

 // Anzeige
 if (!$Text)
 {
  $query->clear()->select('*')->from($hangomat)->order($db->qn('Id') . ' ASC');
  $db->setQuery($query);
  $ergebnis = $db->loadAssocList();

  if(($dat = array_shift($ergebnis)))
  {
   $Text = '<table cellpadding=5 cellspacing=1 class="hmtabelle"><tr align="center" class="hmfarbe2">
<td class="hmfarbe3" rowspan=2>Buchstaben:</td>';

   for ($a=0; $a < $hmlaenge; $a++)
   {
    if ($dat[$hmallbuch[$a]] > 0)
    {
     $W1[$a] = $dat[$hmallbuch[$a]];
     $W2[$a] = $hmallbuch[$a];
    }
    if ($dat['Buchstaben'][$hmcount] == $hmallbuch[$a])
    {
     $Text .= '<td align="center"><input type="submit" value="' . $hmallbuch[$a] . '" name="Buchstabe" class="hminput" style="width:25; height:25;"></td>';
     $hmcount++;
    }
    else
    {
     $Text .= '<td align="center"><input type="button" value="' . $hmallbuch[$a] . '" class="hminput1" style="width:25; height:25;"></td>';
    }
    if ($a == 12)
    {
     $Text .= '</tr><tr class="hmfarbe2">';
    }
   } // end for ($a=0; $a < $hmlaenge; $a++)

   if ($W1 && $W2)
   {
    array_multisort($W1, SORT_NUMERIC, SORT_DESC, $W2);
   }

   for ($a=0; $a < count($W1); $a++)
   {
    $Text2 .= '<td align="center"><b>' . $W2[$a] . '</b></td>';
    $Text3 .= '<td align="center">' . $W1[$a] . '</td>';
   }
   
   
   for ($a=0; $a < strlen($dat['SWort']); $a++)
   {
    $hmswort .= '&nbsp;' . $dat['SWort'][$a];
   }

   $Text .= '</tr></table>';
   $Text .= "<br><table cellpadding=5 cellspacing=1 class=\"hmtabelle\"><tr align=\"center\" class=\"hmfarbe2\"><td align=\"center\" class=\"hmfarbe3\">Buchstaben:</td>" . $Text2 . "</tr><tr align=\"center\" class=\"hmfarbe2\"><td align=\"center\" class=\"hmfarbe3\">Stimmen:</td>" . $Text3 . "</tr></table>
<br><span style=\"FONT-SIZE:20px; FONT-WEIGHT: bold;\">" . $hmswort . "</span><br><br>
Das Wort l&auml;uft seit <b>&nbsp;" . $dat['Anzahl'] . "&nbsp;</b> Tag" . ($dat['Anzahl'] != 1 ? "en" : "")."<br>
Letzter gel&ouml;ster Begriff: <b>&nbsp;" . $dat['LWort'] . "</b>&nbsp;<br>
Letzter bewerteter Buchstabe: <b>&nbsp;" . $dat['Last'] . "&nbsp;</b><br>
<input type=\"text\" name=\"Loesungswort\" style=\"width:300;\" class=\"hminput\">&nbsp;<input type=\"submit\" value=\"&nbsp;L&ouml;sen&nbsp;\" class=\"hminput\"><br><br>" . $jehmtt;
  }
 }
}
else
{
 $Text = "Kein Wort in der Datenbank.<br><br>" . $jehmtt;
}

if (!$Loesungswort)
{
 echo '<form method="post" action="' . $formAction . '" name="hmform"><input type="hidden" value="" name="hmcook">' . $HEADER;
 echo '<tr class="hmheader"><td align="center">' . $Titel . '</td></tr>';
 echo '<tr class="hmfarbe1"><td align="center">' . $Text . '</td></tr>';
 echo $FOOTER . '</form>';
 $Text = '<tr align="center" class="hmheader"><td colspan=4>Die letzten ' . $loeanz . ' L&ouml;ser</td></tr>';
 $Text .= '<tr align="center" class="hmfarbe1"><th>Gel&ouml;st:</th><th>Name:</th><th>Wort:</th><th>Tage:</th></tr>';
 
 $query->clear()->select('*')->from($hangomat_liste)->order($db->qn('Id') . ' DESC');
 $db->setQuery($query, 0, $loeanz);
 foreach ($db->loadAssocList() as $dat1)
 {
  $col = ($col == 'hmfarbe1' ? 'hmfarbe2' : 'hmfarbe1');
  if ($dat1['Mail'])
  {
   // $dat1['Name'] = '<a href="mailto:' . $dat1['Mail'] . '">' . $dat1['Name'] . '</a>';
  }
  $Text .= '<tr class="' . $col . '"><td>' . date('d.m.Y', $dat1['Zeit']) . '</td><td>' . $dat1['Name'] . '</td><td align="center">' . $dat1['Wort'] . '</td><td align="center">' . $dat1['Anzahl'] . '</td></tr>';
 }
 echo $HEADER . $Text . $FOOTER;
}
?>
</center>
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
 .hmtabelle {width:70%;}
 .hmfarbe1 {font-size:0.9em;color:#000;}
 .hmfarbe2 {background-color:transparent;font-size:0.9em;color:#000;}
 .hmfarbe3 {font-size:0.8em;color:#000;}
 .hmheader {background-color:transparent;font-size:1.3em;color:#000;font-weight:bold;}
 .hminput {font-size:1em;color:#000;background-color:#EAEAEA;border:1px ridge #000;}
 .hminput1 {font-size:1em;color:#777;background-color:#999;border:1px ridge #999;}
 ';
 $doc->addStyleDeclaration($css);
 
 $js = '
 function jehmloesch(id)
 {
  var box = confirm("Wirklich l&ouml;schen?");
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
 ';
 $doc->addScriptDeclaration($js);
}

function DebugQuery($query, $line, $debug_exit = true)
{
 echo 'DEBUG Line: ' . $line . ':' .print_r((string) $query,true);
 if ($debug_exit)
 {
  exit;
 }
}
