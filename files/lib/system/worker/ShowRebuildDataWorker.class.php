<?php
namespace show\system\worker;
use show\data\entry\EntryEditor;
use show\data\entry\EntryList;
use show\system\option\EntryOptionHandler;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\search\SearchIndexManager;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\worker\AbstractRebuildDataWorker;
use wcf\system\WCF;

/**
 * Worker implementation for updating show entry.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class ShowRebuildDataWorker extends AbstractRebuildDataWorker {
	/**
	 * @var HtmlInputProcessor
	 */
	protected $htmlInputProcessor;
	
	/**
	 * @inheritDoc
	 */
	protected $limit = 100;
	
	/**
	 * @inheritDoc
	 */
	protected $objectListClassName = EntryList::class;
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		if (!$this->loopCount) {
			// reset activity points
			UserActivityPointHandler::getInstance()->reset('com.uz.show.activityPointEvent.entry');
			
			// reset search index
			SearchIndexManager::getInstance()->reset('com.uz.show.entry');
			
			// reset user storage
			UserStorageHandler::getInstance()->resetAll('showUnreadEntrys');
			UserStorageHandler::getInstance()->resetAll('showWatchedEntrys');
			UserStorageHandler::getInstance()->resetAll('showUnreadWatchedEntrys');
		}
		
		if (!count($this->objectList)) {
			return;
		}
		
		// get label status
		$hasLabels = [];
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('objectTypeID = ?', [ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.label.object', 'com.uz.show.entry')->objectTypeID]);
		$conditionBuilder->add('objectID IN (?)', [$this->objectList->getObjectIDs()]);
		$sql = "SELECT	DISTINCT objectID
				FROM	wcf".WCF_N."_label_object
				".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		while ($objectID = $statement->fetchColumn()) {
			$hasLabels[$objectID] = 1;
		}
		
		// fetch cumulative likes
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("objectTypeID = ?", [ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.like.likeableObject', 'com.uz.show.likeableEntry')]);
		$conditions->add("objectID IN (?)", [$this->objectList->getObjectIDs()]);
		
		$sql = "SELECT	objectID, cumulativeLikes
				FROM	wcf".WCF_N."_like_object
				".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$cumulativeLikes = $statement->fetchMap('objectID', 'cumulativeLikes');
		
		// prepare statements
		$attachmentObjectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.attachment.objectType', 'com.uz.show.entry');
		$sql = "SELECT		COUNT(*) AS attachments
				FROM		wcf".WCF_N."_attachment
				WHERE		objectTypeID = ? AND objectID = ?";
		$attachmentStatement = WCF::getDB()->prepareStatement($sql);
		
		$commentObjectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.comment.commentableContent', 'com.uz.show.entryComment');
		$sql = "SELECT	COUNT(*) AS comments, SUM(responses) AS responses
				FROM	wcf".WCF_N."_comment
				WHERE	objectTypeID = ? AND objectID = ?";
		$commentStatement = WCF::getDB()->prepareStatement($sql);
		
		$entryIDs = $itemsToUser = [];
		WCF::getDB()->beginTransaction();
		foreach ($this->objectList as $entry) {
			$editor = new EntryEditor($entry);
			$data = [];
			
			// count attachments
			$attachmentStatement->execute([$attachmentObjectType->objectTypeID, $entry->entryID]);
			$row = $attachmentStatement->fetchSingleRow();
			$data['attachments'] = $row['attachments'];
			
			// set first attachment
			$data['attachmentID'] = null;
			$attachmentHandler = new AttachmentHandler('com.uz.show.entry', $entry->entryID);
			$attachmentList = $attachmentHandler->getAttachmentList();
			if (!empty($attachmentList->objects)) {
				$attachment = reset($attachmentList->objects);
				$data['attachmentID'] = $attachment->attachmentID;
			}
			
			// count comments
			$commentStatement->execute([$commentObjectType->objectTypeID, $entry->entryID]);
			$row = $commentStatement->fetchSingleRow();
			$data['comments'] = $row['comments'] + $row['responses'];
			
			// update cumulative likes
			$data['cumulativeLikes'] = isset($cumulativeLikes[$entry->entryID]) ? $cumulativeLikes[$entry->entryID] : 0;
			
			// update message
			if (!$entry->enableHtml) {
				$this->getHtmlInputProcessor()->process($entry->message, 'com.uz.show.entry', $entry->entryID, true);
				$data['message'] = $this->getHtmlInputProcessor()->getHtml();
				$data['enableHtml'] = 1;
			}
			else {
				$this->getHtmlInputProcessor()->reprocess($entry->message, 'com.uz.show.entry', $entry->entryID);
				$data['message'] = $this->getHtmlInputProcessor()->getHtml();
			}
			
			if (MessageEmbeddedObjectManager::getInstance()->registerObjects($this->getHtmlInputProcessor())) {
				$data['hasEmbeddedObjects'] = 1;
			}
			else {
				$data['hasEmbeddedObjects'] = 0;
			}
			
			// update label status
			if (isset($hasLabels[$entry->entryID])) {
				$data['hasLabels'] = 1;
			}
			
			$editor->update($data);
			
			if ($entry->userID && !$entry->isDisabled) {
				if (!isset($itemsToUser[$entry->userID])) {
					$itemsToUser[$entry->userID] = 0;
				}
				$itemsToUser[$entry->userID]++;
			}
			
			// update search index
			$optionHandler = new EntryOptionHandler(false);
			$optionHandler->setEntry($entry);
			$optionHandler->enableEditMode(false);
			$options = $optionHandler->getOptions();
			$optionSearch = '';
			if (!empty($options)) {
				foreach($options as $option) {
					$optionSearch .= ' ' . $option['value'];
				}
			}
			
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
			
			$entryIDs[] = $entry->entryID;
		}
		
		WCF::getDB()->commitTransaction();
		
		// update activity points
		UserActivityPointHandler::getInstance()->fireEvents('com.uz.show.activityPointEvent.entry', $itemsToUser, false);
	}
	
	/**
	 * @return HtmlInputProcessor
	 */
	protected function getHtmlInputProcessor() {
		if ($this->htmlInputProcessor === null) {
			$this->htmlInputProcessor = new HtmlInputProcessor();
		}
		
		return $this->htmlInputProcessor;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlOrderBy = 'entry.entryID';
	}
}
