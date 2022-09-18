<?php
namespace show\system\cache\builder;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\builder\AbstractCacheBuilder;
use wcf\system\category\CategoryHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Caches the available label group ids for show categories.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class ShowCategoryLabelCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	protected function rebuild(array $parameters) {
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('objectTypeID = ?', [ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.label.objectType', 'com.uz.show.category')->objectTypeID]);
		$conditionBuilder->add('objectID IN (SELECT categoryID FROM wcf'.WCF_N.'_category WHERE objectTypeID = ?)', [CategoryHandler::getInstance()->getObjectTypeByName('com.uz.show.category')->objectTypeID]);
		
		$sql = "SELECT	groupID, objectID
				FROM	wcf".WCF_N."_label_group_to_object
				".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		
		return $statement->fetchMap('objectID', 'groupID', false);
	}
}
