/**
 * Class and function collection for Show
 * 
 * @author        2018-2022 Zaydowicz
 * @license        GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package        com.uz.show
 */

/**
 * Initialize Show namespaces
 */
var Show = { };
Show.Category = { };
Show.Entry = { };
Show.Entry.Coordinates = { };
Show.Map = { };

/**
 * Marks all categories as read.
 */
Show.Category.MarkAllAsRead = Class.extend({
    /**
     * success callback function
     */
    _callback: null,

    /**
     * action proxy
     */
    _proxy: null,

    /**
     * Initializes the class.
     */
    init: function(callback) {
        this._callback = callback;

        this._proxy = new WCF.Action.Proxy({
            success: $.proxy(this._success, this)
        });

        $('.markAllAsReadButton').click($.proxy(this._click, this));
    },

    /**
     * Handles clicks on the 'mark all as read' button.
     */
    _click: function(event) {
        event.preventDefault();

        this._proxy.setOption('data', {
            actionName: 'markAllAsRead',
            className: 'show\\data\\category\\ShowCategoryAction'
        });

        this._proxy.sendRequest();
    },

    /**
     * Marks all categories as read.
     */
    _success: function(data, textStatus, jqXHR) {
        if (this._callback && $.isFunction(this._callback)) {
            return this._callback();
        }

        var $categoryList = $('.nestedCategoryList');

        // remove badges
        $categoryList.find('.badge.badgeUpdate').hide();
        $('.mainMenu .active .badge').hide();

        var notify = new WCF.System.Notification(WCF.Language.get('wcf.global.success'), 'success');
        notify.show();
    }
});

/**
 * Provides extended actions for entry clipboard actions.
 */
Show.Entry.Clipboard = Class.extend({
    /**
     * category id
     */
    _categoryID: 0,

    /**
     * current environment
     */
    _environment: 'category',

    /**
     * entry update handler
     */
    _updateHandler: null,

    /**
     * Initializes the object.
     */
    init: function (updateHandler, environment, categoryID) {
        this._updateHandler = updateHandler;
        this._environment = environment;
        this._categoryID = (categoryID) ? categoryID : 0;

        require(['EventHandler'], function (EventHandler) {
            EventHandler.add('com.woltlab.wcf.clipboard', 'com.uz.show.entry', this._clipboardAction.bind(this));
        }.bind(this));
    },

    /**
     * Reacts to executed clipboard actions.
     */
    _clipboardAction: function (actionData) {
        if (actionData.data.actionName === 'com.uz.show.entry.assignLabel') {
            Show.Entry.AssignLabelHandler.prepare(actionData.data.parameters);
        }
        else if (actionData.responseData && actionData.responseData.returnValues && actionData.responseData.returnValues.entryData) {
            var entryData = actionData.responseData.returnValues.entryData;
            for (var entryID in entryData) {
                if (entryData.hasOwnProperty(entryID)) {
                    this._updateHandler.update(entryID, entryData[entryID]);
                }
            }
        }
    }
});

/**
 * Inline editor for entrys.
 */
