{foreach from=$entrys item=entry}
    <article class="showEmbeddedEvent embeddedContent" aria-labelledby="{$titleHash}_entryTitle{@$entry->entryID}">

        <div class="embeddedContentLink">
            <div class="embeddedContentCategory">{lang}show.entry.bbcode.type{/lang}</div>

            <div class="embeddedContentTitle" id="{$titleHash}_entryTitle{@$entry->entryID}">{$entry->getSubject()}</div>

            <div class="embeddedContentDescription">
                {@$entry->getExcerpt(500)}
            </div>

            {if SHOW_CATEGORY_SHOW_BBCODE}
                <div>
                    <span class="showEntryCategoryShow">{@$entry->getCategory()->getTitle()}</span>
                </div>
            {/if}

            <a href="{@$entry->getLink()}" class="embeddedContentLinkShadow"></a>
        </div>

        <div class="embeddedContentMeta">
            {user object=$entry->getUserProfile() type='avatar32' class='embeddedContentMetaImage' ariaHidden='true' tabindex='-1'}

            <div class="embeddedContentMetaContent">
                <div class="embeddedContentMetaAuthor">
                    {user object=$entry->getUserProfile() class='username'}
                </div>

                <div class="embeddedContentMetaTime">
                    {@$entry->lastChangeTime|time}
                </div>
            </div>
        </div>
    </article>
{/foreach}
