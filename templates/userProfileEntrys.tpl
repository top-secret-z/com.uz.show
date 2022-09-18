<div class="section">
	{if !$enableEditMode|isset}{assign var='enableEditMode' value=true}{/if}
	
	<ol class="showEntryList">
		{foreach from=$entryList item=entry}
			<li class="showEntry {if $entry->isDeleted} messageDeleted{else}{if $entry->isDisabled} messageDisabled{/if}{/if}">
				<div class="showEntryTile{if $entry->isNew()} new{/if}">
					<a href="{$entry->getLink()}" class="box128">
						<div class="showEntryIconContainer">
							<span class="showEntryIcon">{@$entry->getIconTag(128)}</span>
							
							{if $entry->isFeatured}
								<span class="badge label green jsLabelFeatured">{lang}show.entry.featured{/lang}</span>
							{/if}
						</div>
						
						<div class="showEntryDataContainer">
							<div class="containerHeadline">
								<h3 class="showEntrySubject">
									<span>{$entry->getSubject()}</span>
								</h3>
								<ul class="inlineList dotSeparated showEntryMetaData">
									<li>{$entry->username}</li>
									<li>{if SHOW_LAST_CHANGE_TIME}{@$entry->lastChangeTime|time}{else}{@$entry->time|time}{/if}</li>
								</ul>
								{hascontent}
									<ul class="labelList">
										{content}
											{foreach from=$entry->getLabels() item=label}
												<li><span class="label badge{if $label->getClassNames()} {$label->getClassNames()}{/if}">{lang}{$label->label}{/lang}</span></li>
											{/foreach}
										{/content}
									</ul>
								{/hascontent}
							</div>
							
							<div class="containerContent showEntryTeaser">
								{$entry->getTeaser()}
							</div>
							
							{* leave list delete note in code ufn
							{if $entry->isDeleted && $entry->getDeleteNote()}
								<div class="containerContent showEntryDeleteNote">
									{@$entry->getDeleteNote()}
								</div>
							{/if}
							*}
						</div>
					</a>
					<div class="showEntryFooter">
						{if $enableEditMode && $__wcf->session->getPermission('mod.show.canEditEntry')}
							<div class="jsOnly showEntryEditLink"><a href="{link controller='EntryEdit' object=$entry application='show'}{/link}" class="jsEntryInlineEditor">{lang}wcf.global.button.edit{/lang}</a></div>
						{/if}
						
						<ul class="inlineList showEntryStats">
							<li>
								<span class="icon icon16 fa-eye"></span>
								{lang}show.entry.entryViews{/lang}
							</li>
							
							{if $entry->attachments}
								<li>
									<span class="icon icon16 fa-camera"></span>
									{lang}show.entry.entryAttachments{/lang}
								</li>
							{/if}
							
							{if $entry->enableComments}
								<li>
									<span class="icon icon16 fa-comments"></span>
									{lang}show.entry.entryComments{/lang}
								</li>
							{/if}
							
							{if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike')}
								<li class="wcfLikeCounter{if $entry->cumulativeLikes > 0} likeCounterLiked{elseif $entry->cumulativeLikes < 0}likeCounterDisliked{/if}">
									{if $entry->likes || $entry->dislikes}
										<span class="icon icon16 fa-thumbs-o-{if $entry->cumulativeLikes < 0}down{else}up{/if} jsTooltip" title="{lang likes=$entry->likes dislikes=$entry->dislikes}wcf.like.tooltip{/lang}"></span>{if $entry->cumulativeLikes > 0}+{elseif $entry->cumulativeLikes == 0}&plusmn;{/if}{#$entry->cumulativeLikes}
									{/if}
								</li>
							{/if}
						</ul>
					</div>
				</div>
			</li>
		{/foreach}
		
		{if $entryList|count == 8  && $user->showEntrys > 8}
			<li>
				<a class="button small" href="{link controller='UserEntryList' application='show' object=$user}{/link}">{lang}show.entry.moreEntrys.all{/lang}</a>
			</li>
		{/if}
	</ol>
</div>
