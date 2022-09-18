<?php
namespace show\system\cache\builder;
use wcf\system\cache\builder\AbstractCacheBuilder;
use wcf\system\WCF;

/**
 * Caches the show statistics.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class StatsCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	protected $maxLifetime = 1200;
	
	/**
	 * @inheritDoc
	 */
	protected function rebuild(array $parameters) {
		$data = [];
		
		// number of entry
		$sql = "SELECT	COUNT(*) AS count, SUM(views) AS views
				FROM	show".WCF_N."_entry";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$row = $statement->fetchSingleRow();
		$data['entrys'] = $row['count'];
		$data['views'] = $row['views'];
		
		// number of comments
		$sql = "SELECT	SUM(comments)
				FROM	show".WCF_N."_entry
				WHERE	comments > 0";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$data['comments'] = $statement->fetchSingleColumn();
		
		// number of authors
		$sql = "SELECT	COUNT(DISTINCT userID)
				FROM	show".WCF_N."_entry";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$data['authors'] = $statement->fetchSingleColumn();
		
		// views per day
		$days = ceil((TIME_NOW - SHOW_INSTALL_DATE) / 86400);
		if ($days <= 0) $days = 1;
		$data['viewsPerDay'] = $data['views'] / $days;
		
		return $data;
	}
}
