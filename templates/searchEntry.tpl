<dl>
    <dt><label for="showCategoryID">{lang}show.search.categories{/lang}</label></dt>
    <dd>
        <select name="showCategoryID" id="showCategoryID">
            <option value="">{lang}wcf.global.language.noSelection{/lang}</option>
            {foreach from=$showCategoryList item=category}
                <option value="{@$category->categoryID}">{if $category->getDepth() > 1}{@'&nbsp;&nbsp;&nbsp;&nbsp;'|str_repeat:-1+$category->getDepth()}{/if}{$category->getTitle()}</option>
            {/foreach}
        </select>
    </dd>
</dl>

{event name='fields'}
