/**
 * Class and function collection for Show
 * 
 * @author        Udo Zaydowicz
 * @copyright    2018-2022 Zaydowicz.de
 * @license        Zaydowicz Commercial License <https://zaydowicz.de>
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

Show.Entry.Clipboard = Class.extend({
    _categoryID: 0,
    _environment: "",
    _updateHandler: {},
    init: function() {},
    _clipboardAction: function() {}
});

Show.Entry.InlineEditor = WCF.InlineEditor.extend({
    _environment: "",
    _permissions: {},
    _redirectURL: "",
    _updateHandler: {},
    _setOptions: function() {},
    setUpdateHandler: function() {},
    _getTriggerElement: function() {},
    _show: function() {},
    _validate: function() {},
    _execute: function() {},
    _updateEntry: function() {},
    _updateState: function() {},
    _getPermission: function() {},
    setEnvironment: function() {},
    setPermission: function() {},
    setPermissions: function() {},
    _callbacks: {},
    _dropdowns: {},
    _elements: {},
    _notification: {},
    _options: {},
    _proxy: {},
    _triggerElements: {},
    _updateData: {},
    init: function() {},
    _closeAll: function() {},
    registerCallback: function() {},
    _validateCallbacks: function() {},
    _success: function() {},
    _click: function() {},
    _executeCallback: function() {},
    _hide: function() {}
});

Show.Entry.UpdateHandler = Class.extend({
    _entrys: {},
    init: function() {},
    update: function() {},
    _updateProperty: function() {},
    _handleCustomProperty: function() {},
    _delete: function() {},
    _deleteNote: function() {},
    _disable: function() {},
    _enable: function() {},
    _restore: function() {},
    _setAsFeatured: function() {},
    _trash: function() {},
    _unsetAsFeatured: function() {},
    getValue: function() {}
});

Show.Entry.UpdateHandler.Category = Show.Entry.UpdateHandler.extend({
    _delete: function() {},
    _deleteNote: function() {},
    _disable: function() {},
    _enable: function() {},
    _restore: function() {},
    _setAsFeatured: function() {},
    _trash: function() {},
    _unsetAsFeatured: function() {},
    _entrys: {},
    init: function() {},
    update: function() {},
    _updateProperty: function() {},
    _handleCustomProperty: function() {},
    getValue: function() {}
});

Show.Entry.UpdateHandler.Entry = Show.Entry.UpdateHandler.extend({
    update: function() {},
    _delete: function() {},
    _deleteNote: function() {},
    _disable: function() {},
    _enable: function() {},
    _restore: function() {},
    _setAsFeatured: function() {},
    _trash: function() {},
    _unsetAsFeatured: function() {},
    _entrys: {},
    init: function() {},
    _updateProperty: function() {},
    _handleCustomProperty: function() {},
    getValue: function() {}
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

Show.Entry.WatchedEntryList = Class.extend({
    _button: {},
    _markAllCheckbox: {},
    init: function() {},
    _mark: function() {},
    _markAll: function() {},
    _stopWatching: function() {},
    _updateButtonLabel: function() {}
});

Show.Entry.IconUpload = WCF.Upload.extend({
    _deleteEntryIconButton: {},
    _entryID: 0,
    _icon: {},
    _tmpHash: "",
    init: function() {},
    _getParameters: function() {},
    _success: function() {},
    _upload: function() {},
    _getIcon: function() {},
    _getInnerErrorElement: function() {},
    _confirmDeleteIcon: function() {},
    _deleteIcon: function() {},
    _name: "",
    _buttonSelector: {},
    _fileListSelector: {},
    _fileUpload: {},
    _className: "",
    _iframe: {},
    _internalFileID: 0,
    _options: {},
    _uploadMatrix: {},
    _supportsAJAXUpload: true,
    _overlay: {},
    _createButton: function() {},
    _insertButton: function() {},
    _removeButton: function() {},
    _createUploadMatrix: function() {},
    _error: function() {},
    _progress: function() {},
    _initEntry: function() {},
    _showOverlay: function() {},
    _evaluateResponse: function() {},
    _getFilename: function() {}
});

Show.Entry.LabelChooser = WCF.Label.Chooser.extend({
    _labelGroupsToCategories: {},
    init: function() {},
    _updateLabelGroups: function() {},
    _submit: function() {},
    _container: {},
    _groups: {},
    _showWithoutSelection: false,
    _initContainers: function() {},
    _click: function() {},
    _selectLabel: function() {}
});

Show.Entry.AssignLabelHandler = {
    _categoryID: 0,
    _dialog: {},
    _objectIDs: {},
    prepare: function() {},
    _click: function() {},
    _success: function() {}
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
