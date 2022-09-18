{capture assign='sidebarRight'}
	<section class="box">
		<h2 class="boxTitle">{lang}show.entry.categories{/lang}</h2>
		<div class="boxContent">
			<ol class="boxMenu forceOpen">
				{foreach from=$categoryList item=categoryItem}
					<li{if $category && $category->categoryID == $categoryItem->categoryID} class="active"{/if} data-category-id="{@$categoryItem->categoryID}">
						<a href="{link application='show' controller='Map' object=$categoryItem->getDecoratedObject()}{/link}" class="boxMenuLink">
							<span class="boxMenuLinkTitle">{$categoryItem->getTitle()}</span>
							<span class="badge">{#$categoryItem->getEntrys()}</span>
						</a>
						
						{if $category && ($category->categoryID == $categoryItem->categoryID || $category->isParentCategory($categoryItem->getDecoratedObject())) && $categoryItem->hasChildren()}
							<ol class="boxMenuDepth1">
								{foreach from=$categoryItem item=subCategoryItem}
									<li{if $category && $category->categoryID == $subCategoryItem->categoryID} class="active"{/if} data-category-id="{@$subCategoryItem->categoryID}">
										<a href="{link application='show' controller='Map' object=$subCategoryItem->getDecoratedObject()}{/link}" class="boxMenuLink">
											<span class="boxMenuLinkTitle">{$subCategoryItem->getTitle()}</span>
											<span class="badge">{#$subCategoryItem->getEntrys()}</span>
										</a>
										 
										{if $category && ($category->categoryID == $subCategoryItem->categoryID || $category->isParentCategory($subCategoryItem->getDecoratedObject())) && $subCategoryItem->hasChildren()}
											<ol class="boxMenuDepth2">
												{foreach from=$subCategoryItem item=subSubCategoryItem}
													<li{if $category && $category->categoryID == $subSubCategoryItem->categoryID} class="active"{/if} data-category-id="{@$subSubCategoryItem->categoryID}">
														<a href="{link application='show' controller='Map' object=$subSubCategoryItem->getDecoratedObject()}{/link}" class="boxMenuLink">
															<span class="boxMenuLinkTitle">{$subSubCategoryItem->getTitle()}</span>
															<span class="badge">{#$subSubCategoryItem->getEntrys()}</span>
														</a>
														
														<!-- additional level (3) -->
														{if $category && ($category->categoryID == $subSubCategoryItem->categoryID || $category->parentCategoryID == $subSubCategoryItem->categoryID) && $subSubCategoryItem->hasChildren()}
															<ol class="boxMenuDepth2">
																{foreach from=$subSubCategoryItem item=subSubSubCategoryItem}
																	<li{if $category && $category->categoryID == $subSubSubCategoryItem->categoryID} class="active"{/if} data-category-id="{@$subSubSubCategoryItem->categoryID}">
																		<a href="{link application='show' controller='Map' object=$subSubSubCategoryItem->getDecoratedObject()}{/link}" class="boxMenuLink">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																			<span class="boxMenuLinkTitle">{$subSubSubCategoryItem->getTitle()}</span>
																			<span class="badge">{#$subSubSubCategoryItem->getEntrys()}</span>
																		</a>
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
		</div>
	</section>

	<section class="box">
		<h2 class="boxTitle">{lang}show.entry.location.search{/lang}</h2>
		
		<div class="boxContent">
			<dl>
				<dt></dt>
				<dd><input type="text" id="geocode" name="geocode" class="long" placeholder="{lang}show.entry.location.search.placeholder{/lang}"></dd>
			</dl>
		</div>
	</section>
{/capture}

{include file='header'}

<div class="section">
	<dl class="wide">
		<dt></dt>
		<dd><div id="mapContainer" style="height:{SHOW_GEODATA_MAP_HEIGHT}px;"></div></dd>
	</dl>
</div>

<footer class="contentFooter">
	{hascontent}
		<nav class="contentFooterNavigation">
			<ul>
				{content}{event name='contentFooterNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</footer>

{include file='googleMapsJavaScript'}
<script data-relocate="true">
	$(function() {
		new Show.Map.LargeMap('mapContainer', { }, 'show\\data\\entry\\EntryAction', '#geocode'{if $category}, { categoryID: {@$category->categoryID} }{/if});
	});
</script>

{include file='footer'}
