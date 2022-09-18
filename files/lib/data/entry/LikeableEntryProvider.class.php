<?php
namespace show\data\entry;
use wcf\data\like\object\ILikeObject;
use wcf\data\like\ILikeObjectTypeProvider;
use wcf\system\like\IViewableLikeProvider;
use wcf\system\WCF;

/**
 * Object type provider for entrys.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class LikeableEntryProvider extends EntryProvider implements ILikeObjectTypeProvider, IViewableLikeProvider {
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = LikeableEntry::class;
	
	/**
	 * @inheritDoc
	 */
	public function checkPermissions(ILikeObject $object) {
		return $object->entryID && $object->canRead();
	}
	
	/**
	 * @inheritDoc
	 */
	public function prepare(array $likes) {
		$entryIDs = [];
		foreach ($likes as $like) {
			$entryIDs[] = $like->objectID;
		}
		
		// get entrys
		$entryList = new ViewableEntryList();
		$entryList->setObjectIDs($entryIDs);
		$entryList->readObjects();
		$entrys = $entryList->getObjects();
		
		// set message
		foreach ($likes as $like) {
			if (isset($entrys[$like->objectID])) {
				$entry = $entrys[$like->objectID];
				
				// check permissions
				if (!$entry->canRead()) continue;
				
				$like->setIsAccessible();
				
				// short output
				$text = WCF::getLanguage()->getDynamicVariable('wcf.like.title.com.uz.show.likeableEntry', ['entry' => $entry, 'like' => $like]);
				$like->setTitle($text);
				
				// output
				$like->setDescription($entry->getExcerpt());
			}
		}
	}
}
