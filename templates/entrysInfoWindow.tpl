<section>
    <div class="section">
        <h2 class="sectionTitle">{lang}show.entry.entrysInfoWindow{/lang} <span class="badge">{#$items}</span></p>
    </div>

    <div>
        {foreach from=$entrys item=entry}
            <div class="box48">
                <div>
                    <span>{@$entry->getIconTag(48)}</span>
                </div>

                <div>
                    <h3>
                        <a href="{$entry->getLink()}">{$entry->getSubject()}</a>
                    </h3>
                    <ul class="inlineList dotSeparated">
                        <li>{$entry->username}</li>
                        <li>{if SHOW_LAST_CHANGE_TIME}{@$entry->lastChangeTime|time}{else}{@$entry->time|time}{/if}</li>
                    </ul>
                </div>
            </div>
        {/foreach}
    </div>
</section>
