<?php
namespace show\system\stat;
use wcf\system\stat\AbstractCommentStatDailyHandler;

/**
 * Stat handler implementation for show comments.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class CommentStatDailyHandler extends AbstractCommentStatDailyHandler {
	/**
	 * @inheritDoc
	 */
	protected $objectType = 'com.uz.show.entryComment';
}
