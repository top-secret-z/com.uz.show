<?php
namespace show\page;
use show\data\category\ShowCategory;
use show\data\entry\AccessibleEntryList;
use show\system\cache\builder\StatsCacheBuilder;
use show\system\SHOWCore;
use wcf\data\object\type\ObjectTypeCache;
use wcf\page\SortablePage;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\label\LabelHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Shows a list of entrys.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryListPage extends SortablePage {
	/**
	 * controller
	 */
	public $controllerName = 'EntryList';
	public $controllerParameters = ['application' => 'show'];
	
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = SHOW_ENTRYS_PER_PAGE;
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = AccessibleEntryList::class;
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = SHOW_INDEX_SORTFIELD;
	public $defaultSortOrder = SHOW_INDEX_SORTORDER;
	public $validSortFields = ['username', 'subject', 'time', 'lastChangeTime', 'views', 'cumulativeLikes', 'comments'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['user.show.canViewEntry'];
	
	/**
	 * label filter
	 */
	public $labelIDs = [];
	
	/**
	 * list of available label groups
	 */
	public $labelGroups = [];
	
	/**
	 * simple show statistics
	 */
	public $stats = [];
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'controllerName' => $this->controllerName,
				'controllerObject' => null,
				'feedControllerName' => 'EntryListFeed',
				'hasMarkedItems' => ClipboardHandler::getInstance()->hasMarkedItems(ClipboardHandler::getInstance()->getObjectTypeID('com.uz.show.entry')),
				'labelGroups' => $this->labelGroups,
				'labelIDs' => $this->labelIDs,
				'stats' => $this->stats
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// stats
		if (SHOW_INDEX_ENABLE_STATS && WCF::getSession()->getPermission('user.profile.canViewStatistics')) {
			$this->stats = StatsCacheBuilder::getInstance()->getData();
		}
		
		// add breadcrumbs
		SHOWCore::getInstance()->setLocation();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->labelGroups = ShowCategory::getAccessibleLabelGroups('canViewLabel');
		if (!empty($this->labelGroups) && isset($_REQUEST['labelIDs']) && is_array($_REQUEST['labelIDs'])) {
			$this->labelIDs = $_REQUEST['labelIDs'];
			
			foreach ($this->labelIDs as $groupID => $labelID) {
				$isValid = false;
				
				// ignore zero-values
				if (!is_array($labelID) && $labelID) {
					if (isset($this->labelGroups[$groupID]) && ($labelID == -1 || $this->labelGroups[$groupID]->isValid($labelID))) {
						$isValid = true;
					}
				}
				
				if (!$isValid) {
					unset($this->labelIDs[$groupID]);
				}
			}
		}
		
		if (!empty($_POST)) {
			$labelParameters = '';
			if (!empty($this->labelIDs)) {
				foreach ($this->labelIDs as $groupID => $labelID) {
					$labelParameters .= 'labelIDs['.$groupID.']='.$labelID.'&';
				}
			}
			
			HeaderUtil::redirect(LinkHandler::getInstance()->getLink($this->controllerName, $this->controllerParameters, rtrim($labelParameters, '&')));
			exit;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->applyFilters();
	}
	
	protected function applyFilters() {
		// filter by label
		if (!empty($this->labelIDs)) {
			$objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.label.object', 'com.uz.show.entry')->objectTypeID;
			
			foreach ($this->labelIDs as $groupID => $labelID) {
				if ($labelID == -1) {
					$groupLabelIDs = LabelHandler::getInstance()->getLabelGroup($groupID)->getLabelIDs();
					
					if (!empty($groupLabelIDs)) {
						$this->objectList->getConditionBuilder()->add('entry.entryID NOT IN (SELECT objectID FROM wcf'.WCF_N.'_label_object WHERE objectTypeID = ? AND labelID IN (?))', [$objectTypeID, $groupLabelIDs]);
					}
				}
				else {
					$this->objectList->getConditionBuilder()->add('entry.entryID IN (SELECT objectID FROM wcf'.WCF_N.'_label_object WHERE objectTypeID = ? AND labelID = ?)', [$objectTypeID, $labelID]);
				}
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function readObjects() {
		$this->sqlOrderBy = 'entry.'.$this->sqlOrderBy;
		
		parent::readObjects();
	}
}
