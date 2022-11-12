<div class="section jsClipboardContainer" data-type="com.uz.show.entry">
    <ol class="showEntryList">
        {include file='entryListItems' application='show'}
    </ol>
</div>


<script data-relocate="true">
    $(function() {
        WCF.Language.addObject({
            'show.entry.edit.delete':            '{jslang}show.entry.edit.delete{/jslang}',
            'show.entry.edit.disable':            '{jslang}show.entry.edit.disable{/jslang}',
            'show.entry.edit.enable':            '{jslang}show.entry.edit.enable{/jslang}',
            'show.entry.edit.restore':            '{jslang}show.entry.edit.restore{/jslang}',
            'show.entry.edit.setAsFeatured':    '{jslang}show.entry.edit.setAsFeatured{/jslang}',
            'show.entry.edit.trash':            '{jslang}show.entry.edit.trash{/jslang}',
            'show.entry.edit.unsetAsFeatured':    '{jslang}show.entry.edit.unsetAsFeatured{/jslang}',
            'show.entry.featured':                '{jslang}show.entry.featured{/jslang}',
            'show.entry.confirmDelete':            '{jslang}show.entry.confirmDelete{/jslang}',
            'show.entry.version.confirmDelete':    '{jslang}show.entry.version.confirmDelete{/jslang}',
            'show.entry.confirmTrash':            '{jslang}show.entry.confirmTrash{/jslang}',
            'show.entry.confirmTrash.reason':    '{jslang}show.entry.confirmTrash.reason{/jslang}'
        });

        {if $__wcf->session->getPermission('mod.show.canEditEntry')}
            var $updateHandler = new Show.Entry.UpdateHandler.Category();

            var $inlineEditor = new Show.Entry.InlineEditor('.showEntry');
            $inlineEditor.setEnvironment('category');
            $inlineEditor.setUpdateHandler($updateHandler);
            $inlineEditor.setPermissions({
                canDeleteEntry: {@$__wcf->session->getPermission('mod.show.canDeleteEntry')},
                canDeleteEntryCompletely: {@$__wcf->session->getPermission('mod.show.canDeleteEntryCompletely')},
                canEnableEntry: {@$__wcf->session->getPermission('mod.show.canModerateEntry')},
                canRestoreEntry: {@$__wcf->session->getPermission('mod.show.canRestoreEntry')},
                canSetAsFeatured: {@$__wcf->session->getPermission('mod.show.canEditEntry')}
            });

            var $entryClipboard = new Show.Entry.Clipboard($updateHandler);
            WCF.Clipboard.init('wcf\\page\\DeletedContentListPage', {@$objects->getMarkedItems()}, { }, 0);
        {/if}

        new Show.Entry.MarkAsRead();
    });
</script>
