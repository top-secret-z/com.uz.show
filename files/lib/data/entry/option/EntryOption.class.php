<?php
namespace show\data\entry\option;
use wcf\data\option\Option;
use wcf\system\bbcode\MessageParser;
use wcf\system\bbcode\SimpleMessageParser;
use wcf\system\WCF;
use wcf\util\DateUtil;
use wcf\util\OptionUtil;
use wcf\util\StringUtil;

/**
 * Represents an entry option.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryOption extends Option {
	/**
	 * option value
	 */
	protected $optionValue = '';
	
	/**
	 * @inheritDoc
	 */
	public static function getDatabaseTableAlias() {
		return 'entry_option';
	}
	/**
	 * Returns the formatted value of this option.
	 */
	public function getFormattedOptionValue() {
		switch ($this->optionType) {
			case 'boolean':
				return WCF::getLanguage()->get('show.acp.entry.option.optionType.boolean.'.($this->optionValue ? 'yes' : 'no'));
				
			case 'date':
				$year = $month = $day = 0;
				$optionValue = explode('-', $this->optionValue);
				if (isset($optionValue[0])) $year = intval($optionValue[0]);
				if (isset($optionValue[1])) $month = intval($optionValue[1]);
				if (isset($optionValue[2])) $day = intval($optionValue[2]);
				return DateUtil::format(DateUtil::getDateTimeByTimestamp(gmmktime(12, 1, 1, $month, $day, $year)), DateUtil::DATE_FORMAT);
				
			case 'float':
				return StringUtil::formatDouble(floatval($this->optionValue));
				
			case 'integer':
				return StringUtil::formatInteger(intval($this->optionValue));
				
			case 'radioButton':
			case 'select':
				$selectOptions = OptionUtil::parseSelectOptions($this->selectOptions);
				if (isset($selectOptions[$this->optionValue])) return WCF::getLanguage()->get($selectOptions[$this->optionValue]);
				return '';
				
			case 'multiSelect':
			case 'checkboxes':
				$selectOptions = OptionUtil::parseSelectOptions($this->selectOptions);
				$values = explode("\n", $this->optionValue);
				$result = '';
				foreach ($values as $value) {
					if (isset($selectOptions[$value])) {
						if (!empty($result)) $result .= "<br>\n";
						$result .= WCF::getLanguage()->get($selectOptions[$value]);
					}
				}
				return $result;
				
			case 'textarea':
				return SimpleMessageParser::getInstance()->parse($this->optionValue);
				
			case 'message':
				return MessageParser::getInstance()->parse($this->optionValue);
				
			case 'URL':
				return StringUtil::getAnchorTag($this->optionValue);
				
			default:
				return StringUtil::encodeHTML($this->optionValue);
		}
	}
	
	/**
	 * Returns the value of this option.
	 */
	public function getOptionValue() {
		return $this->optionValue;
	}
	
	/**
	 * Returns true if option is visible
	 */
	public function isVisible() {
		return !$this->isDisabled;
	}
	
	/**
	 * Sets the value of this option.
	 */
	public function setOptionValue($value) {
		$this->optionValue = $value;
	}
	
	/**
	 * Returns the title of this option.
	 */
	public function getOptionTitle() {
		return WCF::getLanguage()->get($this->optionTitle);
	}
	
	/**
	 * Returns the objectID this option.
	 */
	public function getObjectID() {
		return $this->optionID;
	}
}
