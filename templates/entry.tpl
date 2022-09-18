{capture assign='pageTitle'}{$entry->getSubject()}{/capture}

{capture assign='contentHeader'}
	<header class="contentHeader messageGroupContentHeader showEntry{if $entry->isDisabled} messageDisabled{/if}{if $entry->isDeleted} messageDeleted{/if}"
		data-object-id="{@$entry->entryID}"
		data-entry-id="{@$entry->entryID}"
		data-is-deleted="{if $entry->isDeleted}true{else}false{/if}"
		data-is-disabled="{if $entry->isDisabled}true{else}false{/if}"
		data-is-featured="{if $entry->isFeatured}true{else}false{/if}"
	>
		<div class="contentHeaderIcon">
			{@$entry->getIconTag(64)}
		</div>
		
		<div class="contentHeaderTitle">
			<h1 class="contentTitle">
				{if $entry->isFeatured}
					<span class="badge label green jsLabelFeatured">{lang}show.entry.featured{/lang}</span>
				{/if}
				<span>{$entry->getSubject()}</span>
			</h1>
			<ul class="inlineList contentHeaderMetaData">
				{if $entry->hasLabels()}
					<li>
						<span class="icon icon16 fa-tags"></span>
						<ul class="labelList">
							{foreach from=$entry->getLabels() item=label}
								<li><span class="label badge{if $label->getClassNames()} {$label->getClassNames()}{/if}">{lang}{$label->label}{/lang}</span></li>
							{/foreach}
						</ul>
					</li>
				{/if}
				
				<li>
					<span class="icon icon16 fa-user"></span>
					{if $entry->userID}{user object=$entry->getUserProfile()}{else}{$entry->username}{/if}
				</li>
				
				<li>
					<span class="icon icon16 fa-clock-o"></span>
					<a href="{link application='show' controller='Entry' object=$entry}{/link}">{if SHOW_LAST_CHANGE_TIME}{@$entry->lastChangeTime|time}{else}{@$entry->time|time}{/if}</a>
				</li>
				
				{if LOG_IP_ADDRESS && $entry->ipAddress && $__wcf->session->getPermission('admin.user.canViewIpAddress')}
					<li>
						<span class="icon icon16 fa-globe"></span>
						{$entry->getIpAddress()}
					</li>
				{/if}
				
				<li>
					<span class="icon icon16 fa-eye"></span>
					{lang}show.entry.entryViews{/lang}
				</li>
				
				<li>
					<span class="icon icon16 fa-camera"></span>
					{lang}show.entry.entryAttachments{/lang}
				</li>
				
				{if $entry->enableComments}
					<li>
						<span class="icon icon16 fa-comments"></span>
						{lang}show.entry.entryComments{/lang}
					</li>
				{/if}
				
				{event name='entryData'}
			</ul>
		</div>
		
		{hascontent}
			<nav class="contentHeaderNavigation">
				<ul>
					{content}
						
						{event name='contentHeaderNavigation'}
					{/content}
				</ul>
			</nav>
		{/hascontent}
	</header>
{/capture}

