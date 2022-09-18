<?php
namespace show\system\box;
use show\data\entry\AccessibleEntryList;
use wcf\system\box\AbstractDatabaseObjectListBoxController;
use wcf\system\WCF;

/**
 * Box for entry list.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryListBoxController extends AbstractDatabaseObjectListBoxController {
	/**
	 * @inheritDoc
	 */
	protected $conditionDefinition = 'com.uz.show.box.entryList.condition';
	
	/**
	 * @inheritDoc
	 */
	public $defaultLimit = 6;
	
	/**
	 * @inheritDoc
	 */
	protected $sortFieldLanguageItemPrefix = 'show.entry';
	
	/**
	 * @inheritDoc
	 */
	protected static $supportedPositions = ['sidebarLeft', 'sidebarRight', 'contentTop', 'contentBottom', 'top', 'bottom', 'footerBoxes'];
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = [
			'time',
			'comments',
			'views',
			'cumulativeLikes',
			'subject',
			'random'
	];
	
	/**
	 * @inheritDoc
	 */
	protected function getObjectList() {
		$objectList = new AccessibleEntryList();
		
		switch ($this->sortField) {
			case 'comments':
				$objectList->getConditionBuilder()->add('entry.comments > ?', [0]);
				break;
			case 'views':
				$objectList->getConditionBuilder()->add('entry.views > ?', [0]);
				break;
		}
		
		if ($this->sortField == 'random') {
			$this->sortField = 'RAND()';
			$this->sortOrder = ' ';
		}
		
		return $objectList;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getTemplate() {
		return WCF::getTPL()->fetch('boxEntryList', 'show', [
				'boxEntryList' => $this->objectList,
				'boxSortField' => $this->sortField,
				'boxPosition' => $this->box->position
		], true);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function readObjects() {
		$sortField = $this->box->sortField;
		
		if ($sortField != 'random') {
			$this->objectList->sqlOrderBy = 'entry.' . $this->objectList->sqlOrderBy;
		}
		
		parent::readObjects();
	}
}
