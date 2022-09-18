<?php
namespace show\system\event\listener;
use show\system\cache\builder\StatsCacheBuilder;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\WCF;

/**
 * Adds the show stats in the statistics box.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class StatisticsBoxControllerListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		WCF::getTPL()->assign([
				'showStatistics' => StatsCacheBuilder::getInstance()->getData()
		]);
	}
}
