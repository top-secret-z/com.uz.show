<?php
namespace show\system\event\listener;
use wcf\system\event\listener\IParameterizedEventListener;

/**
 * Prunes the stored ip addresses.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class ShowPruneIpAddressesCronjobListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		$eventObj->columns['show'.WCF_N.'_entry']['ipAddress'] = 'time';
	}
}
