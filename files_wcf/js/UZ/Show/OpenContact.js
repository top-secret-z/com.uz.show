/**
 * Opens contact data.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Ajax", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Ui/Dialog"], function (require, exports, tslib_1, Ajax, Language, Dialog_1) {
	"use strict";
	Object.defineProperty(exports, "__esModule", { value: true });
	exports.init = void 0;
	
	Ajax = tslib_1.__importStar(Ajax);
	Language = tslib_1.__importStar(Language);
	Dialog_1 = tslib_1.__importDefault(Dialog_1);
	
	class UZShowOpenContact {
		constructor() {
			var button = document.querySelector('.jsOpenContact');
			button.addEventListener("click", (ev) => this._showDialog(ev));
		}
		
		_showDialog(event) {
			event.preventDefault();
			
			var userID = event.currentTarget.dataset.objectId;
			
			Ajax.api(this, {
				actionName:	'openContact',
				parameters:	{
					userID:	userID
				}
			});
		}
		
		_ajaxSuccess(data) {
			this._render(data);
		}
		
		_render(data) {
			Dialog_1.default.open(this, data.returnValues.template);
		}
		
		_ajaxSetup() {
			return {
				data: {
					className: 'show\\data\\entry\\EntryAction'
				}
			};
		}
		
		_dialogSetup() {
			return {
				id: 		'ContactData',
				options: 	{ title: Language.get('show.contact.dialog') },
				source: 	null
			};
		}
	}
	
	let uZShowOpenContact;
	function init() {
		if (!uZShowOpenContact) {
			uZShowOpenContact = new UZShowOpenContact();
		}
	}
	exports.init = init;
});