Show.Entry.InlineEditor = WCF.InlineEditor.extend({
    /**
     * current editor environment
     */
    _environment: 'entry',

    /**
     * list of permissions
     */
    _permissions: {},

    /**
     * redirect URL
     */
    _redirectURL: '',

    /**
     * entry update handler
     */
    _updateHandler: null,

    /**
     * @see WCF.InlineEditor._setOptions()
     */
    _setOptions: function () {
        this._environment = 'entry';

        this._options = [
            // isDisabled
            {label: WCF.Language.get('show.entry.edit.enable'), optionName: 'enable'},
            {label: WCF.Language.get('show.entry.edit.disable'), optionName: 'disable'},

            // isDeleted
            {label: WCF.Language.get('show.entry.edit.trash'), optionName: 'trash'},
            {label: WCF.Language.get('show.entry.edit.restore'), optionName: 'restore'},
            {label: WCF.Language.get('show.entry.edit.delete'), optionName: 'delete'},

            // divider
            {optionName: 'divider'},

            // isFeatured
            {
                label: WCF.Language.get('show.entry.edit.setAsFeatured'),
                optionName: 'setAsFeatured'
            },
            {
                label: WCF.Language.get('show.entry.edit.unsetAsFeatured'),
                optionName: 'unsetAsFeatured'
            },

            // divider
            {optionName: 'divider'},

            // edit
            {
                label: WCF.Language.get('wcf.global.button.edit'),
                optionName: 'edit',
                isQuickOption: true
            }
        ];
    },

    /**
     * Returns current update handler.
     */
    setUpdateHandler: function (updateHandler) {
        this._updateHandler = updateHandler;
    },

    /**
     * @see WCF.InlineEditor._getTriggerElement()
     */
    _getTriggerElement: function (element) {
        return element.find('.jsEntryInlineEditor');
    },

    /**
     * @see WCF.InlineEditor._show()
     */
    _show: function (event) {
        var $elementID = $(event.currentTarget).data('elementID');

        // build dropdown
        var $trigger = null;
        if (!this._dropdowns[$elementID]) {
            $trigger = this._getTriggerElement(this._elements[$elementID]).addClass('dropdownToggle');
            $trigger.parent().addClass('dropdown');
            this._dropdowns[$elementID] = $('<ul class="dropdownMenu" />').insertAfter($trigger);
        }

        this._super(event);

        if ($trigger !== null) {
            WCF.Dropdown.initDropdown($trigger, true);
        }

        return false;
    },

    /**
     * @see WCF.InlineEditor._validate()
     */
    _validate: function (elementID, optionName) {
        var $entryID = $('#' + elementID).data('entryID');

        switch (optionName) {
            //isDeleted
            case 'delete':
                if (!this._getPermission('canDeleteEntryCompletely')) {
                    return false;
                }

                return (this._updateHandler.getValue($entryID, 'isDeleted'));
                break;
            case 'restore':
                if (!this._getPermission('canRestoreEntry')) {
                    return false;
                }

                return (this._updateHandler.getValue($entryID, 'isDeleted'));
                break;
            case 'trash':
                if (!this._getPermission('canDeleteEntry')) {
                    return false;
                }

                return !(this._updateHandler.getValue($entryID, 'isDeleted'));
                break;

            // isDisabled
            case 'enable':
                if (!this._getPermission('canEnableEntry')) {
                    return false;
                }

                if (this._updateHandler.getValue($entryID, 'isDeleted')) {
                    return false;
                }

                return (this._updateHandler.getValue($entryID, 'isDisabled'));
                break;
            case 'disable':
                if (!this._getPermission('canEnableEntry')) {
                    return false;
                }

                if (this._updateHandler.getValue($entryID, 'isDeleted')) {
                    return false;
                }

                return !(this._updateHandler.getValue($entryID, 'isDisabled'));
                break;

            // isFeatured
            case 'setAsFeatured':
                if (!this._getPermission('canSetAsFeatured')) {
                    return false;
                }

                return !(this._updateHandler.getValue($entryID, 'isFeatured'));
                break;
            case 'unsetAsFeatured':
                if (!this._getPermission('canSetAsFeatured')) {
                    return false;
                }

                return (this._updateHandler.getValue($entryID, 'isFeatured'));
                break;

            // edit
            case 'edit':
                return true;
                break;
        }

        return false;
    },

    /**
     * @see WCF.InlineEditor._execute()
     */
    _execute: function (elementID, optionName) {
        // abort if option is invalid or not accessible
        if (!this._validate(elementID, optionName)) {
            return false;
        }

        switch (optionName) {
            case 'enable':
            case 'disable':
                this._updateEntry(elementID, optionName, {isDisabled: (optionName === 'enable' ? 0 : 1)});
                break;

            case 'delete':
                var self = this;
                WCF.System.Confirmation.show(WCF.Language.get('show.entry.confirmDelete'), function (action) {
                    if (action === 'confirm') {
                        self._updateEntry(elementID, optionName, {deleted: 1});
                    }
                });
                break;

            case 'restore':
                this._updateEntry(elementID, optionName, {isDeleted: 0});
                break;

            case 'trash':
                var self = this;
                WCF.System.Confirmation.show(WCF.Language.get('show.entry.confirmTrash'), function (action) {
                    if (action === 'confirm') {
                        self._updateEntry(elementID, optionName, {
                            isDeleted: 1,
                            reason: $('#wcfSystemConfirmationContent').find('textarea').val()
                        });
                    }
                }, {}, $('<div class="section"><dl><dt><label for="entryDeleteReason">' + WCF.Language.get('show.entry.confirmTrash.reason') + '</label></dt><dd><textarea id="entryDeleteReason" cols="40" rows="4" /></dd></dl></div>'));
                break;

            case 'setAsFeatured':
            case 'unsetAsFeatured':
                this._updateEntry(elementID, optionName, {isFeatured: (optionName === 'setAsFeatured' ? 1 : 0)});
                break;

            case 'edit':
                window.location = this._getTriggerElement($('#' + elementID)).prop('href');
                break;

            default:
                return false;
                break;
        }

        return true;
    },

    /**
     * Updates entry properties.
     */
    _updateEntry: function (elementID, optionName, data) {
        if (optionName === 'delete') {
            var self = this;
            var $entryID = this._elements[elementID].data('entryID');

            new WCF.Action.Proxy({
                autoSend: true,
                data: {
                    actionName:    optionName,
                    className:     'show\\data\\entry\\EntryAction',
                    objectIDs:    [$entryID]
                },
                success: function (data) {
                    self._updateHandler.update($entryID, data.returnValues.entryData[$entryID]);
                }
            });
        }
        else {
            this._updateData.push({
                data:         data,
                elementID:    elementID,
                optionName:    optionName
            });

            this._proxy.setOption('data', {
                actionName:    optionName,
                className:    'show\\data\\entry\\EntryAction',
                objectIDs:    [this._elements[elementID].data('entryID')],
                parameters:    {
                    data: data
                }
            });
            this._proxy.sendRequest();
        }
    },

    /**
     * @see WCF.InlineEditor._updateState()
     */
    _updateState: function(requestData) {
        // redirect user if they may not see deleted entrys
        if (this._environment == 'entry' && this._updateData.length == 1 && this._updateData[0].optionName == 'trash' && !this._getPermission('canViewDeletedEntry')) {
            this._notification.show($.proxy(function () {
                window.location = this._redirectURL;
            }, this));
            return;
        }

        // user feedback
        this._notification.show();

        // update
        for (var $i = 0, $length = this._updateData.length; $i < $length; $i++) {
            var data = this._updateData[$i];
            var entryID = $('#' + data.elementID).data('entryID');
            var updateData = data.data;

            if (data.optionName === 'trash' && requestData && requestData.returnValues && requestData.returnValues.entryData && requestData.returnValues.entryData[entryID]) {
                updateData.deleteNote = requestData.returnValues.entryData[entryID].deleteNote;
            }

            this._updateHandler.update(entryID, updateData);
        }
    },

    /**
     * Returns a specific permission.
     */
    _getPermission: function (permission) {
        if (this._permissions[permission]) {
            return this._permissions[permission];
        }

        return 0;
    },

    /**
     * Sets current environment.
     */
    setEnvironment: function (environment, redirectURL) {
        if (environment !== 'category') {
            environment = 'entry';
        }

        this._environment = environment;
        this._redirectURL = redirectURL;
    },

    /**
     * Sets a permission.
     */
    setPermission: function (permission, value) {
        this._permissions[permission] = value;
    },

    /**
     * Sets permissions.
     */
    setPermissions: function (permissions) {
        for (var $permission in permissions) {
            this.setPermission($permission, permissions[$permission]);
        }
    }
});

