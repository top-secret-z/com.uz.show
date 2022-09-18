<?php
namespace show\page;
use wcf\data\user\User;
use wcf\system\exception\IllegalLinkException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows a list of entrys by a certain user.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class UserEntryListPage extends EntryListPage {
	/**
	 * @inheritDoc
	 */
	public $controllerName = 'UserEntryList';
	
	/**
	 * @inheritDoc
	 */
	public $templateName = 'entryList';
	
	/**
	 * user the listed entrys belong to
	 */
	public $user;
	public $userID = 0;
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'controllerObject' => $this->user,
				'feedControllerName' => '',
				'user' => $this->user,
				'userID' => $this->userID
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		if (isset($_REQUEST['id'])) $this->userID = intval($_REQUEST['id']);
		$this->user = new User($this->userID);
		if (!$this->user->userID) {
			throw new IllegalLinkException();
		}
		$this->controllerParameters['object'] = $this->user;
		parent::readParameters();
		
		$this->canonicalURL = LinkHandler::getInstance()->getLink('UserEntryList', [
				'application' => 'show',
				'object' => $this->user
		], ($this->pageNo > 1 ? 'pageNo=' . $this->pageNo : ''));
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->getConditionBuilder()->add('entry.userID = ?', [$this->userID]);
	}
}
