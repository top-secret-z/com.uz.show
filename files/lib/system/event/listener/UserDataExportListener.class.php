<?php
namespace show\system\event\listener;
use wcf\system\event\listener\IParameterizedEventListener;

/**
 * Exports user data iwa Gdpr.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class UserDataExportListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		// only IP addresses
		$eventObj->data['com.uz.show'] = [
				'ipAddresses' => $eventObj->exportIpAddresses('show'.WCF_N.'_entry', 'ipAddress', 'time', 'userID')
		];
	}
}
