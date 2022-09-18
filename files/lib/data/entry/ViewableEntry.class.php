<?php
namespace show\data\entry;
use show\data\category\ShowCategory;
use show\data\modification\log\ViewableEntryListEntryModificationLog;
use wcf\data\label\Label;
use wcf\data\language\Language;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\language\LanguageFactory;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Represents a viewable entry.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class ViewableEntry extends DatabaseObjectDecorator {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Entry::class;
	
	/**
	 * effective visit time
	 */
	protected $effectiveVisitTime;
	
	/**
	 * list of assigned labels
	 */
	protected $labels = [];
	
	/**
	 * modification log object
	 */
	protected $logEntry;
	
	/**
	 * number of unread entrys
	 */
	protected static $unreadEntrys;
	
	/**
	 * user profile object
	 */
	protected $userProfile;
	
	/**
	 * Adds a label.
	 */
	public function addLabel(Label $label) {
		$this->labels[$label->labelID] = $label;
	}
	
	/**
	 * Returns the number of comments.
	 */
	public function getComments() {
		return $this->comments;
	}
	
	/**
	 * Returns the cumulative likes.
	 */
	public function getCumulativeLikes() {
		return $this->cumulativeLikes;
	}
	
	/**
	 * Returns delete note if applicable.
	 */
	public function getDeleteNote() {
		if ($this->logEntry === null || $this->logEntry->action != 'trash') {
			return '';
		}
		
		return $this->logEntry->__toString();
	}
	
	/**
	 * Returns the icon tag of the given size. If the entry has no icon, the configured FA icon is returned.
	 */
	public function getIconTag($size = Entry::ICON_SIZE) {
		if (SHOW_ENTRY_ICON_ENABLE) {
			if ($this->iconHash) {
				$src = $this->getIconURL();
				return '<img src="'.$src.'" alt="" style="width: '.$size.'px; height: '.$size.'px">';
			}
			else {
				return '<span class="icon icon' . $size . ' ' . SHOW_ENTRY_ICON_DEFAULT . '"></span>';
			}
		}
		
		return $this->getUserProfile()->getAvatar()->getImageTag($size);
	}
	
	/**
	 * Returns a list of labels.
	 */
	public function getLabels() {
		return $this->labels;
	}
	
	/**
	 * Returns the primary label.
	 */
	public function getPrimaryLabel() {
		if (!$this->hasLabels()) return null;
		
		return reset($this->labels);
	}
	
	/**
	 * Returns the language.
	 */
	public function getLanguage() {
		if ($this->languageID) return LanguageFactory::getInstance()->getLanguage($this->languageID);
		
		return null;
	}
	
	/**
	 * Returns the user profile object.
	 */
	public function getUserProfile() {
		if ($this->userProfile === null) {
			$this->userProfile = new UserProfile(new User(null, $this->getDecoratedObject()->data));
		}
		
		return $this->userProfile;
	}
	
	/**
	 * Returns the effective visit time.
	 */
	public function getVisitTime() {
		if ($this->effectiveVisitTime === null) {
			if (WCF::getUser()->userID) {
				$this->effectiveVisitTime = max($this->visitTime, VisitTracker::getInstance()->getVisitTime('com.uz.show.entry'));
			}
			else {
				$this->effectiveVisitTime = max(VisitTracker::getInstance()->getObjectVisitTime('com.uz.show.entry', $this->entryID), VisitTracker::getInstance()->getVisitTime('com.uz.show.entry'));
			}
			if ($this->effectiveVisitTime === null) {
				$this->effectiveVisitTime = 0;
			}
		}
		
		return $this->effectiveVisitTime;
	}
	
	/**
	 * Returns true if entry has one or more labels.
	 */
	public function hasLabels() {
		return !empty($this->labels);
	}
	
	/**
	 * Returns true if this entry is new for the active user.
	 */
	public function isNew() {
		if (SHOW_LAST_CHANGE_NEW) {
			if ($this->lastChangeTime > $this->getVisitTime()) return true;
		}
		else {
			if ($this->time > $this->getVisitTime()) return true;
		}
		
		return false;
	}
	
	/**
	 * Returns 1 if the active user has subscribed this entry.
	 */
	public function isSubscribed() {
		return ($this->watchID ? 1 : 0);
	}
	
	/**
	 * Sets modification log entry.
	 */
	public function setLogEntry(ViewableEntryListEntryModificationLog $logEntry) {
		$this->logEntry = $logEntry;
	}
	
	/**
	 * Returns the viewable entry object with the given id.
	 */
	public static function getEntry($entryID) {
		$list = new ViewableEntryList();
		$list->setObjectIDs([$entryID]);
		$list->readObjects();
		
		return $list->search($entryID);
	}
	
	/**
	 * Returns the number of unread entrys.
	 */
	public static function getUnreadEntrys() {
		if (self::$unreadEntrys === null) {
			self::$unreadEntrys = 0;
			
			if (WCF::getUser()->userID) {
				$data = UserStorageHandler::getInstance()->getField('showUnreadEntrys');
				
				// cache does not exist or is outdated
				if ($data === null) {
					$categoryIDs = ShowCategory::getAccessibleCategoryIDs();
					if (!empty($categoryIDs)) {
						$conditionBuilder = new PreparedStatementConditionBuilder();
						$conditionBuilder->add("entry.categoryID IN (?)", [$categoryIDs]);
						if (SHOW_LAST_CHANGE_NEW) {
							$conditionBuilder->add("entry.lastChangeTime > ?", [VisitTracker::getInstance()->getVisitTime('com.uz.show.entry')]);
							$conditionBuilder->add("(entry.lastChangeTime > tracked_visit.visitTime OR tracked_visit.visitTime IS NULL)");
						}
						else {
							$conditionBuilder->add("entry.time > ?", [VisitTracker::getInstance()->getVisitTime('com.uz.show.entry')]);
							$conditionBuilder->add("(entry.time > tracked_visit.visitTime OR tracked_visit.visitTime IS NULL)");
						}
						
						$conditionBuilder->add("entry.isDisabled = 0 AND entry.isDeleted = 0");
						
						// apply language filter
						if (SHOW_ENABLE_MULTILINGUALISM && LanguageFactory::getInstance()->multilingualismEnabled() && count(WCF::getUser()->getLanguageIDs())) {
							$conditionBuilder->add('(entry.languageID IN (?) OR entry.languageID IS NULL)', [WCF::getUser()->getLanguageIDs()]);
						}
						
						$sql = "SELECT		COUNT(*)
								FROM		show".WCF_N."_entry entry
								LEFT JOIN	wcf".WCF_N."_tracked_visit tracked_visit
								ON			(tracked_visit.objectTypeID = ".VisitTracker::getInstance()->getObjectTypeID('com.uz.show.entry')." AND tracked_visit.objectID = entry.entryID AND tracked_visit.userID = ".WCF::getUser()->userID.")
							".$conditionBuilder;
						$statement = WCF::getDB()->prepareStatement($sql);
						$statement->execute($conditionBuilder->getParameters());
						self::$unreadEntrys = $statement->fetchSingleColumn();
					}
					
					// update storage data
					UserStorageHandler::getInstance()->update(WCF::getUser()->userID, 'showUnreadEntrys', self::$unreadEntrys);
				}
				else {
					self::$unreadEntrys = $data;
				}
			}
		}
		
		return self::$unreadEntrys;
	}
}
