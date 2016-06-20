<form action="{$php_self}">
<input type="hidden" name="state" value="{$state|escape:'html'}">
<input type="hidden" name="mode" value="domains">
<input type="hidden" name="{$session_name|escape:'html'}" value="{$session_id|escape:'html'}">
<input type="hidden" name="domain_mode" value="add_now">
    <div class="row">
        <div class="small-12 medium-8 columns small-centered">
            <h3>Add Domain</h3>
            <input type="text" name="domain" value="{$domain|escape:'html'}">
            <input type="submit" value="Add Domain" class="button expanded">
        </div>
    </div>
</form>