/**
 * Provides a generic update handler for entrys.
 */
Show.Entry.UpdateHandler = Class.extend({
    /**
     * entry list
     */
    _entrys: {},

    /**
     * Initializes the entry update handler.
     */
    init: function () {
        var self = this;
        $('.showEntry').each(function (index, entry) {
            var $entry = $(entry);

            self._entrys[$entry.data('objectID')] = $entry;
        });
    },

    /**
     * Updates a set of properties for given entry id.
     */
    update: function (entryID, data) {
        if (!this._entrys[entryID]) {
            console.debug("[Show.Entry.UpdateHandler] Unknown entry id " + entryID);
            return;
        }

        for (var $property in data) {
            this._updateProperty(entryID, $property, data[$property]);
        }
    },

    /**
     * Wrapper for property updating.
     */
    _updateProperty: function (entryID, property, value) {
        switch (property) {
            case 'deleted':
                this._delete(entryID, value);
                break;

            case 'deleteNote':
                this._deleteNote(entryID, value);
                break;

            case 'isDeleted':
                if (value) {
                    this._trash(entryID);
                }
                else {
                    this._restore(entryID);
                }
                break;

            case 'isDisabled':
                if (value) {
                    this._disable(entryID);
                }
                else {
                    this._enable(entryID);
                }
                break;

            case 'isFeatured':
                if (value) {
                    this._setAsFeatured(entryID);
                }
                else {
                    this._unsetAsFeatured(entryID);
                }
                break;

            default:
                this._handleCustomProperty(entryID, property, value);
                break;
        }
    },

    /**
     * Handles custom properties not known
     */
    _handleCustomProperty: function (entryID, property, value) {
        this._entrys[entryID].trigger('entryUpdateHandlerProperty', [entryID, property, value]);
    },

    /**
     * Deletes an entry.
     */
    _delete: function (entryID, link) {
    },

    /**
     * Displays the delete notice.
     */
    _deleteNote: function (entryID, message) {
    },

    /**
     * Disables an entry.
     */
    _disable: function (entryID) {
        this._entrys[entryID].data('isDisabled', 1);
    },

    /**
     * Enables an entry.
     */
    _enable: function (entryID) {
        this._entrys[entryID].data('isDisabled', 0);
    },

    /**
     * Restores an entry.
     */
    _restore: function (entryID) {
        this._entrys[entryID].data('isDeleted', 0);
    },

    /**
     * Sets an entry as featured.
     */
    _setAsFeatured: function (entryID) {
        this._entrys[entryID].data('isFeatured', 1);
    },

    /**
     * Trashes an entry.
     */
    _trash: function (entryID) {
        this._entrys[entryID].data('isDeleted', 1);
    },

    /**
     * Unsets as entry as featured.
     */
    _unsetAsFeatured: function (entryID) {
        this._entrys[entryID].data('isFeatured', 0);
    },

    /**
     * Returns generic property values for an entry.
     */
    getValue: function (entryID, property) {
        if (!this._entrys[entryID]) {
            console.debug("[Show.Entry.UpdateHandler] Unknown entry id " + entryID);
            return;
        }

        switch (property) {
            case 'isDeleted':
                return this._entrys[entryID].data('isDeleted');
                break;

            case 'isDisabled':
                return this._entrys[entryID].data('isDisabled');
                break;

            case 'isFeatured':
                return this._entrys[entryID].data('isFeatured');
                break;
        }
    }
});

