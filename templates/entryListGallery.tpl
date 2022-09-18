<div class="section">
	{if $items}
		<div class="jsClipboardContainer" data-type="com.uz.show.entry">
			{if $controllerName == 'WatchedEntryList'}
				{assign var='enableEditMode' value=false}
				{assign var='enableWatchMode' value=true}
			{/if}
			
			<ul class="showEntryListGallery">
				{* default values *}
				{if !$enableEditMode|isset}{assign var='enableEditMode' value=true}{/if}
				{if !$enableWatchMode|isset}{assign var='enableWatchMode' value=false}{/if}
				
				{foreach from=$objects item=entry}
					<li class="showEntry entry{@$entry->entryID} jsClipboardObject{if $entry->isDeleted} messageDeleted{else}{if $entry->isDisabled} messageDisabled{/if}{/if}" data-entry-id="{@$entry->entryID}" data-element-id="{@$entry->entryID}" data-object-id="{@$entry->entryID}" data-is-deleted="{@$entry->isDeleted}" data-is-disabled="{@$entry->isDisabled}" data-is-featured="{@$entry->isFeatured}">
						<a href="{$entry->getLink()}">{if $entry->attachmentID}<img src="{link controller='Attachment' id=$entry->attachmentID}{/link}" alt="">{else}<img src="{$entry->getDefaultImageURL()}" alt="">{/if}</a>
						
						<div>
							{if $enableEditMode && $entry->canEdit()}
								<label><input type="checkbox" class="jsClipboardItem" data-object-id="{@$entry->entryID}"></label>
							{/if}
							
							<p>
								<span class="showEntryIconContainer">{* to place featured label *}</span>
								{if $entry->isFeatured}
									<span class="badge label green jsLabelFeatured">{lang}show.entry.featured{/lang}</span>
								{/if}
								<a href="{$entry->getLink()}">{$entry->getSubject()}</a>
							</p>
							
							<div>
								<ul class="inlineList dotSeparated small">
									<li>{if $entry->userID}{user object=$entry->getUserProfile()}{else}{$entry->username}{/if}</li>
									<li>{if SHOW_LAST_CHANGE_TIME}{@$entry->lastChangeTime|time}{else}{@$entry->time|time}{/if}</li>
									{if $enableEditMode && $__wcf->session->getPermission('mod.show.canEditEntry')}
										<li class="jsOnly"><a href="{link controller='EntryEdit' object=$entry application='show'}{/link}" class="jsEntryInlineEditor">{lang}wcf.global.button.edit{/lang}</a></li>
									{/if}
								</ul>
								
								<dl class="plain inlineDataList">
									<dt title="{lang}show.entry.views{/lang}"><span class="icon icon16 fa-eye"></span></dt>
									<dd>{#$entry->views}</dd>
									
									{if $entry->attachments}
										<dt title="{lang}show.entry.attachments{/lang}"><span class="icon icon16 fa-camera"></span></dt>
										<dd>{#$entry->attachments}</dd>
									{/if}
									
									{if $entry->enableComments}
										<dt title="{lang}show.entry.comments{/lang}"><span class="icon icon16 fa-comment-o"></span></dt>
										<dd>{#$entry->comments}</dd>
									{/if}
									
									{if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike') && $entry->cumulativeLikes}
										<dt></dt>
										<dd>{include file='__topReaction' cachedReactions=$entry->cachedReactions render='tiny'}</dd>
									{/if}
									
									{event name='inlineData'}
								</dl>
							</div>
						</div>
					</li>
				{/foreach}
			</ul>
		</div>
	{else}
		<p class="info">{lang}wcf.global.noItems{/lang}</p>
	{/if}
</div>
