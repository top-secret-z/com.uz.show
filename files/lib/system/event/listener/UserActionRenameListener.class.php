<?php
namespace show\system\event\listener;
use wcf\system\event\listener\AbstractUserActionRenameListener;

/**
 * Updates the stored username on user rename.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class UserActionRenameListener extends AbstractUserActionRenameListener {
	/**
	 * @inheritDoc
	 */
	protected $databaseTables = ['show{WCF_N}_entry'];
}