/**
 * Entry update handler for entry list on category page.
 */
Show.Entry.UpdateHandler.Category = Show.Entry.UpdateHandler.extend({
    /**
     * @see Show.Entry.UpdateHandler._delete()
     */
    _delete: function (entryID, link) {
        this._entrys[entryID].remove();
        delete this._entrys[entryID];

        WCF.Clipboard.reload();
    },

    /**
     * @see Show.Entry.UpdateHandler._deleteNote()
     */
    _deleteNote: function (entryID, message) {
        // no delete note in list, leave ufn
        // $('<div class="containerContent showEntryDeleteNote">' + message + '</div>').insertAfter(this._entrys[entryID].find('.showEntryTeaser'));
    },

    /**
     * @see Show.Entry.UpdateHandler._disable()
     */
    _disable: function (entryID) {
        this._super(entryID);

        this._entrys[entryID].addClass('messageDisabled');
    },

    /**
     * @see Show.Entry.UpdateHandler._enable()
     */
    _enable: function (entryID) {
        this._super(entryID);

        this._entrys[entryID].removeClass('messageDisabled');
    },

    /**
     * @see Show.Entry.UpdateHandler._restore()
     */
    _restore: function (entryID) {
        this._super(entryID);

        this._entrys[entryID].removeClass('messageDeleted');
        this._entrys[entryID].find('.showEntryDeleteNote').remove();

        var entry = elByClass('entry' + entryID);
        var attr = entry[0].getAttribute('data-is-disabled');
        if (attr == "1") {
            this._entrys[entryID].addClass('messageDisabled');
        }
    },

    /**
     * @see Show.Entry.UpdateHandler._setAsFeatured()
     */
    _setAsFeatured: function (entryID) {
        this._super(entryID);

        $('<span class="badge label green jsLabelFeatured">' + WCF.Language.get('show.entry.featured') + '</span>').appendTo(this._entrys[entryID].find('.showEntryIconContainer'));
    },

    /**
     * @see Show.Entry.UpdateHandler._trash()
     */
    _trash: function (entryID) {
        this._super(entryID);

        this._entrys[entryID].removeClass('messageDisabled');
        this._entrys[entryID].addClass('messageDeleted');
    },

    /**
     * @see Show.Entry.UpdateHandler._unsetAsFeatured()
     */
    _unsetAsFeatured: function (entryID) {
        this._super(entryID);

        this._entrys[entryID].find('.jsLabelFeatured').remove();
    }
});

/**
 * Entry update handler for entry page.
 */
