<form action="{$php_self}">
<input type="hidden" name="state" value="{$state}">
<input type="hidden" name="mode" value="users">
{if $cid != ""}
<input type="hidden" name="cid" value="{$cid}">
{/if}
<input type="hidden" name="{$session_name}" value="{$session_id}">
<input type="hidden" name="user_mode" value="{$user_mode_next}">
    <div class="row">
        <div class="small-12 medium-8 small-centered columns">
            <h3>{$account_title|escape}</h3>
            <div class="row">
                <div class="small-12 large-6 columns">
                    <label for="first_name">
                        First Name:
                        <input id="first_name" type="text" name="first_name" value="{$first_name|escape}">
                    </label>
                </div>
                <div class="small-12 large-6 columns">
                    <label for="last_name">
                        Last Name:
                        <input id="last_name" type="text" name="last_name" value="{$last_name|escape}">
                    </label>
                </div>
                <div class="small-12 large-6 columns">
                    <label for="email_address">
                        Email Address:
                        <input id="email_address" type="text" name="email_address" value="{$email_address|escape}">
                    </label>
                </div>
                <div class="small-12 large-6 columns">
                    <label for="phone_number">
                        Phone Number:
                        <input id="phone_number" type="text" name="phone" value="{$phone|escape}">
                    </label>
                </div>
                <div class="small-12 large-6 columns">
                    <label for="password">
                        Password:
                        <input id="password" type="password" name="password">
                    </label>
                </div>
                <div class="small-12 large-6 columns">
                    <label for="retype_password">
                        Re-Type Password:
                        <input id="retype_password" type="password" name="password2">
                    </label>
                </div>
                {if $user_account_type == 'senior_admin'}
                    <div class="small-12 large-6 columns">
                        <label for="account_type">
                            Account Type:
                            <select id="account_type" name="account_type">
                                {html_options values=$select_account_type selected=$type_selected output=$select_account_type}
                            </select>
                        </label>
                    </div>
                    <div class="small-12 large-6 columns">
                        <label for="account_status">
                            Account Status:
                            <select id="account_status" name="status">
                                {html_options values=$select_status selected=$status_selected output=$select_status}
                            </select>
                        </label>
                    </div>
                {else}
                    {if $user_mode != 'add_account' && $user_mode != 'add_account_now'}
                        <div class="small-12 large-6 columns">
                            <div class="callout">
                                <strong>Account Type:</strong>
                                {$account_type}
                            </div>
                        </div>
                        <div class="small-12 columns">
                            <div class="callout">
                                <strong>Status</strong>
                                {$account_status}
                            </div>
                        </div>
                    {/if}
                {/if}

                {if $user_account_type == 'senior_admin'}
                    <div class="small-12 columns">
                        Group Owner:
                        <input type="text" name="group_email_address" value="{$group_email_address}">
                    </div>
                {/if}
                <div class="small-12 columns">
                    <input type="submit" value="{$submit}" class="button expanded">
                </div>
            </div>
        </div>
    </div>
</form>
