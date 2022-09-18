<?php
namespace show\form;
use show\data\contact\Contact;
use show\data\contact\ContactAction;
use wcf\form\AbstractForm;
use wcf\system\message\censorship\Censorship;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\menu\user\UserMenu;
use wcf\system\WCF;
use wcf\util\StringUtil;
use wcf\util\UserUtil;

/**
 * Form to edit contact.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class ShowContactForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $loginRequired = true;
	
	/**
	 * @inheritDoc
	 */
	//public $templateName = 'showContact';
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['SHOW_CONTACT_ENABLE'];
	
	/**
	 * data
	 */
	public $isDisabled;
	public $contact;
	public $name;
	public $address;
	public $email;
	public $website;
	public $other;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!SHOW_CONTACT_ENABLE || !WCF::getSession()->getPermission('user.show.canAddEntry')) {
			throw new PermissionDeniedException();
		}
		
		$this->contact = Contact::getContactData(WCF::getUser()->userID, false);
		
		if ($this->contact->contactID) {
			$this->isDisabled = $this->contact->isDisabled;
			$this->name = $this->contact->name;
			$this->address = $this->contact->address;
			$this->other = $this->contact->other;
			$this->email = $this->contact->email;
			$this->website = $this->contact->website;
		}
		else {
			$this->contact = null;
		}
		
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		$this->isDisabled = 0;
		if (isset($_POST['isDisabled'])) $this->isDisabled = 1;
		if (isset($_POST['name'])) $this->name = StringUtil::trim($_POST['name']);
		if (isset($_POST['address'])) $this->address = StringUtil::trim($_POST['address']);
		if (isset($_POST['other'])) $this->other = StringUtil::trim($_POST['other']);
		if (isset($_POST['email'])) $this->email = StringUtil::trim($_POST['email']);
		if (isset($_POST['website'])) $this->website = StringUtil::trim($_POST['website']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'contact' => $this->contact,
				'isDisabled' => $this->isDisabled,
				'name' => $this->name,
				'address' => $this->address,
				'other' => $this->other,
				'email' => $this->email,
				'website' => $this->website
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		// search for censored words
		$result = Censorship::getInstance()->test($this->name);
		if ($result) {
			WCF::getTPL()->assign('censoredWords', $result);
			throw new UserInputException('name', 'censoredWordsFound');
		}
		$result = Censorship::getInstance()->test($this->address);
		if ($result) {
			WCF::getTPL()->assign('censoredWords', $result);
			throw new UserInputException('address', 'censoredWordsFound');
		}
		$result = Censorship::getInstance()->test($this->other);
		if ($result) {
			WCF::getTPL()->assign('censoredWords', $result);
			throw new UserInputException('other', 'censoredWordsFound');
		}
		
		// name
		if (!empty($this->name)) {
			if (mb_strlen($this->name) > 255) {
				throw new UserInputException('name', 'tooLong');
			}
			
			$result = Censorship::getInstance()->test($this->name);
			if ($result) {
				WCF::getTPL()->assign('censoredWords', $result);
				throw new UserInputException('name', 'censoredWordsFound');
			}
		}
		
		// address
		if (!empty($this->address)) {
			if (mb_strlen($this->address) > 65000) {
				throw new UserInputException('address', 'tooLong');
			}
			
			$result = Censorship::getInstance()->test($this->address);
			if ($result) {
				WCF::getTPL()->assign('censoredWords', $result);
				throw new UserInputException('address', 'censoredWordsFound');
			}
		}
		
		// email
		if (!empty($this->email)) {
			if (mb_strlen($this->email) > 255) {
				throw new UserInputException('email', 'tooLong');
			}
			
			if (!UserUtil::isValidEmail($this->email)) {
				throw new UserInputException('email', 'invalid');
			}
		}
		
		// website
		if (!empty($this->website)) {
			if (mb_strlen($this->website) > 255) {
				throw new UserInputException('website', 'tooLong');
			}
			
			if (!preg_match('~^(https?|ftps?)://~', $this->website)) {
				$this->website = 'http://' . $this->website;
			}
			
			if (filter_var($this->website, FILTER_VALIDATE_URL) === false) {
				throw new UserInputException('website', 'invalid');
			}
		}
		
		// other
		if (!empty($this->other)) {
			if (mb_strlen($this->other) > 65000) {
				throw new UserInputException('other', 'tooLong');
			}
			
			$result = Censorship::getInstance()->test($this->other);
			if ($result) {
				WCF::getTPL()->assign('censoredWords', $result);
				throw new UserInputException('other', 'censoredWordsFound');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		$data = array_merge($this->additionalFields, [
				'userID' => WCF::getUser()->userID,
				'isDisabled' => $this->isDisabled,
				'name' => $this->name,
				'address' => $this->address,
				'other' => $this->other,
				'email' => $this->email,
				'website' => $this->website
		]);
		
		if (!$this->contact) {
			$this->objectAction = new ContactAction([], 'create', ['data' => $data]);
			$this->contact = $this->objectAction->executeAction()['returnValues'];
		}
		else {
			$this->objectAction = new ContactAction([$this->contact], 'update',  ['data' => $data]);
			$this->objectAction->executeAction();
		}
		
		$this->saved();
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() {
		// set active tab
		UserMenu::getInstance()->setActiveMenuItem('wcf.user.menu.show.contact');
		
		parent::show();
	}
}
