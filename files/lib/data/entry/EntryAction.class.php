<?php
namespace show\data\entry;
use show\data\category\ShowCategory;
use show\data\contact\Contact;
use show\data\modification\log\EntryListModificationLogList;
use show\system\log\modification\EntryModificationLogHandler;
use show\system\label\object\EntryLabelObjectHandler;
use show\system\upload\EntryIconUploadEntryValidationStrategy;
use show\system\user\notification\object\EntryUserNotificationObject;
use wcf\data\label\Label;
use wcf\data\user\User;
use wcf\data\user\object\watch\UserObjectWatchList;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\comment\CommentHandler;
use wcf\system\edit\EditHistoryManager;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\image\ImageHandler;
use wcf\system\label\LabelHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\like\LikeHandler;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\moderation\queue\ModerationQueueActivationManager;
use wcf\system\request\LinkHandler;
use wcf\system\search\SearchIndexManager;
use wcf\system\tagging\TagEngine;
use wcf\system\upload\UploadHandler;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\user\object\watch\UserObjectWatchHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\FileUtil;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * Executes entry-related actions.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = EntryEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $allowGuestAccess = ['getEntryPreview', 'getMapMarkers', 'openContact'];
	
	/**
	 * entry data
	 */
	public $entry;
	public $viewableEntry;
	public $entryData = [];
	
	/**
	 * category
	 */
	public $category;
	
	/**
	 * contact
	 */
	public $contactData;
	public $username;
	
	/**
	 * @inheritDoc
	 */
	public function create() {
		$data = $this->parameters['data'];
		
		// set default value
		if (!isset($data['enableHtml'])) $data['enableHtml'] = 1;
		if (!isset($data['lastChangeTime'])) $data['lastChangeTime'] = $data['time'];
		
		// count attachments and get first attachment
		$data['attachmentID'] = null;
		if (isset($this->parameters['attachmentHandler']) && $this->parameters['attachmentHandler'] !== null) {
			$attachmentHandler = $this->parameters['attachmentHandler'];
			$data['attachments'] = count($attachmentHandler);
			
			$attachmentList = $attachmentHandler->getAttachmentList();
			if ($data['attachments']) {
				$attachment = reset($attachmentList->objects);
				$data['attachmentID'] = $attachment->attachmentID;
			}
		}
		
		// handle ip address
		if (LOG_IP_ADDRESS) {
			if (!isset($data['ipAddress'])) {
				$data['ipAddress'] = WCF::getSession()->ipAddress;
			}
		}
		else {
			if (isset($data['ipAddress'])) {
				unset($data['ipAddress']);
			}
		}
		
		// html
		if (!empty($this->parameters['htmlInputProcessor'])) {
			$data['message'] = $this->parameters['htmlInputProcessor']->getHtml();
		}
		
		if (!empty($this->parameters['htmlInputProcessor2'])) {
			$data['text2'] = $this->parameters['htmlInputProcessor2']->getHtml();
		}
		
		if (!empty($this->parameters['htmlInputProcessor3'])) {
			$data['text3'] = $this->parameters['htmlInputProcessor3']->getHtml();
		}
		
		if (!empty($this->parameters['htmlInputProcessor4'])) {
			$data['text4'] = $this->parameters['htmlInputProcessor4']->getHtml();
		}
		
		if (!empty($this->parameters['htmlInputProcessor5'])) {
			$data['text5'] = $this->parameters['htmlInputProcessor5']->getHtml();
		}
		
		// save entry
		$entry = call_user_func([$this->className, 'create'], $data);
		$entryEditor = new EntryEditor($entry);
		
		// save entry options
		$optionSearch = '';
		if (!empty($this->parameters['options'])) {
			$sql = "INSERT INTO	show".WCF_N."_entry_option_value
						(entryID, optionID, optionValue)
					VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			foreach ($this->parameters['options'] as $optionID => $optionValue) {
				$statement->execute([$entry->entryID, $optionID, ($optionValue ?: '')]);
				$optionSearch .= ' ' . $optionValue;
			}
		}
		
		// save categories
		$entryEditor->updateCategories($this->parameters['categoryIDs']);
		
		// update search index
		// use message for various other data (MEDIUMTEXT)
		$message = $entry->message;
		if (!empty($entry->location)) $message .= ' ' . str_replace (',', ' ', $entry->location);
		if (!empty($optionSearch)) $message .= ' ' . $optionSearch;
		if (!empty($entry->text2)) $message .= ' ' . $entry->text2;
		if (!empty($entry->text3)) $message .= ' ' . $entry->text2;
		if (!empty($entry->text4)) $message .= ' ' . $entry->text2;
		if (!empty($entry->text5)) $message .= ' ' . $entry->text2;
		
		if (mb_strlen($message) > 10000000) $message = substr($message, 0, 10000000);
		
		SearchIndexManager::getInstance()->set(
			'com.uz.show.entry',
			$entry->entryID,
			$message,
			$entry->subject,
			$entry->time,
			$entry->userID,
			$entry->username,
			$entry->languageID
		);
		
		// update attachments
		if (isset($this->parameters['attachmentHandler']) && $this->parameters['attachmentHandler'] !== null) {
			$this->parameters['attachmentHandler']->updateObjectID($entry->entryID);
		}
		
		// save embedded objects
		if (!empty($this->parameters['htmlInputProcessor'])) {
			$this->parameters['htmlInputProcessor']->setObjectID($entry->entryID);
			if (MessageEmbeddedObjectManager::getInstance()->registerObjects($this->parameters['htmlInputProcessor'])) {
				$entryEditor->update(['hasEmbeddedObjects' => 1]);
			}
		}
		
		if (!empty($this->parameters['htmlInputProcessor2'])) {
			$this->parameters['htmlInputProcessor2']->setObjectID($entry->entryID);
			if (MessageEmbeddedObjectManager::getInstance()->registerObjects($this->parameters['htmlInputProcessor2'])) {
				$entryEditor->update(['hasEmbeddedObjects2' => 1]);
			}
		}
		
		if (!empty($this->parameters['htmlInputProcessor3'])) {
			$this->parameters['htmlInputProcessor3']->setObjectID($entry->entryID);
			if (MessageEmbeddedObjectManager::getInstance()->registerObjects($this->parameters['htmlInputProcessor3'])) {
				$entryEditor->update(['hasEmbeddedObjects3' => 1]);
			}
		}
		
		if (!empty($this->parameters['htmlInputProcessor4'])) {
			$this->parameters['htmlInputProcessor4']->setObjectID($entry->entryID);
			if (MessageEmbeddedObjectManager::getInstance()->registerObjects($this->parameters['htmlInputProcessor4'])) {
				$entryEditor->update(['hasEmbeddedObjects4' => 1]);
			}
		}
		
		if (!empty($this->parameters['htmlInputProcessor5'])) {
			$this->parameters['htmlInputProcessor5']->setObjectID($entry->entryID);
			if (MessageEmbeddedObjectManager::getInstance()->registerObjects($this->parameters['htmlInputProcessor5'])) {
				$entryEditor->update(['hasEmbeddedObjects5' => 1]);
			}
		}
		
		// set language id (cannot be zero)
		$languageID = (!isset($this->parameters['data']['languageID']) || ($this->parameters['data']['languageID'] === null)) ? LanguageFactory::getInstance()->getDefaultLanguageID() : $this->parameters['data']['languageID'];
		
		// save tags
		if (!empty($this->parameters['tags'])) {
			TagEngine::getInstance()->addObjectTags('com.uz.show.entry', $entry->entryID, $this->parameters['tags'], $languageID);
		}
		
		// trigger publication
		if (!$entry->isDisabled) {
			$action = new EntryAction([$entryEditor], 'triggerPublication');
			$action->executeAction();
		}
		// mark for moderated content
		else {
			ModerationQueueActivationManager::getInstance()->addModeratedContent('com.uz.show.entry', $entry->entryID);
		}
		
		// save entry icon
		$this->updateEntryIcon($entry);
		
		return $entry;
	}
	
	/**
	 * Validates parameters to mark entrys as read.
	 */
	public function validateMarkAsRead() {
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
	}
	
	/**
	 * Marks entries as read.
	 */
	public function markAsRead() {
		if (empty($this->parameters['visitTime'])) {
			$this->parameters['visitTime'] = TIME_NOW;
		}
		
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		foreach ($this->getObjects() as $entry) {
			VisitTracker::getInstance()->trackObjectVisit('com.uz.show.entry', $entry->entryID, $this->parameters['visitTime']);
		}
		
		// reset storage
		if (WCF::getUser()->userID) {
			UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'showUnreadEntrys');
			UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'showUnreadWatchedEntrys');
		}
	}
	
	/**
	 * Triggers the publication of entrys.
	 */
	public function triggerPublication() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
	
		foreach ($this->getObjects() as $entry) {
			EntryEditor::updateEntryCounter([$entry->userID => 1]);
			
			// fire activity event
			UserActivityEventHandler::getInstance()->fireEvent('com.uz.show.recentActivityEvent.entry', $entry->entryID, $entry->languageID, $entry->userID, $entry->time);
			UserActivityPointHandler::getInstance()->fireEvent('com.uz.show.activityPointEvent.entry', $entry->entryID, $entry->userID);
			
			// update watched objects
			if (!SHOW_CATEGORY_ENABLE) {
				UserObjectWatchHandler::getInstance()->updateObject(
					'com.uz.show.category',
					$entry->categoryID,
					'category',
					'com.uz.show.entry',
					new EntryUserNotificationObject($entry->getDecoratedObject())
				);
			}
			else {
				$categories = $entry->getDecoratedObject()->getCategories();
				foreach($categories as $category) {
					UserObjectWatchHandler::getInstance()->updateObject(
						'com.uz.show.category',
						$category->categoryID,
						'category',
						'com.uz.show.entry',
						new EntryUserNotificationObject($entry->getDecoratedObject())
					);
				}
			}
		}
		
		// reset user storage
		UserStorageHandler::getInstance()->resetAll('showUnreadEntrys');
		UserStorageHandler::getInstance()->resetAll('showWatchedEntrys');
		UserStorageHandler::getInstance()->resetAll('showUnreadWatchedEntrys');
	}
	
	/**
	 * @inheritDoc
	 */
	public function update() {
		// count attachments and get first attachment
		$data['attachmentID'] = null;
		if (isset($this->parameters['attachmentHandler']) && $this->parameters['attachmentHandler'] !== null) {
			$this->parameters['data']['attachments'] = count($this->parameters['attachmentHandler']);
			
			$attachmentList = $this->parameters['attachmentHandler']->getAttachmentList();
			
			if ($this->parameters['data']['attachments']) {
				$attachment = reset($attachmentList->objects);
				$this->parameters['data']['attachmentID'] = $attachment->attachmentID;
			}
		}
		
		if (!empty($this->parameters['htmlInputProcessor'])) {
			$this->parameters['data']['message'] = $this->parameters['htmlInputProcessor']->getHtml();
		}
		
		if (!empty($this->parameters['htmlInputProcessor2'])) {
			$this->parameters['data']['text2'] = $this->parameters['htmlInputProcessor2']->getHtml();
		}
		
		if (!empty($this->parameters['htmlInputProcessor3'])) {
			$this->parameters['data']['text3'] = $this->parameters['htmlInputProcessor3']->getHtml();
		}
		
		if (!empty($this->parameters['htmlInputProcessor4'])) {
			$this->parameters['data']['text4'] = $this->parameters['htmlInputProcessor4']->getHtml();
		}
		
		if (!empty($this->parameters['htmlInputProcessor5'])) {
			$this->parameters['data']['text5'] = $this->parameters['htmlInputProcessor5']->getHtml();
		}
		
		// update lastVersionTime for edit history
		if (MODULE_EDIT_HISTORY && isset($this->parameters['isEdit']) && isset($this->parameters['data']['message'])) {
			$this->parameters['data']['lastVersionTime'] = TIME_NOW;
		}
		
		// last change
		$this->parameters['data']['lastChangeTime'] = TIME_NOW;
		
		parent::update();
		
		// get ids
		$objectIDs = [];
		foreach ($this->getObjects() as $entry) {
			$objectIDs[] = $entry->entryID;
		}
		
		foreach ($this->getObjects() as $entry) {
			// tags
			if (isset($this->parameters['tags'])) {
				// set language id (cannot be zero)
				$languageID = (!isset($this->parameters['data']['languageID']) || ($this->parameters['data']['languageID'] === null)) ? LanguageFactory::getInstance()->getDefaultLanguageID() : $this->parameters['data']['languageID'];
				
				TagEngine::getInstance()->addObjectTags('com.uz.show.entry', $entry->entryID, $this->parameters['tags'], $languageID);
			}
			
			// categories
			if (isset($this->parameters['categoryIDs'])) {
				$entry->updateCategories($this->parameters['categoryIDs']);
			}
			
			// edit
			if (isset($this->parameters['isEdit']) && isset($this->parameters['data']['message'])) {
				$historySavingEntry = new HistorySavingEntry($entry->getDecoratedObject());
				EditHistoryManager::getInstance()->add(
					'com.uz.show.entry',
					$entry->entryID,
					$entry->message,
					$historySavingEntry->getTime(),
					$historySavingEntry->getUserID(),
					$historySavingEntry->getUsername(),
					isset($this->parameters['editReason']) ? $this->parameters['editReason'] : '',
					WCF::getUser()->userID
				);
			}
			
			// watched entries
			if (!$entry->isDeleted && !$entry->isDisabled) {
				UserObjectWatchHandler::getInstance()->updateObject(
						'com.uz.show.entry',
						$entry->entryID,
						'entry',
						'com.uz.show.entry',
						new EntryUserNotificationObject($entry->getDecoratedObject())
						);
			}
			
			// add log entry
			EntryModificationLogHandler::getInstance()->edit($entry->getDecoratedObject(), (isset($this->parameters['reason']) ? $this->parameters['reason'] : ''));
			
			// update embedded objects
			if (!empty($this->parameters['htmlInputProcessor'])) {
				$this->parameters['htmlInputProcessor']->setObjectID($entry->entryID);
				if ($entry->hasEmbeddedObjects != MessageEmbeddedObjectManager::getInstance()->registerObjects($this->parameters['htmlInputProcessor'])) {
					$entry->update([
						'hasEmbeddedObjects' => $entry->hasEmbeddedObjects ? 0 : 1
					]);
				}
			}
			
			if (!empty($this->parameters['htmlInputProcessor2'])) {
				$this->parameters['htmlInputProcessor2']->setObjectID($entry->entryID);
				if ($entry->hasEmbeddedObjects2 != MessageEmbeddedObjectManager::getInstance()->registerObjects($this->parameters['htmlInputProcessor2'])) {
					$entry->update([
							'hasEmbeddedObjects2' => $entry->hasEmbeddedObjects2 ? 0 : 1
					]);
				}
			}
			
			if (!empty($this->parameters['htmlInputProcessor3'])) {
				$this->parameters['htmlInputProcessor3']->setObjectID($entry->entryID);
				if ($entry->hasEmbeddedObjects3 != MessageEmbeddedObjectManager::getInstance()->registerObjects($this->parameters['htmlInputProcessor3'])) {
					$entry->update([
							'hasEmbeddedObjects3' => $entry->hasEmbeddedObjects3 ? 0 : 1
					]);
				}
			}
			
			if (!empty($this->parameters['htmlInputProcessor4'])) {
				$this->parameters['htmlInputProcessor4']->setObjectID($entry->entryID);
				if ($entry->hasEmbeddedObjects4 != MessageEmbeddedObjectManager::getInstance()->registerObjects($this->parameters['htmlInputProcessor4'])) {
					$entry->update([
							'hasEmbeddedObjects4' => $entry->hasEmbeddedObjects4 ? 0 : 1
					]);
				}
			}
			
			if (!empty($this->parameters['htmlInputProcessor5'])) {
				$this->parameters['htmlInputProcessor5']->setObjectID($entry->entryID);
				if ($entry->hasEmbeddedObjects5 != MessageEmbeddedObjectManager::getInstance()->registerObjects($this->parameters['htmlInputProcessor5'])) {
					$entry->update([
							'hasEmbeddedObjects5' => $entry->hasEmbeddedObjects5 ? 0 : 1
					]);
				}
			}
			
			// updates entry options
			$optionSearch = '';
			if (!empty($this->parameters['options'])) {
				$sql = "DELETE FROM	show".WCF_N."_entry_option_value
						WHERE		entryID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute([$entry->entryID]);
				
				$sql = "INSERT INTO	show".WCF_N."_entry_option_value
							(entryID, optionID, optionValue)
						VALUES		(?, ?, ?)";
				$statement = WCF::getDB()->prepareStatement($sql);
				foreach ($this->parameters['options'] as $optionID => $optionValue) {
					$statement->execute([$entry->entryID, $optionID, ($optionValue ?: '')]);
					
					$optionSearch .= ' ' . $optionValue;
				}
			}
			
			// create new search index entry
			// use message for various other data (MEDIUMTEXT)
			$message = isset($this->parameters['data']['message']) ? $this->parameters['data']['message'] : $message;
			$location = isset($this->parameters['data']['location']) ? $this->parameters['data']['location'] : '';
			$text2 = isset($this->parameters['data']['text2']) ? $this->parameters['data']['text2'] : '';
			$text3 = isset($this->parameters['data']['text3']) ? $this->parameters['data']['text3'] : '';
			$text4 = isset($this->parameters['data']['text4']) ? $this->parameters['data']['text4'] : '';
			$text5 = isset($this->parameters['data']['text5']) ? $this->parameters['data']['text5'] : '';
			
			if (!empty($location)) $message .= ' ' . str_replace (',', ' ', $location);
			if (!empty($optionSearch)) $message .= ' ' . $optionSearch;
			if (!empty($text2)) $message .= ' ' . $text2;
			if (!empty($text3)) $message .= ' ' . $text3;
			if (!empty($text4)) $message .= ' ' . $text4;
			if (!empty($text5)) $message .= ' ' . $text5;
			
			if (mb_strlen($message) > 10000000) $message = substr($message, 0, 10000000);
			
			SearchIndexManager::getInstance()->set(
					'com.uz.show.entry',
					$entry->entryID,
					$message,
					isset($this->parameters['data']['subject']) ? $this->parameters['data']['subject'] : $entry->subject,
					$entry->time,
					$entry->userID,
					$entry->username,
					$entry->languageID
					);
			
			// save entry icon
			$this->updateEntryIcon($entry->getDecoratedObject());
		}
	}
	
	/**
	 * Validates the get entry preview action.
	 */
	public function validateGetEntryPreview() {
		$this->viewableEntry = ViewableEntry::getEntry(reset($this->objectIDs));
		
		if ($this->viewableEntry === null || !$this->viewableEntry->canRead()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Returns a preview of an entry.
	 */
	public function getEntryPreview() {
		WCF::getTPL()->assign([
				'entry' => $this->viewableEntry
		]);
		
		return [
				'template' => WCF::getTPL()->fetch('entryPreview', 'show')
		];
	}
	
	/**
	 * Validates the 'stopWatching' action.
	 */
	public function validateStopWatching() {
		$this->readBoolean('stopWatchingAll', true);
		
		if (!$this->parameters['stopWatchingAll']) {
			if (!isset($this->parameters['entryIDs']) || !is_array($this->parameters['entryIDs'])) {
				throw new UserInputException('entryIDs');
			}
		}
	}
	
	/**
	 * Stops watching certain entrys for a certain user.
	 */
	public function stopWatching() {
		if ($this->parameters['stopWatchingAll']) {
			$entryWatchList = new UserObjectWatchList();
			$entryWatchList->getConditionBuilder()->add('user_object_watch.objectTypeID = ?', [UserObjectWatchHandler::getInstance()->getObjectTypeID('com.uz.show.entry')]);
			$entryWatchList->getConditionBuilder()->add('user_object_watch.userID = ?', [WCF::getUser()->userID]);
			$entryWatchList->readObjects();
			
			$this->parameters['entryIDs'] = [];
			foreach ($entryWatchList as $watchedObject) {
				$this->parameters['entryIDs'][] = $watchedObject->objectID;
			}
		}
		
		UserObjectWatchHandler::getInstance()->deleteObjects('com.uz.show.entry', $this->parameters['entryIDs']);
		UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'showWatchedEntrys');
		UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'showUnreadWatchedEntrys');
	}
	
	/**
	 * Loads entrys for given object ids.
	 */
	protected function loadEntrys() {
		if (empty($this->objectIDs)) {
			throw new UserInputException("objectIDs");
		}
		
		$this->readObjects();
		
		if (empty($this->objects)) {
			throw new UserInputException("objectIDs");
		}
	}
	
	/**
	 * Validates parameters to set entrys as featured.
	 */
	public function validateSetAsFeatured() {
		$this->loadEntrys();
		
		if (!WCF::getSession()->getPermission('mod.show.canEditEntry')) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Sets entrys as featured.
	 */
	public function setAsFeatured() {
		foreach ($this->getObjects() as $entry) {
			$entry->update(['isFeatured' => 1]);
			
			$this->addEntryData($entry->getDecoratedObject(), 'isFeatured', 1);
		}
		
		$this->unmarkEntrys();
		
		return $this->getEntryData();
	}
	
	/**
	 * Validates parameters to unset entrys as featured.
	 */
	public function validateUnsetAsFeatured() {
		$this->loadEntrys();
		
		if (!WCF::getSession()->getPermission('mod.show.canEditEntry')) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Unsets entrys as featured.
	 */
	public function unsetAsFeatured() {
		foreach ($this->getObjects() as $entry) {
			$entry->update(['isFeatured' => 0]);
			
			$this->addEntryData($entry->getDecoratedObject(), 'isFeatured', 0);
		}
		
		$this->unmarkEntrys();
		
		return $this->getEntryData();
	}
	
	/**
	 * Adds entry data.
	 */
	protected function addEntryData(Entry $entry, $key, $value) {
		if (!isset($this->entryData[$entry->entryID])) {
			$this->entryData[$entry->entryID] = [];
		}
		
		$this->entryData[$entry->entryID][$key] = $value;
	}
	
	/**
	 * Returns stored entry data.
	 */
	protected function getEntryData() {
		return [
			'entryData' => $this->entryData
		];
	}
	
	/**
	 * Validating parameters for enabling entrys.
	 */
	public function validateEnable() {
		$this->loadEntrys();
		
		if (!WCF::getSession()->getPermission('mod.show.canModerateEntry')) {
			throw new PermissionDeniedException();
		}
		
		foreach ($this->getObjects() as $entry) {
			if (!$entry->isDisabled || $entry->isDeleted) {
				throw new UserInputException('objectIDs');
			}
		}
	}
	
	/**
	 * Validating parameters for disabling entrys.
	 */
	public function validateDisable() {
		$this->loadEntrys();
		
		if (!WCF::getSession()->getPermission('mod.show.canModerateEntry')) {
			throw new PermissionDeniedException();
		}
		
		foreach ($this->getObjects() as $entry) {
			if ($entry->isDisabled || $entry->isDeleted) {
				throw new UserInputException('objectIDs');
			}
		}
	}
	
	/**
	 * Enables given entrys.
	 */
	public function enable() {
		if (empty($this->objects)) $this->readObjects();
		
		$entryIDs = [];
		foreach ($this->getObjects() as $entry) {
			$entry->update(['isDisabled' => 0, 'lastChangeTime' => TIME_NOW]);
			
			$this->addEntryData($entry->getDecoratedObject(), 'isDisabled', 0);
			EntryModificationLogHandler::getInstance()->enable($entry->getDecoratedObject());
			
			$entryIDs[] = $entry->entryID;
		}
		
		// publish entrys
		$entryAction = new EntryAction($this->objects, 'triggerPublication');
		$entryAction->executeAction();
		
		$this->removeModeratedContent($entryIDs);
		
		$this->unmarkEntrys();
		
		return $this->getEntryData();
	}
	
	/**
	 * Disables given entrys.
	 */
	public function disable() {
		if (empty($this->objects)) $this->readObjects();
		
		$entryData = $userCounters = [];
		foreach ($this->getObjects() as $entry) {
			$entry->update(['isDisabled' => 1]);
			
			$this->addEntryData($entry->getDecoratedObject(), 'isDisabled', 1);
			
			// add moderated content
			ModerationQueueActivationManager::getInstance()->addModeratedContent('com.uz.show.entry', $entry->entryID);
			
			EntryModificationLogHandler::getInstance()->disable($entry->getDecoratedObject());
			
			$entryData[$entry->entryID] = $entry->userID;
			
			if (!isset($userCounters[$entry->userID])) {
				$userCounters[$entry->userID] = 0;
			}
			$userCounters[$entry->userID]--;
		}
		
		// remove user activity events
		$this->removeActivityEvents($entryData, true);
		
		// decrease user entry counter
		if (!empty($userCounters)) {
			EntryEditor::updateEntryCounter($userCounters);
		}
		
		// reset user storage
		UserStorageHandler::getInstance()->resetAll('showUnreadEntrys');
		UserStorageHandler::getInstance()->resetAll('showWatchedEntrys');
		UserStorageHandler::getInstance()->resetAll('showUnreadWatchedEntrys');
		
		$this->unmarkEntrys();
		
		return $this->getEntryData();
	}
	
	/**
	 * Removes moderated content entries for given entry  ids.
	 */
	protected function removeModeratedContent(array $entryIDs) {
		ModerationQueueActivationManager::getInstance()->removeModeratedContent('com.uz.show.entry', $entryIDs);
	}
	
	/**
	 * Removes user activity events for entrys.
	 */
	protected function removeActivityEvents(array $entryData) {
		$entryIDs = array_keys($entryData);
		$userToItems = [];
		foreach ($entryData as $userID) {
			if (!$userID) {
				continue;
			}
			
			if (!isset($userToItems[$userID])) {
				$userToItems[$userID] = 0;
			}
			$userToItems[$userID]++;
		}
		
		// remove entry data
		UserActivityEventHandler::getInstance()->removeEvents('com.uz.show.recentActivityEvent.entry', $entryIDs);
		UserActivityPointHandler::getInstance()->removeEvents('com.uz.show.activityPointEvent.entry', $userToItems);
	}
	
	/**
	 * Validating parameters for trashing entrys.
	 */
	public function validateTrash() {
		$this->loadEntrys();
		$this->readString('reason', true, 'data');
		
		if (!WCF::getSession()->getPermission('mod.show.canDeleteEntry')) {
			if (!WCF::getSession()->getPermission('user.show.canDeleteEntry') || !WCF::getUser()->userID) {
				throw new PermissionDeniedException();
			}
				
			foreach ($this->getObjects() as $entry) {
				if ($entry->userID != WCF::getUser()->userID) {
					throw new PermissionDeniedException();
				}
			}
		}
		
		foreach ($this->getObjects() as $entry) {
			if ($entry->isDeleted) {
				throw new UserInputException('objectIDs');
			}
		}
	}
	
	/**
	 * Trashes given entrys.
	 */
	public function trash() {
		$entryIDs = [];
		foreach ($this->getObjects() as $entry) {
			$entry->update(['deleteTime' => TIME_NOW, 'isDeleted' => 1]);
			
			$this->addEntryData($entry->getDecoratedObject(), 'isDeleted', 1);
			EntryModificationLogHandler::getInstance()->trash($entry->getDecoratedObject(), $this->parameters['data']['reason']);
			
			$entryIDs[] = $entry->entryID;
		}
		
		// get delete notes
		$logList = new EntryListModificationLogList();
		$logList->setEntryData($entryIDs, 'trash');
		$logList->getConditionBuilder()->add("modification_log.time = ?", [TIME_NOW]);
		$logList->readObjects();
		$logEntries = [];
		foreach ($logList as $logEntry) {
			$logEntries[$logEntry->objectID] = $logEntry->__toString();
		}
		
		foreach ($this->getObjects() as $entry) {
			$this->addEntryData($entry->getDecoratedObject(), 'deleteNote', $logEntries[$entry->entryID]);
		}
		
		$this->unmarkEntrys();
		
		// reset user storage
		UserStorageHandler::getInstance()->resetAll('showUnreadEntrys');
		UserStorageHandler::getInstance()->resetAll('showWatchedEntrys');
		UserStorageHandler::getInstance()->resetAll('showUnreadWatchedEntrys');
		
		return $this->getEntryData();
	}
	
	/**
	 * Validating parameters for deleting entrys.
	 */
	public function validateDelete() {
		$this->loadEntrys();
		
		if (!WCF::getSession()->getPermission('mod.show.canDeleteEntryCompletely')) {
			throw new PermissionDeniedException();
		}
		
		foreach ($this->getObjects() as $entry) {
			if (!$entry->isDeleted) {
				throw new UserInputException('objectIDs');
			}
		}
	}
	
	/**
	 * Deletes given entrys.
	 */
	public function delete() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		$entryIDs = $entryData = $attachmentEntryIDs = $userCounters = [];
		foreach ($this->getObjects() as $entry) {
			$entryIDs[] = $entry->entryID;
			
			if ($entry->attachments) {
				$attachmentEntryIDs[] = $entry->entryID;
			}
			
			if (!$entry->isDisabled) {
				$entryData[$entry->entryID] = $entry->userID;
				
				if (!isset($userCounters[$entry->userID])) {
					$userCounters[$entry->userID] = 0;
				}
				$userCounters[$entry->userID]--;
			}
		}
		
		// remove user activity events
		if (!empty($entryData)) {
			$this->removeActivityEvents($entryData);
		}
		
		// remove entrys
		foreach ($this->getObjects() as $entry) {
			$entry->delete();
			
			// delete entry icon
			if ($entry->getIconLocation()) {
				@unlink($entry->getIconLocation());
			}
			
			$this->addEntryData($entry->getDecoratedObject(), 'deleted', LinkHandler::getInstance()->getLink('EntryList', ['application' => 'show']));
			EntryModificationLogHandler::getInstance()->delete($entry->getDecoratedObject());
		}
		
		if (!empty($entryIDs)) {
			// delete like data
			LikeHandler::getInstance()->removeLikes('com.uz.show.likeableEntry', $entryIDs);
			
			// remove edit history
			EditHistoryManager::getInstance()->delete('com.uz.show.entry', $entryIDs);
			
			// delete comments
			CommentHandler::getInstance()->deleteObjects('com.uz.show.entryComment', $entryIDs);
			
			// delete tag to object entries
			TagEngine::getInstance()->deleteObjects('com.uz.show.entry', $entryIDs);
			
			// delete entry from search index
			SearchIndexManager::getInstance()->delete('com.uz.show.entry', $entryIDs);
			
			// delete embedded objects
			MessageEmbeddedObjectManager::getInstance()->removeObjects('com.uz.show.entry', $entryIDs);
			
			// delete the log entries except for deleting the entry
			EntryModificationLogHandler::getInstance()->deleteLogs($entryIDs, ['delete']);
		}
		
		// decrease user entry counter
		if (!empty($userCounters)) {
			EntryEditor::updateEntryCounter($userCounters);
		}
		
		// delete attachments
		if (!empty($attachmentEntryIDs)) {
			AttachmentHandler::removeAttachments('com.uz.show.entry', $attachmentEntryIDs);
		}
		
		// delete subscriptions
		UserObjectWatchHandler::getInstance()->deleteObjects('com.uz.show.entry', $entryIDs);
		
		// delete label assignments
		LabelHandler::getInstance()->removeLabels(LabelHandler::getInstance()->getObjectType('com.uz.show.entry')->objectTypeID, $entryIDs);
		
		// reset user storage
		UserStorageHandler::getInstance()->resetAll('showUnreadEntrys');
		UserStorageHandler::getInstance()->resetAll('showWatchedEntrys');
		UserStorageHandler::getInstance()->resetAll('showUnreadWatchedEntrys');
		
		$this->unmarkEntrys();
		
		return $this->getEntryData();
	}
	
	/**
	 * Validating parameters for restoring entrys.
	 */
	public function validateRestore() {
		$this->loadEntrys();
		
		if (!WCF::getSession()->getPermission('mod.show.canDeleteEntry')) {
			throw new PermissionDeniedException();
		}
		
		foreach ($this->getObjects() as $entry) {
			if (!$entry->isDeleted) {
				throw new UserInputException('objectIDs');
			}
		}
	}
	
	/**
	 * Restores given entrys.
	 */
	public function restore() {
		$entryIDs = [];
		foreach ($this->getObjects() as $entry) {
			$entry->update(['deleteTime' => 0, 'isDeleted' => 0]);
			$entryIDs[] = $entry->entryID;
			
			$this->addEntryData($entry->getDecoratedObject(), 'isDeleted', 0);
			EntryModificationLogHandler::getInstance()->restore($entry->getDecoratedObject());
		}
		
		$this->unmarkEntrys();
		
		// reset user storage
		UserStorageHandler::getInstance()->resetAll('showUnreadEntrys');
		UserStorageHandler::getInstance()->resetAll('showWatchedEntrys');
		UserStorageHandler::getInstance()->resetAll('showUnreadWatchedEntrys');
		
		return $this->getEntryData();
	}
	
	/**
	 * Validates the 'uploadIcon' action.
	 */
	public function validateUploadIcon() {
		if (!SHOW_ENTRY_ICON_ENABLE) {
			throw new IllegalLinkException();
		}
		
		$this->readString('tmpHash');
		$this->readInteger('entryID', true);
		
		// check permissions
		if (!$this->parameters['entryID'] && !WCF::getSession()->getPermission('user.show.canAddEntry')) {
			throw new PermissionDeniedException();
		}
		
		if ($this->parameters['entryID']) {
			$this->entry = new Entry($this->parameters['entryID']);
			if (!$this->entry->entryID) {
				throw new UserInputException('entryID');
			}
			
			if (!$this->entry->canEdit()) {
				throw new PermissionDeniedException();
			}
		}
		
		$uploadHandler = $this->parameters['__files'];
		
		if (count($uploadHandler->getFiles()) != 1) {
			throw new IllegalLinkException();
		}
		
		// check uploaded entry
		$uploadHandler->validateFiles(new EntryIconUploadEntryValidationStrategy());
	}
	
	/**
	 * Handles uploading an entry icon.
	 */
	public function uploadIcon() {
		$files = $this->parameters['__files']->getFiles();
		$entry = reset($files);
		
		try {
			if (!$entry->getValidationErrorType()) {
				$imageData = $entry->getImageData();
				$neededMemory = $imageData['width'] * $imageData['height'] * ($entry->getFileExtension() == 'png' ? 4 : 3) * 2.1;
				if (FileUtil::checkMemoryLimit($neededMemory)) {
					$adapter = ImageHandler::getInstance()->getAdapter();
					$adapter->loadFile($entry->getLocation());
					
					$entryLocation = FileUtil::getTemporaryFilename();
					$thumbnail = $adapter->createThumbnail(Entry::ICON_SIZE, Entry::ICON_SIZE, false);
					$adapter->writeImage($thumbnail, $entryLocation);
					
					$iconLocation = SHOW_DIR.'images/entry/'.$this->parameters['tmpHash'].'.'.$entry->getFileExtension();
					if (@copy($entryLocation, $iconLocation)) {
						@unlink($entryLocation);
						
						// store extension within session variables
						WCF::getSession()->register('showEntryIcon-'.$this->parameters['tmpHash'], $entry->getFileExtension());
						
						return [
							'url' => WCF::getPath('show').'images/entry/'.$this->parameters['tmpHash'].'.'.$entry->getFileExtension()
						];
					}
					else {
						throw new UserInputException('image', 'uploadFailed');
					}
				}
				else {
					throw new UserInputException('image', 'tooLarge');
				}
			}
		}
		catch (UserInputException $e) {
			$entry->setValidationErrorType($e->getType());
		}
		
		return ['errorType' => $entry->getValidationErrorType()];
	}
	
	/**
	 * Validates the 'deleteIcon' action.
	 */
	public function validateDeleteIcon() {
		if (!SHOW_ENTRY_ICON_ENABLE) {
			throw new IllegalLinkException();
		}
		
		$this->readString('tmpHash');
		$this->readInteger('entryID', true);
		
		if (!$this->parameters['entryID']) {
			if (!WCF::getSession()->getPermission('user.show.canAddEntry')) {
				throw new PermissionDeniedException();
			}
			
			// check if user has uploaded any entry icon
			$iconExtension = WCF::getSession()->getVar('showEntryIcon-'.$this->parameters['tmpHash']);
			if (!$iconExtension || !file_exists(SHOW_DIR.'images/entry/'.$this->parameters['tmpHash'].'.'.$iconExtension)) {
				throw new IllegalLinkException();
			}
		}
		else {
			$this->entry = new Entry($this->parameters['entryID']);
			if (!$this->entry->entryID) {
				throw new UserInputException('entryID');
			}
			
			if (!$this->entry->canEdit()) {
				throw new PermissionDeniedException();
			}
			
			if (!$this->entry->getIconLocation()) {
				// check if user has uploaded any entry icon
				$iconExtension = WCF::getSession()->getVar('showEntryIcon-'.$this->parameters['tmpHash']);
				if (!$iconExtension || !file_exists(SHOW_DIR.'images/entry/'.$this->parameters['tmpHash'].'.'.$iconExtension)) {
					throw new IllegalLinkException();
				}
			}
		}
	}
	
	/**
	 * Deletes an entry icon.
	 */
	public function deleteIcon() {
		if ($this->entry) {
			@unlink($this->entry->getIconLocation());
			
			$entryEditor = new EntryEditor($this->entry);
			$entryEditor->update(['iconHash' => '', 'iconExtension' => '']);
		}
		
		$iconExtension = WCF::getSession()->getVar('showEntryIcon-'.$this->parameters['tmpHash']);
		if ($iconExtension) {
			@unlink(SHOW_DIR.'images/entry/'.$this->parameters['tmpHash'].'.'.$iconExtension);
			WCF::getSession()->unregister('showEntryIcon-'.$this->parameters['tmpHash']);
		}
	}
	
	/**
	 * Updates the icon of the given entry.
	 */
	public function updateEntryIcon(Entry $entry) {
		if (!isset($this->parameters['tmpHash'])) {
			return;
		}
		
		$fileExtension = WCF::getSession()->getVar('showEntryIcon-'.$this->parameters['tmpHash']);
		if ($fileExtension !== null) {
			$oldFilename = SHOW_DIR.'images/entry/'.$this->parameters['tmpHash'].'.'.$fileExtension;
			if (file_exists($oldFilename)) {
				// delete old entry icon
				if ($entry->getIconLocation()) {
					@unlink($entry->getIconLocation());
				}
				
				$iconHash = sha1_file($oldFilename);
				$newFilename = SHOW_DIR.'images/entry/'.substr($iconHash, 0, 2).'/'.$entry->entryID.'.'.$fileExtension;
				$directory = dirname($newFilename);
				
				// check if directory exists
				if (!@file_exists($directory)) {
					FileUtil::makePath($directory);
				}
				
				if (@rename($oldFilename, $newFilename)) {
					$entryEditor = new EntryEditor($entry);
					$entryEditor->update(['iconHash' => $iconHash, 'iconExtension' => $fileExtension]);
				}
				else {
					@unlink($oldFilename);
				}
			}
		}
	}
	
	/**
	 * Validates 'assignLabel' action.
	 */
	public function validateAssignLabel() {
		WCF::getSession()->checkPermissions(['mod.show.canEditEntry']);
		
		$this->readInteger('categoryID');
		
		$this->category = ShowCategory::getCategory($this->parameters['categoryID']);
		if ($this->category === null) {
			throw new UserInputException('category');
		}
		
		if (!$this->category->isAccessible()) {
			throw new PermissionDeniedException();
		}
		
		// validate entrys
		$this->readObjects();
		if (empty($this->objects)) {
			throw new UserInputException('objectIDs');
		}
		
		// reload entrys with assigned categories
		$entryList = new EntryList();
		$entryList->decoratorClassName = EntryEditor::class;
		$entryList->setObjectIDs($this->objectIDs);
		$entryList->readObjects();
		$this->objects = $entryList->getObjects();
		
		foreach ($this->getObjects() as $entry) {
			if ($this->category->categoryID != $entry->categoryID) {
				throw new UserInputException('objectIDs');
			}
		}
		
		// validate label ids
		$this->parameters['labelIDs'] = empty($this->parameters['labelIDs']) ? [] : ArrayUtil::toIntegerArray($this->parameters['labelIDs']);
		if (!empty($this->parameters['labelIDs'])) {
			$labelGroups = $this->category->getLabelGroups();
			if (empty($labelGroups)) {
				throw new PermissionDeniedException();
			}
			
			foreach ($this->parameters['labelIDs'] as $groupID => $labelID) {
				if (!isset($labelGroups[$groupID]) || !$labelGroups[$groupID]->isValid($labelID)) {
					throw new UserInputException('labelIDs');
				}
			}
		}
	}
	
	/**
	 * Assigns labels to entrys and returns the updated list.
	 */
	public function assignLabel() {
		$objectTypeID = LabelHandler::getInstance()->getObjectType('com.uz.show.entry')->objectTypeID;
		$entryIDs = [];
		foreach ($this->getObjects() as $entry) {
			$entryIDs[] = $entry->entryID;
		}
		
		// fetch old labels for modification log creation
		$oldLabels = LabelHandler::getInstance()->getAssignedLabels($objectTypeID, $entryIDs);
		
		foreach ($this->getObjects() as $entry) {
			LabelHandler::getInstance()->setLabels($this->parameters['labelIDs'], $objectTypeID, $entry->entryID);
			
			// update hasLabels flag
			$entry->update(['hasLabels' => !empty($this->parameters['labelIDs']) ? 1 : 0]);
		}
		
		$assignedLabels = LabelHandler::getInstance()->getAssignedLabels($objectTypeID, $entryIDs);
		
		$labels = [];
		if (!empty($assignedLabels)) {
			$tmp = [];
			
			// get labels from first object
			$labelList = reset($assignedLabels);
			
			// log adding new labels
			WCF::getDB()->beginTransaction();
			foreach ($this->getObjects() as $entry) {
				$newLabels = $labelList;
				if (!empty($oldLabels[$entry->entryID])) {
					$newLabels = array_diff_key($labelList, $oldLabels[$entry->entryID]);
				}
				
				foreach ($newLabels as $label) {
					EntryModificationLogHandler::getInstance()->setLabel($entry->getDecoratedObject(), $label);
				}
			}
			WCF::getDB()->commitTransaction();
			
			foreach ($labelList as $label) {
				$tmp[$label->labelID] = [
						'cssClassName' => $label->cssClassName,
						'label' => $label->getTitle(),
						'link' => LinkHandler::getInstance()->getLink('EntryList', ['application' => 'show', 'object' => $this->category], 'labelIDs['.$label->groupID.']='.$label->labelID)
				];
			}
			
			// sort labels by label group show order
			$labelGroups = EntryLabelObjectHandler::getInstance()->getLabelGroups();
			foreach ($labelGroups as $labelGroup) {
				foreach ($tmp as $labelID => $labelData) {
					if ($labelGroup->isValid($labelID)) {
						$labels[] = $labelData;
						break;
					}
				}
			}
		}
		
		$this->unmarkEntrys($entryIDs);
		
		return ['labels' => $labels];
	}
	
	/**
	 * Unmarks entrys.
	 */
	protected function unmarkEntrys(array $entryIDs = []) {
		if (empty($entryIDs)) {
			foreach ($this->getObjects() as $entry) {
				$entryIDs[] = $entry->entryID;
			}
		}
		
		if (!empty($entryIDs)) {
			ClipboardHandler::getInstance()->unmark($entryIDs, ClipboardHandler::getInstance()->getObjectTypeID('com.uz.show.entry'));
		}
	}
	
	/**
	 * Validates the 'getMapMarkers' action.
	 */
	public function validateGetMapMarkers() {
		// validate new boundaries
		if (!isset($this->parameters['eastLongitude']) || !is_numeric($this->parameters['eastLongitude'])) {
			throw new UserInputException('eastLongitude');
		}
		if ($this->parameters['eastLongitude'] > 180 || $this->parameters['eastLongitude'] < -180) {
			throw new UserInputException('eastLongitude');
		}
		if (!isset($this->parameters['northLatitude']) || !is_numeric($this->parameters['northLatitude'])) {
			throw new UserInputException('northLatitude');
		}
		if ($this->parameters['northLatitude'] > 90 || $this->parameters['northLatitude'] < -90) {
			throw new UserInputException('northLatitude');
		}
		if (!isset($this->parameters['southLatitude']) || !is_numeric($this->parameters['southLatitude'])) {
			throw new UserInputException('southLatitude');
		}
		if ($this->parameters['southLatitude'] > 90 || $this->parameters['southLatitude'] < -90) {
			throw new UserInputException('southLatitude');
		}
		if (!isset($this->parameters['westLongitude']) || !is_numeric($this->parameters['westLongitude'])) {
			throw new UserInputException('westLongitude');
		}
		if ($this->parameters['westLongitude'] > 180 || $this->parameters['westLongitude'] < -180) {
			throw new UserInputException('westLongitude');
		}
		if (!isset($this->parameters['excludedObjectIDs'])) {
			$this->parameters['excludedObjectIDs'] = [];
		}
		else if (is_string($this->parameters['excludedObjectIDs'])) {
			try {
				$this->parameters['excludedObjectIDs'] = JSON::decode($this->parameters['excludedObjectIDs']);
			}
			catch (SystemException $e) {
				throw new UserInputException('excludedObjectIDs');
			}
		}
		else if (!is_array($this->parameters['excludedObjectIDs'])) {
			throw new UserInputException('excludedObjectIDs');
		}
		
		// category
		$this->readInteger('categoryID', true);
		if ($this->parameters['categoryID']) {
			$category = ShowCategory::getCategory($this->parameters['categoryID']);
			if ($category === null) {
				throw new IllegalLinkException();
			}
			if (!$category->isAccessible()) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * Loads the entry markers.
	 */
	public function getMapMarkers() {
		if ($this->parameters['categoryID']) {
			$entryList = new CategoryEntryList($this->parameters['categoryID'], true);
		}
		else {
			$entryList = new AccessibleEntryList();
		}
		
		$entryList->getConditionBuilder()->add('entry.latitude <> ?', [0]);
		$entryList->getConditionBuilder()->add('entry.longitude <> ?', [0]);
		$entryList->getConditionBuilder()->add('entry.latitude <= ?', [$this->parameters['northLatitude']]);
		$entryList->getConditionBuilder()->add('entry.latitude >= ?', [$this->parameters['southLatitude']]);
		$entryList->getConditionBuilder()->add('entry.longitude <= ?', [$this->parameters['eastLongitude']]);
		$entryList->getConditionBuilder()->add('entry.longitude >= ?', [$this->parameters['westLongitude']]);
		
		if (!empty($this->parameters['excludedObjectIDs'])) {
			$entryList->getConditionBuilder()->add('entry.entryID NOT IN (?)', [$this->parameters['excludedObjectIDs']]);
		}
		$entryList->readObjects();
		
		// group entrys by location
		$groupedEntrys = [];
		foreach ($entryList as $entry) {
			$index = $entry->latitude.' '.$entry->longitude;
			if (!isset($groupedEntrys[$index])) {
				$groupedEntrys[$index] = [];
			}
			
			$groupedEntrys[$index][$entry->entryID] = $entry;
		}
		
		$markers = [];
		foreach ($groupedEntrys as $entrys) {
			$entry = reset($entrys);
			if (count($entrys) == 1) {
				$markers[] = [
						'infoWindow' => WCF::getTPL()->fetch('entryInfoWindow', 'show', [
								'entry' => $entry
						]),
						'latitude' => $entry->latitude,
						'longitude' => $entry->longitude,
						'objectID' => $entry->entryID,
						'title' => $entry->title
				];
			}
			else {
				$dialog = null;
				if (count($entrys) > 5) {
					$dialog = WCF::getTPL()->fetch('entryListItems', 'show', [
							'enableEditMode' => false,
							'objects' => $entrys
					]);
				}
				
				$markers[] = [
						'dialog' => $dialog,
						'infoWindow' => WCF::getTPL()->fetch('entrysInfoWindow', 'show', [
								'entrys' => $entrys,
								'items' => count($entrys)
						]),
						'latitude' => $entry->latitude,
						'location' => $entry->location,
						'longitude' => $entry->longitude,
						'objectIDs' => array_keys($entrys),
						'title' => WCF::getLanguage()->getDynamicVariable('show.entry.entrysInfoWindow')
				];
			}
		}
		
		return ['markers' => $markers];
	}
	
	/**
	 * Validates the 'openContact' action.
	 */
	public function validateOpenContact() {
		if (!SHOW_CONTACT_ENABLE || !WCF::getSession()->getPermission('user.show.canViewContact')) {
			throw new PermissionDeniedException();
		}
		
		if (!$this->parameters['userID']) {
			throw new PermissionDeniedException();
		}
		$user = new User($this->parameters['userID']);
		if (!$user->userID) {
			throw new IllegalLinkException();
		}
		$this->username = $user->username;
		
		$this->contactData = Contact::getContactData($this->parameters['userID']);
		if (!$this->contactData->contactID || $this->contactData->isDisabled) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * Opens contact dialog.
	 */
	public function openContact() {
		
		return [
				'template' => WCF::getTPL()->fetch('showContactDialog', 'show', [
						'contact' => $this->contactData,
						'username' => $this->username
				])
		];
	}
}