{capture assign='sidebarRight'}
	{event name='boxesTop'}
	
	{if $showContact}
		<section class="box">
			<h2 class="boxTitle">{lang}show.contact.data{/lang}</h2>
			
			<div class="boxContent">
				<p>{lang}show.contact.data.exist{/lang}</p>
				<br>
				<span class="button small jsOpenContact pointer" data-object-id="{@$entry->userID}"> <span>{lang}show.contact.open{/lang}</span></span>
			</div>
		</section>
	{/if}
	
	{if SHOW_IMAGES_BOX_ENABLE && $entry->attachmentID}
		<section class="box">
			<h2 class="boxTitle">{lang}show.entry.images.box{/lang} <span class="badge">{#$entry->attachments}</span></h2>
			
			<div class="boxContent">
				<ul class="sidebarAttachmentList jsImageViewer">
					{foreach from=$attachmentList->getGroupedObjects($entry->entryID) item=attachment}
						<li><a href="{link controller='Attachment' id=$attachment->attachmentID}{/link}"{if $attachment->isImage} class="jsImageViewer" title="{$attachment->filename}"{/if}>
							{if $attachment->tinyThumbnailType}
								<img src="{link controller='Attachment' id=$attachment->attachmentID}tiny=1{/link}" class="attachmentTinyThumbnail jsTooltip" title="{$attachment->filename}" alt="">
							{else}
								<span class="icon icon64 fa-{@$attachment->getIconName()}"></span>
							{/if}
						</a></li>
					{/foreach}
				</ul>
			</div>
		</section>
	{/if}
	
	{if GOOGLE_MAPS_API_KEY && SHOW_GEODATA_TYPE != 1 && SHOW_GEODATA_BOX_ENABLE && !$entry->location|empty}
		<section class="box">
			<h2 class="boxTitle">{lang}show.entry.geodata.location{/lang}</h2>
			
			<div class="boxContent">
				<small>{$entry->location}</small>
				<div class="sidebarGoogleMap" id="imageMap"></div>
				<br>
			</div>
		</section>
	{/if}
	
	{if $userEntryList|count}
		<section class="box">
			<h2 class="boxTitle">{lang}show.entry.moreUserEntrys{/lang}</h2>
			
			<div class="boxContent">
				<ul class="sidebarItemList">
					{foreach from=$userEntryList item=userEntry}
						<li class="box24">
							<a href="{link application='show' controller='Entry' object=$userEntry}{/link}">{@$userEntry->getIconTag(24)}</a>
							
							<div class="sidebarItemTitle">
								<h3><a href="{link application='show' controller='Entry' object=$userEntry}{/link}" class="showEntryLink" data-entry-id="{@$userEntry->entryID}" title="{$userEntry->getSubject()}">{$userEntry->getSubject()}</a></h3>
								<small>{user object=$userEntry->getUserProfile()} <span class="separatorLeft">{@$userEntry->time|time}</span></small>
							</div>
						</li>
					{/foreach}
				</ul>
				
				{if $userEntryList|count >= 5}
					<a href="{link application='show' controller='UserEntryList' object=$entry->getUserProfile()}{/link}" class="button small more">{lang}show.entry.moreEntrys.all{/lang}</a>
				{/if}
			</div>
		</section>
	{/if}
	
	{event name='boxes'}
{/capture}

{capture assign='contentInteractionButtons'}
	{if $__wcf->user->userID && $__wcf->user->userID != $entry->userID}
		<a href="#" class="jsSubscribeButton contentInteractionButton button small jsOnly{if $entry->isSubscribed()} active{/if}" data-object-type="com.uz.show.entry" data-object-id="{@$entry->entryID}"><span class="icon icon16 fa-bookmark{if !$entry->isSubscribed()}-o{/if}"></span> <span>{lang}wcf.user.objectWatch.button.subscribe{/lang}</span></a>
	{/if}
{/capture}

{capture assign='contentInteractionDropdownItems'}
	{if $__wcf->session->getPermission('mod.show.canEditEntry')}
		<li><a href="{link application='show' controller='EntryLog' id=$entry->entryID}{/link}">{lang}show.entry.log{/lang}</a></li>
	{/if}
{/capture}

{include file='header'}

{if $entry->isDisabled && !$__wcf->session->getPermission('mod.show.canModerateEntry')}
	<p class="info">{lang}show.entry.moderation.disabledEntry{/lang}</p>
{/if}

<div class="section tabMenuContainer">
	<nav class="tabMenu">
		<ul>
			<li><a href="{@$__wcf->getAnchor('tab1')}">{lang}{SHOW_TAB1_TITLE}{/lang}</a></li>
			{if $tabs[2]}<li><a href="{@$__wcf->getAnchor('tab2')}">{lang}{SHOW_TAB2_TITLE}{/lang}</a></li>{/if}
			{if $tabs[3]}<li><a href="{@$__wcf->getAnchor('tab3')}">{lang}{SHOW_TAB3_TITLE}{/lang}</a></li>{/if}
			{if $tabs[4]}<li><a href="{@$__wcf->getAnchor('tab4')}">{lang}{SHOW_TAB4_TITLE}{/lang}</a></li>{/if}
			{if $tabs[5]}<li><a href="{@$__wcf->getAnchor('tab5')}">{lang}{SHOW_TAB5_TITLE}{/lang}</a></li>{/if}
			
			{if $entry->enableComments}
				{if $commentList|count || $commentCanAdd}
					<li><a href="{@$__wcf->getAnchor('comments')}">{lang}show.entry.comments{/lang}{if $entry->comments} <span class="badge">{#$entry->comments}</span>{/if}</a></li>
				{/if}
			{/if}
			
			{event name='tabMenuTabs'}
		</ul>
	</nav>
	
	<div id="tab1" class="showEntryContent tabMenuContent{if $entry->getUserProfile()->userOnlineGroupID} userOnlineGroupMarking{@$entry->getUserProfile()->userOnlineGroupID}{/if}"
		{@$__wcf->getReactionHandler()->getDataAttributes('com.uz.show.likeableEntry', $entry->entryID)}
	>
		{assign var='objectID' value=$entry->entryID}
		
		<div class="section">
			{hascontent}
				<div class="section showEntryOptions">
					<dl>
						{content}
							{if !$options|empty}
								{foreach from=$options item=entryOptionData}
									{assign var=entryOption value=$entryOptionData[object]}
									{if $entryOption->tab == 1}
										{if $entryOption->getOptionValue()}
											<dt>{lang}{$entryOption->optionTitle}{/lang}</dt>
											<dd>{@$entryOption->getFormattedOptionValue()}</dd>
										{/if}
									{/if}
								{/foreach}
							{/if}
						{/content}
					</dl>
				</div>
			{/hascontent}
			
			<div class="section">
				<div class="section htmlContent">
					{if $entry->getTeaser()}
						<p class="showEntryTeaser">{$entry->getTeaser()}</p>
					{/if}
					
					{@$entry->getFormattedMessage()}
				</div>
			</div>
			
			{if !$tags|empty}
				<ul class="tagList section">
					{foreach from=$tags item=tag}
						<li><a href="{link controller='Tagged' object=$tag}objectType=com.uz.show.entry{/link}" class="tag">{$tag->name}</a></li>
					{/foreach}
				</ul>
			{/if}
			
			{if $entry->getDeleteNote()}
				<div class="section">
					<p class="showEntryDeleteNote">{@$entry->getDeleteNote()}</p>
				</div>
			{/if}
			
			{if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike') && $entryLikeData|isset}
				<div class="section">
					<div class="section reactionSummaryList">{include file="reactionSummaryList" reactionData=$entryLikeData objectType="com.uz.show.likeableEntry" objectID=$entry->entryID}</div>
				</div>
			{/if}
			
			<div class="section">
				<ul id="entryButtonContainer" class="buttonList smallButtons showEntryButtons buttonGroup jsEntryInlineEditorContainer" data-entry-id="{@$entry->entryID}">
					{if $entry->canEdit()}<li><a href="{link application='show' controller='EntryEdit' id=$entry->entryID}{/link}" class="button jsEntryInlineEditor" title="{lang}show.entry.edit{/lang}"><span class="icon icon16 fa-pencil"></span> <span>{lang}wcf.global.button.edit{/lang}</span></a></li>{/if}
					{if $entry->canEdit() && $entry->hasOldVersions()}
						<li><a href="{link controller='EditHistory' objectType='com.uz.show.entry' objectID=$entry->entryID}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}show.entry.hasOldVersions{/lang}</span></a></li>
					{/if}
					<li>
						<a href="{$entry->getLink()}" class="button wsShareButton jsOnly" data-link-title="{$entry->getSubject()}" data-bbcode="[entry]{@$entry->getObjectID()}[/entry]">
							<span class="icon icon16 fa-share-alt"></span> <span>{lang}wcf.message.share{/lang}</span>
						</a>
					</li>
			
					{event name='entryButtons'}
					
					{if $__wcf->session->getPermission('user.profile.canReportContent')}<li class="jsReportEntry jsOnly" data-object-id="{@$entry->entryID}"><a href="#" title="{lang}wcf.moderation.report.reportContent{/lang}" class="button jsTooltip"><span class="icon icon16 fa-warning"></span> <span class="invisible">{lang}wcf.moderation.report.reportContent{/lang}</span></a></li>{/if}
					{if MODULE_LIKE && $__wcf->getUser()->userID && $__wcf->getSession()->getPermission('user.like.canViewLike') && $entry->userID != $__wcf->user->userID}<li><a href="#" id="entryReactButton" class="reactButton jsTooltip button{if $entryLikeData[$entry->entryID]|isset && $entryLikeData[$entry->entryID]->reactionTypeID} active{/if}" title="{lang}wcf.reactions.react{/lang}" data-reaction-type-id="{if $entryLikeData[$entry->entryID]|isset && $entryLikeData[$entry->entryID]->reactionTypeID}{$entryLikeData[$entry->entryID]->reactionTypeID}{else}0{/if}"><span class="icon icon16 fa-smile-o"></span> <span class="invisible">{lang}wcf.reactions.react{/lang}</span></a></li>{/if}
					
				</ul>
			</div>
			
		</div>
	</div>
	
	<!-- Tab 2 -->
	{if $tabs[2]}
		<div id="tab2" class="tabMenuContent hidden">
			{if SHOW_TAB2_WYSIWYG}
				{hascontent}
					<section class="section htmlContent">
						{content}
							{@$entry->getFormattedMessage2()}
						{/content}
					</section>
				{/hascontent}
			{/if}
			
			{hascontent}
				<div class="section showEntryOptions">
					<dl>
						{content}
							{if !$options|empty}
								{foreach from=$options item=entryOptionData}
									{assign var=entryOption value=$entryOptionData[object]}
									{if $entryOption->tab == 2}
										{if $entryOption->getOptionValue()}
											<dt>{lang}{$entryOption->optionTitle}{/lang}</dt>
											<dd>{@$entryOption->getFormattedOptionValue()}</dd>
										{/if}
									{/if}
								{/foreach}
							{/if}
						{/content}
					</dl>
				</div>
			{/hascontent}
			
			{if SHOW_IMAGES_TAB == 2}
				{include application='show' file='entryAttachments'}
			{/if}
		</div>
	{/if}
	
	<!-- Tab 3 -->
	{if $tabs[3]}
		<div id="tab3" class="tabMenuContent hidden">
			{if SHOW_TAB3_WYSIWYG}
				{hascontent}
					<section class="section htmlContent">
						{content}
							{@$entry->getFormattedMessage3()}
						{/content}
					</section>
				{/hascontent}
			{/if}
			
			{hascontent}
				<div class="section showEntryOptions">
					<dl>
						{content}
							{if !$options|empty}
								{foreach from=$options item=entryOptionData}
									{assign var=entryOption value=$entryOptionData[object]}
									{if $entryOption->tab == 3}
										{if $entryOption->getOptionValue()}
											<dt>{lang}{$entryOption->optionTitle}{/lang}</dt>
											<dd>{@$entryOption->getFormattedOptionValue()}</dd>
										{/if}
									{/if}
								{/foreach}
							{/if}
						{/content}
					</dl>
				</div>
			{/hascontent}
			
			{if SHOW_IMAGES_TAB == 3}
				{include application='show' file='entryAttachments'}
			{/if}
		</div>
	{/if}
	
	<!-- Tab 4 -->
	{if $tabs[4]}
		<div id="tab4" class="tabMenuContent hidden">
			{if SHOW_TAB4_WYSIWYG}
				{hascontent}
					<section class="section htmlContent">
						{content}
							{@$entry->getFormattedMessage4()}
						{/content}
					</section>
				{/hascontent}
			{/if}
			
			{hascontent}
				<div class="section showEntryOptions">
					<dl>
						{content}
							{if !$options|empty}
								{foreach from=$options item=entryOptionData}
									{assign var=entryOption value=$entryOptionData[object]}
									{if $entryOption->tab == 4}
										{if $entryOption->getOptionValue()}
											<dt>{lang}{$entryOption->optionTitle}{/lang}</dt>
											<dd>{@$entryOption->getFormattedOptionValue()}</dd>
										{/if}
									{/if}
								{/foreach}
							{/if}
						{/content}
					</dl>
				</div>
			{/hascontent}
			
			{if SHOW_IMAGES_TAB == 4}
				{include application='show' file='entryAttachments'}
			{/if}
		</div>
	{/if}
	
	<!-- Tab 5 -->
	{if $tabs[5]}
		<div id="tab5" class="tabMenuContent hidden">
			{if SHOW_TAB5_WYSIWYG}
				{hascontent}
					<section class="section htmlContent">
						{content}
							{@$entry->getFormattedMessage5()}
						{/content}
					</section>
				{/hascontent}
			{/if}
			
			{hascontent}
				<div class="section showEntryOptions">
					<dl>
						{content}
							{if !$options|empty}
								{foreach from=$options item=entryOptionData}
									{assign var=entryOption value=$entryOptionData[object]}
									{if $entryOption->tab == 5}
										{if $entryOption->getOptionValue()}
											<dt>{lang}{$entryOption->optionTitle}{/lang}</dt>
											<dd>{@$entryOption->getFormattedOptionValue()}</dd>
										{/if}
									{/if}
								{/foreach}
							{/if}
						{/content}
					</dl>
				</div>
			{/hascontent}
			
			{if SHOW_IMAGES_TAB == 5}
				{include application='show' file='entryAttachments'}
			{/if}
		</div>
	{/if}
		
	{if $entry->enableComments}
		{if $commentList|count || $commentCanAdd}
			<div id="comments" class="tabMenuContent">
				{include file='__commentJavaScript' commentContainerID='showEntryCommentList'}
				
				<ul id="showEntryCommentList" class="commentList containerList" data-can-add="{if $commentCanAdd}true{else}false{/if}" data-object-id="{@$entryID}" data-object-type-id="{@$commentObjectTypeID}" data-comments="{if $entry->comments}{@$commentList->countObjects()}{else}0{/if}" data-last-comment-time="{@$lastCommentTime}">
					{include file='commentListAddComment' wysiwygSelector='showEntryCommentListAddComment'}
					{include file='commentList'}
				</ul>
			</div>
		{/if}
	{/if}
	
	{event name='tabMenuContents'}
</div>

{if GOOGLE_MAPS_API_KEY && SHOW_GEODATA_TYPE != 1 && SHOW_GEODATA_BOX_ENABLE && !$entry->location|empty}
	{include file='googleMapsJavaScript'}
{/if}

<script data-relocate="true">
	$(function() {
		WCF.Language.addObject({
			'show.entry.edit.delete':					'{jslang}show.entry.edit.delete{/jslang}',
			'show.entry.edit.disable':					'{jslang}show.entry.edit.disable{/jslang}',
			'show.entry.edit.enable':					'{jslang}show.entry.edit.enable{/jslang}',
			'show.entry.edit.restore':					'{jslang}show.entry.edit.restore{/jslang}',
			'show.entry.edit.setAsFeatured':			'{jslang}show.entry.edit.setAsFeatured{/jslang}',
			'show.entry.edit.trash':					'{jslang}show.entry.edit.trash{/jslang}',
			'show.entry.edit.unsetAsFeatured':			'{jslang}show.entry.edit.unsetAsFeatured{/jslang}',
			'show.entry.featured':						'{jslang}show.entry.featured{/jslang}',
			'show.entry.confirmDelete':					'{jslang}show.entry.confirmDelete{/jslang}',
			'show.entry.confirmTrash':					'{jslang}show.entry.confirmTrash{/jslang}',
			'show.entry.confirmTrash.reason':			'{jslang}show.entry.confirmTrash.reason{/jslang}',
			'wcf.message.share':						'{jslang}wcf.message.share{/jslang}',
			'wcf.message.share.permalink':				'{jslang}wcf.message.share.permalink{/jslang}',
			'wcf.message.share.permalink.bbcode':		'{jslang}wcf.message.share.permalink.bbcode{/jslang}',
			'wcf.message.share.permalink.html':			'{jslang}wcf.message.share.permalink.html{/jslang}',
			'wcf.moderation.report.reportContent':		'{jslang}wcf.moderation.report.reportContent{/jslang}',
			'wcf.moderation.report.success':			'{jslang}wcf.moderation.report.success{/jslang}',
			'wcf.user.objectWatch.manageSubscription':	'{jslang}wcf.user.objectWatch.manageSubscription{/jslang}'
		});
		
		var $updateHandler = new Show.Entry.UpdateHandler.Entry();
		var $inlineEditor = new Show.Entry.InlineEditor('.jsEntryInlineEditorContainer');
		$inlineEditor.setEnvironment('entry', '{link application='show' controller='EntryList' encode=false}{/link}');
		$inlineEditor.setUpdateHandler($updateHandler);
		$inlineEditor.setPermissions({
			canDeleteEntry:				{if $entry->canDelete()}1{else}0{/if},
			canDeleteEntryCompletely:	{@$__wcf->session->getPermission('mod.show.canDeleteEntryCompletely')|intval},
			canEnableEntry:				{@$__wcf->session->getPermission('mod.show.canModerateEntry')|intval},
			canRestoreEntry:			{@$__wcf->session->getPermission('mod.show.canRestoreEntry')|intval},
			canSetAsFeatured:			{@$__wcf->session->getPermission('mod.show.canEditEntry')|intval},
			canViewDeletedEntry:		{@$__wcf->session->getPermission('mod.show.canViewDeletedEntry')|intval}
		});
		
		{if $__wcf->session->getPermission('user.profile.canReportContent')}
			new WCF.Moderation.Report.Content('com.uz.show.entry', '.jsReportEntry');
		{/if}
		
		new WCF.User.ObjectWatch.Subscribe();
		new WCF.Message.BBCode.CodeViewer();
		
		WCF.Clipboard.init('show\\page\\EntryPage', {@$hasMarkedItems}, { }, 0);
		
		{if GOOGLE_MAPS_API_KEY && SHOW_GEODATA_TYPE != 1 && SHOW_GEODATA_BOX_ENABLE && !$entry->location|empty}
			var $map = new WCF.Location.GoogleMaps.Map('imageMap');
			WCF.Location.GoogleMaps.Util.focusMarker($map.addMarker({@$entry->latitude}, {@$entry->longitude}, '{$entry->getSubject()|encodeJS}'));
		{/if}
	});
</script>

{if MODULE_LIKE && $__wcf->getUser()->userID && $__wcf->getSession()->getPermission('user.like.canViewLike')}
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Ui/Reaction/Handler'], function(UiReactionHandler) {
			new UiReactionHandler('com.uz.show.likeableEntry', {
				// settings
				isSingleItem: true,
				
				// selectors
				buttonSelector: '#entryReactButton',
				containerSelector: '#tab1',
				summaryListSelector: '.reactionSummaryList'
			});
		});
	</script>
{/if}

{if $showContact}
	<script data-relocate="true">
		require(['Language', 'UZ/Show/OpenContact'], function(Language, UZShowOpenContact) {
			Language.addObject({
				'show.contact.open':	'{jslang}show.contact.open{/jslang}',
				'show.contact.dialog':	'{jslang}show.contact.dialog{/jslang}'
			});
			
			UZShowOpenContact.init();
		});
	</script>
{/if}

{event name='additionalJavascript'}

{include file='footer'}
