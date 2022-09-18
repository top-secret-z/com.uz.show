<?php
namespace show\system\menu\user;
use wcf\system\menu\user\DefaultUserMenuItemProvider;
use wcf\system\WCF;

/**
 * UserMenuItemProvider for Show contact.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class ShowContactMenuItemProvider extends DefaultUserMenuItemProvider {
	/**
	 * @inheritDoc
	 */
	public function isVisible() {
		if (!SHOW_CONTACT_ENABLE || !WCF::getSession()->getPermission('user.show.canAddEntry')) {
			return false;
		}
		
		return true;
	}
}
