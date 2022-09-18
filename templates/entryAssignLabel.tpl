<section class="section" id="entryAssignLabel">
    <h2 class="sectionTitle">{lang}wcf.label.labels{/lang}</h2>

    <dl>
        {foreach from=$labelGroups item=labelGroup}
            {if $labelGroup|count}
                <dt>{$labelGroup->groupName|language}</dt>
                <dd>
                    <ul class="labelList" data-object-id="{@$labelGroup->groupID}">
                        <li class="dropdown labelChooser" data-group-id="{@$labelGroup->groupID}" data-force-selection="{if $labelGroup->forceSelection}true{else}false{/if}">
                            <div class="dropdownToggle"><span class="badge label">{lang}wcf.label.none{/lang}</span></div>
                            <div class="dropdownMenu">
                                <ul class="scrollableDropdownMenu">
                                    {foreach from=$labelGroup item=label}
                                        <li data-label-id="{@$label->labelID}"><span><span class="badge label{if $label->getClassNames()} {@$label->getClassNames()}{/if}">{lang}{$label->label}{/lang}</span></span></li>
                                    {/foreach}
                                </ul>
                            </div>
                        </li>
                    </ul>
                </dd>
            {/if}
        {/foreach}
    </dl>
</section>
<div class="formSubmit">
    <button class="buttonPrimary">{lang}wcf.global.button.submit{/lang}</button>
</div>

<script data-relocate="true">
    $(function() {
        WCF.Language.addObject({
            'wcf.label.error.missing': '{jslang}wcf.label.error.missing{/jslang}'
        });

        new WCF.Label.Chooser({ }, '#entryAssignLabel');
    });
</script>
