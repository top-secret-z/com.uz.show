{if $items}
	<div class="section jsClipboardContainer" data-type="com.uz.show.entry">
		<div class="section">
				{if $controllerName == 'WatchedEntryList'}
					{assign var='enableEditMode' value=false}
					{assign var='enableWatchMode' value=true}
				{/if}
				
				<ol class="showEntryList">
					{include file='entryListItems' application='show'}
				</ol>
			</div>
	</div>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}
