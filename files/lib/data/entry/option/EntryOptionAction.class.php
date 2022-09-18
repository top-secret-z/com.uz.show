<?php
namespace show\data\entry\option;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;

/**
 * Executes entry option-related actions.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryOptionAction extends AbstractDatabaseObjectAction implements IToggleAction {
	/**
	 * @inheritDoc
	 */
	protected $className = EntryOptionEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsCreate = ['admin.show.canManageEntryOption'];
	protected $permissionsDelete = ['admin.show.canManageEntryOption'];
	protected $permissionsUpdate = ['admin.show.canManageEntryOption'];
	
	/**
	 * @inheritDoc
	 */
	public function validateToggle() {
		$this->validateUpdate();
	}
	
	/**
	 * @inheritDoc
	 */
	public function toggle() {
		foreach ($this->getObjects() as $optionEditor) {
			$optionEditor->update([
					'isDisabled' => 1 - $optionEditor->isDisabled
			]);
		}
	}
}
