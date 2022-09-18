<?php
namespace show\acp\form;
use show\data\entry\option\EntryOption;
use show\data\entry\option\EntryOptionAction;
use show\data\entry\option\EntryOptionEditor;
use wcf\data\package\PackageCache;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Shows the entry option add form.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryOptionAddForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'show.acp.menu.link.show';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.show.canManageEntryOption'];
	
	/**
	 * option data
	 */
	public $defaultValue = '';
	public $optionDescription = '';
	public $optionTitle = '';
	public $optionType = 'text';
	public $required = 0;
	public $showOrder = 0;
	public $selectOptions = '';
	public $tab = 1;
	public $validationPattern = '';
	
	/**
	 * types and selects
	 * @var array
	 */
	public static $availableOptionTypes = [
			'boolean',
			'checkboxes',
			'date',
			'integer',
			'float',
			'multiSelect',
			'radioButton',
			'select',
			'text',
			'textarea',
			'URL'
	];
	public static $optionTypesUsingSelectOptions = [
			'checkboxes',
			'multiSelect',
			'radioButton',
			'select'
	];
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables();
		
		WCF::getTPL()->assign([
				'action' => 'add',
				'availableOptionTypes' => self::$availableOptionTypes,
				'defaultValue' => $this->defaultValue,
				'optionTypesUsingSelectOptions' => self::$optionTypesUsingSelectOptions,
				'optionType' => $this->optionType,
				'required' => $this->required,
				'selectOptions' => $this->selectOptions,
				'showOrder' => $this->showOrder,
				'tab' => $this->tab,
				'validationPattern' => $this->validationPattern
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		I18nHandler::getInstance()->register('optionTitle');
		I18nHandler::getInstance()->register('optionDescription');
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		I18nHandler::getInstance()->readValues();
		
		if (I18nHandler::getInstance()->isPlainValue('optionDescription')) $this->optionDescription = I18nHandler::getInstance()->getValue('optionDescription');
		if (I18nHandler::getInstance()->isPlainValue('optionTitle')) $this->optionTitle = I18nHandler::getInstance()->getValue('optionTitle');
		
		if (isset($_POST['defaultValue'])) $this->defaultValue = $_POST['defaultValue'];
		if (isset($_POST['optionType'])) $this->optionType = $_POST['optionType'];
		if (isset($_POST['required'])) $this->required = intval($_POST['required']);
		if (isset($_POST['selectOptions'])) $this->selectOptions = $_POST['selectOptions'];
		if (isset($_POST['showOrder'])) $this->showOrder = intval($_POST['showOrder']);
		if (isset($_POST['tab'])) $this->tab = intval($_POST['tab']);
		if (isset($_POST['validationPattern'])) $this->validationPattern = $_POST['validationPattern'];
		
		if ($this->optionType == 'boolean' || $this->optionType == 'integer') $this->defaultValue = intval($this->defaultValue);
		if ($this->optionType == 'float') $this->defaultValue = floatval($this->defaultValue);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		// validate name
		if (!I18nHandler::getInstance()->validateValue('optionTitle')) {
			if (I18nHandler::getInstance()->isPlainValue('optionTitle')) {
				throw new UserInputException('optionTitle');
			}
			else {
				throw new UserInputException('optionTitle', 'multilingual');
			}
		}
		
		// validate description
		if (!I18nHandler::getInstance()->validateValue('optionDescription', false, true)) {
			throw new UserInputException('optionDescription');
		}
		
		// option type
		if (!in_array($this->optionType, self::$availableOptionTypes)) {
			throw new UserInputException('optionType');
		}
		
		// select options
		if (in_array($this->optionType, self::$optionTypesUsingSelectOptions) && empty($this->selectOptions)) {
			throw new UserInputException('selectOptions');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		// additional data
		$additionalData = [];
		if ($this->optionType == 'select') $additionalData['allowEmptyValue'] = true;
		
		$this->objectAction = new EntryOptionAction([], 'create', ['data' => array_merge($this->additionalFields, [
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
		$returnValues = $this->objectAction->executeAction();
		
		// save i18n values
		$this->saveI18nValue($returnValues['returnValues'], 'optionDescription');
		$this->saveI18nValue($returnValues['returnValues'], 'optionTitle');
		
		$this->saved();
		
		// reset values
		$this->defaultValue = $this->optionDescription = $this->optionTitle = $this->optionType = $this->selectOptions = $this->validationPattern = '';
		$this->optionType = 'text';
		$this->required = $this->showOrder = $this->tab = 0;
		
		I18nHandler::getInstance()->reset();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * Saves i18n values.
	 */
	public function saveI18nValue(EntryOption $entryOption, $columnName) {
		if (!I18nHandler::getInstance()->isPlainValue($columnName)) {
			I18nHandler::getInstance()->save($columnName, 'show.entry.option'.$entryOption->optionID.($columnName == 'description' ? '.description' : ''), 'show.entry', PackageCache::getInstance()->getPackageID('com.uz.show'));
			
			// update
			$editor = new EntryOptionEditor($entryOption);
			$editor->update([$columnName => 'show.entry.option'.$entryOption->optionID.($columnName == 'description' ? '.description' : '')]);
		}
	}
}
