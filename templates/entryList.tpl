{if $controllerName == 'EntryList'}
    {if !$__wcf->isLandingPage()}
        {capture assign='pageTitle'}{$__wcf->getActivePage()->getTitle()}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}
    {/if}
{elseif $controllerName == 'CategoryEntryList'}
    {capture assign='pageTitle'}{$category->getTitle()}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}
    {capture assign='contentTitle'}{$category->getTitle()}{/capture}
    {capture assign='contentDescription'}{if $category->descriptionUseHtml}{@$category->getDescription()}{else}{$category->getDescription()}{/if}{/capture}
{elseif $controllerName == 'UserEntryList'}
    {capture assign='pageTitle'}{lang}show.entry.userEntrys{/lang}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}
    {capture assign='contentTitle'}{lang}show.entry.userEntrys{/lang}{/capture}
{/if}

{capture assign='headContent'}
    {if !$feedControllerName|empty}
        {if $__wcf->getUser()->userID}
            <link rel="alternate" type="application/rss+xml" title="{lang}wcf.global.button.rss{/lang}" href="{link application='show' controller=$feedControllerName object=$controllerObject appendSession=false}at={@$__wcf->getUser()->userID}-{@$__wcf->getUser()->accessToken}{/link}">
        {else}
            <link rel="alternate" type="application/rss+xml" title="{lang}wcf.global.button.rss{/lang}" href="{link application='show' controller=$feedControllerName object=$controllerObject appendSession=false}{/link}">
        {/if}
    {/if}
    {if $pageNo < $pages}
        <link rel="next" href="{link application='show' controller=$controllerName object=$controllerObject}pageNo={@$pageNo+1}{/link}">
    {/if}
    {if $pageNo > 1}
        <link rel="prev" href="{link application='show' controller=$controllerName object=$controllerObject}{if $pageNo > 2}pageNo={@$pageNo-1}{/if}{/link}">
    {/if}
    <link rel="canonical" href="{link application='show' controller=$controllerName object=$controllerObject}{if $pageNo > 1}pageNo={@$pageNo}{/if}{/link}">
{/capture}

{assign var='additionalLinkParameters' value=''}
{if $labelIDs|count}{capture append='additionalLinkParameters'}{foreach from=$labelIDs key=labelGroupID item=labelID}&labelIDs[{@$labelGroupID}]={@$labelID}{/foreach}{/capture}{/if}

{capture assign='sidebarRight'}
    {if !$labelGroups|empty}
        <form id="sidebarForm" method="post" action="{link application='show' controller=$controllerName object=$controllerObject}{/link}">
            <section class="box">
                <h2 class="boxTitle">{lang}wcf.label.label{/lang}</h2>

                <div class="boxContent">
                    <dl>
                        {foreach from=$labelGroups item=labelGroup}
                            {if $labelGroup|count}
                                <dt>{$labelGroup->getTitle()}</dt>
                                <dd>
                                    <ul class="labelList jsOnly">
                                        <li class="dropdown labelChooser" id="labelGroup{@$labelGroup->groupID}" data-group-id="{@$labelGroup->groupID}">
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
                                        {foreach from=$labelGroups item=labelGroup}
                                            <select name="labelIDs[{@$labelGroup->groupID}]">
                                                <option value="0">{lang}wcf.label.none{/lang}</option>
                                                <option value="-1">{lang}wcf.label.withoutSelection{/lang}</option>
                                                {foreach from=$labelGroup item=label}
                                                    <option value="{@$label->labelID}"{if $labelIDs[$labelGroup->groupID]|isset && $labelIDs[$labelGroup->groupID] == $label->labelID} selected{/if}>{lang}{$label->label}{/lang}</option>
                                                {/foreach}
                                            </select>
                                        {/foreach}
                                    </noscript>
                                </dd>
                            {/if}
                        {/foreach}
                    </dl>
                    <div class="formSubmit">
                        <input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
                    </div>
                </div>
            </section>
        </form>
    {/if}
{/capture}