Show.Entry.UpdateHandler.Entry = Show.Entry.UpdateHandler.extend({
    /**
     * @see Show.Entry.UpdateHandler.update()
     */
    update: function (entryID, data) {
        if (this._entrys[entryID]) {
            if (data.isDeleted !== undefined && !data.isDeleted) {
                this._restore(entryID, true);

                delete data.isDeleted;
            }
            if (data.isDisabled !== undefined && !data.isDisabled) {
                this._enable(entryID, true);

                delete data.isDisabled;
            }
        }

        this._super(entryID, data);
    },

    /**
     * @see Show.Entry.UpdateHandler._delete()
     */
    _delete: function (entryID, link) {
        new WCF.PeriodicalExecuter(function (pe) {
            pe.stop();

            window.location = link;
        }, 1000);
    },

    /**
     * @see Show.Entry.UpdateHandler._deleteNote()
     */
    _deleteNote: function (entryID, message) {
        $('<div class="section"><p class="showEntryDeleteNote">' + message + '</p></div>').insertBefore($('#overview .showEntryLikesSummery'));
    },

    /**
     * @see Show.Entry.UpdateHandler._disable()
     */
    _disable: function (entryID) {
        this._super(entryID);

        this._entrys[entryID].addClass('messageDisabled');
    },

    /**
     * @see Show.Entry.UpdateHandler._enable()
     */
    _enable: function (entryID) {
        this._super(entryID);

        this._entrys[entryID].removeClass('messageDisabled');
    },

    /**
     * @see Show.Entry.UpdateHandler._restore()
     */
    _restore: function (entryID) {
        this._super(entryID);

        this._entrys[entryID].removeClass('messageDeleted');
        $('#overview .showEntryDeleteNote').remove();

        var entry = elByClass('entry' + entryID);
        var attr = entry[0].getAttribute('data-is-disabled');
        if (attr == "true") {
            this._entrys[entryID].addClass('messageDisabled');
        }
    },

    /**
     * @see Show.Entry.UpdateHandler._setAsFeatured()
     */
    _setAsFeatured: function (entryID) {
        this._super(entryID);

        $('<span class="badge label green jsLabelFeatured">' + WCF.Language.get('show.entry.featured') + '</span>').prependTo($('.showEntry .contentTitle'));
    },

    /**
     * @see Show.Entry.UpdateHandler._trash()
     */
    _trash: function (entryID) {
        this._super(entryID);

        this._entrys[entryID].addClass('messageDeleted');
    },

    /**
     * @see Show.Entry.UpdateHandler._unsetAsFeatured()
     */
    _unsetAsFeatured: function (entryID) {
        this._super(entryID);

        $('.jsLabelFeatured').remove();
    }
});

/**
 * Provides the entry preview.

 */
Show.Entry.Preview = WCF.Popover.extend({
    /**
     * action proxy
     */
    _proxy: null,

    /**
     * @see WCF.Popover.init()
     */
    init: function() {
        this._super('.showEntryLink');

        this._proxy = new WCF.Action.Proxy({
            showLoadingOverlay: false
        });

        WCF.DOMNodeInsertedHandler.addCallback('Show.Entry.Preview', $.proxy(this._initContainers, this));
    },

    /**
     * @see WCF.Popover._loadContent()
     */
    _loadContent: function() {
        var $link = $('#' + this._activeElementID);
        this._proxy.setOption('data', {
            actionName:    'getEntryPreview',
            className:    'show\\data\\entry\\EntryAction',
            objectIDs:    [ $link.data('entryID') ]
        });

        var $elementID = this._activeElementID;
        var self = this;
        this._proxy.setOption('success', function(data, textStatus, jqXHR) {
            self._insertContent($elementID, data.returnValues.template, true);
        });
        this._proxy.sendRequest();
    }
});

/**
 * Handles unsubscribing multiple entrys.
 */
