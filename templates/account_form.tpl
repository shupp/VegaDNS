<form action="{$php_self}" method="POST">
<input type="hidden" name="state" value="{$state}">
<input type="hidden" name="mode" value="users">
{if $user_id != ""}
<input type="hidden" name="user_id" value="{$user_id}">
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
        </tr>
        </table>
    </tr>
    </table>
        {/if}
    {/if}

<!-- PERMISSIONS -->

    <table width="100%">
    <tr>
    <td class="border">
    <table width="100%" border="0" cellspacing="5" cellpadding="0" bgcolor="white">
        <tr>
            <td class="underline"><p><br>Permissions</td>
        </tr>
    </table>
    <table width="100%" border="0" cellspacing="5" cellpadding="0" bgcolor="white">
    <tr><td align="center">
    <table border="0" cellspacing="5" cellpadding="0" bgcolor="white">
        <tr>
            <td align="center" colspan="5" class="border"><input type="radio" name="perms_type" value="inherit" {if $perms_type == 'inherit'}checked{/if}> By default, inherit permissions</td>
        </tr>
        <tr>
            <td align="right">Group:</td>
                <td>{if $group_perms.group_edit == 1}+{else}-{/if} Edit</td>
                <td>{if $group_perms.group_create == 1}+{else}-{/if} Create</td>
                <td>{if $group_perms.group_delete == 1}+{else}-{/if} Delete</td>
            <td></td>
        </tr>
        <tr>
            <td align="right">User:</td>
                <td>{if $group_perms.accouedit == 1}+{else}-{/if} Edit</td>
                <td>{if $group_perms.accoucreate == 1}+{else}-{/if} Create</td>
                <td>{if $group_perms.accoudelete == 1}+{else}-{/if} Delete</td>
            <td></td>
        </tr>
        <tr>
            <td align="right">Domain:</td>
                <td>{if $group_perms.domain_edit == 1}+{else}-{/if} Edit</td>
                <td>{if $group_perms.domain_create == 1}+{else}-{/if} Create</td>
                <td>{if $group_perms.domain_delete == 1}+{else}-{/if} Delete</td>
                <td>{if $group_perms.domain_delegate == 1}+{else}-{/if} Delegate</td>
        </tr>
        <tr>
            <td align="right">Domain Record:</td>
                <td>{if $group_perms.record_edit == 1}+{else}-{/if} Edit</td>
                <td>{if $group_perms.record_create == 1}+{else}-{/if} Create</td>
                <td>{if $group_perms.record_delete == 1}+{else}-{/if} Delete</td>
                <td>{if $group_perms.record_delegate == 1}+{else}-{/if} Delegate</td>
        </tr>
        <tr>
            <td align="right">Self:</td>
                <td>{if $group_perms.self_edit == 1}+{else}-{/if} Edit</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>
    </td>
    </tr>
    </table>
    </td>
    </tr>


