<form action="{$php_self}">
<input type="hidden" name="state" value="{$state}">
<input type="hidden" name="mode" value="users">
{if $cid != ""}
<input type="hidden" name="cid" value="{$cid}">
{/if}
<input type="hidden" name="{$session_name}" value="{$session_id}">
<input type="hidden" name="user_mode" value="{$user_mode_next}">

<table border=0 bgcolor="white">
<tr><td>
    <table border=0 width="100%">
    <tr bgcolor="#cccccc"><td align="center">{$account_title|escape}</td></tr>
    </table>

    <table border=0 width=100%>
    <tr bgcolor="#eeeeee">
        <td>First Name:</td>
        <td><input type="text" name="first_name" value="{$first_name|escape}"></td>
    </tr>
    <tr bgcolor="#eeeeee">
        <td>Last Name:</td>
        <td><input type="text" name="last_name" value="{$last_name|escape}"></td>
    </tr>
    <tr bgcolor="#eeeeee">
        <td>Email Address:</td>
        <td><input type="text" name="email_address" value="{$email_address|escape}"></td>
    </tr>
    <tr bgcolor="#eeeeee">
        <td>Phone Number:</td>
        <td><input type="text" name="phone" value="{$phone|escape}"></td>
    </tr>
    <tr bgcolor="#eeeeee">
        <td>Password:</td>
        <td><input type="password" name="password"></td>
    </tr>
    <tr bgcolor="#eeeeee">
        <td>Re-Type Password:</td>
        <td><input type="password" name="password2"></td>
    {if $user_account_type == 'senior_admin'}
    <tr bgcolor=#eeeeee>
        <td>Account Type:</td>
        <td><select name="account_type">
                {html_options values=$select_account_type selected=$type_selected output=$select_account_type}
            </select>
        </td>
    </tr>
    <tr bgcolor="#eeeeee">
        <td>Account Status:</td>
        <td><select name="status">
                {html_options values=$select_status selected=$status_selected output=$select_status}
            </select>
        </td>
    </tr>
    {else}
         {if $user_mode != 'add_account' && $user_mode != 'add_account_now'}
        <tr bgcolor="#eeeeee">
            <td>Account Type:</td>
            <td>{$account_type}</td>
        </tr>
        <tr bgcolor="#eeeeee">
            <td>Status</td>
            <td>{$account_status}</td>
        {/if}
    {/if}

    {if $user_account_type == 'senior_admin'}
    <tr bgcolor="#eeeeee">
        <td>Group Owner:</td>
        <td><input type="text" name="group_email_address" value="{$group_email_address}">
        </td>
    </tr>
    {/if}
    </table>
</td></tr>
</table>
    <br><input type="submit" value="{$submit}">
</form>
