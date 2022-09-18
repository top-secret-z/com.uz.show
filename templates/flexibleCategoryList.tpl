{if !$flexibleCategoryList|isset}{assign var=flexibleCategoryList value=$flexCategoryList}{/if}
{if !$flexibleCategoryListName|isset}{assign var=flexibleCategoryListName value='categoryIDs'}{/if}
{if !$flexibleCategoryListID|isset}{assign var=flexibleCategoryListID value='flexibleCategoryList'}{/if}
{if !$flexibleCategoryListSelectedIDs|isset}{assign var=flexibleCategoryListSelectedIDs value=$categoryIDs}{/if}
<ol class="showFlexibleCategoryList" id="{$flexibleCategoryListID}">
    {foreach from=$flexibleCategoryList item=categoryItem}
        <li>
            <div class="containerHeadline">
                <h3><label{if $categoryItem->getDescription()} class="jsTooltip" title="{$categoryItem->getDescription()}"{/if}><input type="checkbox" name="{$flexibleCategoryListName}[]" value="{@$categoryItem->categoryID}" class="jsCategory"{if $categoryItem->categoryID|in_array:$flexibleCategoryListSelectedIDs}checked="checked" {/if}/> {$categoryItem->getTitle()}</label></h3>
            </div>

            {if $categoryItem->hasChildren()}
                <ol>
                    {foreach from=$categoryItem item=subCategoryItem}
                        <li>
                            <label{if $subCategoryItem->getDescription()} class="jsTooltip" title="{$subCategoryItem->getDescription()}"{/if} style="font-size: 1rem;"><input type="checkbox" name="{$flexibleCategoryListName}[]" value="{@$subCategoryItem->categoryID}" class="jsChildCategory"{if $subCategoryItem->categoryID|in_array:$flexibleCategoryListSelectedIDs}checked="checked" {/if}/> {$subCategoryItem->getTitle()}</label>

                            {if $subCategoryItem->hasChildren()}
                                <ol>
                                    {foreach from=$subCategoryItem item=subSubCategoryItem}
                                        <li>
                                            <label{if $subSubCategoryItem->getDescription()} class="jsTooltip" title="{$subSubCategoryItem->getDescription()}"{/if}><input type="checkbox" name="{$flexibleCategoryListName}[]" value="{@$subSubCategoryItem->categoryID}" class="jsSubChildCategory"{if $subSubCategoryItem->categoryID|in_array:$flexibleCategoryListSelectedIDs}checked="checked" {/if}/> {$subSubCategoryItem->getTitle()}</label>

                                            {if $subSubCategoryItem->hasChildren()}
                                                <ol>
                                                    {foreach from=$subSubCategoryItem item=subSubSubCategoryItem}
                                                        <li>
                                                            <label{if $subSubSubCategoryItem->getDescription()} class="jsTooltip" title="{$subSubSubCategoryItem->getDescription()}"{/if}><input type="checkbox" name="{$flexibleCategoryListName}[]" value="{@$subSubSubCategoryItem->categoryID}" class="jsSubSubChildCategory"{if $subSubSubCategoryItem->categoryID|in_array:$flexibleCategoryListSelectedIDs}checked="checked" {/if}/> {$subSubSubCategoryItem->getTitle()}</label>

                                                            {if $subSubSubCategoryItem->hasChildren()}
                                                                <ol>
                                                                    {foreach from=$subSubSubCategoryItem item=subSubSubSubCategoryItem}
                                                                        <li>
                                                                            <label{if $subSubSubSubCategoryItem->getDescription()} class="jsTooltip" title="{$subSubSubSubCategoryItem->getDescription()}"{/if}><input type="checkbox" name="{$flexibleCategoryListName}[]" value="{@$subSubSubSubCategoryItem->categoryID}" class="jsSubSubSubChildCategory"{if $subSubSubSubCategoryItem->categoryID|in_array:$flexibleCategoryListSelectedIDs}checked="checked" {/if}/> {$subSubSubSubCategoryItem->getTitle()}</label>
                                                                        </li>
                                                                    {/foreach}
                                                                </ol>
                                                            {/if}
                                                        </li>
                                                    {/foreach}
                                                </ol>
                                            {/if}
                                        </li>
                                    {/foreach}
                                </ol>
                            {/if}
                        </li>
                    {/foreach}
                </ol>
            {/if}
        </li>
    {/foreach}
</ol>
