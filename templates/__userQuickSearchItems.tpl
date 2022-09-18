{if $user->showEntrys && $__wcf->session->getPermission('user.show.canViewEntry')}
	<li><a href="{link application='show' controller='UserEntryList' object=$user}{/link}">{lang}show.entry.entrys{/lang}</a></li>
{/if}
