<?php
namespace show\system\box;

/**
 * Box for the tag cloud of an entry.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class TagCloudBoxController extends \wcf\system\box\TagCloudBoxController {
	/**
	 * @inheritDoc
	 */
	protected $objectType = 'com.uz.show.entry';
	
	/**
	 * @inheritDoc
	 */
	protected $neededPermission = 'user.show.canViewEntry';
}
