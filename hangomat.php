<?php
/**
 * ################################################################################
 * ###    Hang-o-Mat v. 2.5   ###   Copyright Jan Erdmann @ http://www.je0.de   ###
 * ################################################################################
 * @edit 2016-08-30 for Joomla 3.6.2 by ghsvs.de
 * @version 2018.01.07 (tested with Joomla 4.0.0 dev)
*/
?>
<?php
defined('_JEXEC') or die;

$hangoHelper = new hangoHelper($module->id);

if ($hangoHelper->stopExecution)
{
	return;
}

$loggedIn = $hangoHelper->getLoggedin();
$userFormShow = true;

$html = array();

$html[] = '<div class="div4whole-hangomat">';

## START Administrator Formular.
if ($loggedIn)
{
	$userFormShow = false;
	$html[] = '<form action="' . $hangoHelper->formAction . '" method="post" name="hango" class=form4admin>';
	$html[] = '<div class="div4admin">';
	$html[] = '<h4>' . JText::_('HANGOMAT_ADMIN_AREA') . '</h4>';
	$refreshItems = false;
	// Admin has entered new word. Save in db!
	if ( ($hangowort = trim(mb_strtoupper($hangoHelper->input->get('hangowort', '', 'string')))) )
	{
		$allowed = $hangoHelper->checkAllowedCharacters($hangowort);
		if ($allowed !== true)
		{
			$html[] = '<p class="alert alert-error alert-danger alerter">';
			$html[] = JText::sprintf('HANGOMAT_CHARACTER_NOT_ALLOWED',
				$hangowort,
				$allowed,
				implode(', ', $hangoHelper->moeglicheBuchstaben_array)
			);
			$html[] = '</p>';
		}
		else
		{
			$hangoHelper->insertHangowort($hangowort);
			$refreshItems = true;
		}
	}
	
	// Delete db entry?
	if ( ($Hangodel = (int) $hangoHelper->input->get('Hangodel')))
	{
		$hangoHelper->deleteHangowort($Hangodel);
		$refreshItems = true;
	}

	// Delete own ip?
	if ($hangoHelper->input->get('deleteip'))
	{
		$hangoHelper->deleteHangomatIp(true);
	}

	if ($hangoHelper->input->get('deleteAllIps'))
	{
		$hangoHelper->deleteHangomatIp();
	}

	if ($refreshItems)
	{
		$hangoHelper->getCurrentItem($refreshItems);
	}
	
	if ($hangoHelper->items)
	{
		$html[] = '<ul class="list-striped">';
		foreach ($hangoHelper->items as $Id => $wort)
		{
			$html[] = '<li>';
			$html[] = '<a href="javascript:jehmloesch(\'' . $wort['Id'] . '\')">löschen (Id:' . $wort['Id'] . ')</a> ';
			$html[] = $wort['Wort'];
			if ($wort['Id'] == $hangoHelper->currentItem['Id'])
			{
				$html[] = ' (Aktuelles Wort)';
			}
			$html[] = '</li>';
		}
		$html[] = '</ul>';
		$html[] = '<input type="hidden" value="" name="Hangodel" />';
	}
	else
	{
		$html[] = '<p>' . JText::_('HANGOMAT_NO_WORDS_IN_DB') . '</p>';
	}

	$html[] = '<h5>' . JText::_('HANGOMAT_CREATE_NEW_WORD') . '</h5>';
	$html[] = '<p><input type="text" name="hangowort" value=""><br />';
	$html[] = '<input type="submit" value="schreiben" name="hangwort"></p>';

	$html[] = '<h5>' . JText::_('HANGOMAT_DELETE_YOUR_IP') . '</h5>';
	$html[] = '<p><input type="submit" value="lösche Deine IP" name="deleteip" class="btn-danger"></p>';

	$html[] = '<h5>' . JText::_('HANGOMAT_DELETE_ALL_IPS') . '</h5>';
	$html[] = '<p><input type="submit" value="Ip-TabulaRasa" name="deleteAllIps" class="btn-danger"></p>';

	$html[] = '<h5>' . JText::_('HEUTE getippte Buchstaben vorzeitig auswerten') . '</h5>';
	$html[] = '<p><input type="submit" value="TagWechsel ausführen" name="simulateTagwechsel" class="btn-danger"></p>';

	$html[] = '<h5>' . JText::_('Abmelden') . '</h5>';
	$html[] = '<p><input type="button" value="Adminlogout" onclick="jehmlogin(0)"></p>';
	
	$html[] = '</div><!--/div4admin-->';
	$html[] = '</form>';
	$html[] = '<script>document.forms.hango.onkeypress = stopRKey;</script>';
}
## ENDE Administrator Formular.


## START ANZEIGE FÜR BESUCHER

############## LOESUNGSWORT
// User has solved the word.
if ($hangoHelper->winnerFormShow === true)
{
	$userFormShow = false;
	$html[] = '<form method="post" action="' . $hangoHelper->formAction . '" name="winnerForm" class="form4winner">';
	$html[] = '<p class="alert alert-success alerter">';
	$html[] = JText::sprintf('HANGOMAT_WORD_CORRECTLY_SOLVED', $hangoHelper->Loesungswort);
	$html[] = '</p>';
	$html[] = '<p>Name:<br /><input type="text" name="hm_name" maxlength=50></p>';
	$html[] = '<p>E-Mail:<br /><input type="email" name="hm_mail" maxlength=50></p>';
	$html[] = '<p><input type="submit" value="Eintragen"></p>';
	$html[] = '<input type="hidden" name="winnerFormInsertid" value="' . $hangoHelper->winnerFormInsertid . '">';
	$html[] = '</form><!--/form winnerForm-->';
	$html[] = '<script>document.forms.winnerForm.onkeypress = stopRKey;</script>';	
}
// User has entered word but it is wrong.
elseif ($hangoHelper->winnerFormShow === false)
{
	$userFormShow = false;
	$html[] = '<p class="alert alert-error alert-danger alerter">';
	$html[] = JText::sprintf('HANGOMAT_WORD_WRONGLY_SOLVED', $hangoHelper->Loesungswort, $hangoHelper->formAction);
	$html[] = '</p>';
}
// User has entered a word but is blocked for today.
elseif ($hangoHelper->winnerFormShow === -1)
{
	$userFormShow = false;
	$html[] = '<p class="alert alert-error alert-danger alerter">';
	$html[] = JText::sprintf('HANGOMAT_WORD_BLOCKED', $hangoHelper->formAction);
	$html[] = '</p>';
}
// Tagwechsel hat zeitgleich gelöst.
elseif ($hangoHelper->winnerFormShow === -2)
{
	$userFormShow = false;
	$html[] = '<p class="alert alert-error alert-danger alerter">';
	$html[] = JText::sprintf('HANGOMAT_SYSTEM_WAS_FASTER', $hangoHelper->formAction);
	$html[] = '</p>';
}
############## LOESUNGSWORT ENDE

############## BUCHSTABE START
// User was allowed to select a letter.
if ($hangoHelper->buchstabeState === true)
{
	$userFormShow = false;
	$html[] = '<p class="alert alert-success alerter">';
	$html[] = JText::sprintf('HANGOMAT_BUCHSTABE_SAVED', $hangoHelper->Buchstabe, $hangoHelper->formAction);
	$html[] = '</p>';
}
// Not allowed Buchstabe.
elseif ($hangoHelper->buchstabeState === false)
{
	$userFormShow = false;
	$html[] = '<p class="alert alert-error alert-danger alerter">';
	$html[] = JText::sprintf('HANGOMAT_BUCHSTABE_BLOCKED', $hangoHelper->Buchstabe, $hangoHelper->formAction);
	$html[] = '</p>';
}
// User selected allowed letter but user is blocked for today.
elseif (is_string($hangoHelper->buchstabeState) && mb_strlen($hangoHelper->Buchstabe) == 1)
{
	$userFormShow = false;
	$html[] = '<p class="alert alert-error alert-danger alerter">';
	$html[] = JText::sprintf('HANGOMAT_BUCHSTABE_USER_BLOCKED', $hangoHelper->buchstabeState, $hangoHelper->formAction);
	$html[] = '</p>';
}
// Tagwechsel hat zeitgleich gelöst.
elseif ((int) $hangoHelper->buchstabeState === -2)
{
	$userFormShow = false;
	$html[] = '<p class="alert alert-error alert-danger alerter">';
	$html[] = JText::sprintf('HANGOMAT_SYSTEM_WAS_FASTER', $hangoHelper->formAction);
	$html[] = '</p>';
}
############## BUCHSTABE ENDE

