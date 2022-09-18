<?php
namespace show\system\entry;
use show\data\entry\Entry;
use show\data\entry\EntryList;
use wcf\system\SingletonFactory;

/**
 * Caches entry objects for entry-related user notifications.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryDataHandler extends SingletonFactory {
	/**
	 * list of cached entrys
	 */
	protected $entryIDs = [];
	protected $entrys = [];
	
	/**
	 * Caches an entry id.
	 */
	public function cacheEntryID($entryID) {
		if (!in_array($entryID, $this->entryIDs)) {
			$this->entryIDs[] = $entryID;
		}
	}
	
	/**
	 * Returns the entry with the given id.
	 */
	public function getEntry($entryID) {
		if (!empty($this->entryIDs)) {
			$this->entryIDs = array_diff($this->entryIDs, array_keys($this->entrys));
			
			if (!empty($this->entryIDs)) {
				$entryList = new EntryList();
				$entryList->setObjectIDs($this->entryIDs);
				$entryList->readObjects();
				$this->entrys += $entryList->getObjects();
				$this->entryIDs = [];
			}
		}
		
		if (isset($this->entrys[$entryID])) {
			return $this->entrys[$entryID];
		}
		
		return null;
	}
}
