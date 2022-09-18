<?php
namespace show\data\entry;
use wcf\data\search\ISearchResultObject;
use wcf\system\search\SearchResultTextParser;

/**
 * Represents a show search result.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class SearchResultEntry extends ViewableEntry implements ISearchResultObject {
	/**
	 * @inheritDoc
	 */
	public function getContainerLink() {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getContainerTitle() {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getFormattedMessage() {
		return SearchResultTextParser::getInstance()->parse($this->getDecoratedObject()->getSimplifiedFormattedMessage());
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink($query = '') {
		return $this->getDecoratedObject()->getLink();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectTypeName() {
		return 'com.uz.show.entry';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSubject() {
		return $this->getDecoratedObject()->getSubject();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTime() {
		return $this->time;
	}
}
