{if SHOW_MESSAGE_SIDEBAR_ENABLE_USER_ENTRYS && $userProfile->showEntrys}
	<dt><a href="{link application='show' controller='UserEntryList' object=$userProfile}{/link}" title="{lang user=$userProfile}show.entry.userEntrys{/lang}" class="jsTooltip">{lang}show.entry.entrys{/lang}</a></dt>
	<dd>{#$userProfile->showEntrys}</dd>
{/if}