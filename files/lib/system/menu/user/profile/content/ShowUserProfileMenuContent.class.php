<?php
namespace show\system\menu\user\profile\content;
use show\data\entry\AccessibleEntryList;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\menu\user\profile\content\IUserProfileMenuContent;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles user profile show content.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class ShowUserProfileMenuContent extends SingletonFactory implements IUserProfileMenuContent {
	/**
	 * list of accessible entries
	 */
	protected $entryLists = [];
	
	/**
	 * @inheritdoc
	 */
	public function getContent($userID) {
		return WCF::getTPL()->fetch('userProfileEntrys', 'show', [
				'entryList' => $this->getEntryList($userID),
				'user' => UserProfileRuntimeCache::getInstance()->getObject($userID)
		]);
	}
	
	/**
	 * @inheritdoc
	 */
	public function isVisible($userID) {
		$user = UserProfileRuntimeCache::getInstance()->getObject($userID);
		if ($user !== null && $user->showEntrys && count($this->getEntryList($userID))) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns the list with all entries created by the user
	 */
	protected function getEntryList($userID) {
		if (!isset($this->entryLists[$userID])) {
			$entryList = new AccessibleEntryList();
			$entryList->getConditionBuilder()->add('entry.userID = ?', [$userID]);
			$entryList->sqlLimit = 8;
			$entryList->readObjects();
			
			$this->entryLists[$userID] = $entryList;
		}
		
		return $this->entryLists[$userID];
	}
}
