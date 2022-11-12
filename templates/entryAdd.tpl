{capture assign='pageTitle'}{lang}show.entry.{@$action}{/lang}{/capture}

{capture assign='contentTitle'}{lang}show.entry.{@$action}{/lang}{/capture}

{include file='header'}

{if $action == 'add' && !$__wcf->session->getPermission('user.show.canAddEntryWithoutModeration')}
    <p class="info">{lang}show.entry.moderation.info{/lang}</p>
{/if}

{if $action == 'edit' && $entry->userID != $__wcf->user->userID}
    <p class="warning">{lang}show.entry.edit.other{/lang}</p>
{/if}

{include file='formError'}

<form id="messageContainer" class="jsFormGuard" method="post" action="{if $action == 'add'}{link application='show' controller='EntryAdd'}{/link}{else}{link application='show' controller='EntryEdit' id=$entryID}{/link}{/if}">

    <div class="section tabMenuContainer">
        <nav class="tabMenu">
            <ul>
                <li><a href="{@$__wcf->getAnchor('tab1')}">{lang}{SHOW_TAB1_TITLE}{/lang}</a></li>
                {if $tabs[2]}<li><a href="{@$__wcf->getAnchor('tab2')}">{lang}{SHOW_TAB2_TITLE}{/lang}</a></li>{/if}
                {if $tabs[3]}<li><a href="{@$__wcf->getAnchor('tab3')}">{lang}{SHOW_TAB3_TITLE}{/lang}</a></li>{/if}
                {if $tabs[4]}<li><a href="{@$__wcf->getAnchor('tab4')}">{lang}{SHOW_TAB4_TITLE}{/lang}</a></li>{/if}
                {if $tabs[5]}<li><a href="{@$__wcf->getAnchor('tab5')}">{lang}{SHOW_TAB5_TITLE}{/lang}</a></li>{/if}

            </ul>
        </nav>

        <!-- Tab 1 -->
        <div id="tab1" class="tabMenuContent hidden">
            <section class="section">
                <h2 class="sectionTitle">{lang}{SHOW_TAB1_TITLE}{/lang}</h2>

                <dl{if $errorField == 'subject'} class="formError"{/if}>
                    <dt><label for="subject">{lang}wcf.global.title{/lang}</label></dt>
                    <dd>
                        <input type="text" id="subject" name="subject" value="{$subject}" maxlength="255" class="long">
                        {if $errorField == 'subject'}
                            <small class="innerError">
                                {if $errorType == 'empty'}
                                    {lang}wcf.global.form.error.empty{/lang}
                                {elseif $errorType == 'censoredWordsFound'}
                                    {lang}wcf.message.error.censoredWordsFound{/lang}
                                {else}
                                    {lang}show.entry.subject.error.{@$errorType}{/lang}
                                {/if}
                            </small>
                        {/if}
                    </dd>
                </dl>

                <dl{if $errorField == 'categoryID'} class="formError"{/if}>
                    <dt><label for="categoryID">{lang}show.entry.category{/lang}</label></dt>
                    <dd>
                        <select name="categoryID" id="categoryID">
                            <option value="0">{lang}wcf.global.noSelection{/lang}</option>

                            {foreach from=$categoryNodeList item=category}
                                <option value="{@$category->categoryID}"{if $category->categoryID == $categoryID} selected{/if}>{if $category->getDepth() > 1}{@"&nbsp;&nbsp;&nbsp;&nbsp;"|str_repeat:($category->getDepth() - 1)}{/if}{$category->getTitle()}</option>
                            {/foreach}
                        </select>
                        {if $errorField == 'categoryID'}
                            <small class="innerError">
                                {if $errorType == 'empty'}
                                    {lang}wcf.global.form.error.empty{/lang}
                                {elseif $errorType == 'invalid'}
                                    {lang}wcf.global.form.error.noValidSelection{/lang}
                                {else}
                                    {lang}show.entry.categoryID.error.{@$errorType}{/lang}
                                {/if}
                            </small>
                        {/if}
                    </dd>
                </dl>

                {include file='messageFormMultilingualism'}

                {if $labelGroups|count}
                    {foreach from=$labelGroups item=labelGroup}
                        {if $labelGroup|count}
                            <dl{if $errorField == 'label' && $errorType[$labelGroup->groupID]|isset} class="formError"{/if}>
                                <dt><label>{$labelGroup->getTitle()}</label></dt>
                                <dd>
                                    <ul class="labelList jsOnly" data-object-id="{@$labelGroup->groupID}">
                                        <li class="dropdown labelChooser" id="labelGroup{@$labelGroup->groupID}" data-group-id="{@$labelGroup->groupID}" data-force-selection="{if $labelGroup->forceSelection}true{else}false{/if}">
                                            <div class="dropdownToggle" data-toggle="labelGroup{@$labelGroup->groupID}"><span class="badge label">{lang}wcf.label.none{/lang}</span></div>
                                            <div class="dropdownMenu">
                                                <ul class="scrollableDropdownMenu">
                                                    {foreach from=$labelGroup item=label}
                                                        <li data-label-id="{@$label->labelID}"><span><span class="badge label{if $label->getClassNames()} {@$label->getClassNames()}{/if}">{lang}{$label->label}{/lang}</span></span></li>
                                                    {/foreach}
                                                </ul>
                                            </div>
                                        </li>
                                    </ul>
                                    <noscript>
                                        <select name="labelIDs[{@$labelGroup->groupID}]">
                                            {foreach from=$labelGroup item=label}
                                                <option value="{@$label->labelID}">{lang}{$label->label}{/lang}</option>
                                            {/foreach}
                                        </select>
                                    </noscript>
                                    {if $errorField == 'label' && $errorType[$labelGroup->groupID]|isset}
                                        <small class="innerError">
                                            {if $errorType[$labelGroup->groupID] == 'missing'}
                                                {lang}wcf.label.error.missing{/lang}
                                            {else}
                                                {lang}wcf.label.error.invalid{/lang}
                                            {/if}
                                        </small>
                                    {/if}
                                </dd>
                            </dl>
                        {/if}
                    {/foreach}
                {/if}

                {if SHOW_ENTRY_ICON_ENABLE}
                    <dl id="entryIconUpload" class="showEntryIconUpload{if $errorField == 'icon'} formError{/if}">
                        <dt><label for="icon">{lang}show.entry.icon{/lang}</label></dt>
                        <dd>
                            {if $iconLocation}
                                <img src="{$iconLocation}" alt="" id="entryIcon">
                            {/if}
                            <ul class="buttonList">
                                <li>
                                    <div id="entryIconUploadButton"></div>
                                </li>
                                <li>
                                    <button type="button" class="button" id="deleteEntryIcon" {if !$iconLocation} style="display: none;"{/if}>{lang}show.entry.icon.delete{/lang}</button>
                                </li>
                            </ul>
                            {if $errorField == 'icon'}
                                <small class="innerError">
                                    {if $errorType == 'empty'}
                                        {lang}wcf.global.form.error.empty{/lang}
                                    {else}
                                        {lang}show.entry.icon.error.{@$errorType}{/lang}
                                    {/if}
                                </small>
                            {/if}
                            <small>{lang}show.entry.icon.description{/lang}</small>
                        </dd>
                    </dl>
                {/if}

                <dl{if $errorField == 'teaser'} class="formError"{/if}>
                    <dt><label for="teaser">{lang}show.entry.teaser{/lang}</label></dt>
                    <dd>
                        <textarea id="teaser" name="teaser" rows="5" cols="40">{$teaser}</textarea>
                        {if $errorField == 'teaser'}
                            <small class="innerError">
                                {if $errorType == 'empty'}
                                    {lang}wcf.global.form.error.empty{/lang}
                                {elseif $errorType == 'tooLong'}
                                    {lang maxTextLength=SHOW_MAX_TEASER_LENGTH}wcf.message.error.tooLong{/lang}
                                {elseif $errorType == 'censoredWordsFound'}
                                    {lang}wcf.message.error.censoredWordsFound{/lang}
                                {else}
                                    {lang}show.entry.teaser.error.{@$errorType}{/lang}
                                {/if}
                            </small>
                        {/if}
                        <small>{lang}show.entry.teaser.description{/lang}</small>
                    </dd>
                </dl>

                {if MODULE_EDIT_HISTORY && $action == 'edit'}
                    <dl{if $errorField == 'editReason'} class="formError"{/if}>
                        <dt><label for="editReason">{lang}show.entry.editReason{/lang}</label></dt>
                        <dd>
                            <textarea rows="3" cols="40" id="editReason" name="editReason">{$editReason}</textarea>
                            {if $errorField == 'editReason'}
                                <small class="innerError">
                                    {if $errorType == 'empty'}
                                        {lang}wcf.global.form.error.empty{/lang}
                                    {else}
                                        {lang}show.entry.editReason.error.{@$errorType}{/lang}
                                    {/if}
                                </small>
                            {/if}
                        </dd>
                    </dl>
                {/if}

                {if MODULE_TAGGING && $__wcf->session->getPermission('user.show.canSetTags')}{include file='tagInput'}{/if}
            </section>

            {if SHOW_CATEGORY_ENABLE}
                <section class="section">
                    <h2 class="sectionTitle"><label><input type="checkbox" id="enableCategories" name="enableCategories" value="1"{if $enableCategories} checked{/if}> {lang}show.entry.categories.enable{/lang}</label></h2>

                    <div class="jsCategoriesField">
                        {include application='show' file='flexibleCategoryList'}
                    </div>
                    {if $errorField == 'categories'}
                        <small class="innerError">
                            {if $errorType == 'empty'}
                                {lang}wcf.global.form.error.empty{/lang}
                            {else}
                                {lang}show.entry.categories.error.{@$errorType}{/lang}
                            {/if}
                        </small>
                    {/if}
                </section>
            {/if}

            {if GOOGLE_MAPS_API_KEY && SHOW_GEODATA_TYPE != 1}
                <section class="section">
                    {if SHOW_GEODATA_TYPE == 2}
                        <h2 class="sectionTitle"><label><input type="checkbox" id="enableCoordinates" name="enableCoordinates" value="1"{if $enableCoordinates} checked{/if}> {lang}show.entry.geodata.enable{/lang}</label></h2>
                    {/if}
                    {if SHOW_GEODATA_TYPE == 3}
                        <h2 class="sectionTitle"><label>{lang}show.entry.geodata.enter{/lang}</label></h2>
                    {/if}
                    <dl class="wide jsCoordinatesField">
                        <dt></dt>
                        <dd id="mapContainer" class="googleMap"></dd>
                    </dl>

                    <dl class="jsCoordinatesField">
                        <dt><label for="geocode">{lang}show.entry.geodata.location{/lang}</label></dt>
                        <dd>
                            <input type="text" id="geocode" name="geocode" class="long" value="{$geocode}">
                            <small>{lang}show.entry.geodata.location.description{/lang}</small>
                        </dd>
                    </dl>
                </section>
            {/if}

            {if $tab1Options}
                <section class="section">
                    <h2 class="sectionTitle">{lang}show.entry.options{/lang}</h2>

                    {include application='show' file='entryOptionFieldList1'}
                </section>
            {/if}

            <section class="section">
                <h2 class="sectionTitle">{lang}show.entry.message{/lang}</h2>

                <dl class="wide{if $errorField == 'text'} formError{/if}">
                    <dt><label for="text">{lang}show.entry.message{/lang}</label></dt>
                    <dd>
                        <textarea id="text" name="text" rows="20" cols="40">{$text}</textarea>
                        {if $errorField == 'text'}
                            <small class="innerError">
                                {if $errorType == 'empty'}
                                    {lang}wcf.global.form.error.empty{/lang}
                                {elseif $errorType == 'tooLong'}
                                    {lang}wcf.message.error.tooLong{/lang}
                                {elseif $errorType == 'censoredWordsFound'}
                                    {lang}wcf.message.error.censoredWordsFound{/lang}
                                {elseif $errorType == 'disallowedBBCodes'}
                                    {lang}wcf.message.error.disallowedBBCodes{/lang}
                                {else}
                                    {lang}show.entry.message.error.{@$errorType}{/lang}
                                {/if}
                            </small>
                        {/if}
                    </dd>
                </dl>

                {include application='show' file='entryMessageFormTabs' wysiwygContainerID='text'}

                {event name='messageFields'}
            </section>

            {event name='sections'}

            {* {include file='messageFormTabs' wysiwygContainerID='text'} *}
        </div>

        <!-- Tab 2 -->
        {if $tabs[2]}
            <div id="tab2" class="tabMenuContent hidden">
                {if SHOW_TAB2_WYSIWYG}
                    <section class="section">

                        <dl{if $errorField == 'text2'} class="formError"{/if}>
                            <dt><label for="text2">{lang}{SHOW_TAB2_WYSIWYG_TITLE}{/lang}</label></dt>
                            <dd>
                                <textarea id="text2" name="text2" rows="20" cols="40" data-autosave=0 data-disable-media=1>{$text2}</textarea>
                                {if $errorField == 'text2'}
                                    <small class="innerError">
                                        {if $errorType == 'empty'}
                                            {lang}wcf.global.form.error.empty{/lang}
                                        {elseif $errorType == 'tooLong'}
                                            {lang}wcf.message.error.tooLong{/lang}
                                        {elseif $errorType == 'censoredWordsFound'}
                                            {lang}wcf.message.error.censoredWordsFound{/lang}
                                        {elseif $errorType == 'disallowedBBCodes'}
                                            {lang}wcf.message.error.disallowedBBCodes{/lang}
                                        {else}
                                            {lang}show.entry.message.error.{@$errorType}{/lang}
                                        {/if}
                                    </small>
                                {/if}
                            </dd>
                        </dl>
                    </section>
                {/if}

                <section class="section">    
                    {include application='show' file='entryOptionFieldList2'}
                </section>

                {if SHOW_IMAGES_TAB == 2}
                    {include application='show' file='entryAddAttachments'}
                {/if}
            </div>
        {/if}

        <!-- Tab 3 -->
        {if $tabs[2]}
            <div id="tab3" class="tabMenuContent hidden">
                <section class="section">
                    {if SHOW_TAB3_WYSIWYG}
                        <section class="section">
                            <h2 class="sectionTitle">{lang}{SHOW_TAB3_WYSIWYG_TITLE}{/lang}</h2>

                            <dl class="wide{if $errorField == 'text3'} formError{/if}">
                                <dt><label for="text3">{lang}show.entry.text{/lang}</label></dt>
                                <dd>
                                    <textarea id="text3" name="text3" rows="20" cols="40" data-autosave=0 data-disable-media=1>{$text3}</textarea>
                                    {if $errorField == 'text3'}
                                        <small class="innerError">
                                            {if $errorType == 'empty'}
                                                {lang}wcf.global.form.error.empty{/lang}
                                            {elseif $errorType == 'tooLong'}
                                                {lang}wcf.message.error.tooLong{/lang}
                                            {elseif $errorType == 'censoredWordsFound'}
                                                {lang}wcf.message.error.censoredWordsFound{/lang}
                                            {elseif $errorType == 'disallowedBBCodes'}
                                                {lang}wcf.message.error.disallowedBBCodes{/lang}
                                            {else}
                                                {lang}show.entry.message.error.{@$errorType}{/lang}
                                            {/if}
                                        </small>
                                    {/if}
                                </dd>
                            </dl>
                        </section>

                    {/if}

                    {include application='show' file='entryOptionFieldList3'}
                </section>

                {if SHOW_IMAGES_TAB == 3}
                    {include application='show' file='entryAddAttachments'}
                {/if}
            </div>
        {/if}

        <!-- Tab 4 -->
        {if $tabs[4]}
            <div id="tab4" class="tabMenuContent hidden">
                <section class="section">
                    {if SHOW_TAB4_WYSIWYG}
                        <section class="section">
                            <h2 class="sectionTitle">{lang}{SHOW_TAB4_WYSIWYG_TITLE}{/lang}</h2>

                            <dl class="wide{if $errorField == 'text4'} formError{/if}">
                                <dt><label for="text4">{lang}show.entry.text{/lang}</label></dt>
                                <dd>
                                    <textarea id="text4" name="text4" rows="20" cols="40" data-autosave=0 data-disable-media=1>{$text4}</textarea>
                                    {if $errorField == 'text4'}
                                        <small class="innerError">
                                            {if $errorType == 'empty'}
                                                {lang}wcf.global.form.error.empty{/lang}
                                            {elseif $errorType == 'tooLong'}
                                                {lang}wcf.message.error.tooLong{/lang}
                                            {elseif $errorType == 'censoredWordsFound'}
                                                {lang}wcf.message.error.censoredWordsFound{/lang}
                                            {elseif $errorType == 'disallowedBBCodes'}
                                                {lang}wcf.message.error.disallowedBBCodes{/lang}
                                            {else}
                                                {lang}show.entry.message.error.{@$errorType}{/lang}
                                            {/if}
                                        </small>
                                    {/if}
                                </dd>
                            </dl>
                        </section>

                    {/if}

                    {include application='show' file='entryOptionFieldList4'}
                </section>

                {if SHOW_IMAGES_TAB == 4}
                    {include application='show' file='entryAddAttachments'}
                {/if}
            </div>
        {/if}

        <!-- Tab 5 -->
        {if $tabs[5]}
            <div id="tab5" class="tabMenuContent hidden">
                <section class="section">
                    {if SHOW_TAB5_WYSIWYG}
                        <section class="section">
                            <h2 class="sectionTitle">{lang}{SHOW_TAB5_WYSIWYG_TITLE}{/lang}</h2>

                            <dl class="wide{if $errorField == 'text5'} formError{/if}">
                                <dt><label for="text5">{lang}show.entry.text{/lang}</label></dt>
                                <dd>
                                    <textarea id="text5" name="text5" rows="20" cols="40" data-autosave=0 data-disable-media=1>{$text5}</textarea>
                                    {if $errorField == 'text5'}
                                        <small class="innerError">
                                            {if $errorType == 'empty'}
                                                {lang}wcf.global.form.error.empty{/lang}
                                            {elseif $errorType == 'tooLong'}
                                                {lang}wcf.message.error.tooLong{/lang}
                                            {elseif $errorType == 'censoredWordsFound'}
                                                {lang}wcf.message.error.censoredWordsFound{/lang}
                                            {elseif $errorType == 'disallowedBBCodes'}
                                                {lang}wcf.message.error.disallowedBBCodes{/lang}
                                            {else}
                                                {lang}show.entry.message.error.{@$errorType}{/lang}
                                            {/if}
                                        </small>
                                    {/if}
                                </dd>
                            </dl>
                        </section>

                    {/if}

                    {include application='show' file='entryOptionFieldList5'}
                </section>

                {if SHOW_IMAGES_TAB == 5}
                    {include application='show' file='entryAddAttachments'}
                {/if}
            </div>
        {/if}

    </div>

    <div class="formSubmit">
        <input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">

        {if $action == 'edit'}
            {include file='messageFormPreviewButton' previewMessageObjectType='com.uz.show.entry' previewMessageObjectID=$entry->entryID}
        {else}
            {include file='messageFormPreviewButton' previewMessageObjectType='com.uz.show.entry' previewMessageObjectID=0}
        {/if}

        {csrfToken}
        <input type="hidden" name="tmpHash" value="{$tmpHash}">
    </div>
