<article class="message messageReduced">
	<section class="messageContent">
		<header class="messageHeader">
			<div class="box32 messageHeaderWrapper">
				{if $entry->userID}
					<a href="{link controller='User' object=$entry->getUserProfile()}{/link}" aria-hidden="true">{@$entry->getUserProfile()->getAvatar()->getImageTag(32)}</a>
				{else}
					<span>{@$entry->getUserProfile()->getAvatar()->getImageTag(32)}</span>
				{/if}
				
				<div class="messageHeaderBox">
					<h2 class="messageTitle">
						<a href="{link application='show' controller='Entry' object=$entry}{/link}">{$entry->getSubject()}</a>
					</h2>
					
					<ul class="messageHeaderMetaData">
						<li>{if $entry->userID}<a href="{link controller='User' object=$entry->getUserProfile()}{/link}" class="username">{$entry->username}</a>{else}<span class="username">{$entry->username}</span>{/if}</li>
						<li><span class="messagePublicationTime">{if SHOW_LAST_CHANGE_TIME}{@$entry->lastChangeTime|time}{else}{@$entry->time|time}{/if}</span></li>
						
						{event name='messageHeaderMetaData'}
					</ul>
					
					<ul class="messageStatus">
						{if $entry->isDeleted}<li><span class="badge label red jsIconDeleted">{lang}wcf.message.status.deleted{/lang}</span></li>{/if}
						{if $entry->isDisabled}<li><span class="badge label green jsIconDisabled">{lang}wcf.message.status.disabled{/lang}</span></li>{/if}
						
						{event name='messageStatus'}
					</ul>
				</div>
			</div>
		</header>
		
		<div class="messageBody">
			{event name='beforeMessageText'}
			
			<div class="messageText">
				{@$entry->getFormattedMessage()}
			</div>
			
			{event name='afterMessageText'}
		</div>
		
		<footer class="messageFooter">
			{event name='messageFooter'}
			
			<div class="messageFooterGroup">
				<ul class="messageFooterButtons buttonList smallButtons">
					
					{event name='messageFooterButtons'}
				</ul>
			</div>
		</footer>
	</section>
</article>
