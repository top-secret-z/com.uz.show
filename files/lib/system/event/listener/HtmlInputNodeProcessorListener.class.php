<?php
namespace show\system\event\listener;
use show\data\entry\AccessibleEntryList;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\event\listener\AbstractHtmlInputNodeProcessorListener;
use wcf\system\request\LinkHandler;

/**
 * Parses URLs of show entries.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class HtmlInputNodeProcessorListener extends AbstractHtmlInputNodeProcessorListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		// replace entry links
		if (BBCodeHandler::getInstance()->isAvailableBBCode('entry')) {
			$regex = $this->getRegexFromLink(LinkHandler::getInstance()->getLink('Entry', [
					'application' => 'show',
					'forceFrontend' => true
			]), 'overview');
			$entryIDs = $this->getObjectIDs($eventObj, $regex);
			
			if (!empty($entryIDs)) {
				$entryList = new AccessibleEntryList();
				$entryList->getConditionBuilder()->add('entry.entryID IN (?)', [array_unique($entryIDs)]);
				$entryList->readObjects();
				
				$this->replaceLinksWithBBCode($eventObj, $regex, $entryList->getObjects(), 'entry');
			}
		}
	}
}
