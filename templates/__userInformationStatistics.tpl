{if $user->showEntrys}
    <dt><a href="{link application='show' controller='UserEntryList' object=$user}{/link}" title="{lang}show.entry.userEntrys{/lang}" class="jsTooltip">{lang}show.entry.entrys{/lang}</a></dt>
    <dd>{#$user->showEntrys}</dd>
{/if}
