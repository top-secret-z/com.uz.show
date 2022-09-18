<?php
namespace show\page;
use show\data\entry\FeedEntryList;
use wcf\page\AbstractFeedPage;
use wcf\system\WCF;

/**
 * Shows entrys in feed.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryListFeedPage extends AbstractFeedPage {
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['user.show.canViewEntry'];
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'supportsEnclosure' => true
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// read the entrys
		$this->items = new FeedEntryList();
		$this->items->sqlLimit = 20;
		$this->items->readObjects();
		$this->title = WCF::getLanguage()->get('show.entry.entrys');
	}
}
