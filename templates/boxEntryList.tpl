{if $boxPosition == 'sidebarLeft' || $boxPosition == 'sidebarRight'}
	<ol class="sidebarItemList">
		{foreach from=$boxEntryList item=entry}
			<li>
				<a href="{link application='show' controller='Entry' object=$entry}{/link}" class="showEntryLink box24" data-entry-id="{@$entry->entryID}" title="{$entry->getSubject()}">
					<span>{@$entry->getIconTag(24)}</span>
					
					<div>
						<h3>{$entry->getSubject()}</h3>
						
						{if $boxSortField == 'time'}
							<small>{$entry->username} <span class="separatorLeft">{@$entry->time|time}</span></small>
						{elseif $boxSortField == 'lastChangeTime'}
							<small>{$entry->username} <span class="separatorLeft">{if SHOW_LAST_CHANGE_TIME}{@$entry->lastChangeTime|time}{else}{@$entry->time|time}{/if}</span></small>
						{elseif $boxSortField == 'cumulativeLikes'}
							<small>{if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike') && $entry->getCumulativeLikes()}{include file='__topReaction' cachedReactions=$entry->cachedReactions render='full'}{/if}</small>
							
						{/if}
					</div>
				</a>
			</li>
		{/foreach}
	</ol>
{else}
	<ol class="showEntryList">
		{include file='entryListItems' application='show' objects=$boxEntryList}
	</ol>
{/if}
