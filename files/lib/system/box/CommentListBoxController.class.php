<?php
namespace show\system\box;
use show\data\category\ShowCategory;
use wcf\data\comment\ViewableCommentList;
use wcf\system\box\AbstractCommentListBoxController;
use wcf\system\WCF;

/**
 * Box controller implementation for a list of show comments.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class CommentListBoxController extends AbstractCommentListBoxController {
	/**
	 * @inheritDoc
	 */
	protected $objectTypeName = 'com.uz.show.entryComment';
	
	/**
	 * @inheritDoc
	 */
	protected function applyObjectTypeFilters(ViewableCommentList $commentList) {
		$accessibleCategoryIDs = ShowCategory::getAccessibleCategoryIDs();
		if (WCF::getSession()->getPermission('user.show.canViewEntry') && !empty($accessibleCategoryIDs)) {
			$commentList->sqlJoins .= ' INNER JOIN show' . WCF_N . '_entry entry ON (comment.objectID = entry.entryID)';
			$commentList->sqlSelects = 'entry.subject AS title';
			
			$commentList->getConditionBuilder()->add('entry.categoryID IN (?)', [$accessibleCategoryIDs]);
			
			if (!WCF::getSession()->getPermission('mod.show.canModerateEntry')) {
				$commentList->getConditionBuilder()->add('entry.isDisabled = ?', [0]);
			}
			if (!WCF::getSession()->getPermission('mod.show.canViewDeletedEntry')) {
				$commentList->getConditionBuilder()->add('entry.isDeleted = ?', [0]);
			}
		}
		else {
			$commentList->getConditionBuilder()->add('0 = 1');
		}
	}
}
