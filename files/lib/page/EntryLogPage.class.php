<?php
namespace show\page;
use show\data\entry\ViewableEntry;
use show\data\modification\log\EntryLogModificationLogList;
use show\system\SHOWCore;
use wcf\page\SortablePage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the entry log page.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryLogPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'time';
	public $defaultSortOrder = 'DESC';
	public $validSortFields = ['logID', 'time', 'username'];
	
	/**
	 * entry data
	 */
	public $entryID = 0;
	public $entry;
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = EntryLogModificationLogList::class;
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['mod.show.canEditEntry'];
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'entry' => $this->entry
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// add breadcrumbs
		if (!SHOW_CATEGORY_ENABLE) {
			SHOWCore::getInstance()->setLocation($this->entry->getCategory()->getParentCategories(), $this->entry->getCategory(), $this->entry->getDecoratedObject());
		}
		else {
			SHOWCore::getInstance()->setLocation([], null, $this->entry->getDecoratedObject());
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->entryID = intval($_REQUEST['id']);
		$this->entry = ViewableEntry::getEntry($this->entryID);
		if ($this->entry === null) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->setEntry($this->entry->getDecoratedObject());
	}
}