if (!$hangoHelper->items && !$loggedIn)
{
	$html[] = '<p class="alert alert-error alert-danger alerter">' . JText::_('HANGOMAT_NO_WORDS_IN_DB') . '</p>';
	$userFormShow = false;
}

############## NORMALES BESUCHERFORMULAR START (aber auch AdministratorLogin)
$html[] = '<form method="post" action="' . $hangoHelper->formAction . '" name="hmform" class="form4users">';
$html[] = '<input type="hidden" value="" name="hmcook">';

if ($userFormShow)
{
	#### START Zu lösendes Wort (mit Unterstrichen) ausgeben.
	$wortlaenge = $hangoHelper->calculateWortLength();

	$html[] = '<div class="div4zu-loesendes-wort">';
	if ($wortlaenge['spaces'])
	{
		$html[] = '<h4>' . JText::_('HANGOMAT_HEADLINE_SOLVE_TEXT') . '</h4>';
	}
	else
	{
		$html[] = '<h4>' . JText::_('HANGOMAT_HEADLINE_SOLVE_WORD') . '</h4>';
	}
	$html[] = '<p class="aktuellesWort">';
	$html[] = '&nbsp;' . implode('&nbsp;', $hangoHelper->SWort_array) . '&nbsp;';
	$html[] = '</p><!--/aktuellesWort-->';
	
	$html[] = '<p class="description">';
	
	if ($wortlaenge['spaces'])
	{
		$html[] = JText::sprintf('HANGOMAT_TEXT_CONTAINS', $wortlaenge['words'], $wortlaenge['letters']);
	}
	else
	{
		$html[] = JText::sprintf('HANGOMAT_WORD_CONTAINS', $wortlaenge['letters']);
	
	}
	$html[] = '<br />' . JText::sprintf('HANGOMAT_WORDTEXT_DURATION', $hangoHelper->currentItem['Anzahl']);

	if ($hangoHelper->currentItem['LWort'])
	{
		$html[] = '<br />' . JText::sprintf('HANGOMAT_LAST_WORT', $hangoHelper->currentItem['LWort']);
	}
	if ($hangoHelper->currentItem['Last'])
	{
		$html[] = '<br />' . JText::sprintf('HANGOMAT_LAST_VOTED_CHARACTER', $hangoHelper->currentItem['Last']);
	}
	$html[] = '</p><!--/description-->';
	
	$html[] = '</div><!--/div4zu-loesendes-wort-->';
	#### ENDE Zu lösendes Wort (mit Unterstrichen) ausgeben.
	
	#### START Buchstabenfelder.
	$html[] = '<div class="div4buchstaben">';
	$html[] = '<h5>' . JText::_('HANGOMAT_SELECT_A_CHARACTER') . '</h5>';
	
	// Alle grundlegend möglichen Buchstaben durchlaufen.
	$todayVoted = array();
	foreach ($hangoHelper->characters_array as $letter => $votedCount)
	{
		$html[] = '<p class="p4letter">';
	
		if ($votedCount)
		{
			$todayVoted[$letter] = $votedCount;
		}
		if (array_key_exists($letter, $hangoHelper->Buchstaben_array))
		{
			$html[] = '<input type="submit" value="' . $letter . '" name="Buchstabe">';
		}
		else
		{
			$html[] = '<span title="' . JText::sprintf('HANGOMAT_CHARACTER_BLOCKED', $letter) . '">' . $letter . '</span>';
		}
	
		$html[] = '</p><!--/p4letter-->';
	}
	
	$html[] = '</div><!--/div4buchstaben-->';
	#### ENDE Buchstabenfelder.
	
	#### START Heute getippte Buchstaben ausgeben.
	$html[] = '<div class="div4stimmen-heute">';
	$html[] = '<h6>' . JText::_('HANGOMAT_ALREADY_VOTED_TODAY') . '</h6>';
	if (!$todayVoted)
	{
		$html[] = '<div>' . JText::_('HANGOMAT_NOBODY_VOTED_TODAY') . '</div>';
	}
	foreach ($todayVoted as $letter => $votedCount)
	{
		$html[] = '<p>';
		$html[] = '<span class="span4letter">' . $letter . '</span>';
		$html[] = '<span class="span4votes">' . $votedCount . 'x</span>';
		$html[] = '</p>';
	}
	$html[] = '</div><!--/div4stimmen-heute-->';
	#### ENDE Heute getippte Buchstaben ausgeben.
	
	#### START Lösungswort direkt eingeben.
	$html[] = '<div class="div4loesungswort-eingabe">';
	$html[] = '<h5>' . JText::_('HANGOMAT_SOLVE_WORD') . '</h5>';
	$html[] = '<p>';
	$html[] = '<input type="text" name="Loesungswort"><br />';
	$html[] = '<input type="submit" value="' . JText::_('HANGOMAT_SUBMIT_SOLVE') . '">';
	$html[] = '</p>';
	$html[] = '</div><!--/div4loesungswort-eingabe-->';
	#### ENDE Lösungswort direkt eingeben.
} // end if $userFormShow

#### START Die letzten X Löser anzeigen.
if ($hangoHelper->Liste)
{
	$html[] = '<div class="div4letzte-loeser">';
	$html[] = '<h5>' . JText::sprintf('HANGOMAT_LAST_WINNERS', count($hangoHelper->Liste)) . '</h5>';
	$html[] = '<ul class="list-striped">';
	foreach ($hangoHelper->Liste as $winner)
	{
		$html[] = '<li>';
		if ($winner['tagwechsel'])
		{
			$html[] = JText::sprintf('HANGOMAT_LAST_WINNERS_WINNER_TAGWECHSEL',
				$winner['Wort'],
				$winner['Anzahl'],
				date('d.m.Y', $winner['Zeit'])
			);
		}
		else
		{
			$html[] = JText::sprintf('HANGOMAT_LAST_WINNERS_WINNER',
				$winner['Name'],
				$winner['Wort'],
				$winner['Anzahl'],
				date('d.m.Y', $winner['Zeit'])
			);
		}
		$html[] = '</li>';
	}
	$html[] = '</ul>';
	$html[] = '</div><!--/div4letzte-loeser-->';
}
#### ENDE Die letzten X Löser anzeigen.

$html[] = !$loggedIn ? '<p><input type="button" value="Adminlogin" onclick="jehmlogin(1)"></p>' : '';

$html[] = '</form><!--/form hmform-->';
$html[] = '<script>document.forms.hmform.onkeypress = stopRKey;</script>';
############## NORMALES BESUCHERFORMULAR ENDE

$html[] = '</div><!--/div4whole-hangomat-->';

## ENDE ANZEIGE FÜR BESUCHER

echo implode("\n", $html);
?>
<?php
class hangoHelper
{
	private $hangomat = 'hangomat';
	private $hangomat_ip = 'hangomat_ip';
	private $hangomat_liste = 'hangomat_liste';
	public $stopExecution = false;
	private $hmconfig;
	private $db;
	private $tables = array();
	public $formAction;
	private $currentUser = array();
	private $heuteTag;
	private $moduleId;
	private $needsNoUpdate = array();
	// items of table hangomat. w\o currentItem.
	public $items = null;
	public $currentItem;
	// Winner list (hangomat_liste).
	public $Liste;
	public $input;
	// Array of configured characters. Values: Votes of current date.
	public $moeglicheBuchstaben_array = array();
	private $Wort_array = array();
	public $SWort_array = array();
	public $Buchstaben_array = array();
	public $characters_array = array();
	private $voted_array = array();
	// Lösungsversuche.
	// true: solved word. false: tried to solve but wrong. -1: user blocked.
	public $winnerFormShow;
	public $winnerFormInsertid;
	public $Loesungswort;
	public $Buchstabe;
	public $buchstabeState;
	// Lösungsversuche ENDE.
	private $spielmodus;

