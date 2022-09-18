<?php
namespace show\system\moderation\queue\activation;
use show\data\entry\EntryAction;
use show\data\entry\ViewableEntry;
use show\system\moderation\queue\AbstractEntryModerationQueueHandler;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\system\moderation\queue\activation\IModerationQueueActivationHandler;
use wcf\system\WCF;

/**
 * Implementation of IModerationQueueHandler for entry versions.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryModerationQueueActivationHandler extends AbstractEntryModerationQueueHandler implements IModerationQueueActivationHandler {
	/**
	 * @inheritDoc
	 */
	public function enableContent(ModerationQueue $queue) {
		if ($this->isValid($queue->objectID) && $this->getEntry($queue->objectID)->isDisabled) {
			$objectAction = new EntryAction([$this->getEntry($queue->objectID)], 'enable');
			$objectAction->executeAction();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getDisabledContent(ViewableModerationQueue $queue) {
		return WCF::getTPL()->fetch('moderationEntry', 'show', [
				'entry' => new ViewableEntry($queue->getAffectedObject())
		]);
	}
}