<!-- /PERMISSIONS -->
<!-- PERMISSIONS2 -->

    <table width="100%">
    <tr>
    <td class="border">
    <table width="100%" border="0" cellspacing="5" cellpadding="0" bgcolor="white">
    <tr><td align="center">
    <table border="0" cellspacing="5" cellpadding="0" bgcolor="white">
        <tr>
            <td align="center" colspan="5" class="border" align="left"><input type="radio" name="perms_type" value="define"{if $perms_type == 'define'}checked{/if}>Or, define permissions:</td>
        </tr>
        <tr>
            <td align="right">Group:</td>
            {if $default_perms.group_edit == 1}
                <td><input type="checkbox" name="group_edit" {if $user_perms.group_edit == 1}checked{/if}> Edit</td>
            {else}
                <td class="greyed" align="right"> - Edit</td>
            {/if}
            {if $default_perms.group_create == 1}
                <td><input type="checkbox" name="group_create" {if $user_perms.group_create == 1}checked{/if}> Create</td>
            {else}
                <td class="greyed" align="right"> - Create</td>
            {/if}
            {if $default_perms.group_delete == 1}
                <td><input type="checkbox" name="group_delete" {if $user_perms.group_delete == 1}checked{/if}> Delete</td>
            {else}
                <td class="greyed" align="right"> - Delete</td>
            {/if}
            <td></td>
        </tr>
        <tr>
            <td align="right">User:</td>
            {if $default_perms.accouedit == 1}
                <td><input type="checkbox" name="accouedit" {if $user_perms.accouedit == 1}checked{/if}> Edit</td>
            {else}
                <td class="greyed" align="right"> - Edit</td>
            {/if}
            {if $default_perms.accoucreate == 1}
                <td><input type="checkbox" name="accoucreate" {if $user_perms.accoucreate == 1}checked{/if}> Create</td>
            {else}
                <td class="greyed" align="right"> - Create</td>
            {/if}
            {if $default_perms.accoudelete == 1}
                <td><input type="checkbox" name="accoudelete" {if $user_perms.accoudelete == 1}checked{/if}> Delete</td>
            {else}
                <td class="greyed" align="right"> - Delete</td>
            {/if}
            <td></td>
        </tr>
        <tr>
            <td align="right">Domain:</td>
            {if $default_perms.domain_edit == 1}
                <td><input type="checkbox" name="domain_edit" {if $user_perms.domain_edit == 1}checked{/if}> Edit</td>
            {else}
                <td class="greyed" align="right"> - Edit</td>
            {/if}
            {if $default_perms.domain_create == 1}
                <td><input type="checkbox" name="domain_create" {if $user_perms.domain_create == 1}checked{/if}> Create</td>
            {else}
                <td class="greyed" align="right"> - Create</td>
            {/if}
            {if $default_perms.domain_delete == 1}
                <td><input type="checkbox" name="domain_delete" {if $user_perms.domain_delete == 1}checked{/if}> Delete</td>
            {else}
                <td class="greyed" align="right"> - Delete</td>
            {/if}
            {if $default_perms.domain_delegate == 1}
                <td><input type="checkbox" name="domain_delegate" {if $user_perms.domain_delegate == 1}checked{/if}> Delegate</td>
            {else}
                <td class="greyed" align="right"> - Delegate</td>
            {/if}
        </tr>
        <tr>
            <td align="right">Domain Record:</td>
            {if $default_perms.record_edit == 1}
                <td><input type="checkbox" name="record_edit" {if $user_perms.record_edit == 1}checked{/if}> Edit</td>
            {else}
                <td class="greyed" align="right"> - Edit</td>
            {/if}
            {if $default_perms.record_create == 1}
                <td><input type="checkbox" name="record_create" {if $user_perms.record_create == 1}checked{/if}> Create</td>
            {else}
                <td class="greyed" align="right"> - Create</td>
            {/if}
            {if $default_perms.record_delete == 1}
                <td><input type="checkbox" name="record_delete" {if $user_perms.record_delete == 1}checked{/if}> Delete</td>
            {else}
                <td class="greyed" align="right"> - Delete</td>
            {/if}
            {if $default_perms.record_delegate == 1}
                <td><input type="checkbox" name="record_delegate" {if $user_perms.record_delegate == 1}checked{/if}> Delegate</td>
            {else}
                <td class="greyed" align="right"> - Delegate</td>
            {/if}
        </tr>
        <tr>
            <td align="right">Self:</td>
            {if $default_perms.self_edit == 1}
                <td><input type="checkbox" name="self_edit" {if $user_perms.self_edit == 1}checked{/if}> Edit</td>
            {else}
                <td class="greyed" align="right"> - Edit</td>
            {/if}
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>
    </td>
    </tr>
    </table>
    </td>
    </tr>

<!-- /PERMISSIONS2 -->



    </table>
</td></tr>
</table>
    <br><input type="submit" value="{$submit}">
</form>
