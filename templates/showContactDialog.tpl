<section class="section">
    <h2 class="sectionTitle">{lang}show.contact.data.user{/lang}</h2>

    {if !$contact->name|empty}
        <dl>
            <dt><label>{lang}show.contact.name{/lang}</label></dt>
            <dd>{$contact->name}</dd>
        </dl>
    {/if}

    {if !$contact->address|empty}
        <dl>
            <dt><label>{lang}show.contact.address{/lang}</label></dt>
            <dd>{@$contact->address|newlineToBreak}</dd>
        </dl>
    {/if}

    {if !$contact->email|empty}
        <dl>
            <dt><label>{lang}show.contact.email{/lang}</label></dt>
            <dd><a href="mailto:{$contact->email}">{$contact->email}</a></dd>
        </dl>
    {/if}

    {if !$contact->website|empty}
        <dl>
            <dt><label>{lang}show.contact.website{/lang}</label></dt>
            <dd><a href="{$contact->website}" class="externalURL"{if EXTERNAL_LINK_REL_NOFOLLOW || EXTERNAL_LINK_TARGET_BLANK} rel="{if EXTERNAL_LINK_REL_NOFOLLOW}nofollow{/if}{if EXTERNAL_LINK_TARGET_BLANK}{if EXTERNAL_LINK_REL_NOFOLLOW} {/if}noopener noreferrer{/if}"{/if}{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}>{$contact->website}</a></dd>
        </dl>
    {/if}

    {if !$contact->other|empty}
        <dl>
            <dt><label>{lang}show.contact.other{/lang}</label></dt>
            <dd>{@$contact->other|newlineToBreak}</dd>
        </dl>
    {/if}
</section>
