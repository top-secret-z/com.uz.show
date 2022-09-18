{if $__show->isActiveApplication()}
    {if $enableComments|isset && $__wcf->session->getPermission('user.show.canDisableCommentFunction')}
        <dt></dt>
        <dd>
            <label><input name="enableComments" type="checkbox" value="1"{if $enableComments} checked{/if}> {lang}show.entry.enableComments{/lang}</label>
            <small>{lang}show.entry.enableComments.description{/lang}</small>
        </dd>
    {/if}
{/if}
