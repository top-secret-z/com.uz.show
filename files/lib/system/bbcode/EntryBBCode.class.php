<?php
namespace show\system\bbcode;
use wcf\system\bbcode\AbstractBBCode;
use wcf\system\bbcode\BBCodeParser;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Parses the [entry] bbcode tag.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryBBCode extends AbstractBBCode {
	/**
	 * @inheritDoc
	 */
	public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) {
		$entryIDs = [];
		if (isset($openingTag['attributes'][0])) {
			$entryIDs = array_unique(ArrayUtil::toIntegerArray(explode(',', $openingTag['attributes'][0])));
		}
		
		$entrys = [];
		foreach ($entryIDs as $entryID) {
			$entry = MessageEmbeddedObjectManager::getInstance()->getObject('com.uz.show.entry', $entryID);
			if ($entry !== null && $entry->canRead()) {
				$entrys[] = $entry;
			}
		}
		
		if (!empty($entrys)) {
			if ($parser->getOutputType() == 'text/html') {
				return WCF::getTPL()->fetch('entryBBCode', 'show', [
						'entrys' => $entrys,
						'titleHash' => substr(StringUtil::getRandomID(), 0, 8)
				], true);
			}
			
			$result = '';
			foreach ($entrys as $entry) {
				if (!empty($result)) $result .= ' ';
				$result .= StringUtil::getAnchorTag(LinkHandler::getInstance()->getLink('Entry', [
						'application' => 'show',
						'object' => $entry
				]));
			}
			
			return $result;
		}
		
		if (!empty($entryIDs)) {
			$result = '';
			foreach ($entryIDs as $entryID) {
				if ($entryID) {
					if (!empty($result)) $result .= ' ';
					$result .= StringUtil::getAnchorTag(LinkHandler::getInstance()->getLink('Entry', [
							'application' => 'show',
							'id' => $entryID
					]));
				}
			}
			
			return $result;
		}
	}
}
