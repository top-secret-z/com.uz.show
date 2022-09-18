<?php
namespace show\page;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the list of entrys by the active user.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class MyEntryListPage extends EntryListPage {
	/**
	 * @inheritDoc
	 */
	public $controllerName = 'MyEntryList';
	
	/**
	 * @inheritDoc
	 */
	public $loginRequired = true;
	
	/**
	 * @inheritDoc
	 */
	public $templateName = 'entryList';
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'feedControllerName' => ''
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->canonicalURL = LinkHandler::getInstance()->getLink('MyEntryList', ['application' => 'show'], ($this->pageNo > 1 ? 'pageNo=' . $this->pageNo : ''));
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->getConditionBuilder()->add('entry.userID = ?', [WCF::getUser()->userID]);
	}
}
