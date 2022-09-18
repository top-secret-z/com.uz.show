<?php
namespace show\acp\page;
use show\data\entry\option\EntryOptionList;
use wcf\page\SortablePage;
use wcf\system\WCF;

/**
 * Shows the list of entry options.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryOptionListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'show.acp.menu.link.show.entry.option.list';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.show.canManageEntryOption'];
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'showOrder';
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = EntryOptionList::class;
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['optionID', 'optionTitle', 'optionType', 'showOrder', 'tab'];
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		$titles[1] = WCF::getLanguage()->get(SHOW_TAB1_TITLE);
		$titles[2] = WCF::getLanguage()->get(SHOW_TAB2_TITLE);
		$titles[3] = WCF::getLanguage()->get(SHOW_TAB3_TITLE);
		$titles[4] = WCF::getLanguage()->get(SHOW_TAB4_TITLE);
		$titles[5] = WCF::getLanguage()->get(SHOW_TAB5_TITLE);
		
		WCF::getTPL()->assign([
				'titles' => $titles
		]);
	}
}
