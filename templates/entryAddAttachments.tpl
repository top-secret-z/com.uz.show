{js application='wcf' file='WCF.Attachment' bundle='WCF.Combined'}

<script data-relocate="true">
    $(function () {
        WCF.Language.addObject({
            'wcf.attachment.upload.error.invalidExtension':            '{jslang}wcf.attachment.upload.error.invalidExtension{/jslang}',
            'wcf.attachment.upload.error.tooLarge':                    '{jslang}wcf.attachment.upload.error.tooLarge{/jslang}',
            'wcf.attachment.upload.error.reachedLimit':                '{jslang}wcf.attachment.upload.error.reachedLimit{/jslang}',
            'wcf.attachment.upload.error.reachedRemainingLimit':    '{jslang}wcf.attachment.upload.error.reachedRemainingLimit{/jslang}',
            'wcf.attachment.upload.error.uploadFailed':                '{jslang}wcf.attachment.upload.error.uploadFailed{/jslang}',
            'wcf.global.button.upload':                                '{jslang}wcf.global.button.upload{/jslang}',
            'wcf.attachment.insert':                                '{jslang}wcf.attachment.insert{/jslang}',
            'wcf.attachment.insertAll':                                '{jslang}wcf.attachment.insertAll{/jslang}',
            'wcf.attachment.insertFull':                            '{jslang}wcf.attachment.insertFull{/jslang}',
            'wcf.attachment.insertThumbnail':                        '{jslang}wcf.attachment.insertThumbnail{/jslang}',
            'wcf.attachment.delete.sure':                            '{jslang}wcf.attachment.delete.sure{/jslang}'
        });

        new WCF.Attachment.Upload($('#attachments > dl > dd > div'), $('#attachments > ul'), '{@$attachmentObjectType}', '{@$attachmentObjectID}', '{$tmpHash|encodeJS}', '{@$attachmentParentObjectID}', {@$attachmentHandler->getMaxCount()});
        new WCF.Action.Delete('wcf\\data\\attachment\\AttachmentAction', '.formAttachmentList > li');

        // toDo hide insert button
        $(".jsButtonAttachmentInsertAll").hide();
    });
</script>

<div class="section">
    <p>{lang}show.entry.images.instruction{/lang}</p>
</div>

<div id="attachments" class="jsOnly formAttachmentContent tabMenuContent section">
    <ul class="formAttachmentList clearfix"{if !$attachmentHandler->getAttachmentList()|count} style="display: none"{/if}>
        {foreach from=$attachmentHandler->getAttachmentList() item=$attachment}
            <li class="box128" data-object-id="{@$attachment->attachmentID}" data-height="{@$attachment->height}" data-width="{@$attachment->width}">
                {if $attachment->tinyThumbnailType}
                    <img src="{link controller='Attachment' object=$attachment}tiny=1{/link}" alt="" class="attachmentTinyThumbnail"/>
                {else}
                    <span class="icon icon48 fa-paper-clip"></span>
                {/if}

                <div>
                    <div>
                        <p>
                            <a href="{link controller='Attachment' object=$attachment}{/link}"{if $attachment->isImage} title="{$attachment->filename}" class="jsImageViewer"{/if}>{$attachment->filename}</a>
                        </p>
                        <small>{@$attachment->filesize|filesize}</small>
                    </div>

                    <ul class="buttonGroup">
                        <li>
                            <span class="button small jsDeleteButton" data-object-id="{@$attachment->attachmentID}" data-confirm-message="{lang}wcf.attachment.delete.sure{/lang}">
                                {lang}wcf.global.button.delete{/lang}
                            </span>
                        </li>
                    </ul>
                </div>
            </li>
        {/foreach}
    </ul>

    <dl class="wide{if $errorField == 'images'} formError{/if}">
        <dt></dt>
        <dd>
            <div data-max-size="{@$attachmentHandler->getMaxSize()}"></div>
            <small>{lang}wcf.attachment.upload.limits{/lang}</small>
            {if $errorField == 'images'}
                <small class="innerError">
                    {lang}show.entry.images.error.{@$errorType}{/lang}
                </small>
            {/if}
        </dd>
    </dl>
</div>
