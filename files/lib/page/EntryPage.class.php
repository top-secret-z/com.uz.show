<?php
namespace show\page;
use show\data\contact\Contact;
use show\data\entry\AccessibleEntryList;
use show\data\entry\EntryAction;
use show\data\entry\EntryEditor;
use show\data\entry\ViewableEntry;
use show\system\label\object\EntryLabelObjectHandler;
use show\system\option\EntryOptionHandler;
use show\system\SHOWCore;
use wcf\data\attachment\Attachment;
use wcf\data\tag\Tag;
use wcf\data\user\UserProfile;
use wcf\page\AbstractPage;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\comment\CommentHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\language\LanguageFactory;
use wcf\system\reaction\ReactionHandler;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\request\LinkHandler;
use wcf\system\tagging\TagEngine;
use wcf\system\MetaTagHandler;
use wcf\system\WCF;

/**
 * Shows an entry.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryPage extends AbstractPage {
	/**
	 * attachment list
	 */
	public $attachmentList;
	
	/**
	 * entry's category
	 */
	public $category;
	
	/**
	 * entry related
	 */
	public $entry;
	public $entryID = 0;
	public $entryLikeData = [];
	public $userEntryList;
	
	/**
	 * contact related
	 */
	public $showContact;
	
	/**
	 * comment related
	 */
	public $commentList;
	public $commentManager;
	public $commentObjectTypeID = 0;
	
	/**
	 * entry option handler object
	 */
	public $optionHandler;
	
	
	/**
	 * list of tags
	 */
	public $tags = [];
	
	/**
	 * user profile of the entry author
	 */
	public $userProfile;
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		// tabs display
		$tabs[1] = 1;
		$tabs[2] = $tabs[3] = $tabs[4] = $tabs[5] = 0;
		$options = $this->optionHandler->getOptions();
		
		if (!empty($options)) {
			foreach ($options as $data) {
				$option = $data['object'];
				$tabs[$option->tab] = 1;
			}
		}
		
		if (SHOW_TAB2_ENABLE && (SHOW_TAB2_WYSIWYG || SHOW_IMAGES_TAB == 2)) $tabs[2] = 1;
		if (SHOW_TAB3_ENABLE && (SHOW_TAB3_WYSIWYG || SHOW_IMAGES_TAB == 3)) $tabs[3] = 1;
		if (SHOW_TAB4_ENABLE && (SHOW_TAB4_WYSIWYG || SHOW_IMAGES_TAB == 4)) $tabs[4] = 1;
		if (SHOW_TAB5_ENABLE && (SHOW_TAB5_WYSIWYG || SHOW_IMAGES_TAB == 5)) $tabs[5] = 1;
		
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'attachmentList' => $this->attachmentList,
				'category' => $this->category,
				'commentCanAdd' => WCF::getSession()->getPermission('user.show.canAddComment'),
				'commentList' => $this->commentList,
				'commentObjectTypeID' => $this->commentObjectTypeID,
				'entry' => $this->entry,
				'entryID' => $this->entryID,
				'entryLikeData' => $this->entryLikeData,
				'hasMarkedItems' => ClipboardHandler::getInstance()->hasMarkedItems(ClipboardHandler::getInstance()->getObjectTypeID('com.uz.show.entry')),
				'lastCommentTime' => $this->commentList ? $this->commentList->getMinCommentTime() : 0,
				'likeData' => (MODULE_LIKE && $this->commentList) ? $this->commentList->getLikeData() : [],
				'options' => $this->optionHandler->getOptions(),
				'tabs' => $tabs,
				'tags' => $this->tags,
				'userEntryList' => $this->userEntryList,
				'userProfile' => $this->userProfile,
				'showContact' => $this->showContact
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// init option handler
		$this->optionHandler = new EntryOptionHandler(false);
		$this->optionHandler->setEntry($this->entry->getDecoratedObject());
		$this->optionHandler->enableEditMode(false);
		
		// update entry visit
		if ($this->entry->isNew()) {
			$entryAction = new EntryAction([$this->entry->getDecoratedObject()], 'markAsRead', [
					'viewableEntry' => $this->entry
			]);
			$entryAction->executeAction();
		}
		
		// views
		$entryEditor = new EntryEditor($this->entry->getDecoratedObject());
		$entryEditor->updateCounters(['views' => 1]);
		
		// get author's user profile
		$this->userProfile = $this->entry->getUserProfile();
		
		// get comments
		if ($this->entry->enableComments) {
			$this->commentObjectTypeID = CommentHandler::getInstance()->getObjectTypeID('com.uz.show.entryComment');
			$this->commentManager = CommentHandler::getInstance()->getObjectType($this->commentObjectTypeID)->getProcessor();
			$this->commentList = CommentHandler::getInstance()->getCommentList($this->commentManager, $this->commentObjectTypeID, $this->entryID);
		}
		
		// get other entrys by this author
		$this->userEntryList = new AccessibleEntryList();
		$this->userEntryList->getConditionBuilder()->add('entry.userID = ?', [$this->entry->userID]);
		$this->userEntryList->getConditionBuilder()->add('entry.entryID <> ?', [$this->entry->entryID]);
		$this->userEntryList->sqlLimit = 5;
		$this->userEntryList->readObjects();
		
		// get labels
		if ($this->entry->hasLabels) {
			$assignedLabels = EntryLabelObjectHandler::getInstance()->getAssignedLabels([$this->entry->entryID]);
			if (isset($assignedLabels[$this->entry->entryID])) {
				foreach ($assignedLabels[$this->entry->entryID] as $label) {
					$this->entry->addLabel($label);
				}
			}
		}
		
		// get tags
		if (MODULE_TAGGING && WCF::getSession()->getPermission('user.tag.canViewTag')) {
			$this->tags = TagEngine::getInstance()->getObjectTags(
				'com.uz.show.entry',
				$this->entry->entryID,
				[$this->entry->languageID === null ? LanguageFactory::getInstance()->getDefaultLanguageID() : ""]
			);
		}
		
		// get likes
		if (MODULE_LIKE) {
			// entry reactions
			$objectType = ReactionHandler::getInstance()->getObjectType('com.uz.show.likeableEntry');
			ReactionHandler::getInstance()->loadLikeObjects($objectType, [$this->entryID]);
			$this->entryLikeData = ReactionHandler::getInstance()->getLikeObjects($objectType);
		}
		
		// set location
		if (!SHOW_CATEGORY_ENABLE) {
			SHOWCore::getInstance()->setLocation($this->entry->getCategory()->getParentCategories(), $this->entry->getCategory());
		}
		
		// add meta/og tags
		MetaTagHandler::getInstance()->addTag('og:title', 'og:title', $this->entry->getSubject() . ' - ' . WCF::getLanguage()->get(PAGE_TITLE), true);
		MetaTagHandler::getInstance()->addTag('og:url', 'og:url', LinkHandler::getInstance()->getLink('Entry', ['application' => 'show', 'object' => $this->entry, 'appendSession' => false]), true);
		MetaTagHandler::getInstance()->addTag('og:type', 'og:type', 'article', true);
		MetaTagHandler::getInstance()->addTag('og:description', 'og:description', $this->entry->getTeaser(), true);
		
		// add image attachments as og:image tags
		$i = 0;
		$this->attachmentList = $this->entry->getAttachments();
		$this->entry->loadEmbeddedObjects();
		MessageEmbeddedObjectManager::getInstance()->setActiveMessage('com.uz.show.entry', $this->entryID);
		$attachments = array_merge(($this->attachmentList !== null ? $this->attachmentList->getGroupedObjects($this->entryID) : []), MessageEmbeddedObjectManager::getInstance()->getObjects('com.woltlab.wcf.attachment'));
		
		foreach ($attachments as $attachment) {
			if ($attachment->showAsImage() && $attachment->width >= 200 && $attachment->height >= 200) {
				MetaTagHandler::getInstance()->addTag('og:image' . $i, 'og:image', LinkHandler::getInstance()->getLink('Attachment', ['object' => $attachment]), true);
				MetaTagHandler::getInstance()->addTag('og:image:width' . $i, 'og:image:width', $attachment->width, true);
				MetaTagHandler::getInstance()->addTag('og:image:height' . $i, 'og:image:height', $attachment->height, true);
				$i++;
			}
		}
		
		// add tags as meta keywords
		if (!empty($this->tags)) {
			$keywords = '';
			foreach ($this->tags as $tag) {
				if (!empty($keywords)) $keywords .= ', ';
				$keywords .= $tag->name;
			}
			MetaTagHandler::getInstance()->addTag('keywords', 'keywords', $keywords);
		}
		
		// check contact data
		$this->showContact = Contact::checkContact($this->entry->userID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!empty($_REQUEST['id'])) $this->entryID = intval($_REQUEST['id']);
		$this->entry = ViewableEntry::getEntry($this->entryID);
		if ($this->entry === null) {
			throw new IllegalLinkException();
		}
		
		// check permissions
		if (!$this->entry->canRead()) {
			throw new PermissionDeniedException();
		}
		
		$this->canonicalURL = $this->entry->getLink();
		$this->category = $this->entry->getCategory();
	}
}
