<?php
namespace show\system\attachment;
use show\data\entry\Entry;
use show\data\entry\EntryList;
use wcf\system\attachment\AbstractAttachmentObjectType;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Attachment object type implementation for entrys.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryAttachmentObjectType extends AbstractAttachmentObjectType {
	/**
	 * @inheritDoc
	 */
	public function canDelete($objectID) {
		if ($objectID) {
			$entry = new Entry($objectID);
			if ($entry->canEdit()) return true;
		}
		
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function canDownload($objectID) {
		if ($objectID) {
			$entry = new Entry($objectID);
			if ($entry->canRead()) return true;
		}
		
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function canUpload($objectID, $parentObjectID = 0) {
		if ($objectID) {
			$entry = new Entry($objectID);
			if ($entry->canEdit()) return true;
		}
		
		return WCF::getSession()->getPermission('user.show.canAddEntry');
	}
	
	/**
	 * @inheritDoc
	 */
	public function canViewPreview($objectID) {
		return $this->canDownload($objectID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function cacheObjects(array $objectIDs) {
		$entryList = new EntryList();
		$entryList->setObjectIDs(array_unique($objectIDs));
		$entryList->readObjects();
		
		foreach ($entryList->getObjects() as $objectID => $object) {
			$this->cachedObjects[$objectID] = $object;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getAllowedExtensions() {
		return ArrayUtil::trim(explode("\n", WCF::getSession()->getPermission('user.show.allowedAttachmentExtensions')));
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMaxCount() {
		return WCF::getSession()->getPermission('user.show.maxAttachmentCount');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMaxSize() {
		return WCF::getSession()->getPermission('user.show.maxAttachmentSize');
	}
	
	/**
	 * @inheritDoc
	 */
	public function setPermissions(array $attachments) {
		$entryIDs = [];
		foreach ($attachments as $attachment) {
			// set default permissions
			$attachment->setPermissions(['canDownload' => false, 'canViewPreview' => false]);
			
			if ($this->getObject($attachment->objectID) === null) {
				$entryIDs[] = $attachment->objectID;
			}
		}
		
		if (!empty($entryIDs)) {
			$this->cacheObjects($entryIDs);
		}
		
		foreach ($attachments as $attachment) {
			$entry = $this->getObject($attachment->objectID);
			if ($entry !== null) {
				if (!$entry->canRead()) continue;
				
				$attachment->setPermissions([]);
			}
			else if ($attachment->tmpHash != '' && $attachment->userID == WCF::getUser()->userID) {
				$attachment->setPermissions(['canDownload' => true, 'canViewPreview' => true]);
			}
		}
	}
}