Show.Entry.WatchedEntryList = Class.extend({
    /**
     * button to stop watching all/marked entrys
     */
    _button: null,

    /**
     * mark all-checkbox
     */
    _markAllCheckbox: null,

    /**
     * Creates a new instance of Show.Entry.WatchedEntryList.
     */
    init: function () {
        this._button = $('#stopWatchingButton').click($.proxy(this._stopWatching, this));
        this._markAllCheckbox = $('.jsMarkAllWatchedEntrys').change($.proxy(this._markAll, this));

        $('.jsWatchedEntry').change($.proxy(this._mark, this));
    },

    /**
     * Handles changing a watched entry checkbox.
     */
    _mark: function (event) {
        $(event.target).parents('tr').toggleClass('jsMarked');

        if (this._markAllCheckbox.is(':checked')) {
            this._markAllCheckbox.prop('checked', false);
        }
        else {
            this._markAllCheckbox.prop('checked', $('.jsWatchedEntry:not(:checked)').length == 0);
        }

        this._updateButtonLabel();
    },

    /**
     * Handles changing the 'mark all' checkbox.
     */
    _markAll: function (event) {
        $('.jsWatchedEntry').prop('checked', this._markAllCheckbox.prop('checked')).parents('tr').toggleClass('jsMarked', this._markAllCheckbox.prop('checked'));

        this._updateButtonLabel();
    },

    /**
     * Handles a click on the stop watching-button.
     */
    _stopWatching: function (event) {
        var $selectedEntrys = $('.jsWatchedEntry:checked');
        var $entryIDs = [];
        var $stopWatchingAll = false;
        if ($selectedEntrys.length) {
            $selectedEntrys.each(function (index, element) {
                $entryIDs.push($(element).data('objectID'));
            });
        }
        else {
            $stopWatchingAll = true;
        }

        var $languageItem = 'show.entry.watchedEntrys.stopWatchingMarked.confirmMessage';
        if ($stopWatchingAll) {
            $languageItem = 'show.entry.watchedEntrys.stopWatchingAll.confirmMessage';
        }

        WCF.System.Confirmation.show(WCF.Language.get($languageItem), function (action) {
            if (action === 'confirm') {
                new WCF.Action.Proxy({
                    autoSend: true,
                    data: {
                        actionName:    'stopWatching',
                        className:    'show\\data\\entry\\EntryAction',
                        parameters:    {
                            stopWatchingAll:    $stopWatchingAll,
                            entryIDs:            $entryIDs
                        }
                    },
                    success: function () {
                        window.location.reload();
                    }
                });
            }
        });
    },

    /**
     * Updates the label of the 'stop watching' button.
     */
    _updateButtonLabel: function () {
        var $selectedEntrys = $('.jsWatchedEntry:checked');

        var $text = '';
        if ($selectedEntrys.length) {
            $text = WCF.Language.get('show.entry.watchedEntrys.stopWatchingMarked', {
                count: $selectedEntrys.length
            });
        }
        else {
            $text = WCF.Language.get('show.entry.watchedEntrys.stopWatchingAll');
        }

        this._button.html($text);
    }
});

/**
 * Handles uploading entry icons.
 */
Show.Entry.IconUpload = WCF.Upload.extend({
    /**
     * button to delete the current entry icon
     */
    _deleteEntryIconButton: null,

    /**
     * id of the entry the uploaded icon belongs to
     */
    _entryID: 0,

    /**
     * icon element
     */
    _icon: null,

    /**
     * temporary hash
     */
    _tmpHash: '',

    /**
     * Initializes a new Show.Entry.IconUpload object.
     */
    init: function (entryID, tmpHash) {
        this._entryID = entryID;
        this._tmpHash = tmpHash;
        this._icon = $('#entryIcon');
        this._deleteEntryIconButton = $('#deleteEntryIcon').click($.proxy(this._confirmDeleteIcon, this));

        this._super($('#entryIconUploadButton'), $('<ul />'), 'show\\data\\entry\\EntryAction', {action: 'uploadIcon'});
    },

    /**
     * @see WCF.Upload. _getParameters()
     */
    _getParameters: function () {
        return {
            entryID: this._entryID,
            tmpHash: this._tmpHash
        };
    },

    /**
     * @see WCF.Upload._success()
     */
    _success: function (uploadID, data) {
        if (data.returnValues.url) {
            // show image
            this._getIcon().show().attr('src', data.returnValues.url + '?timestamp=' + Date.now());

            // hide error
            this._buttonSelector.next('.innerError').remove();

            // show success message
            var $notification = new WCF.System.Notification(WCF.Language.get('wcf.global.success'));
            $notification.show();

            this._deleteEntryIconButton.show();
        }
        else if (data.returnValues.errorType) {
            this._getInnerErrorElement().text(WCF.Language.get('show.entry.icon.error.' + data.returnValues.errorType));
        }
    },

    /**
     * @see WCF.Upload._upload()
     */
    _upload: function () {
        this._super();

        if (this._fileUpload) {
            this._removeButton();
            this._createButton();
        }
    },

    /**
     * Returns the entry icon element.
     */
    _getIcon: function () {
        if (!this._icon.length) {
            this._icon = $('<img src="" alt="" id="entryIcon" />').prependTo($('#entryIconUpload > dd'));
        }

        return this._icon;
    },

    /**
     * Returns the inner error element for the entry icon.
     */
    _getInnerErrorElement: function () {
        var $span = $('#entryIconUploadButton').next('.innerError');
        if (!$span.length) {
            $span = $('<small class="innerError" />').insertAfter($('#entryIconUploadButton'));
        }

        return $span;
    },

    /**
     * Confirms deleting the current entry icon.
     */
    _confirmDeleteIcon: function (event) {
        event.preventDefault();

        WCF.System.Confirmation.show(WCF.Language.get('show.entry.icon.delete.confirmMessage'), $.proxy(function (action) {
            if (action === 'confirm') {
                this._deleteIcon();
            }
        }, this));
    },

    /**
     * Deletes the current entry icon.
     */
    _deleteIcon: function () {
        new WCF.Action.Proxy({
            autoSend: true,
            data: {
                actionName: 'deleteIcon',
                className: 'show\\data\\entry\\EntryAction',
                parameters: this._getParameters()
            }
        });

        this._deleteEntryIconButton.hide();
        this._icon.hide();
    },
});

