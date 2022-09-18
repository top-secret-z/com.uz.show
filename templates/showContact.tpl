{capture assign='pageTitle'}{lang}show.contact.edit{/lang}{/capture}
{capture assign='contentTitle'}{lang}show.contact.edit{/lang}{/capture}


{include file='userMenuSidebar'}

{include file='header' __disableAds=true __sidebarLeftHasMenu=true}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.edit{/lang}</p>
{/if}

{include file='formError'}

<form method="post" action="{link application='show' controller='ShowContact'}{/link}">
	<section class="section">
		<dl>
			<dt></dt>
			<dd>
				<label><input type="checkbox" name="isDisabled" value="1"{if $isDisabled} checked{/if}> {lang}show.contact.isDisabled{/lang}</label>
			</dd>
		</dl>
		
		<dl{if $errorField == 'name'} class="formError"{/if}>
			<dt><label for="name">{lang}show.contact.name{/lang}</label></dt>
			<dd>
				<input type="text" name="name" id="name" value="{$name}" class="long" maxlength="255">
				{if $errorField == 'name'}
					<small class="innerError">
						{lang}show.contact.name.error.{@$errorType}{/lang}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'address'} class="formError"{/if}>
			<dt><label for="address">{lang}show.contact.address{/lang}</label></dt>
			<dd>
				<textarea id="address" name="address" cols="40" rows="3">{$address}</textarea>
				{if $errorField == 'address'}
					<small class="innerError">
						{lang}show.contact.address.error.{@$errorType}{/lang}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'email'} class="formError"{/if}>
			<dt><label for="email">{lang}show.contact.email{/lang}</label></dt>
			<dd>
				<input type="text" name="email" id="email" value="{$email}" class="long" maxlength="255">
				{if $errorField == 'email'}
					<small class="innerError">
						{lang}show.contact.email.error.{@$errorType}{/lang}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'website'} class="formError"{/if}>
			<dt><label for="website">{lang}show.contact.website{/lang}</label></dt>
			<dd>
				<input type="text" name="website" id="website" value="{$website}" class="long" maxlength="255">
				{if $errorField == 'website'}
					<small class="innerError">
						{lang}show.contact.website.error.{@$errorType}{/lang}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'other'} class="formError"{/if}>
			<dt><label for="other">{lang}show.contact.other{/lang}</label></dt>
			<dd>
				<textarea id="other" name="other" cols="40" rows="3">{$other}</textarea>
				{if $errorField == 'other'}
					<small class="innerError">
						{lang}show.contact.website.error.{@$errorType}{/lang}
					</small>
				{/if}
			</dd>
		</dl>
		
	</section>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer'}
