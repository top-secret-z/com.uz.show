{include file='header'}

{hascontent}
	<div class="paginationTop">
		{content}
			{pages print=true assign=pagesLinks application='show' controller='EntryLog' id=$entry->entryID link="pageNo=%d"}
		{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section">
		<ul class="containerList">
			{foreach from=$objects item=entry}
				<li>
					<div class="box48">
						<a href="{link controller='User' object=$entry->getUserProfile()}{/link}" title="{$entry->username}" aria-hidden="true">{@$entry->getUserProfile()->getAvatar()->getImageTag(48)}</a>
						
						<div class="details">
							<div class="containerHeadline">
								<h3>{user object=$entry->getUserProfile()}</h3>
								<small>{@$entry->time|time}</small>
							</div>
							
							<p>{@$entry}</p>
						</div>
					</div>
				</li>
			{/foreach}
		</ul>
	</div>
{else}
	<p class="info">{lang}show.entry.log.noEntries{/lang}</p>
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
					{event name='contentFooterNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</footer>

{include file='footer'}
