<?php
namespace show\system\box;
use show\data\category\ShowCategoryNodeTree;
use show\page\CategoryEntryListPage;
use show\page\EntryPage;
use wcf\system\box\AbstractBoxController;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;

/**
 * Box for show categories.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class CategoriesBoxController extends AbstractBoxController {
	/**
	 * @inheritDoc
	 */
	protected static $supportedPositions = ['sidebarLeft', 'sidebarRight'];
	
	/**
	 * @inheritDoc
	 */
	protected function loadContent() {
		// get categories
		$categoryTree = new ShowCategoryNodeTree('com.uz.show.category');
		$categoryList = $categoryTree->getIterator();
		$categoryList->setMaxDepth(0);
		
		if (iterator_count($categoryList)) {
			// get active category
			$activeCategory = null;
			if (RequestHandler::getInstance()->getActiveRequest() !== null) {
				if (RequestHandler::getInstance()->getActiveRequest()->getRequestObject() instanceof CategoryEntryListPage || RequestHandler::getInstance()->getActiveRequest()->getRequestObject() instanceof EntryPage) {
					if (RequestHandler::getInstance()->getActiveRequest()->getRequestObject()->category !== null) {
						$activeCategory = RequestHandler::getInstance()->getActiveRequest()->getRequestObject()->category;
					}
				}
			}
			
			$this->content = WCF::getTPL()->fetch('boxCategories', 'show', ['categoryList' => $categoryList, 'activeCategory' => $activeCategory], true);
		}
	}
}
