<?php
namespace show\system\moderation\queue\report;
use show\data\entry\ViewableEntry;
use show\system\moderation\queue\AbstractEntryModerationQueueHandler;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\system\moderation\queue\report\IModerationQueueReportHandler;
use wcf\system\WCF;

/**
 * An implementation of IModerationQueueReportHandler for entrys.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryModerationQueueReportHandler extends AbstractEntryModerationQueueHandler implements IModerationQueueReportHandler {
	/**
	 * @inheritDoc
	 */
	protected $definitionName = 'com.woltlab.wcf.moderation.report';
	
	/**
	 * @inheritDoc
	 */
	protected $objectType = 'com.uz.show.entry';
	
	/**
	 * @inheritDoc
	 */
	public function canReport($objectID) {
		if (!$this->isValid($objectID)) {
			return false;
		}
		
		if (!$this->getEntry($objectID)->canRead()) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getReportedContent(ViewableModerationQueue $queue) {
		return WCF::getTPL()->fetch('moderationEntry', 'show', [
			'entry' => new ViewableEntry($queue->getAffectedObject())
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getReportedObject($objectID) {
		if ($this->isValid($objectID)) {
			return $this->getEntry($objectID);
		}
		
		return null;
	}
}
