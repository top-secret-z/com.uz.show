<?php
namespace show\data\contact;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents an contact.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class Contact extends DatabaseObject {
	/**
	 * Returns true if the active user can edit this contact.
	 */
	public function canEdit() {
		if ($this->userID == WCF::getUser()->userID) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Get user's contact data
	 */
	public static function getContactData($userID, $isDisabled = true) {
		if ($isDisabled) {
			$sql = "SELECT	*
					FROM	show".WCF_N."_contact
					WHERE	userID = ? AND isDisabled = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$userID, 0]);
		}
		else {
			$sql = "SELECT	*
					FROM	show".WCF_N."_contact
					WHERE	userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$userID]);
		}
		
		$row = $statement->fetchArray();
		if(!$row) $row = [];
		
		return new self(null, $row);
	}
	/**
	 * check contact
	 */
	public static function checkContact($userID) {
		if (!SHOW_CONTACT_ENABLE || !WCF::getSession()->getPermission('user.show.canViewContact')) {
			return false;
		}
		
		$data = self::getContactData($userID);
		if (!$data->contactID) {
			return false;
		}
		if (empty($data->name) && empty($data->address) && empty($data->email) && empty($data->website) && empty($data->other)) {
			return false;
		}
		
		return true;
	}
}
