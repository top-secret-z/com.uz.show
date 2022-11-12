{* default values *}
{if !$enableEditMode|isset}{assign var='enableEditMode' value=true}{/if}
{if !$enableWatchMode|isset}{assign var='enableWatchMode' value=false}{/if}

{foreach from=$objects item=entry}
    <li class="showEntry entry{@$entry->entryID} jsClipboardObject{if $entry->isDeleted} messageDeleted{else}{if $entry->isDisabled} messageDisabled{/if}{/if}" data-entry-id="{@$entry->entryID}" data-element-id="{@$entry->entryID}" data-object-id="{@$entry->entryID}" data-is-deleted="{@$entry->isDeleted}" data-is-disabled="{@$entry->isDisabled}" data-is-featured="{@$entry->isFeatured}">
        <div class="showEntryTile{if $entry->isNew()} new{/if}">
            <a href="{$entry->getLink()}" class="box128">
                <div class="showEntryIconContainer">
                    <span class="showEntryIcon">{@$entry->getIconTag(128)}</span>

                    {if $entry->isFeatured}
                        <span class="badge label green jsLabelFeatured">{lang}show.entry.featured{/lang}</span>
                    {/if}
                </div>

                <div class="showEntryDataContainer">
                    <div class="containerHeadline">
                        <h3 class="showEntrySubject">
                            {if $enableEditMode && $__wcf->session->getPermission('mod.show.canEditEntry')}
                                <label class="jsOnly"><input type="checkbox" class="jsClipboardItem" data-object-id="{@$entry->entryID}"></label>
                            {elseif $enableWatchMode}
                                <label class="jsOnly"><input type="checkbox" class="jsWatchedEntry" data-object-id="{@$entry->entryID}"></label>
                            {/if}
                            <span>{$entry->getSubject()}</span>
                        </h3>
                        <ul class="inlineList dotSeparated showEntryMetaData">
                            <li>{$entry->username}</li>
                            <li>{if SHOW_LAST_CHANGE_TIME}{@$entry->lastChangeTime|time}{else}{@$entry->time|time}{/if}</li>
                        </ul>
                        {hascontent}
                            <ul class="labelList">
                                {content}
                                    {foreach from=$entry->getLabels() item=label}
                                        <li><span class="label badge{if $label->getClassNames()} {$label->getClassNames()}{/if}">{lang}{$label->label}{/lang}</span></li>
                                    {/foreach}
                                {/content}
                            </ul>
                        {/hascontent}
                    </div>

                    {if SHOW_CATEGORY_SHOW}
                        <p class="showEntryCategory">{@$entry->getCategory()->getTitle()}</p>
                    {/if}

                    <div class="containerContent showEntryTeaser">
                        {$entry->getTeaser()}
                    </div>

                    {* leave list delete note in code ufn
                    {if $entry->isDeleted && $entry->getDeleteNote()}
                        <div class="containerContent showEntryDeleteNote">
                            {@$entry->getDeleteNote()}
                        </div>
                    {/if}
                    *}
                </div>
            </a>
            <div class="showEntryFooter">
                {if $enableEditMode && $__wcf->session->getPermission('mod.show.canEditEntry')}
                    <div class="jsOnly showEntryEditLink"><a href="{link controller='EntryEdit' object=$entry application='show'}{/link}" class="jsEntryInlineEditor">{lang}wcf.global.button.edit{/lang}</a></div>
                {elseif $enableWatchMode}
                    <div class="jsOnly showEntryEditLink"><a class="jsSubscribeButton" data-object-id="{@$entry->entryID}" data-object-type="com.uz.show.entry">{lang}wcf.user.objectWatch.manageSubscription{/lang}</a></div>
                {/if}

                <ul class="inlineList showEntryStats">
                    <li>
                        <span class="icon icon16 fa-eye"></span>
                        {lang}show.entry.entryViews{/lang}
                    </li>

                    {if $entry->attachments}
                        <li>
                            <span class="icon icon16 fa-camera"></span>
                            {lang}show.entry.entryAttachments{/lang}
                        </li>
                    {/if}

                    {if $entry->enableComments}
                        <li>
                            <span class="icon icon16 fa-comments"></span>
                            {lang}show.entry.entryComments{/lang}
                        </li>
                    {/if}

                    {event name='entryStats'}

                    {if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike') && $entry->cumulativeLikes}
                        <li>
                            {include file='__topReaction' cachedReactions=$entry->cachedReactions render='short'}
                        </li>
                    {/if}
                </ul>
            </div>
        </div>
    </li>
{/foreach}
