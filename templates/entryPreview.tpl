<div class="showEntryTile showEntryPreview">
    <div class="box128">
        <div class="showEntryIconContainer">
            <span class="showEntryIcon">{@$entry->getIconTag(128)}</span>

            {if $entry->isFeatured}
                <span class="badge label green jsLabelFeatured">{lang}show.entry.featured{/lang}</span>
            {/if}
        </div>

        <div class="showEntryDataContainer">
            <div class="containerHeadline">
                <h3 class="showEntrySubject">
                    <a href="{$entry->getLink()}">{$entry->getSubject()}</a>
                </h3>
                <ul class="inlineList dotSeparated showEntryMetaData">
                    <li>{$entry->username}</li>
                    <li>{if SHOW_LAST_CHANGE_TIME}{@$entry->lastChangeTime|time}{else}{@$entry->time|time}{/if}</li>
                </ul>
                {hascontent}
                    <ul class="labelList">
                        {content}
                            {foreach from=$entry->getLabels() item=label}
                                <li><span class="label badge{if $label->getClassNames()} {$label->getClassNames()}{/if} jsTooltip" title="{lang}show.entry.labeledEntrys{/lang}">{lang}{$label->label}{/lang}</span></li>
                            {/foreach}
                        {/content}
                    </ul>
                {/hascontent}
            </div>

            <div class="containerContent showEntryTeaser">
                {$entry->getTeaser()}
            </div>

            {event name='previewData'}
        </div>
    </div>
</div>