{capture assign='contentHeaderNavigation'}
    {if $controllerName == 'EntryList' || $controllerName == 'CategoryEntryList' || $controllerName == 'MyEntryList' || $controllerName == 'UserEntryList'}
        {if $__wcf->session->getPermission('user.show.canAddEntry') && $controllerName != 'UserEntryList'}
            <li><a href="{link application='show' controller='EntryAdd'}{/link}" class="button buttonPrimary"><span class="icon icon16 fa-plus"></span> <span>{lang}show.entry.add{/lang}</span></a></li>
        {/if}
    {/if}
{/capture}

{capture assign='contentInteractionPagination'}
    {pages print=true assign=pagesLinks application='show' controller=$controllerName  object=$controllerObject link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$additionalLinkParameters"}
{/capture}

{assign var='linkParameters' value=''}

{capture assign='contentInteractionButtons'}
    {if $items}
        {if $controllerName == 'EntryList' || $controllerName == 'CategoryEntryList' || $controllerName == 'MyEntryList' || $controllerName == 'UserEntryList'}
            {if SHOW_INDEX_STYLE == 1 || SHOW_INDEX_STYLE == 3}
                <div class="contentInteractionButton dropdown jsOnly">
                    <a href="#" class="button small dropdownToggle"><span class="icon icon16 fa-sort-amount-asc"></span> <span>{lang}show.entry.sort{/lang}</span></a>
                    <ul class="dropdownMenu">
                        <li><a href="{link application='show' controller=$controllerName object=$controllerObject}pageNo={@$pageNo}&sortField=subject&sortOrder={if $sortField == 'subject' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}show.entry.sort.subject{/lang}{if $sortField == 'subject'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                        <li><a href="{link application='show' controller=$controllerName object=$controllerObject}pageNo={@$pageNo}&sortField=username&sortOrder={if $sortField == 'username' && $sortOrder == 'DESC'}ASC{else}DESC{/if}{@$linkParameters}{/link}">{lang}show.entry.sort.username{/lang}{if $sortField == 'username'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                        <li><a href="{link application='show' controller=$controllerName object=$controllerObject}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'DESC'}ASC{else}DESC{/if}{@$linkParameters}{/link}">{lang}show.entry.sort.time{/lang}{if $sortField == 'time'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                        <li><a href="{link application='show' controller=$controllerName object=$controllerObject}pageNo={@$pageNo}&sortField=lastChangeTime&sortOrder={if $sortField == 'lastChangeTime' && $sortOrder == 'DESC'}ASC{else}DESC{/if}{@$linkParameters}{/link}">{lang}show.entry.sort.lastChangeTime{/lang}{if $sortField == 'lastChangeTime'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                        <li><a href="{link application='show' controller=$controllerName object=$controllerObject}pageNo={@$pageNo}&sortField=views&sortOrder={if $sortField == 'views' && $sortOrder == 'DESC'}ASC{else}DESC{/if}{@$linkParameters}{/link}">{lang}show.entry.sort.views{/lang}{if $sortField == 'views'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                        <li><a href="{link application='show' controller=$controllerName object=$controllerObject}pageNo={@$pageNo}&sortField=comments&sortOrder={if $sortField == 'comments' && $sortOrder == 'DESC'}ASC{else}DESC{/if}{@$linkParameters}{/link}">{lang}show.entry.sort.comments{/lang}{if $sortField == 'comments'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                        <li><a href="{link application='show' controller=$controllerName object=$controllerObject}pageNo={@$pageNo}&sortField=cumulativeLikes&sortOrder={if $sortField == 'cumulativeLikes' && $sortOrder == 'DESC'}ASC{else}DESC{/if}{@$linkParameters}{/link}">{lang}show.entry.sort.cumulativeLikes{/lang}{if $sortField == 'cumulativeLikes'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>

                        {event name='sortOptions'}
                    </ul>
                </div>
            {/if}
        {/if}
    {/if}

    {if $controllerName == 'CategoryEntryList' && $__wcf->user->userID}
        <a href="#" class="contentInteractionButton jsSubscribeButton jsOnly button small{if $category->isSubscribed()} active{/if}" data-object-type="com.uz.show.category" data-object-id="{@$category->categoryID}"><span class="icon icon16 fa-bookmark{if !$category->isSubscribed()}-o{/if}"></span> <span>{lang}wcf.user.objectWatch.button.subscribe{/lang}</span></a>
        <script data-relocate="true">
            $(function() {
                WCF.Language.addObject({
                    'wcf.user.objectWatch.manageSubscription': '{jslang}wcf.user.objectWatch.manageSubscription{/jslang}'
                });

                new WCF.User.ObjectWatch.Subscribe();
            });
        </script>
    {/if}

    {if $controllerName == 'EntryList' || $controllerName == 'CategoryEntryList' || $controllerName == 'UnreadEntryList'}
        <a href="#" class="markAllAsReadButton contentInteractionButton button small jsOnly"><span class="icon icon16 fa-check"></span> <span>{lang}show.category.markAllAsRead{/lang}</span></a>
    {/if}
{/capture}

