<?php
namespace show\system\page\handler;
use show\data\category\ShowCategory;
use show\data\category\ShowCategoryCache;
use wcf\system\page\handler\AbstractLookupPageHandler;
use wcf\system\page\handler\IOnlineLocationPageHandler;
use wcf\system\page\handler\TDecoratedCategoryLookupPageHandler;
use wcf\system\page\handler\TDecoratedCategoryOnlineLocationLookupPageHandler;

/**
 * Menu page handler for the category entry list page.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class CategoryEntryListPageHandler extends AbstractLookupPageHandler implements IOnlineLocationPageHandler {
	use TDecoratedCategoryOnlineLocationLookupPageHandler;
	
	/**
	 * @inheritDoc
	 */
	protected function getDecoratedCategoryClass() {
		return ShowCategory::class;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getOutstandingItemCount($objectID = null) {
		return ShowCategoryCache::getInstance()->getUnreadEntrys($objectID);
	}
}
