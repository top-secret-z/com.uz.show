<ul class="sidebarItemList">
    {foreach from=$mostActiveAuthors item=activeAuthor}
        <li class="box24">
            <a href="{link application='show' controller='UserEntryList' object=$activeAuthor}{/link}">{@$activeAuthor->getAvatar()->getImageTag(24)}</a>

            <div class="sidebarItemTitle">
                <h3><a href="{link application='show' controller='UserEntryList' object=$activeAuthor}{/link}">{$activeAuthor->username}</a></h3>
                <small>{lang}show.entry.userEntryCounter{/lang}</small>
            </div>
        </li>
    {/foreach}
</ul>
