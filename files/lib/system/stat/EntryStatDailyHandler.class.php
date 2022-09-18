<?php
namespace show\system\stat;
use wcf\system\stat\AbstractStatDailyHandler;

/**
 * Stat handler implementation for entrys.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryStatDailyHandler extends AbstractStatDailyHandler {
	/**
	 * @inheritDoc
	 */
	public function getData($date) {
		return [
				'counter' => $this->getCounter($date, 'show'.WCF_N.'_entry', 'time'),
				'total' => $this->getTotal($date, 'show'.WCF_N.'_entry', 'time')
		];
	}
}