	public function __construct($moduleId)
	{
		JFactory::getLanguage()->load('hangomat', __DIR__);

		if (file_exists(__DIR__ . '/hangomat-configuration.php'))
		{
			require_once(__DIR__ . '/hangomat-configuration.php');

			if (empty($hmconfig) || !is_array($hmconfig))
			{
				$hmconfig = array();
			}
			$this->hmconfig = new Joomla\Registry\Registry($hmconfig);
		}
		else
		{
			JFactory::getApplication()->enqueueMessage(JText::_('HANGOMAT_CONFIGURATION_FILE_NOT_FOUND'), 'error');
			$this->stopExecution = true;
			return;
		}
		
		// Create db connection. $this->db and $this->tables.
		if (!$this->createDb())
		{
			$this->stopExecution = true;
			return;			
		}
		
		// Only serverType mysql (MySQL, MySQLi, MySQL (PDO)) is currently supported (because of update scripts).
		if (!$this->is_supported())
		{
			$this->stopExecution = true;
			return;	
		}
		
		$this->moduleId = $moduleId;
		
		$Prefix = $this->db->getPrefix();
		$this->hangomat = $Prefix . $this->hangomat;
		$this->hangomat_ip = $Prefix . $this->hangomat_ip;
		$this->hangomat_liste = $Prefix . $this->hangomat_liste;

		// Check for hangomat db tables. Add them if missing.
		if (!$this->allTablesExist() && !$this->createHangomatTables())
		{
			$this->stopExecution = true;
			return;	
		}

		### At this point we are sure that all db tables exist. ###
		
		// Table structures differ for versions <= 2017.07.10.
		// Update tables (new columns, collation and more).
		if (!$this->updateTables())
		{
			$this->stopExecution = true;
			return;
		}

		### At this point we are sure that all db tables have updated structure. ###
		
		// If all tables are empty one can insert test datas.
		if ($this->hmconfig->get('insertTestData', false) && $this->insertTestData() === false)
		{
			$this->stopExecution = true;
			return;
		}

		$this->spielmodus = 'evaluateLastVotes_' . strtolower(trim($this->hmconfig->get('spielmodus', 'normal')));
		if (!method_exists($this, $this->spielmodus))
		{
			JFactory::getApplication()->enqueueMessage(
				JText::sprintf('HANGOMAT_SPIELMODUS_NOT_EXISTS',
				$this->spielmodus
			), 'warning');
			$this->spielmodus = 'evaluateLastVotes_normal';
		}

		$this->heuteTag = date('Ymd');
		
		// Load/Create hangomat_ip *array* of current User. ($this->currentUser).
		// No db actions here.
		$this->getCurrentUser();
		
		// Initial load of $this->currentItem and other items ($this->items).
		$this->getCurrentItem();
		
		// Configured characters.
		$moeglicheBuchstaben = trim($this->hmconfig->get('moeglicheBuchstaben', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'));
		$this->moeglicheBuchstaben_array = $this->str_split_unicode($moeglicheBuchstaben);
		
		$this->input = JFactory::getApplication()->input->post;

		if ($this->currentItem)
		{
			if (!$this->updateCurrentWort())
			{
				$this->stopExecution = true;
				return;
			}

			// Tagwechsel? Dann setze abgestimmte Buchstaben, die korrekt etc.
			$simulateTagwechsel = 
				$this->input->get('simulateTagwechsel', '', 'STRING') == 'TagWechsel ausführen'
				&& $this->getLoggedin();

			if ($this->currentItem['Tag'] != $this->heuteTag || $simulateTagwechsel)
			{
				// Werte aus und leere hangomat_ip.
				if (!$this->evaluateLastVotes() || !$this->deleteHangomatIp())
				{
					$this->stopExecution = true;
					return;
				}
			}

			### At this point we are sure that currentItem is updated and clean for following procedures. ###

			// User tried to solve the word.

			#if ($this->currentItem['state'] != 1)
			{
				if (
					$this->Loesungswort
						= trim(mb_strtoupper($this->input->get('Loesungswort', '', 'STRING')))
				){
					// Block it if evaluateLastVotes has happend at same time and word already solved.
					if ($this->currentItem['state'] == 1)
					{
						$this->winnerFormShow = -2;
					}
					// Blocked user.
					elseif ($this->currentUser['Try'])
					{
						$this->winnerFormShow = -1;
					}
					elseif ($this->Loesungswort == $this->currentItem['Wort'])
					{
						// Correct and not blocked.
						// Marker legt anonymen Eintrag in hangomat_liste an.
						// User erhält beim nächsten Laden grüne Erfolgsmeldung und kann Daten eingeben.
						$this->currentItem['state'] = 2;
					}
					else
					{
						// Explizit false für "Loesungswort, aber falsch".
						$this->winnerFormShow = false;
						
						// Zwar komisch, aber war so im Original: Wenn falsches Lösungswort, lösche alle Zeilen der IP => Setze gleich wieder neuen Eintrag mit Try = 1. Das bedeutet, Buchstabe kann erneut versucht werden.
						$this->currentUser['Vote'] = 0;
						$this->currentUser['Try'] = 1;
						$this->currentUser['Buch'] = '';
						$this->updateCurrentUser();
					}
				}
				// User tried to solve a Buchstabe.
				elseif ($this->Buchstabe = trim(mb_strtoupper($this->input->get('Buchstabe', '', 'STRING'))))
				{
					// Block it if evaluateLastVotes has happend at same time and word already solved.
					if ($this->currentItem['state'] == 1)
					{
						$this->buchstabeState = -2;
					}
					// Block user.
					elseif ($this->currentUser['Vote'])
					{
						// Hinterlege den heute schon vom User getippten Buchstaben.
						$this->buchstabeState = $this->currentUser['Buch'];
					}
					// Not allowed Buchstabe.
					// Normalerweise, sollte das niemals möglich sein, weil ja nur "erlaubte" Zeichen
					// klickbar sind. Kann rausfliegen??
					elseif (!array_key_exists($this->Buchstabe, $this->Buchstaben_array))
					{
						$this->buchstabeState = false;
					}
					// Allowed Buchstabe.
					else
					{
						$this->buchstabeState = true;
	
						$this->currentUser['Vote'] = 1;
						$this->currentUser['Buch'] = $this->Buchstabe;
						$this->updateCurrentUser();
						
						if (!$this->updateHangomat())
						{
							$this->stopExecution = true;
							return;
						}
					}
				}
			}

			// Gelöst durch Buchstaben des letzten Tages (state=1) oder Eingabe des Lösungswortes (state=2).
			// Lege schon mal anonymen Eintrag in hangomat_liste an.
			if ($this->currentItem['state'] > 0)
			{
				$this->insertListe();
				if (!$this->setNewCurrentItem())
				{
					$this->stopExecution = true;
					return;
				}
				if ($this->currentItem)
				{
					if (!$this->updateCurrentWort())
					{
						$this->stopExecution = true;
						return;
					}
				}
			}
			
			// Wenn es ein Loesungswort von User war, hatte er Möglichkeit Namen einzugeben.
			if ($this->input->get('winnerFormInsertid', 0, 'INTEGER'))
			{
				$this->updateListe();
			}
		}

		$this->formAction = htmlspecialchars(JUri::getInstance()->toString());
		
		// Get winner list $this->Liste (hangomat_list).
		$this->getListe();
		
		// Load CSS and JS in page HEAD.
		$this->addCSS();
		$this->addJS();
	}
	
	private function createDb()
	{
		if ($this->hmconfig->get('useExternalDB') === true)
		{
			$options = array(
				'driver' => trim($this->hmconfig->get('driver', 'mysqli')),
				'host' => trim($this->hmconfig->get('host', 'localhost')),
				'user' => trim($this->hmconfig->get('user', '')), 
				'password' => trim($this->hmconfig->get('password', '')),
				'database' => trim($this->hmconfig->get('database', '')),
				'prefix' => trim($this->hmconfig->get('dbprefix'))
			);
			$this->db = JDatabaseDriver::getInstance($options);
			try
			{
				$this->tables = $this->db->getTableList();
			}
			catch (Exception $e)
			{
				JFactory::getApplication()->enqueueMessage(JText::sprintf('HANGOMAT_EXTERNAL_DB_NOT_CONNECTABLE', $e->getCode(), $e->getMessage()), 'error');
				return false;
			}
		}
		else
		{
			$this->db = JFactory::getDbo();
			try
			{
				$this->tables = $this->db->getTableList();
			}
			catch (Exception $e)
			{
				JFactory::getApplication()->enqueueMessage(JText::sprintf('HANGOMAT_DB_NOT_CONNECTABLE', $e->getCode(), $e->getMessage()), 'error');
				return false;
			}
		}
		return true;
	}
	
	/**
	 Check for needed db tables.
	*/
	private function allTablesExist()
	{
		if (
		 !in_array($this->hangomat, $this->tables)
			|| !in_array($this->hangomat_ip, $this->tables)
			|| !in_array($this->hangomat_liste, $this->tables)
		){
			return false;
		}
		return true;
	}

	/**
	
	*/
	private function createHangomatTables()
	{
		$sql = array();
		
		$tabelle = $this->hangomat;
		if (!in_array($tabelle, $this->tables))
		{
			$sql[$tabelle] = "CREATE TABLE " . $this->db->qn($tabelle) . " (
				`Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`Buchstaben` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
				`Wort` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
				`SWort` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
				`Last` char(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
				`Tag` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
				`Anzahl` int(10) NOT NULL DEFAULT '0',
				`LWort` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
				`characters` varchar(5120) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '{}',
				`state` TINYINT( 3 ) NOT NULL DEFAULT '0',
				`voted` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
				PRIMARY KEY (`Id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
		}
	
		$tabelle = $this->hangomat_ip; 
		if (!in_array($tabelle, $this->tables))
		{
			$sql[$tabelle] = "CREATE TABLE " . $this->db->qn($tabelle) . " (
			`Vote` int(10) NOT NULL DEFAULT '0',
			`Try` int(10) NOT NULL DEFAULT '0',
			`Ip` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
			`Buch` char(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		}
	
		$tabelle = $this->hangomat_liste;
		if (!in_array($tabelle, $this->tables))
		{
			$sql[$tabelle] = "CREATE TABLE " . $this->db->qn($tabelle) . "
			(
			`Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`Zeit` int(15) NOT NULL default '0',
			`Name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
			`HP` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
			`Mail` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
			`Wort` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
			`Anzahl` int(10) NOT NULL default '0' COMMENT 'Laufzeit Tage',
			`tagwechsel` int(10) NOT NULL default '0' COMMENT 'System Buchstabenauswertung zu Tagwechsel',
			PRIMARY KEY  (`Id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		}

		foreach ($sql as $key => $query)
		{
			$this->db->setQuery($query);
			try
			{
				$this->db->execute();
			}
			catch (Exception $e)
			{
				JFactory::getApplication()->enqueueMessage(JText::sprintf('HANGOMAT_DB_CREATE_TABLE_ERROR', $key), 'error');
				$this->enqueueError(__LINE__, $e);
				return false;
			}
			$this->tables[] = $key;
			
			// Newly created db tables don't need an update.
			$this->needsNoUpdate[] = $key;
		}
		return true;
	}

	/**
		If configured try to insert test datas.
		Only possible if alle tables are empty. So, check for existence first.
	*/
	private function insertTestData()
	{
		$sql = array();
		$exists = array();

		$tabelle = $this->hangomat_liste;
		$query = $this->db->getQuery(true)
		->select($this->db->qn('Id'))->from($this->db->qn($tabelle));
		$this->db->setQuery($query, 0, 1);
		if ($this->db->loadAssoc())
		{
			$exists[] = $tabelle;
		}
		else
		{
			$sql[$tabelle] = "INSERT INTO " . $this->db->qn($tabelle) . " (
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
		}

		if (empty($exists))
		{
			$tabelle = $this->hangomat_ip;
			$query = $this->db->getQuery(true)
			->select($this->db->qn('Ip'))->from($this->db->qn($tabelle));
			$this->db->setQuery($query, 0, 1);			
			if ($this->db->loadAssoc())
			{
				$exists[] = $tabelle;
			}
			else
			{
				$sql[$tabelle] = "INSERT INTO " . $this->db->qn($tabelle) . " (`Vote`, `Try`, `Ip`, `Buch`) VALUES
(1, 0, '91.115.139.220', 'G');";
			}
		}

		if (empty($exists))
		{
			$tabelle = $this->hangomat;
			$query = $this->db->getQuery(true)
			->select($this->db->qn('Id'))->from($this->db->qn($tabelle));
			$this->db->setQuery($query, 0, 1);
			if ($this->db->loadAssoc())
			{
				$exists[] = $tabelle;
			}
			else
			{
				$characters_ = array(
					'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0,
					'G' => 0, 'H' => 0, 'I' => 0, 'J' => 0, 'K' => 0, 'L' => 0,
					'M' => 0, 'N' => 0, 'O' => 0, 'P' => 0, 'Q' => 0, 'R' => 0,
					'S' => 0, 'T' => 0, 'U' => 0, 'V' => 0, 'W' => 0, 'X' => 0,
					'Y' => 0, 'Z' => 0
				);		
				$characters = $this->db->q(json_encode($characters_));
				$sql[$tabelle] = "INSERT INTO " . $this->db->qn($tabelle) . " (
				`Id`, `Buchstaben`, `Wort`, `SWort`, `Last`, `Tag`, `Anzahl`, `LWort`, `characters`) VALUES
				(467, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'STELLWERK', '_________', '', '', 0, '', " . $characters . "),
				(468, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'LUFTRAUM', '________', '', '', 0, '', " . $characters . "),
				(469, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'SCHNAPSIDEE', '___________', '', '', 0, '', " . $characters . "),
				(470, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'KAMPFJET', '________', '', '', 0, '', " . $characters . ");";
	
				$characters_['G'] = 1;
				$characters = $this->db->q(json_encode($characters_));
				$sql[$tabelle . '_2'] = "INSERT INTO " . $this->db->qn($tabelle) . " (
				`Id`, `Buchstaben`, `Wort`, `SWort`, `Last`, `Tag`, `Anzahl`, `LWort`, `characters`) VALUES
				(466, 'ABCDFGHJKLMOPQSUVWXYZ', 'BEFLAGGUNG', '_E______N_', 'N', 'Saturday', 5, 'BABYLEICHT', " . $characters . ");";
			}
		}
		
		if (!empty($exists))
		{
			JFactory::getApplication()->enqueueMessage(JText::sprintf('HANGOMAT_DB_NOT_EMPTY_INSERTTESTDATA', implode(', ', $exists)), 'warning');
			// Keine Variable, kein false, da kein Abbruch erfolgen soll, sondern lediglich Warnung.
			return;
		}
		
		foreach ($sql as $key => $query)
		{
			$this->db->setQuery($query);
			try
			{
				$this->db->execute();
			}
			catch (Exception $e)
			{
				JFactory::getApplication()->enqueueMessage(JText::sprintf('HANGOMAT_DB_INSERTTESTDATA_ERROR', $key), 'error');
				$this->enqueueError(__LINE__, $e);
				return false;
			}
		}
		return true;
	}
	
	/**
	 Updates <= release V.2017.07.10.
	*/
	private function updateTables()
	{
		// Needs no update because newly created tables.
		if (count($this->needsNoUpdate) == 3)
		{
			return true;
		}

		$sql = array();

		$oldTableStructure = false;

		$tabelle = $this->hangomat;
		$tableColumns_hangomat = $this->db->getTableColumns($tabelle, $typeOnly = false);

		if (array_key_exists('A', $tableColumns_hangomat))
		{
			$oldTableStructure = true;
		}

		if (!in_array($tabelle, $this->needsNoUpdate))
		{
			$varChars255 = array('Buchstaben', 'Wort', 'SWort', 'Tag', 'LWort');
			$tableColumns = $tableColumns_hangomat;

			foreach ($varChars255 as $column)
			{
				if (
					strtolower($tableColumns[$column]->Type) != 'varchar(255)'
					|| strtolower($tableColumns[$column]->Collation) != 'utf8_general_ci'
				){
					$sql[$tabelle . '.' . $column] = 'ALTER TABLE ' . $this->db->qn($tabelle) . ' MODIFY ' . $this->db->qn($column) . ' VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ""';
				}
			} // $varChars255 end
			
			// char(1)
			if (
				strtolower($tableColumns['Last']->Type) != 'char(1)'
				|| strtolower($tableColumns['Last']->Collation) != 'utf8_general_ci'
			){
				$sql[$tabelle . '.Last'] = 'ALTER TABLE ' . $this->db->qn($tabelle) . ' MODIFY ' . $this->db->qn('Last') . ' CHAR( 1 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ""';
			} // char(1) end
			
			if (!isset($tableColumns['characters']))
			{
				$sql[$tabelle . '.characters'] = 'ALTER TABLE ' . $this->db->qn($tabelle) . ' ADD ' . $this->db->qn('characters') . ' VARCHAR( 5120 ) NOT NULL DEFAULT "{}"';
			}
			
			if (!isset($tableColumns['state']))
			{
				$sql[$tabelle . '.state'] = 'ALTER TABLE ' . $this->db->qn($tabelle) . ' ADD ' . $this->db->qn('state') . ' TINYINT( 3 ) NOT NULL DEFAULT "0"';
			}
			
			if (!isset($tableColumns['voted']))
			{
				$sql[$tabelle . '.voted'] = 'ALTER TABLE ' . $this->db->qn($tabelle) . ' ADD ' . $this->db->qn('voted') . '  VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ""';
			}
		} // if hangomat needsNoUpdate end
		
		$tabelle = $this->hangomat_liste;
		if (!in_array($tabelle, $this->needsNoUpdate))
		{
			$varChars255 = array('Name', 'Wort');
			$tableColumns = $this->db->getTableColumns($tabelle, $typeOnly = false);
			foreach ($varChars255 as $column)
			{
				if (
					strtolower($tableColumns[$column]->Type) != 'varchar(255)'
					|| strtolower($tableColumns[$column]->Collation) != 'utf8_general_ci'
				){
					$sql[$tabelle . '.' . $column] = 'ALTER TABLE ' . $this->db->qn($tabelle) . ' MODIFY ' . $this->db->qn($column) . ' VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ""';
				}
			} // $varChars255 end
			
			
			if (!isset($tableColumns['tagwechsel']))
			{
				$sql[$tabelle . '.tagwechsel'] = 'ALTER TABLE ' . $this->db->qn($tabelle) . ' ADD ' . $this->db->qn('tagwechsel') . ' INT( 10 ) NOT NULL DEFAULT "0" COMMENT "System Buchstabenauswertung zu Tagwechsel"';
			}
		} // if hangomat_liste needsNoUpdate end
		
		$tabelle = $this->hangomat_ip;
		if (!in_array($tabelle, $this->needsNoUpdate))
		{
			$tableColumns = $this->db->getTableColumns($tabelle, $typeOnly = false);
			// char(1)
			if (
				strtolower($tableColumns['Buch']->Type) != 'char(1)'
				|| strtolower($tableColumns['Buch']->Collation) != 'utf8_general_ci'
			){
				$sql[$tabelle . '.Buch'] = 'ALTER TABLE ' . $this->db->qn($tabelle) . ' MODIFY ' . $this->db->qn('Buch') . ' CHAR( 1 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ""';
			} // char(1) end
			// varchar(255)
			if (
				strtolower($tableColumns['Ip']->Type) != 'varchar(255)'
				|| strtolower($tableColumns['Ip']->Collation) != 'utf8_general_ci'
			){
				$sql[$tabelle . '.Ip'] = 'ALTER TABLE ' . $this->db->qn($tabelle) . ' MODIFY ' . $this->db->qn('Ip') . ' VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ""';
			} // varchar(255) end
		} // if hangomat_ip needsNoUpdate end
		
		foreach ($sql as $key => $query)
		{
			$this->db->setQuery($query);
			try
			{
				$this->db->execute();
			}
			catch (Exception $e)
			{
				JFactory::getApplication()->enqueueMessage(JText::sprintf('HANGOMAT_DB_UPDATE_ERROR', $key), 'error');
				$this->enqueueError(__LINE__, $e);
				return false;
			}
		}
		
		if ($oldTableStructure)
		{
			// Update characzters column of current word.
			$characters = array();
			$query = $this->db->getQuery(true)
			->select('*')->from($this->db->qn($this->hangomat))
			->order($this->db->qn('Id') . ' ASC');
			$this->db->setQuery($query, 0, 1);
			$current = $this->db->loadAssoc();
			if ($current)
			{
				foreach($current as $key => $value)
				{
					if (mb_strlen($key) == 1)
					{
						// e.g. $characters['A'] = 1
						$characters[$key] = (int) $value;
					}
				}
				$query = $this->db->getQuery(true);
				$query->update($this->db->qn($this->hangomat))
				->set($this->db->qn('characters') . ' = ' . $this->db->q(json_encode($characters)))
				->where($this->db->qn('Id') . ' = ' . (integer) $current['Id']);
				$this->db->setQuery($query);
				try
				{
					$this->db->execute();
				}
				catch (Exception $e)
				{
					$this->enqueueError(__LINE__, $e);
					return false;
				}
			} // if $current end
			
			// Remove old columns.
			$drop = array(
				' DROP A', ' DROP B', ' DROP C', ' DROP D', ' DROP E', ' DROP F',
				' DROP G', ' DROP H',
				' DROP I', ' DROP J', ' DROP K', ' DROP L', ' DROP M', ' DROP N',
				' DROP O', ' DROP P',
				' DROP Q', ' DROP R', ' DROP S', ' DROP T', ' DROP U', ' DROP V',
				' DROP W', ' DROP X',
				' DROP Y', ' DROP Z'
			);			
			$sql = 'ALTER TABLE ' . $this->db->qn($this->hangomat) . implode(', ', $drop);
			$this->db->setQuery($sql);
			try
			{
				$this->db->execute();
			}
			catch (Exception $e)
			{
				$this->enqueueError(__LINE__, $e);
				return false;
			}
		} // if $oldTableStructure end
		$this->needsNoUpdate[] = array($this->hangomat, $this->hangomat_ip, $this->hangomat_liste);
		return true;
	}
	
	/**
	 
	*/
	private function getItems()
	{
		$select = $this->db->qn(array(
			'Id', 'Buchstaben', 'Wort', 'SWort', 'Last',
			'Tag', 'Anzahl', 'LWort', 'characters', 'state', 'voted'
		));

		$query = $this->db->getQuery(true)
		->select($select)->from($this->db->qn($this->hangomat))
		->order($this->db->qn('Id') . ' ASC');
		$this->db->setQuery($query);
		$this->items = $this->db->loadAssocList('Id');
	}

	/**
	 public!
	*/
	public function getCurrentItem($refreshItems = false)
	{
		if ($refreshItems || is_null($this->items))
		{
			$this->getItems();
		}
		$this->currentItem = $this->shiftFirstArrayItem($this->items);
		$this->refreshUnicodeArrays();
	}
	
	/**
	 Returns Array.
	*/
	private function getCurrentUser()
	{
		$userIp = '';
		if (!empty($_SERVER['REMOTE_ADDR']))
		{
			$userIp = $_SERVER['REMOTE_ADDR'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$userIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		elseif (!empty($_SERVER['HTTP_CLIENT_IP']))
		{
			$userIp = $_SERVER['HTTP_CLIENT_IP'];
		}
		
		if (!empty($_SERVER['HTTP_USER_AGENT']))
		{
			$userIp .= '_' . md5($_SERVER['HTTP_USER_AGENT']);
		}
		elseif (!empty($_SERVER['REMOTE_HOST']))
		{
			$userIp .= '_' . md5($_SERVER['REMOTE_HOST']);
		}
		
		$userIp .= '_' . $this->heuteTag;
		
		$select = $this->db->qn(array('Vote', 'Try', 'Ip', 'Buch'));
		$query = $this->db->getQuery(true)
		->select($select)->from($this->db->qn($this->hangomat_ip))
		->where($this->db->qn('Ip') . ' = ' . $this->db->q($userIp));
		$this->db->setQuery($query);
		$this->currentUser = $this->db->loadAssoc();

		if (!$this->currentUser)
		{
			$this->currentUser['Vote'] = 0;
			$this->currentUser['Try'] = 0;
			$this->currentUser['Ip'] = $userIp;
			$this->currentUser['Buch'] = '';
			$this->currentUser['existsInDB'] = false;
			// Nein! Erzeugt zu viel DB-Last.
			#$tmp = (object) $this->currentUser;
			#$this->db->insertObject($this->hangomat_ip, $tmp);
		}
		else
		{
			$this->currentUser['existsInDB'] = true;
		}
	}

	private function getListe()
	{
		if ( ($loeanz = (int) $this->hmconfig->get('loeanz', 10)) && $loeanz > 0)
		{
			$select = $this->db->qn(array(
				#'Id',
				'Zeit', 
				'Name', 
				#'HP', 
				#'Mail', 
				'Wort', 
				'Anzahl',
				'tagwechsel'
			));
	
			$query = $this->db->getQuery(true)
			->select($select)->from($this->db->qn($this->hangomat_liste))
			->order($this->db->qn('Zeit') . ' DESC');
			$this->db->setQuery($query, 0, $loeanz);
			$this->Liste = $this->db->loadAssocList();
		}
	}
	
	/**
	array_shift but keeps keys of Array.
	*/
	private function shiftFirstArrayItem(&$array, $array_shift = false)
	{
		$firstItem = reset($array);
		if ($array_shift)
		{
			unset($array[key($array)]);
		}
		return $firstItem;
	}
	
	/**
		Checks for missing letters, e.g. because there are new ones in configuration.
		After a new currentItem has been created.
	*/
	private function updateCurrentWort()
	{
		$update = false;
		$characters = $this->characters_array;
		$voted = $this->voted_array;

		// Check for new Buchstaben in configuration.
		foreach ($this->moeglicheBuchstaben_array as $key => $value)
		{
			if (!array_key_exists($value, $characters))
			{
				$characters[$value] = 0;
				$update = true;
			}
		}
		
		// Check also for missing letters that are in current Wort.
		foreach ($this->Wort_array  as $key => $value)
		{
			if (!trim($value)) continue;
			if (!array_key_exists($value, $characters))
			{
				$characters[$value] = 0;
				$update = true;
			}
		}
		
		### At this point $characters contains all possible letters. ###
		
		// Check also for missing already voted letters in column voted.
		foreach ($this->SWort_array  as $key => $value)
		{
			if (!trim($value) || $value == '_') continue;
			if (!array_key_exists($value, $voted))
			{
				// z.B. $voted['K'] = 12; 12 ist nur index für array_flip.
				$voted[$value] = $voted ? (max($voted) + 1) : 0;
				$update = true;
			}
		}

		// Old Tag format (named days, e.g. Saturday)?
		$Tag = $this->currentItem['Tag'];
		if (!is_numeric($Tag))
		{
			if ($Tag != date('l'))
			{
				$Tag = $this->calculateYesterday();
			}
			else
			{
				$Tag = $this->heuteTag;
			}
			$update = true;
		}
		
		// Is new currentItem.
		if ($this->currentItem['state'] == -1)
		{
			$Buchstaben = array();
			foreach ($characters as $key => $value)
			{
				$Buchstaben[] = $key;
			}
			$update = true;
		}

		// Sometimes there is no last word (LWort). e.g. when #__hangomat ran empty.
		if (!$this->currentItem['LWort'])
		{
			$query = $this->db->getQuery(true)
			->select($this->db->qn('Wort'))->from($this->db->qn($this->hangomat_liste))
			->order($this->db->qn('Zeit') . ' DESC');
			$this->db->setQuery($query);
			$this->currentItem['LWort'] = $this->db->loadResult();
			if ($this->currentItem['LWort'])
			{
				$update = true;
			}
		}

		if ($update)
		{
			$query = $this->db->getQuery(true)
			->update($this->db->qn($this->hangomat))
			->set($this->db->qn('characters') . ' = ' . $this->db->q(json_encode($characters)))
			->set($this->db->qn('Tag') . ' = ' . $this->db->q($Tag))
			->set($this->db->qn('LWort') . ' = ' . $this->db->q($this->currentItem['LWort']))
			->set($this->db->qn('voted') . ' = ' . $this->db->q(implode('', array_flip($voted))))
			->where($this->db->qn('Id') . ' = ' . (int) $this->currentItem['Id']);
			
			if ($this->currentItem['state'] == -1)
			{
				$query->set($this->db->qn('state') . ' = 0')
				->set($this->db->qn('Buchstaben') . ' = ' . $this->db->q(implode('', $Buchstaben)));
			}
			
			$this->db->setQuery($query);
			try
			{
				$this->db->execute();
			}
			catch (Exception $e)
			{
				$this->enqueueError(__LINE__, $e);
				return false;
			}
			$this->getCurrentItem($refreshItems = true);
		}
		return true;
	}

	/**
	 Tagwechsel? Dann setze abgestimmte Buchstaben, die korrekt etc.
	*/
	private function evaluateLastVotes()
	{
		$update = false;
		$characters = $this->characters_array;
		$Buchstaben = $this->Buchstaben_array;
		$SWort = $this->SWort_array;

		// Collector for voted letters.
		$voted = $this->voted_array;

		$Last = $this->currentItem['Last'];

		// Kein einziger Tipp?		
		if (!max($characters))
		{
			$randomCharacter = '';
			$i = 10000;
			$j = 0;
			while($j != $i)
			{
				$randomIndex = rand(min($Buchstaben), max($Buchstaben));
				$randomCharacter = array_search($randomIndex, $Buchstaben);
				if (!array_key_exists($randomCharacter, $voted))
				{
					break;
				}
				$randomCharacter = '';
				$j++;
			}
			if (!$randomCharacter)
			{
				// Hilfloser Fallback. Irgendwas.
				$randomCharacter = array_search(rand(min($Buchstaben), max($Buchstaben)), $Buchstaben);

			}
			$characters[$randomCharacter] = 1;
		}

		$spielmodus = $this->spielmodus;
		$this->$spielmodus(
			$update,
			$characters,
			$Buchstaben,
			$SWort,
			$voted,
			$Last
		);

		// Always Update because of always changed Tag and Anzahl.
		$Anzahl = $this->currentItem['Anzahl'] + $this->calculateDays($this->currentItem['Tag']);

		$query = $this->db->getQuery(true)
			->update($this->db->qn($this->hangomat))
			->where($this->db->qn('Id') . ' = ' . (int) $this->currentItem['Id'])
			->set($this->db->qn('Tag') . ' = ' . $this->db->q($this->heuteTag))
			->set($this->db->qn('Anzahl') . ' = ' . $this->db->q($Anzahl));

		if ($update)
		{
			$query->set($this->db->qn('Buchstaben') . ' = ' . $this->db->q(implode('', array_flip($Buchstaben))))
			->set($this->db->qn('SWort') . ' = ' . $this->db->q(implode('', $SWort)))
			->set($this->db->qn('Last') . ' = ' . $this->db->q($Last))
			->set($this->db->qn('characters') . ' = ' . $this->db->q(json_encode($characters)))
			->set($this->db->qn('voted') . ' = ' . $this->db->q(implode('', array_flip($voted))));
		}

		// Wort ist fertig, aber kein Gewinner, da kein Lösungswort eingegeben.
		if (!array_diff_assoc($SWort, $this->Wort_array))
		{
			$query->set($this->db->qn('state') . ' = 1');
		}

		$this->db->setQuery($query);
		try
		{
			$this->db->execute();
		}
		catch (Exception $e)
		{
			$this->enqueueError(__LINE__, $e);
			return false;
		}

		$this->getCurrentItem($refreshItems = true);

		return true;
	}

	
	/**
	Modus 'normal'.
	Wie schon immer.
	1 Buchstabe pro Tag.
	*/
	private function evaluateLastVotes_normal(
		&$update,
		&$characters,
		&$Buchstaben,
		&$SWort,
		&$voted,
		&$Last
	){	
		$update = true;
		$collector = array();
		$hmmax = 0;

		foreach ($characters as $key => $value)
		{
			// Reset.
			$characters[$key] = 0;

			// number of votes ( = $value) for character ( = $key).
			if ($value)
			{
				$collector[$value][] = $key;
				if ($value > $hmmax)
				{
					$hmmax = $value;
				}
			}
		}

		$randomIndex = rand(0, count($collector[$hmmax]) - 1);
		$Last = $collector[$hmmax][$randomIndex];
		unset($Buchstaben[$Last]);

		// Otherwise following array_flip will flop.
		$voted[$Last] = $voted ? (max($voted) + 1) : 0;

		if (in_array($Last, $this->Wort_array))
		{
			// Replace underscores in $SWort.
			foreach ($this->Wort_array as $i => $character)
			{
				if ($character == $Last)
				{
					$SWort[$i] = $character;
				}
			}
		}
	}

	/**
	Modus 'schnell'. Alle getippten Zeichen auswerten und deaktivieren (rot).
	*/
	private function evaluateLastVotes_schnell(
		&$update,
		&$characters,
		&$Buchstaben,
		&$SWort,
		&$voted,
		&$Last
	){
		foreach ($characters as $key => $value)
		{
			// Somebody voted for this character yesterday.
			if ($value)
			{
				$update = true;

				// Reset.
				$characters[$key] = 0;
				
				// Otherwise following array_flip will flop.
				$voted[$key] = $voted ? (max($voted) + 1) : 0;

				// Im Modus 0 ALLE gevoteten Zeichen deaktivieren.
				unset($Buchstaben[$key]);
				$Last = $key;

				if (in_array($key, $this->Wort_array))
				{
					// Replace underscores in $SWort.
					foreach ($this->Wort_array as $i => $character)
					{
						if ($character == $key)
						{
							$SWort[$i] = $character;
						}
					}
				}
			}
		}
	}

	private function setNewCurrentItem()
	{
		// das erledigt deleteHangowort():
		#$this->deleteHangomatIp();

		unset($this->items[$this->currentItem['Id']]);

		if ($new = array_shift($this->items))
		{
			$new = (object) $new;
			$new->LWort = $this->currentItem['Wort'];
			$new->Tag = $this->heuteTag;
			// Marker for new current item for updateCurrentWort().
			$new->state = -1;
			
			$ret = $this->db->updateObject($this->hangomat, $new, 'Id');
			if (!$ret)
			{
				$this->enqueueError(__LINE__, $e);
				return false;
			}
		}
		$this->deleteHangowort($this->currentItem['Id']);
		$this->getCurrentItem(true);
		return true;
	}
	
	private function calculateDays($old)
	{
		return floor((strtotime($this->heuteTag) - strtotime($old)) / 86400);
	}

	private function calculateYesterday()
	{
		return date('Ymd', strtotime($this->heuteTag) - 86400);
	}
	
	/**
	 public!
	*/
	public function calculateWortLength()
	{
		$spaces = mb_substr_count($this->currentItem['Wort'], ' ');

		return array(
			'spaces' => $spaces,
			'letters' => mb_strlen($this->currentItem['Wort']) - $spaces,
			'words' => $spaces + 1
		);
	}

	private function refreshUnicodeArrays()
	{
			if ($this->currentItem)
			{
				$this->Wort_array = $this->str_split_unicode($this->currentItem['Wort']);
				$this->SWort_array = $this->str_split_unicode($this->currentItem['SWort']);
				// array_flip because has unique values, then keys.
				$this->Buchstaben_array = array_flip($this->str_split_unicode($this->currentItem['Buchstaben']));
				$this->characters_array = json_decode($this->currentItem['characters'], $assoc = true);
				// array_flip because has unique values, then keys.
				$this->voted_array = array_flip($this->str_split_unicode($this->currentItem['voted']));
			}
			else
			{
				$this->Wort_array = $this->SWort_array = $this->voted_array = 
				$this->Buchstaben_array = $this->characters_array = array();
			}
	}
	
	/**
		Admin created a new word.
		public!
	*/
	public function insertHangowort($hangowort)
	{
		$SWort = '';
		$Wort = preg_replace('/\s\s+/', ' ', $hangowort);
		$parts = mb_split(' ', $Wort);
		
		foreach ($parts as $key => $part)
		{
			$parts[$key] = str_repeat('_', mb_strlen($part));
		}
		$SWort = implode(' ', $parts);
		
		$characters = array();
		foreach($this->moeglicheBuchstaben_array as $key => $value)
		{
			$characters[$value] = 0;
		}

		$columns = array(
			'Buchstaben',
			'Wort',
			'SWort',
			'characters'
		);
		$values =  array(
			$this->db->q(implode('', $this->moeglicheBuchstaben_array)),
		 	$this->db->q($Wort),
			$this->db->q($SWort),
			$this->db->q(json_encode($characters))
		);
		$query = $this->db->getQuery(true);
		$query->insert($this->db->qn($this->hangomat))
		->columns($this->db->qn($columns))->values(implode(',', $values));
		$this->db->setQuery($query);
		try
		{
			$this->db->execute();
		}
		catch (Exception $e)
		{
			$this->enqueueError(__LINE__, $e);
			return false;
		}
		return true;
	}
	
	/**
	 A word has been solved. Add entry in hangomat_liste.
	*/
	private function insertListe()
	{
		$Mail = JText::_('HANGOMAT_NOT_SPECIFIED');
		
		// Durch User gelöst (Loesungswort).
		// Hinweis: User kann Eintrag nach nächstem Seitenladen überschreiben (s. $this->winnerFormShow).
		if ($this->currentItem['state'] == 2)
		{
			$Name = JText::_('HANGOMAT_ANONYMOUS');
		}
		else
		{
			$Name = JText::_('HANGOMAT_NO_WINNER');
		}

		$columns = array(
			'Zeit',
			'Name',
			#'HP',
			'Mail',
			'Wort',
			'Anzahl',
			// Von System (Buchstabenauswertung) bei Tagwechsel gelöst?
			'tagwechsel'
		);
		$values =  array(
			$this->db->q(time()),
			$this->db->q($Name),
			#$this->db->q(''),
			$this->db->q($Mail),
			$this->db->q($this->currentItem['Wort']),
			$this->db->q($this->currentItem['Anzahl']),
			$this->db->q( ($this->currentItem['state'] == 1 ? 1 : 0) )
		);
		$query = $this->db->getQuery(true);
		$query->insert($this->db->qn($this->hangomat_liste))
		->columns($this->db->qn($columns))->values(implode(',', $values));
		$this->db->setQuery($query);
		try
		{
			$this->db->execute();
		}
		catch (Exception $e)
		{
			$this->enqueueError(__LINE__, $e);
			return false;
		}
		
		// Solved by User. Showwinnerform for further user details.
		if ($this->currentItem['state'] == 2)
		{
			$this->winnerFormInsertid = (int) $this->db->insertid();
			$this->winnerFormShow = true;
		}
		return true;
	}
	
	/**
		Bisschen konfus. Lass ich aber erst mal:
		$Buch, falls falscher EinzelBuchstabe, der Buchstabe (char).
		$Try, falls falsches Lösungswort bzw. heute schon Lösungswort probiert, 1.
		$Vote, in beiden Fällen 1 für falsches Voting.
	*/
	private function updateCurrentUser()
	{
		$existsInDB = $this->currentUser['existsInDB'];
		unset($this->currentUser['existsInDB']);
		$user = (object) $this->currentUser;
		if ($existsInDB == 0)
		{
			$this->db->insertObject($this->hangomat_ip, $user);
		}
		elseif ($existsInDB == 1)
		{
			$this->db->updateObject($this->hangomat_ip, $user, 'Ip');
		}
		
		// Refresh PHP Array.
		$this->getCurrentUser();
	}

	/**
	 New allowed letter voted.
	*/
	private function updateHangomat()
	{
		// Do we  really need this "if"? Comes from original script.
		if (!in_array($this->Buchstabe, $this->SWort_array))
		{
			$characters = $this->characters_array;
			$characters[$this->Buchstabe]++;
			$query = $this->db->getQuery(true);
			$query->update($this->db->qn($this->hangomat))
			->set($this->db->qn('characters') . ' = ' . $this->db->q(json_encode($characters)))
			->set($this->db->qn('state') . ' = 0')
			->where($this->db->qn('Id') . ' = ' . (integer) $this->currentItem['Id']);
			$this->db->setQuery($query);
			try
			{
				$this->db->execute();
			}
			catch (Exception $e)
			{
				$this->enqueueError(__LINE__, $e);
				return false;
			}
			$this->getCurrentItem($refreshItems = true);
		}
		return true;
	}
	
	/**
		Insert winner datas into hangomat_liste.
	*/
	private function updateListe()
	{
		$update = false;

		if ($Name = trim($this->input->get('hm_name', '', 'string')))
		{
			$update = true;
		}

		if ($Mail = trim($this->input->get('hm_mail', '', 'string')))
		{
			$update = true;

			if (!JMailHelper::isEmailAddress($Mail))
			{
				$Mail = '';
				JFactory::getApplication()->enqueueMessage(JText::_('HANGOMAT_INVALID_EMAIL'));
			}
		}
		
		if (! ($winnerFormInsertid = $this->input->get('winnerFormInsertid', 0, 'INTEGER')))
		{
			$update = false;
		}
		
		// Other datas already inserted in insertListe().
		if ($update && ($Name || $Email))
		{
			$query = $this->db->getQuery(true)
			->update($this->db->qn($this->hangomat_liste))
			->where($this->db->qn('id') . ' = ' . $winnerFormInsertid);
			if ($Name)
			{
				$query->set($this->db->qn('Name') . ' = ' . $this->db->q($Name));
			}
			if ($Mail)
			{
				$query->set($this->db->qn('Mail') . ' = ' . $this->db->q($Mail));
			}
			$this->db->setQuery($query);
			try
			{
				$this->db->execute();
			}
			catch (Exception $e)
			{
				$this->enqueueError(__LINE__, $e);
				return false;
			}
		}
		return true;
	}

	/**
		Check for not allowed letters (because not defined in configuration).
		public!
	*/
	public function checkAllowedCharacters($hangowort)
	{
		foreach ($this->str_split_unicode($hangowort) as $hangowort_letter)
		{
			if ($hangowort_letter == ' ')
			{
				continue;
			}

			if (!in_array($hangowort_letter, $this->moeglicheBuchstaben_array))
			{
				return $hangowort_letter;
			}
		}
		return true;
	}
	
	/**
		Admin deleted a word.
		Or word has been solved somehow.
		public!
	*/
	public function deleteHangowort($Hangodel)
	{
		$query = $this->db->getQuery(true);

		if (!empty($this->currentItem) && (int) $this->currentItem['Id'] === (int) $Hangodel)
		{
			$query->delete($this->hangomat_ip);
			$this->db->setQuery($query);
			try
			{
				$this->db->execute();
			}
			catch (Exception $e)
			{
				$this->enqueueError(__LINE__, $e);
				
				// Nein! Nicht unbedingt nötig. Wichtiger ist löschen unten.
				#return false;
			}
		}

		$query->clear()
		->delete($this->hangomat)
		->where($this->db->qn('Id') . '=' . $this->db->q($Hangodel));
		$this->db->setQuery($query);
		try
		{
			$this->db->execute();
		}
		catch (Exception $e)
		{
			$this->enqueueError(__LINE__, $e);
			return false;
		}
		return true;
	}
	
	/**
	Truncate whole table.
	$currentIp: delete only row of current user (only admins).
	public!
	*/
	public function deleteHangomatIp($currentIp = false)
	{
		// Paranoia check.
		$deleteAllIps = $this->input->get('deleteAllIps', '', 'STRING');
		if ($deleteAllIps && ($deleteAllIps != 'Ip-TabulaRasa' || !$this->getLoggedin()))
		{
			return;
		}

		$query = $this->db->getQuery(true);

		// Paranoia double check.
		$currentIp = ($currentIp === true && $this->input->get('deleteip'));
		if ($currentIp && $this->getLoggedin())
		{
			if (!$this->currentUser['Ip'])
			{
				return;
			}
			$query->where($this->db->qn('Ip') . ' = ' . $this->db->q($this->currentUser['Ip']));
		}

		$query->delete($this->db->qn($this->hangomat_ip));

		$this->db->setQuery($query); 
		
		try
		{
			$this->db->execute();
		}
		catch (Exception $e)
		{
			$this->enqueueError(__LINE__, $e);
			return false;
		}
		$this->getCurrentUser();
		return true;
	}

	/**
		Check if admin is logged in and write into session.
		public!
	*/	
	public function getLoggedin()
	{
		$session = JFactory::getSession();
		$sessionKey = 'hangomat_hmcook.' . $this->moduleId;
		$hmcook = trim(JFactory::getApplication()->input->post->get('hmcook', '', 'string'));

		if (!empty($hmcook) && $hmcook === trim($this->hmconfig->get('adminpass', '')))
		{
			$session->set($sessionKey, 1);
		}
		elseif ($hmcook == 'Logout')
		{
			$session->set($sessionKey, null);
		}
		return $session->get($sessionKey, null);
	}
	
	/**
	serverType mysql = MySQL, MySQLi, MySQL (PDO)
	*/
	private function is_supported()
	{
		if ($this->db->getServerType() != 'mysql')
		{
			JFactory::getApplication()->enqueueMessage(JText::sprintf('HANGOMAT_DB_DRIVER_NOT_SUPPORTED', $this->db->getServerType()), 'error');
			return false;
		}
		return true;
	}

	/*
	http://php.net/manual/de/function.str-split.php
	*/
	private function str_split_unicode($str, $l = 0)
	{
		$ret = array();
		$len = mb_strlen($str, 'UTF-8');
		for ($i = 0; $i < $len; $i++)
		{
			$ret[] = mb_substr($str, $i, 1, 'UTF-8');
		}
		return $ret;
	}

	private function addCSS()
	{
		$currentDir = basename(__DIR__);
		$fileExists = JHtml::_('stylesheet', $currentDir . '/hangomat.css', array('relative' => true, 'pathOnly' => 'true'));

		if ($fileExists)
		{
			JHtml::_('stylesheet', $currentDir . '/hangomat.css', array('version' => 'auto', 'relative' => true));
		}
		else
		{
			JFactory::getDocument()->addStyleDeclaration($this->getCSS());
		}
	}
	
	private function addJS()
	{
		$currentDir = basename(__DIR__);
		$fileExists = JHtml::_('script', $currentDir . '/hangomat.js', array('relative' => true, 'pathOnly' => 'true'));

		if ($fileExists)
		{
			JHtml::_('stylesheet', $currentDir . '/hangomat.js', array('version' => 'auto', 'relative' => true));
		}
		else
		{
			JFactory::getDocument()->addScriptDeclaration($this->getJS());
		}
	}
	
	private function getCSS()
	{
		return '

/* START Hangomat CSS */
.div4whole-hangomat{
 width: 100%;
 text-align: center;
}
.div4whole-hangomat h5
{
 margin-top: 24px;
}
.div4whole-hangomat h4
{
 margin-top: 24px;
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
 line-height: 2em;
}
/* END Hangomat CSS */

		';
	}
	
	private function getJS()
	{
		$js = '/* START Hangomat JS */';

		if ($this->getLoggedin())
		{
			$js .= '
				function jehmloesch(id)
				{
					var box = confirm("Wirklich löschen?");
					if (box == true)
					{
						document.forms.hango.elements.Hangodel.value = id;
						document.forms.hango.submit();
					}
				}';
		}

		$js .= '
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
			$js .= '/* END Hangomat JS */';
			return $js;
	}
	
	private function enqueueError($line, $e)
	{
		JFactory::getApplication()->enqueueMessage('Hangomat: Zeile ' . $line . ': ' . JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'error');
	}
}
