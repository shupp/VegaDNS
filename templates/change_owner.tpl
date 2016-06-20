<form action="{$php_self}">
<input type="hidden" name="state" value="{$state}">
<input type="hidden" name="mode" value="domains">
<input type="hidden" name="{$session_name}" value="{$session_id}">
<input type="hidden" name="domain_mode" value="change_owner_now">
<input type="hidden" name="domain_id" value="{$domain_id}">
<input type="hidden" name="domain" value="{$domain}">
    <div class="row">
        <div class="small-12 medium-8 small-centered">
            <div class="callout warning">
                Change Ownership for {$domain|escape}
            </div>
            <p><strong>Current Owner: </strong>{$owner_row}</p>
            <p><strong>Current Group Owner: </strong>{$group_owner_row}</p>
            <label for="new_owner">
                New Owner (email address)
                {if $user_account_type == 'group_admin'}
                    <select name="email_address">
                        {html_options values=$users_email_array output=$users_email_array selected=$user_email_selected}
                    </select>
                {else if $user_account_type  == 'senior_admin'}
                    <input type="text" name="email_address" value="{$email_address}">
                {/if}
            </label>
            {if $user_account_type == 'senior_admin'}
                <label for="new_group_owner">
                    New Group Owner (email address)
                    <input type="text" name="group_email_address" value="{$group_email_address}">
                </label>
            {/if}
            <input type="submit" value="save" class="button expanded right">
        </div>
    </div>
</form>
