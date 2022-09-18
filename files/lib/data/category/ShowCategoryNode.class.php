<?php
namespace show\data\category;
use wcf\data\category\CategoryNode;

/**
 * Represents a show category node.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class ShowCategoryNode extends CategoryNode {
	/**
	 * number of entrys / unread entrys of the category
	 */
	protected $entrys;
	protected $unreadEntrys;
	
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ShowCategory::class;
	
	/**
	 * Returns entry count of the category.
	 */
	public function getEntrys() {
		if ($this->entrys === null) {
			$this->entrys = ShowCategoryCache::getInstance()->getEntrys($this->categoryID);
		}
		
		return $this->entrys;
	}
	
	/**
	 * Returns unread entry count of the category.
	 */
	public function getUnreadEntrys() {
		if ($this->unreadEntrys === null) {
			$this->unreadEntrys = ShowCategoryCache::getInstance()->getUnreadEntrys($this->categoryID);
		}
		
		return $this->unreadEntrys;
	}
}
