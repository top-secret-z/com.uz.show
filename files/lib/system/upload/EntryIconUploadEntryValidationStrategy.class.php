<?php
namespace show\system\upload;
use show\data\entry\Entry;
use wcf\system\upload\DefaultUploadFileValidationStrategy;
use wcf\system\upload\UploadFile;

/**
 * Validates uploaded entry icons.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryIconUploadEntryValidationStrategy extends DefaultUploadFileValidationStrategy {
	/**
	 * Creates a new EntryIconUploadEntryValidationStrategy object.
	 */
	public function __construct() {
		parent::__construct(PHP_INT_MAX, ['jpg', 'jpeg', 'png']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate(UploadFile $uploadFile) {
		if (parent::validate($uploadFile)) {
			
			// check if entry is an image
			$imageData = $uploadFile->getImageData();
			if ($imageData === null) {
				$uploadFile->setValidationErrorType('noImage');
				return false;
			}
			
			// check if image is too small
			if ($imageData['height'] < Entry::ICON_SIZE || $imageData['width'] < Entry::ICON_SIZE) {
				$uploadFile->setValidationErrorType('tooSmall');
				return false;
			}
		}
		
		return true;
	}
}
