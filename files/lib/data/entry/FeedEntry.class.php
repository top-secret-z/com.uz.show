<?php
namespace show\data\entry;
use wcf\data\IFeedEntryWithEnclosure;
use wcf\data\TUserContent;
use wcf\system\feed\enclosure\FeedEnclosure;
use wcf\system\request\LinkHandler;

/**
 * Represents a viewable entry for RSS feeds.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class FeedEntry extends ViewableEntry implements IFeedEntryWithEnclosure {
	use TUserContent;
	
	/**
	 * @var FeedEnclosure
	 */
	protected $enclosure;
	
	/**
	 * @inheritdoc
	 */
	public function __toString() {
		return $this->getDecoratedObject()->__toString();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getCategories() {
		$categories = [];
		$category = $this->getDecoratedObject()->getCategory();
		if ($category !== null) {
			$categories[] = $category->getTitle();
			foreach ($category->getParentCategories() as $category) {
				$categories[] = $category->getTitle();
			}
		}
		
		return $categories;
	}
	
	/**
	 * @inheritdoc
	 */
	public function getComments() {
		return $this->comments;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEnclosure() {
		if ($this->enclosure === null) {
			if ($this->iconHash) {
				$MIMEType = 'image/jpeg';
				switch ($this->iconExtension) {
					case 'gif':
						$MIMEType = 'image/gif';
						break;
					case 'png':
						$MIMEType = 'image/png';
						break;
				}
				
				$this->enclosure = new FeedEnclosure($this->getIconURL(), $MIMEType, @filesize($this->getIconLocation()));
			}
		}
		
		return $this->enclosure;
	}
	
	/**
	 * @inheritdoc
	 */
	public function getExcerpt($maxLength = 255) {
		return $this->getDecoratedObject()->getExcerpt($maxLength);
	}
	
	/**
	 * @inheritdoc
	 */
	public function getFormattedMessage() {
		return $this->getDecoratedObject()->getFormattedMessage();
	}
	
	/**
	 * @inheritdoc
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('Entry', [
				'application' => 'show',
				'object' => $this->getDecoratedObject(),
				'appendSession' => false,
				'encodeTitle' => true
		]);
	}
	
	/**
	 * @inheritdoc
	 */
	public function getMessage() {
		return $this->getDecoratedObject()->getMessage();
	}
	
	/**
	 * @inheritdoc
	 */
	public function getTitle() {
		return $this->getDecoratedObject()->getTitle();
	}
	
	/**
	 * @inheritdoc
	 */
	public function isVisible() {
		return $this->canRead();
	}
}
