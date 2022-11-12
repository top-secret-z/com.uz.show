{if $items}
    {if !$enableEditMode|isset}{assign var='enableEditMode' value=true}{/if}
    {if !$enableWatchMode|isset}{assign var='enableWatchMode' value=false}{/if}

    {if $controllerName == 'WatchedEntryList'}
        {assign var='enableEditMode' value=false}
        {assign var='enableWatchMode' value=true}
    {/if}

    <div class="section tabularBox messageGroupList jsClipboardContainer" data-type="com.uz.show.entry">
        <ol class="tabularList">
            <li class="tabularListRow tabularListRowHead">
                <ol class="tabularListColumns">
                    {if $enableEditMode && $__wcf->session->getPermission('mod.show.canEditEntry')}
                        <li>&nbsp;</li>
                        <li>&nbsp;</li>
                    {/if}
                    <li class="columnSort">
                        <ul class="inlineList">
                            <li>
                                <a rel="nofollow" href="{link application='show' controller=$controllerName object=$controllerObject}pageNo={@$pageNo}&sortField={$sortField}&sortOrder={if $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$additionalLinkParameters}{/link}">
                                    <span class="icon icon16 fa-sort-amount-{$sortOrder|strtolower} jsTooltip" title="{lang}show.entry.sort{/lang} ({lang}wcf.global.sortOrder.{if $sortOrder === 'ASC'}ascending{else}descending{/if}{/lang})"></span>
                                </a>
                            </li>
                            <li>
                                <div class="dropdown">
                                    <span class="dropdownToggle">{lang}show.entry.sort.{$sortField}{/lang}</span>

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
                            </li>
                        </ul>
                    </li>

                </ol>
            </li>

            {foreach from=$objects item=entry}
                <li class="tabularListRow">
                    <ol id="entry{@$entry->entryID}" class="showEntry entry{@$entry->entryID} tabularListColumns messageGroup jsClipboardObject{if $entry->isNew()} new{/if}{if $entry->isDeleted} messageDeleted{else}{if $entry->isDisabled} messageDisabled{/if}{/if}" data-entry-id="{@$entry->entryID}" data-element-id="{@$entry->entryID}" data-object-id="{@$entry->entryID}" data-is-deleted="{@$entry->isDeleted}" data-is-disabled="{@$entry->isDisabled}" data-is-featured="{@$entry->isFeatured}">

                        {if $enableEditMode && $__wcf->session->getPermission('mod.show.canEditEntry')}
                            <li class="columnMark jsOnly">
                                <label><input type="checkbox" class="jsClipboardItem" data-object-id="{@$entry->entryID}"></label>
                            </li>
                        {/if}
                        <li class="columnIcon columnAvatar">
                            <div>
                                <p>{@$entry->getIconTag(48)}</p>

                                {event name='icons'}
                            </div>
                        </li>

                        <li class="columnSubject">
                            <h3>
                                <span class="showEntryIconContainer">{* to place featured label *}</span>
                                {if $entry->isFeatured}
                                    <span class="badge label green jsLabelFeatured">{lang}show.entry.featured{/lang}</span>
                                {/if}

                                <span class="messageGroupLink"><a href="{$entry->getLink()}">{$entry->getSubject()}</a></span>
                            </h3>

                            {if $entry->hasLabels()}
                                <ul class="labelList">
                                    {foreach from=$entry->getLabels() item=label}
                                        <li><span class="label badge{if $label->getClassNames()} {$label->getClassNames()}{/if}">{lang}{$label->label}{/lang}</span></li>
                                    {/foreach}
                                </ul>
                            {/if}

                            <aside class="statusDisplay">
                                <ul class="inlineList statusIcons">
                                    {if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike') && $entry->cumulativeLikes}{include file='__topReaction' cachedReactions=$entry->cachedReactions render='short'}{/if}
                                    {if $entry->isSubscribed()}<li><span class="icon icon16 fa-bookmark jsTooltip jsSubscribeButton" title="{lang}show.entry.subscribed{/lang}" data-object-id="{@$entry->entryID}" data-object-type="com.uz.show.entry" data-remove-on-unsubscribe="true"></span></li>{/if}

                                    {event name='statusIcons'}
                                </ul>
                            </aside>

                            <ul class="inlineList dotSeparated small messageGroupInfo">
                                <li class="messageGroupAuthor">{if $entry->userID}{user object=$entry->getUserProfile()}{else}{$entry->username}{/if}</li>
                                <li class="messageGroupTime">{if SHOW_LAST_CHANGE_TIME}{@$entry->lastChangeTime|time}{else}{@$entry->time|time}{/if}</li>
                                {if SHOW_CATEGORY_SHOW}
                                    <li class="showMessageGroupCategory">{@$entry->getCategory()->getTitle()}</li>
                                {/if}
                                {if $enableEditMode && $__wcf->session->getPermission('mod.show.canEditEntry')}
                                    <li class="showEntryEditLink messageGroupEditLink jsOnly"><a href="{link controller='EntryEdit' object=$entry application='show'}{/link}" class="jsEntryInlineEditor">{lang}wcf.global.button.edit{/lang}</a></li>
                                {/if}

                                {event name='messageGroupInfo'}
                            </ul>
                        </li>

                        <li class="columnStats">
                            <dl class="plain statsDataList">
                                <dt>{@$entry->attachments}</dt>
                                <dd><span class="icon icon16 fa-camera"></span></dd>
                            </dl>
                            <dl class="plain statsDataList">
                                <dt>{@$entry->comments}</dt>
                                <dd><span class="icon icon16 fa-comment-o"></span></dd>
                            </dl>

                            {event name='statsData'}

                            <div class="messageGroupListStatsSimple">{if $entry->views}<br><span class="icon icon16 fa-eye"></span> {@$entry->views|shortUnit}{/if}</div>
                        </li>

                        {event name='columns'}
                    </ol>
                </li>
            {/foreach}
        </ol>
    </div>
{else}
    <p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}
