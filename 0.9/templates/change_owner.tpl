<form action="{$php_self}">
<input type="hidden" name="state" value="{$state}">
<input type="hidden" name="mode" value="domains">
<input type="hidden" name="{$session_name}" value="{$session_id}">
<input type="hidden" name="domain_mode" value="change_owner_now">
<input type="hidden" name="domain_id" value="{$domain_id}">
<input type="hidden" name="domain" value="{$domain}">

<table border=0 bgcolor="white">
<tr><td>
    <table border=0 width="100%">
    <tr bgcolor="#cccccc">
        <td align="center">Change Ownership for {$domain|escape}</td>
    </tr>
    </table>

    <table border=0 width="100%">
    <tr bgcolor="#eeeeee">
        <td>Current Owner:&nbsp</td>
        <td>{$owner_row}</td>
    </tr>
    <tr bgcolor="#eeeeee">
        <td>Current Group Owner:&nbsp</td>
        <td>{$group_owner_row}</td>
    </tr>
    <tr bgcolor="#eeeeee">
        <td nowrap>New Owner (email address)</td>
        <td>

        {if $user_account_type == 'group_admin'}
            <select name="email_address">
            {html_options values=$users_email_array output=$users_email_array selected=$user_email_selected}
            </select>
        {else if $user_account_type  == 'senior_admin'}
            <input type="text" name="email_address" value="{$email_address}">
        {/if}
        </td>
    </tr>
    {if $user_account_type == 'senior_admin'}
    <tr bgcolor="#eeeeee">
        <td nowrap>New Group Owner (email address)</td>
        <td><input type="text" name="group_email_address" value="{$group_email_address}">
        </td>
    </tr>
    {/if}
    </table>
</td></tr>
</table>
    <br><input type="submit" value="save">
</form>
