<?php
namespace show\system\page\handler;
use wcf\system\page\handler\AbstractMenuPageHandler;

/**
 * Provides the map page.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class MapPageHandler extends AbstractMenuPageHandler {
	/**
	 * @inheritDoc
	 */
	public function isVisible($objectID = null) {
		if (!SHOW_GEODATA_MAP_ENABLE) return false;
		
		return true;
	}
}