{capture assign='contentInteractionDropdownItems'}
    {if !$feedControllerName|empty}
        <li><a rel="alternate" href="{if $__wcf->getUser()->userID}{link application='show' controller=$feedControllerName object=$controllerObject appendSession=false}at={@$__wcf->getUser()->userID}-{@$__wcf->getUser()->accessToken}{/link}{else}{link application='show' controller=$feedControllerName object=$controllerObject appendSession=false}{/link}{/if}" class="rssFeed">{lang}wcf.global.button.rss{/lang}</a></li>
    {/if}
{/capture}

{include file='header'}

{if SHOW_INDEX_STYLE == 1}
    {include file='entryListTile' application='show'}
{/if}

{if SHOW_INDEX_STYLE == 2}
    {include file='entryListList' application='show'}
{/if}

{if SHOW_INDEX_STYLE == 3}
    {include file='entryListGallery' application='show'}
{/if}

<footer class="contentFooter">
    {hascontent}
        <div class="paginationBottom">
            {content}{@$pagesLinks}{/content}
        </div>
    {/hascontent}

    {hascontent}
        <nav class="contentFooterNavigation">
            <ul>
                {content}
                    {if $controllerName == 'EntryList' || $controllerName == 'CategoryEntryList'}
                        {if $__wcf->session->getPermission('user.show.canAddEntry')}
                            <li><a href="{link application='show' controller='EntryAdd'}{/link}" class="button buttonPrimary"><span class="icon icon16 fa-plus"></span> <span>{lang}show.entry.add{/lang}</span></a></li>
                        {/if}
                    {/if}
                    {if $controllerName == 'WatchedEntryList'}
                        {if $objects|count}
                            <li class="jsOnly"><a id="stopWatchingButton" class="button">{lang}show.entry.watchedEntrys.stopWatchingAll{/lang}</a></li>
                        {/if}
                    {/if}

                    {event name='contentFooterNavigation'}
                {/content}
            </ul>
        </nav>
    {/hascontent}
</footer>

{if $controllerName == 'EntryList'}
    {capture assign='footerBoxes'}
        {if SHOW_INDEX_ENABLE_STATS && $__wcf->session->getPermission('user.profile.canViewStatistics')}
            <section class="box">
                <h2 class="boxTitle">{lang}show.index.stats{/lang}</h2>

                <div class="boxContent">
                    {lang}show.index.stats.detail{/lang}
                </div>
            </section>
        {/if}
    {/capture}
{/if}