/**
 * Handles displaying label groups based on the selected categories.
 */
Show.Entry.LabelChooser = WCF.Label.Chooser.extend({
    /**
     * maps the available label group ids to the categories
     */
    _labelGroupsToCategories: null,

    /**
     * Initializes a new Show.Entry.LabelHandler object.
     */
    init: function (labelGroupsToCategories, selectedLabelIDs, containerSelector, submitButtonSelector, showWithoutSelection) {
        this._super(selectedLabelIDs, containerSelector, submitButtonSelector, showWithoutSelection);
        this._labelGroupsToCategories = labelGroupsToCategories;

        this._updateLabelGroups();

        $('#categoryID').change($.proxy(this._updateLabelGroups, this));
    },

    /**
     * Updates the visible label groups based on the selected categories.
     */
    _updateLabelGroups: function () {
        // hide all label choosers first
        $('.labelChooser').each(function (index, element) {
            $(element).parents('dl:eq(0)').hide();
        })

        var visibleGroupIDs = [];
        var categoryID = parseInt($('#categoryID').val());

        if (this._labelGroupsToCategories[categoryID]) {
            for (var i = 0, length = this._labelGroupsToCategories[categoryID].length; i < length; i++) {
                $('#labelGroup' + this._labelGroupsToCategories[categoryID][i]).parents('dl:eq(0)').show();
            }
        }
    },

    /**
     * @see WCF.Label.Chooser._submit()
     */
    _submit: function () {
        // must be on first tab, otherwise open it
        var tab1 = elById('tab1');
        if (tab1.classList.contains('hidden')) {
            var tab = null;
            for (var i = 2; i < 6; i++) {
                tab = elById('tab'+i);
                if (tab && tab.classList.contains('active')) {
                    tab.classList.remove('active');
                    tab.classList.add('hidden');
                    break;
                }
            }

            tab1.classList.add('active');
            tab1.classList.remove('hidden');
        }

        // delete non-selected groups to avoid sumitting these labels
        for (var groupID in this._groups) {
            if (!this._groups[groupID].is(':visible')) {
                delete this._groups[groupID];
            }
        }

        this._super();
    }
});

/**
 * Assigns labels to entrys.
 */
Show.Entry.AssignLabelHandler = {
    /**
     * category id
     */
    _categoryID: 0,

    /**
     * dialog overlay
     */
    _dialog: null,

    /**
     * list of entry ids
     */
    _objectIDs: [],

    /**
     * Shows the assignment form.
     */
    prepare: function (parameters) {
        this._categoryID = parameters.categoryID;
        this._objectIDs = parameters.objectIDs;

        if (this._dialog === null) {
            this._dialog = $('<div />').appendTo(document.body);
            this._dialog.html(parameters.template);
            this._dialog.wcfDialog({
                title: WCF.Language.get('show.entry.edit.assignLabel')
            });
        }
        else {
            this._dialog.html(parameters.template);
            this._dialog.wcfDialog('open');
        }

        this._dialog.find('.formSubmit > .buttonPrimary').click($.proxy(this._click, this));
    },

    /**
     * Handles clicks on the submit button.
     */
    _click: function () {
        var $labelIDs = {};
        this._dialog.find('.labelList > .dropdown').each(function (index, dropdown) {
            var $dropdown = $(dropdown);
            if ($dropdown.data('labelID')) {
                $labelIDs[$dropdown.data('groupID')] = $dropdown.data('labelID');
            }
        });

        new WCF.Action.Proxy({
            autoSend: true,
            data: {
                actionName: 'assignLabel',
                className: 'show\\data\\entry\\EntryAction',
                objectIDs: this._objectIDs,
                parameters: {
                    categoryID: this._categoryID,
                    labelIDs: $labelIDs
                }
            },
            success: $.proxy(this._success, this)
        });
    },

    /**
     * Handles successful AJAX requests.
     */
    _success: function (data, textStatus, jqXHR) {
        var $labels = data.returnValues.labels;

        for (var $i = 0; $i < data.objectIDs.length; $i++) {
            var $column = $('#entry' + data.objectIDs[$i] + ' > td.columnSubject');
            var $labelList = $column.children('.labelList');
            if ($labelList.length) {
                if ($labels.length) {
                    // remove existing labels
                    $labelList.empty();
                }
                else {
                    // remove label list
                    $labelList.remove();
                }
            }
            else if ($labels.length) {
                // create label list
                $labelList = $('<ul class="labelList" />').prependTo($column);
            }

            for (var $j = 0; $j < $labels.length; $j++) {
                var $label = $labels[$j];
                var $listItem = $('<li><a href="' + $label.link + '" class="badge label ' + $label.cssClassName + '">' + WCF.String.escapeHTML($label.label) + '</a></li>').appendTo($labelList);
                $listItem.before(' ');
            }
        }

        this._dialog.wcfDialog('close');
        WCF.Clipboard.reload();

        new WCF.System.Notification().show();
    }
};

