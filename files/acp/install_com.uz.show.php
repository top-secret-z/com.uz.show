<?php
use wcf\data\category\CategoryEditor;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\box\BoxHandler;
use wcf\system\WCF;

/**
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */

// add default category
$sql = "SELECT	objectTypeID
		FROM	wcf".WCF_N."_object_type
		WHERE	definitionID = ? AND objectType = ?";
$statement = WCF::getDB()->prepareStatement($sql, 1);
$statement->execute([ObjectTypeCache::getInstance()->getDefinitionByName('com.woltlab.wcf.category')->definitionID, 'com.uz.show.category']);

CategoryEditor::create([
		'objectTypeID' => $statement->fetchColumn(),
		'title' => 'Default Category',
		'time' => TIME_NOW
]);

// assign box 'com.woltlab.wcf.UsersOnline' to EntryListPage = top menu
BoxHandler::getInstance()->addBoxToPageAssignments('com.woltlab.wcf.UsersOnline', ['com.uz.show.EntryList']);
