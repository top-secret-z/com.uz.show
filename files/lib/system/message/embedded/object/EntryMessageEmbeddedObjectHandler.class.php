<?php
namespace show\system\message\embedded\object;
use show\data\entry\AccessibleEntryList;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\message\embedded\object\AbstractMessageEmbeddedObjectHandler;
use wcf\util\ArrayUtil;

/**
 * Message embedded object handler implementation for show entrys.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryMessageEmbeddedObjectHandler extends AbstractMessageEmbeddedObjectHandler {
	/**
	 * @inheritDoc
	 */
	public function loadObjects(array $objectIDs) {
		$entryList = new AccessibleEntryList();
		$entryList->getConditionBuilder()->add('entry.entryID IN (?)', [$objectIDs]);
		$entryList->readObjects();
		return $entryList->getObjects();
	}
	
	/**
	 * @inheritDoc
	 */
	public function parse(HtmlInputProcessor $htmlInputProcessor, array $embeddedData) {
		if (!empty($embeddedData['entry'])) {
			$parsedEntryIDs = [];
			foreach ($embeddedData['entry'] as $attributes) {
				if (!empty($attributes[0])) {
					$parsedEntryIDs = array_merge($parsedEntryIDs, ArrayUtil::toIntegerArray(explode(',', $attributes[0])));
				}
			}
			
			$entryIDs = array_unique(array_filter($parsedEntryIDs));
			if (!empty($entryIDs)) {
				$entryList = new AccessibleEntryList();
				$entryList->getConditionBuilder()->add('entry.entryID IN (?)', [$entryIDs]);
				$entryList->readObjectIDs();
				
				return $entryList->getObjectIDs();
			}
		}
		
		return [];
	}
}
