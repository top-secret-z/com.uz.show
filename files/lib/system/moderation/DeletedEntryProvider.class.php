<?php
namespace show\system\moderation;
use show\data\entry\DeletedEntryList;
use wcf\system\moderation\AbstractDeletedContentProvider;

/**
 * Implementation of IDeletedContentProvider for deleted entrys.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class DeletedEntryProvider extends AbstractDeletedContentProvider {
	/**
	 * @inheritDoc
	 */
	public function getObjectList() {
		return new DeletedEntryList();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTemplateName() {
		return 'deletedEntryList';
	}
}
