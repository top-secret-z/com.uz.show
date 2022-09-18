<?php
namespace show\acp\form;
use show\data\entry\option\EntryOption;
use show\data\entry\option\EntryOptionAction;
use wcf\data\package\PackageCache;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Shows the entry option edit form.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryOptionEditForm extends EntryOptionAddForm {
	/**
	 * option data
	 */
	public $entryOption;
	public $optionID = 0;
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables(!empty($_POST));
		
		WCF::getTPL()->assign([
				'action' => 'edit',
				'entryOption' => $this->entryOption,
				'optionID' => $this->optionID
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			I18nHandler::getInstance()->setOptions('optionDescription', PackageCache::getInstance()->getPackageID('com.uz.show'), $this->entryOption->optionDescription, 'show.entry.option\d+.description');
			I18nHandler::getInstance()->setOptions('optionTitle', PackageCache::getInstance()->getPackageID('com.uz.show'), $this->entryOption->optionTitle, 'show.entry.option\d+');
			
			$this->defaultValue = $this->entryOption->defaultValue;
			$this->optionType = $this->entryOption->optionType;
			$this->required = $this->entryOption->required;
			$this->selectOptions = $this->entryOption->selectOptions;
			$this->showOrder = $this->entryOption->showOrder;
			$this->tab = $this->entryOption->tab;
			$this->validationPattern = $this->entryOption->validationPattern;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->optionID = intval($_REQUEST['id']);
		$this->entryOption = new EntryOption($this->optionID);
		if (!$this->entryOption->optionID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		// update description
		$this->optionDescription = 'show.entry.option'.$this->entryOption->optionID.'.description';
		if (I18nHandler::getInstance()->isPlainValue('optionDescription')) {
			I18nHandler::getInstance()->remove($this->optionDescription);
			$this->optionDescription = I18nHandler::getInstance()->getValue('optionDescription');
		}
		else {
			I18nHandler::getInstance()->save('optionDescription', $this->optionDescription, 'show.entry', PackageCache::getInstance()->getPackageID('com.uz.show'));
		}
		
		// update title
		$this->optionTitle = 'show.entry.option'.$this->entryOption->optionID;
		if (I18nHandler::getInstance()->isPlainValue('optionTitle')) {
			I18nHandler::getInstance()->remove($this->optionTitle);
			$this->optionTitle = I18nHandler::getInstance()->getValue('optionTitle');
		}
		else {
			I18nHandler::getInstance()->save('optionTitle', $this->optionTitle, 'show.entry', PackageCache::getInstance()->getPackageID('com.uz.show'));
		}
		
		$additionalData = is_array($this->entryOption->additionalData) ? $this->entryOption->additionalData : [];
		if ($this->entryOption->optionType == 'select' && empty($additionalData)) {
			$additionalData['allowEmptyValue'] = true;
		}
		
		// update option
		$this->objectAction = new EntryOptionAction([$this->entryOption], 'update', ['data' => array_merge($this->additionalFields, [
				'defaultValue' => $this->defaultValue,
				'optionDescription' => $this->optionDescription,
				'optionTitle' => $this->optionTitle,
				'optionType' => $this->optionType,
				'required' => $this->required,
				'selectOptions' => $this->selectOptions,
				'showOrder' => $this->showOrder,
				'tab' => $this->tab,
				'validationPattern' => $this->validationPattern,
				'additionalData' => !empty($additionalData) ? serialize($additionalData) : ''
		])]);
		$this->objectAction->executeAction();
		
		$this->saved();
		
		WCF::getTPL()->assign('success', true);
	}
}
