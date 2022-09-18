<?php
namespace show\data\category;
use show\system\cache\builder\ShowCategoryLabelCacheBuilder;
use wcf\data\category\AbstractDecoratedCategory;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\data\IAccessibleObject;
use wcf\data\ITitledLinkObject;
use wcf\system\category\CategoryHandler;
use wcf\system\category\CategoryPermissionHandler;
use wcf\system\label\LabelHandler;
use wcf\system\request\LinkHandler;
use wcf\system\user\object\watch\UserObjectWatchHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Represents a show category.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class ShowCategory extends AbstractDecoratedCategory implements IAccessibleObject, ITitledLinkObject {
	/**
	 * acl permissions of this category grouped by the userID
	 */
	protected $userPermissions = [];
	
	/**
	 * object type name
	 */
	const OBJECT_TYPE_NAME = 'com.uz.show.category';
	
	/**
	 * ids of subscribed show categories
	 */
	protected static $subscribedCategories = null;
	
	/**
	 * Returns accessible category IDs.
	 */
	public static function getAccessibleCategoryIDs($permissions = ['canViewCategory']) {
		$categoryIDs = [];
		foreach (CategoryHandler::getInstance()->getCategories(self::OBJECT_TYPE_NAME) as $category) {
			$result = true;
			$category = new ShowCategory($category);
			foreach ($permissions as $permission) {
				$result = $result && $category->getPermission($permission);
			}
			
			if ($result) $categoryIDs[] = $category->categoryID;
		}
		
		return $categoryIDs;
	}
	
	/**
	 * Returns the label groups for accessible categories.
	 */
	public static function getAccessibleLabelGroups($permission = 'canSetLabel') {
		$labelGroupsToCategories = ShowCategoryLabelCacheBuilder::getInstance()->getData();
		$accessibleCategoryIDs = self::getAccessibleCategoryIDs();
		
		$groupIDs = [];
		foreach ($labelGroupsToCategories as $categoryID => $__groupIDs) {
			if (in_array($categoryID, $accessibleCategoryIDs)) {
				$groupIDs = array_merge($groupIDs, $__groupIDs);
			}
		}
		
		if (empty($groupIDs)) return [];
		return LabelHandler::getInstance()->getLabelGroups(array_unique($groupIDs), true, $permission);
	}
	
	/**
	 * Returns the label groups available in the category.
	 */
	public function getLabelGroups($permission = 'canSetLabel') {
		$labelGroups = [];
		
		$labelGroupsToCategories = ShowCategoryLabelCacheBuilder::getInstance()->getData();
		if (isset($labelGroupsToCategories[$this->categoryID])) {
			$labelGroups = LabelHandler::getInstance()->getLabelGroups($labelGroupsToCategories[$this->categoryID], true, $permission);
		}
		
		return $labelGroups;
	}
	
	/**
	 * Returns subscribed category IDs.
	 */
	public static function getSubscribedCategoryIDs() {
		if (self::$subscribedCategories === null) {
			self::$subscribedCategories = [];
			
			if (WCF::getUser()->userID) {
				$data = UserStorageHandler::getInstance()->getField('showSubscribedCategories');
				
				// cache does not exist or is outdated
				if ($data === null) {
					$objectTypeID = UserObjectWatchHandler::getInstance()->getObjectTypeID('com.uz.show.category');
					
					$sql = "SELECT	objectID
							FROM	wcf".WCF_N."_user_object_watch
							WHERE	objectTypeID = ? AND userID = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute([$objectTypeID, WCF::getUser()->userID]);
					self::$subscribedCategories = $statement->fetchAll(\PDO::FETCH_COLUMN);
					
					// update storage data
					UserStorageHandler::getInstance()->update(WCF::getUser()->userID, 'showSubscribedCategories', serialize(self::$subscribedCategories));
				}
				else {
					self::$subscribedCategories = unserialize($data);
				}
			}
		}
		
		return self::$subscribedCategories;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('CategoryEntryList', [
				'application' => 'show',
				'object' => $this->getDecoratedObject(),
				'forceFrontend' => true
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getPermission($permission, User $user = null) {
		if ($user === null) {
			$user = WCF::getUser();
		}
		
		if (!isset($this->userPermissions[$user->userID])) {
			$this->userPermissions[$user->userID] = CategoryPermissionHandler::getInstance()->getPermissions($this->getDecoratedObject(), $user);
		}
		
		if (isset($this->userPermissions[$user->userID][$permission])) {
			return $this->userPermissions[$user->userID][$permission];
		}
		
		if ($this->getParentCategory()) {
			return $this->getParentCategory()->getPermission($permission, $user);
		}
		
		if ($permission == 'canViewCategory') $permission = 'canViewEntry';
		if ($permission == 'canUseCategory') $permission = 'canAddEntry';
		
		if ($user->userID === WCF::getSession()->getUser()->userID) {
			return WCF::getSession()->getPermission('user.show.'.$permission);
		}
		else {
			$userProfile = new UserProfile($user);
			return $userProfile->getPermission('user.show.'.$permission);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return WCF::getLanguage()->get($this->title);
	}
	
	/**
	 * @inheritDoc
	 */
	public function isAccessible(User $user = null) {
		if ($this->getObjectType()->objectType != self::OBJECT_TYPE_NAME) return false;
		
		// check permissions
		return $this->getPermission('canViewCategory', $user);
	}
	
	/**
	 * Returns true if the active user has subscribed to this category.
	 */
	public function isSubscribed() {
		return in_array($this->categoryID, self::getSubscribedCategoryIDs());
	}
}
