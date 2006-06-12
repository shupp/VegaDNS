    <table width="90%">
    <tr>
    <td class="border">
    <form action="{$php_self}" method="post">
    <input type="hidden" name="{$session_name}" value="{$session_id}">
    <input type="hidden" name="state" value="{$state}">
    <input type="hidden" name="mode" value="{$mode}">
    <input type="hidden" name="group_mode" value="edit_sub_now">
    <input type="hidden" name="group_to_edit" value="{$group_to_edit}">
    <table width="100%" border="0" cellspacing="5" cellpadding="0" bgcolor="white">
        <tr>
            <td class="underline"><p><br>Edit SubGroup</td>
        </tr>
    </table>
    <table width="100%" border="0" cellspacing="5" cellpadding="0" bgcolor="white">
    <tr><td align="center">
    <table border="0" cellspacing="5" cellpadding="0" bgcolor="white">
        <tr>
            <td align="right">Group Name:</td>
            <td colspan="4">{$group_name}</td>
        </tr>
        <tr>
            <td align="center" colspan="5" class="border">By default, users of this group will have the following privileges:</td>
        </tr>
        <tr>
            <td align="right">Group:</td>
            {if $default_perms.group_edit == 1}
                <td><input type="checkbox" name="group_edit" {if $group_perms.group_edit == 1}checked{/if}> Edit</td>
            {else}
                <td class="greyed" align="right"> - Edit</td>
            {/if}
            {if $default_perms.group_create == 1}
                <td><input type="checkbox" name="group_create" {if $group_perms.group_create == 1}checked{/if}> Create</td>
            {else}
                <td class="greyed" align="right"> - Create</td>
            {/if}
            {if $default_perms.group_delete == 1}
                <td><input type="checkbox" name="group_delete" {if $group_perms.group_delete == 1}checked{/if}> Delete</td>
            {else}
                <td class="greyed" align="right"> - Delete</td>
            {/if}
            <td></td>
        </tr>
        <tr>
            <td align="right">User:</td>
            {if $default_perms.accouedit == 1}
                <td><input type="checkbox" name="accouedit" {if $group_perms.accouedit == 1}checked{/if}> Edit</td>
            {else}
                <td class="greyed" align="right"> - Edit</td>
            {/if}
            {if $default_perms.accoucreate == 1}
                <td><input type="checkbox" name="accoucreate" {if $group_perms.accoucreate == 1}checked{/if}> Create</td>
            {else}
                <td class="greyed" align="right"> - Create</td>
            {/if}
            {if $default_perms.accoudelete == 1}
                <td><input type="checkbox" name="accoudelete" {if $group_perms.accoudelete == 1}checked{/if}> Delete</td>
            {else}
                <td class="greyed" align="right"> - Delete</td>
            {/if}
            <td></td>
        </tr>
        <tr>
            <td align="right">Domain:</td>
            {if $default_perms.domain_edit == 1}
                <td><input type="checkbox" name="domain_edit" {if $group_perms.domain_edit == 1}checked{/if}> Edit</td>
            {else}
                <td class="greyed" align="right"> - Edit</td>
            {/if}
            {if $default_perms.domain_create == 1}
                <td><input type="checkbox" name="domain_create" {if $group_perms.domain_create == 1}checked{/if}> Create</td>
            {else}
                <td class="greyed" align="right"> - Create</td>
            {/if}
            {if $default_perms.domain_delete == 1}
                <td><input type="checkbox" name="domain_delete" {if $group_perms.domain_delete == 1}checked{/if}> Delete</td>
            {else}
                <td class="greyed" align="right"> - Delete</td>
            {/if}
            {if $default_perms.domain_delegate == 1}
                <td><input type="checkbox" name="domain_delegate" {if $group_perms.domain_delegate == 1}checked{/if}> Delegate</td>
            {else}
                <td class="greyed" align="right"> - Delegate</td>
            {/if}
        </tr>
        <tr>
            <td align="right">Domain Record:</td>
            {if $default_perms.record_edit == 1}
                <td><input type="checkbox" name="record_edit" {if $group_perms.record_edit == 1}checked{/if}> Edit</td>
            {else}
                <td class="greyed" align="right"> - Edit</td>
            {/if}
            {if $default_perms.record_create == 1}
                <td><input type="checkbox" name="record_create" {if $group_perms.record_create == 1}checked{/if}> Create</td>
            {else}
                <td class="greyed" align="right"> - Create</td>
            {/if}
            {if $default_perms.record_delete == 1}
                <td><input type="checkbox" name="record_delete" {if $group_perms.record_delete == 1}checked{/if}> Delete</td>
            {else}
                <td class="greyed" align="right"> - Delete</td>
            {/if}
            {if $default_perms.record_delegate == 1}
                <td><input type="checkbox" name="record_delegate" {if $group_perms.record_delegate == 1}checked{/if}> Delegate</td>
            {else}
                <td class="greyed" align="right"> - Delegate</td>
            {/if}
        </tr>
        <tr>
            <td align="right">Self:</td>
            {if $default_perms.self_edit == 1}
                <td><input type="checkbox" name="self_edit" {if $group_perms.self_edit == 1}checked{/if}> Edit</td>
            {else}
                <td class="greyed" align="right"> - Edit</td>
            {/if}
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>
    <input type="submit" value="edit">
    </td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    </form>
