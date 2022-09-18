{include file='header' pageTitle='show.acp.entry.option.'|concat:$action}

<script data-relocate="true">
    $(function() {
        var $optionTypesUsingSelectOptions = [{implode from=$optionTypesUsingSelectOptions item=optionTypeUsingSelectOptions}'{@$optionTypeUsingSelectOptions}'{/implode}];

        $('#optionType').change(function(event) {
            var $value = $(event.currentTarget).val();
            if (WCF.inArray($value, $optionTypesUsingSelectOptions)) {
                $('#selectOptionsDL').show();
            }
            else {
                $('#selectOptionsDL').hide();
            }
        });
        $('#optionType').trigger('change');
    });
</script>

<header class="contentHeader">
    <div class="contentHeaderTitle">
        <h1 class="contentTitle">{lang}show.acp.entry.option.{$action}{/lang}</h1>
    </div>

    <nav class="contentHeaderNavigation">
        <ul>
            <li><a href="{link application='show' controller='EntryOptionList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}show.acp.menu.link.show.entry.option.list{/lang}</span></a></li>

            {event name='contentHeaderNavigation'}
        </ul>
    </nav>
</header>

{include file='formError'}

{if $success|isset}
    <p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<form method="post" action="{if $action == 'add'}{link application='show' controller='EntryOptionAdd'}{/link}{else}{link application='show' controller='EntryOptionEdit' id=$optionID}{/link}{/if}">
    <div class="section">
        <dl{if $errorField == 'optionTitle'} class="formError"{/if}>
            <dt><label for="optionTitle">{lang}wcf.global.name{/lang}</label></dt>
            <dd>
                <input type="text" id="optionTitle" name="optionTitle" value="{$i18nPlainValues['optionTitle']}" required autofocus class="long">
                {if $errorField == 'optionTitle'}
                    <small class="innerError">
                        {if $errorType == 'multilingual'}
                            {lang}wcf.global.form.error.multilingual{/lang}
                        {else}
                            {lang}show.acp.entry.option.name.error.{@$errorType}{/lang}
                        {/if}
                    </small>
                {/if}
            </dd>
        </dl>
        {include file='multipleLanguageInputJavascript' elementIdentifier='optionTitle' forceSelection=false}

        <dl{if $errorField == 'optionDescription'} class="formError"{/if}>
            <dt><label for="optionDescription">{lang}wcf.global.description{/lang}</label></dt>
            <dd>
                <textarea name="optionDescription" id="optionDescription" cols="40" rows="10">{$i18nPlainValues[optionDescription]}</textarea>
                {if $errorField == 'optionDescription'}
                    <small class="innerError">
                        {if $errorType == 'empty'}
                            {lang}wcf.global.form.error.empty{/lang}
                        {else}
                            {lang}show.acp.entry.option.description.error.{@$errorType}{/lang}
                        {/if}
                    </small>
                {/if}
            </dd>
        </dl>
        {include file='multipleLanguageInputJavascript' elementIdentifier='optionDescription' forceSelection=false}

        <dl>
            <dt><label for="showOrder">{lang}show.acp.entry.option.showOrder{/lang}</label></dt>
            <dd>
                <input type="number" id="showOrder" name="showOrder" value="{@$showOrder}" class="short">
            </dd>
        </dl>

        <dl>
            <dt><label for="tab">{lang}show.acp.entry.option.tab{/lang}</label></dt>
            <dd>
                <select name="tab" id="tab">
                    <option value="1"{if $tab == 1} selected{/if}>1 {lang}{SHOW_TAB1_TITLE}{/lang}</option>
                    <option value="2"{if $tab == 2} selected{/if}>2 {lang}{SHOW_TAB2_TITLE}{/lang}</option>
                    <option value="3"{if $tab == 3} selected{/if}>3 {lang}{SHOW_TAB3_TITLE}{/lang}</option>
                    <option value="4"{if $tab == 4} selected{/if}>4 {lang}{SHOW_TAB4_TITLE}{/lang}</option>
                    <option value="5"{if $tab == 5} selected{/if}>5 {lang}{SHOW_TAB5_TITLE}{/lang}</option>

                </select>
            </dd>
        </dl>

        {event name='dataFields'}
    </div>

    <section class="section">
        <h2 class="sectionTitle">{lang}show.acp.entry.option.typeData{/lang}</h2>

        <dl{if $errorField == 'optionType'} class="formError"{/if}>
            <dt><label for="optionType">{lang}show.acp.entry.option.optionType{/lang}</label></dt>
            <dd>
                <select name="optionType" id="optionType">
                    {foreach from=$availableOptionTypes item=availableOptionType}
                        <option value="{$availableOptionType}"{if $availableOptionType == $optionType} selected{/if}>{lang}show.acp.entry.option.optionType.{$availableOptionType}{/lang}</option>
                    {/foreach}
                </select>
                {if $errorField == 'optionType'}
                    <small class="innerError">
                        {if $errorType == 'empty'}
                            {lang}wcf.global.form.error.empty{/lang}
                        {else}
                            {lang}show.acp.entry.option.optionType.error.{@$errorType}{/lang}
                        {/if}
                    </small>
                {/if}
            </dd>
        </dl>

        <dl>
            <dt><label for="defaultValue">{lang}show.acp.entry.option.defaultValue{/lang}</label></dt>
            <dd>
                <input type="text" id="defaultValue" name="defaultValue" value="{$defaultValue}" class="long">
                <small>{lang}show.acp.entry.option.defaultValue.description{/lang}</small>
            </dd>
        </dl>

        <dl id="selectOptionsDL"{if $errorField == 'selectOptions'} class="formError"{/if}>
            <dt><label for="selectOptions">{lang}show.acp.entry.option.selectOptions{/lang}</label></dt>
            <dd>
                <textarea name="selectOptions" id="selectOptions" cols="40" rows="10">{$selectOptions}</textarea>
                {if $errorField == 'selectOptions'}
                    <small class="innerError">
                        {if $errorType == 'empty'}
                            {lang}wcf.global.form.error.empty{/lang}
                        {else}
                            {lang}show.acp.entry.option.selectOptions.error.{@$errorType}{/lang}
                        {/if}
                    </small>
                {/if}
                <small>{lang}show.acp.entry.option.selectOptions.description{/lang}</small>
            </dd>
        </dl>

        <dl{if $errorField == 'validationPattern'} class="formError"{/if}>
            <dt><label for="validationPattern">{lang}show.acp.entry.option.validationPattern{/lang}</label></dt>
            <dd>
                <input type="text" id="validationPattern" name="validationPattern" value="{$validationPattern}" class="long">
                {if $errorField == 'validationPattern'}
                    <small class="innerError">
                        {if $errorType == 'empty'}
                            {lang}wcf.global.form.error.empty{/lang}
                        {else}
                            {lang}show.acp.entry.option.validationPattern.error.{@$errorType}{/lang}
                        {/if}
                    </small>
                {/if}
                <small>{lang}show.acp.entry.option.validationPattern.description{/lang}</small>
            </dd>
        </dl>

        <dl>
            <dt></dt>
            <dd>
                <label><input type="checkbox" name="required" id="required" value="1"{if $required == 1} checked{/if}> {lang}show.acp.entry.option.required{/lang}</label>
            </dd>
        </dl>

        {event name='typeDataFields'}
    </section>

    {event name='sections'}

    <div class="formSubmit">
        <input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
        {csrfToken}
    </div>
</form>

{include file='footer'}
