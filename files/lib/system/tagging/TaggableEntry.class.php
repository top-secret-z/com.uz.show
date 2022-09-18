<?php
namespace show\system\tagging;
use show\data\entry\TaggedEntryList;
use wcf\system\tagging\AbstractCombinedTaggable;

/**
 * Implementation of ITaggable for entry tagging.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class TaggableEntry extends AbstractCombinedTaggable {
	/**
	 * @inheritDoc
	 */
	public function getObjectListFor(array $tags) {
		return new TaggedEntryList($tags);
	}
		
	/**
	 * @inheritDoc
	 */
	public function getTemplateName() {
		return 'taggedEntryList';
	}
}