{if $controllerName == 'WatchedEntryList'}
    <script data-relocate="true">
        $(function() {
            WCF.Language.addObject({
                'wcf.user.objectWatch.manageSubscription':                        '{jslang}wcf.user.objectWatch.manageSubscription{/jslang}',
                'show.entry.watchedEntrys.stopWatchingAll':                        '{jslang}show.entry.watchedEntrys.stopWatchingAll{/jslang}',
                'show.entry.watchedEntrys.stopWatchingAll.confirmMessage':        '{jslang}show.entry.watchedEntrys.stopWatchingAll.confirmMessage{/jslang}',
                'show.entry.watchedEntrys.stopWatchingMarked':                    '{jslang}show.entry.watchedEntrys.stopWatchingMarked{/jslang}',
                'show.entry.watchedEntrys.stopWatchingMarked.confirmMessage':    '{jslang}show.entry.watchedEntrys.stopWatchingMarked.confirmMessage{/jslang}'
            });

            new Show.Entry.WatchedEntryList();
            new WCF.User.ObjectWatch.Subscribe();
        });
    </script>
{else}
    <script data-relocate="true">
        $(function() {
            WCF.Language.addObject({
                'show.entry.edit.assignLabel':        '{jslang}show.entry.edit.assignLabel{/jslang}',
                'show.entry.edit.delete':            '{jslang}show.entry.edit.delete{/jslang}',
                'show.entry.edit.disable':            '{jslang}show.entry.edit.disable{/jslang}',
                'show.entry.edit.enable':            '{jslang}show.entry.edit.enable{/jslang}',
                'show.entry.edit.restore':            '{jslang}show.entry.edit.restore{/jslang}',
                'show.entry.edit.setAsFeatured':    '{jslang}show.entry.edit.setAsFeatured{/jslang}',
                'show.entry.edit.trash':            '{jslang}show.entry.edit.trash{/jslang}',
                'show.entry.edit.unsetAsFeatured':    '{jslang}show.entry.edit.unsetAsFeatured{/jslang}',
                'show.entry.featured':                '{jslang}show.entry.featured{/jslang}',
                'show.entry.confirmDelete':            '{jslang}show.entry.confirmDelete{/jslang}',
                'show.entry.version.confirmDelete':    '{jslang}show.entry.version.confirmDelete{/jslang}',
                'show.entry.confirmTrash':            '{jslang}show.entry.confirmTrash{/jslang}',
                'show.entry.confirmTrash.reason':    '{jslang}show.entry.confirmTrash.reason{/jslang}'
            });

            {if $__wcf->session->getPermission('mod.show.canEditEntry')}
                var $updateHandler = new Show.Entry.UpdateHandler.Category();

                var $inlineEditor = new Show.Entry.InlineEditor('.showEntry');
                $inlineEditor.setEnvironment('category');
                $inlineEditor.setUpdateHandler($updateHandler);
                $inlineEditor.setPermissions({
                    canDeleteEntry:                {@$__wcf->session->getPermission('mod.show.canDeleteEntry')},
                    canDeleteEntryCompletely:    {@$__wcf->session->getPermission('mod.show.canDeleteEntryCompletely')},
                    canEnableEntry:                {@$__wcf->session->getPermission('mod.show.canModerateEntry')},
                    canRestoreEntry:            {@$__wcf->session->getPermission('mod.show.canRestoreEntry')},
                    canSetAsFeatured:            {@$__wcf->session->getPermission('mod.show.canEditEntry')}
                });

                var $entryClipboard = new Show.Entry.Clipboard($updateHandler);
                WCF.Clipboard.init('show\\page\\EntryListPage', {@$hasMarkedItems}, { }, {if !$category|empty}{@$category->categoryID}{else}0{/if});
            {/if}

            {if $controllerName == 'UnreadEntryList'}
                new Show.Category.MarkAllAsRead(function() {
                    window.location = '{link application='show' controller='EntryList'}{/link}';
                });
            {else}
                new Show.Category.MarkAllAsRead();
            {/if}

            {if !$labelGroups|empty}
                WCF.Language.addObject({
                    'wcf.label.none':                '{jslang}wcf.label.none{/jslang}',
                    'wcf.label.withoutSelection':    '{jslang}wcf.label.withoutSelection{/jslang}'
                });

                new WCF.Label.Chooser({ {implode from=$labelIDs key=groupID item=labelID}{@$groupID}: {@$labelID}{/implode} }, '#sidebarForm', undefined, true);
            {/if}
        });
    </script>
{/if}

{include file='footer'}