</form>

{if GOOGLE_MAPS_API_KEY && SHOW_GEODATA_TYPE != 1}
    {include file='googleMapsJavaScript'}
{/if}

<script data-relocate="true">
    $(function() {
        WCF.Language.addObject({
            'show.entry.icon.error.invalidExtension':    '{jslang}show.entry.icon.error.invalidExtension{/jslang}',
            'show.entry.icon.error.noImage':            '{jslang}show.entry.icon.error.noImage{/jslang}',
            'show.entry.icon.error.tooSmall':            '{jslang}show.entry.icon.error.tooSmall{/jslang}',
            'show.entry.icon.error.tooLarge':            '{jslang}show.entry.icon.error.tooLarge{/jslang}',
            'show.entry.icon.error.uploadFailed':        '{jslang}show.entry.icon.error.uploadFailed{/jslang}',
            'show.entry.icon.delete.confirmMessage':    '{jslang}show.entry.icon.delete.confirmMessage{/jslang}',
            'wcf.label.none':                            '{jslang}wcf.label.none{/jslang}',
        });

        {if !$labelGroups|empty}
            new Show.Entry.LabelChooser({ {implode from=$labelGroupsToCategories key=__labelCategoryID item=labelGroupIDs}{@$__labelCategoryID}: [ {implode from=$labelGroupIDs item=labelGroupID}{@$labelGroupID}{/implode} ] {/implode} }, { {implode from=$labelIDs key=groupID item=labelID}{@$groupID}: {@$labelID}{/implode} }, '#messageContainer');
        {/if}

        new WCF.Message.FormGuard();

        {if SHOW_ENTRY_ICON_ENABLE}
            new Show.Entry.IconUpload({if $action == 'edit'}{@$entry->entryID}{else}0{/if}, '{$tmpHash}');
        {/if}

        {if GOOGLE_MAPS_API_KEY && SHOW_GEODATA_TYPE != 1}
            $locationInput = new WCF.Location.GoogleMaps.LocationInput('mapContainer', undefined, '#geocode', {if $latitude || $longitude}{@$latitude}, {@$longitude}{else}null, null{/if}, 'show\\data\\entry\\EntryAction');
            {if !$latitude && !$longitude}
                WCF.Location.Util.getLocation($.proxy(function(latitude, longitude) {
                    if (latitude !== undefined && longitude !== undefined) {
                        WCF.Location.GoogleMaps.Util.moveMarker($locationInput.getMarker(), latitude, longitude, true);

                        google.maps.event.trigger($locationInput.getMap().getMap(), 'resize');
                        WCF.Location.GoogleMaps.Util.focusMarker($locationInput.getMarker());
                    }
                }, this));
            {/if}

            google.maps.event.trigger($locationInput.getMap().getMap(), 'resize');
            WCF.Location.GoogleMaps.Util.focusMarker($locationInput.getMarker());
            new Show.Entry.Coordinates.Handler($locationInput);

            {if SHOW_GEODATA_TYPE == 2}
                var $enableCoordinates = $('#enableCoordinates').change(function () {
                    if ($enableCoordinates.is(':checked')) {
                        $('.jsCoordinatesField').show();
                        google.maps.event.trigger($locationInput.getMap().getMap(), 'resize');
                        WCF.Location.GoogleMaps.Util.focusMarker($locationInput.getMarker());
                    }
                    else {
                        $('.jsCoordinatesField').hide();
                    }
                });
                $enableCoordinates.trigger('change');
            {/if}

            {if SHOW_CATEGORY_ENABLE}
                var $enableCategories = $('#enableCategories').change(function () {
                    if ($enableCategories.is(':checked')) {
                        $('.jsCategoriesField').show();
                        google.maps.event.trigger($locationInput.getMap().getMap(), 'resize');
                        WCF.Location.GoogleMaps.Util.focusMarker($locationInput.getMarker());
                    }
                    else {
                        $('.jsCategoriesField').hide();
                    }
                });
                $enableCategories.trigger('change');
            {/if}
        {/if}
    });
</script>

{include file='footer'}

{include file='wysiwyg' wysiwygSelector='text'}
{if $tabs[2] && SHOW_TAB2_WYSIWYG} {include file='wysiwyg' wysiwygSelector='text2'}{/if}
{if $tabs[3] && SHOW_TAB3_WYSIWYG} {include file='wysiwyg' wysiwygSelector='text3'}{/if}
{if $tabs[4] && SHOW_TAB4_WYSIWYG} {include file='wysiwyg' wysiwygSelector='text4'}{/if}
{if $tabs[5] && SHOW_TAB5_WYSIWYG} {include file='wysiwyg' wysiwygSelector='text5'}{/if}