/**
 * Appends latitude/longitude to form parameters on submit.
 */
Show.Entry.Coordinates.Handler = Class.extend({
    /**
     * form element
     */
    _form: null,

    /**
     * location input object
     */
    _locationInput: null,

    /**
     * Initializes the class.
     */
    init: function(locationInput) {
        this._locationInput = locationInput;

        this._form = $('#messageContainer').submit($.proxy(this._submit, this));
    },

    /**
     * Handles the submit event.
     */
    _submit: function(event) {
        if (this._form.data('geocodingCompleted')) {
            return true;
        }

        var $location = $.trim($('#geocode').val());
        if (!$location) {
            WCF.Location.GoogleMaps.Util.reverseGeocoding($.proxy(this._reverseGeocoding, this), this._locationInput.getMarker());

            event.preventDefault();
            return false;
        }

        this._setCoordinates();
    },

    /**
     * Performs a reverse geocoding request.
     */
    _reverseGeocoding: function(location) {
        $('#geocode').val(location);

        this._setCoordinates();
        this._form.trigger('submit');
    },

    /**
     * Appends the coordinates to form parameters.
     */
    _setCoordinates: function() {
        var $formSubmit = this._form.find('.formSubmit');
        $('<input type="hidden" name="latitude" value="' + this._locationInput.getMarker().getPosition().lat() + '" />').appendTo($formSubmit);
        $('<input type="hidden" name="longitude" value="' + this._locationInput.getMarker().getPosition().lng() + '" />').appendTo($formSubmit);

        this._form.data('geocodingCompleted', true);
    }
});

/**
 * Handles large map with all entrys.
 */
Show.Map.LargeMap = WCF.Location.GoogleMaps.LargeMap.extend({
    /**
     * @see    WCF.Location.GoogleMaps.Map.init()
     */
    init: function(mapContainerID, mapOptions, actionClassName, locationSearchInputSelector, additionalParameters) {
        if (!mapOptions) mapOptions = {};
        mapOptions.stringifyExcludedObjectIds = true;

        this._super(mapContainerID, mapOptions, actionClassName, locationSearchInputSelector, additionalParameters);
    },

    /**
     * @see    WCF.Location.GoogleMaps.Map._success()
     */
    _success: function(data, textStatus, jqXHR) {
        if (data.returnValues && data.returnValues.markers) {
            for (var $i = 0, $length = data.returnValues.markers.length; $i < $length; $i++) {
                var $markerInfo = data.returnValues.markers[$i];

                this.addMarker($markerInfo.latitude, $markerInfo.longitude, $markerInfo.title, null, $markerInfo.infoWindow, $markerInfo.dialog, $markerInfo.location);

                if ($markerInfo.objectID) {
                    this._objectIDs.push($markerInfo.objectID);
                }
                else if ($markerInfo.objectIDs) {
                    this._objectIDs = this._objectIDs.concat($markerInfo.objectIDs);
                }
            }
        }
    },

    /**
     * @see    WCF.Location.GoogleMaps.LargeMap.addMarker()
     */
    addMarker: function(latitude, longitude, title, icon, information, entryDialog, location) {
        var $information = $(information).get(0);
        var $marker = this._super(latitude, longitude, title, icon, $information);

        return $marker.infoWindow;
    }
});
