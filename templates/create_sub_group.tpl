    <table width="90%">
    <tr>
    <td class="border">
    <form action="{$php_self}" method="post">
    <input type="hidden" name="{$session_name}" value="{$session_id}">
    <input type="hidden" name="state" value="{$state}">
    <input type="hidden" name="mode" value="{$mode}">
    <input type="hidden" name="group_mode" value="create_sub_now">
    <input type="hidden" name="group" value="{$group}">
    <table width="100%" border="0" cellspacing="5" cellpadding="0" bgcolor="white">
        <tr>
            <td class="underline"><p><br>Create New SubGroup</td>
        </tr>
    </table>
    <table width="100%" border="0" cellspacing="5" cellpadding="0" bgcolor="white">
    <tr><td align="center">
    <table border="0" cellspacing="5" cellpadding="0" bgcolor="white">
        <tr>
            <td align="right">Group Name:</td>
            <td colspan="4"><input type="text" name="name" value="{$name}"></td>
        </tr>
        <tr>
            <td align="center" colspan="5" class="border">By default, users of this group will have the following privileges:</td>
        </tr>
        <tr>
            <td align="right">Group:</td>
            {if $default_perms.group_edit == 1}
                <td><input type="checkbox" name="group_edit" checked> Edit</td>
            {else}
                <td class="greyed" align="right"> - Edit</td>
            {/if}
            {if $default_perms.group_create == 1}
                <td><input type="checkbox" name="group_create" checked> Create</td>
            {else}
                <td class="greyed" align="right"> - Create</td>
            {/if}
            {if $default_perms.group_delete == 1}
                <td><input type="checkbox" name="group_delete" checked> Delete</td>
            {else}
                <td class="greyed" align="right"> - Delete</td>
            {/if}
            <td></td>
        </tr>
        <tr>
            <td align="right">User:</td>
            {if $default_perms.accouedit == 1}
                <td><input type="checkbox" name="accouedit" checked> Edit</td>
            {else}
                <td class="greyed" align="right"> - Edit</td>
            {/if}
            {if $default_perms.accoucreate == 1}
                <td><input type="checkbox" name="accoucreate" checked> Create</td>
            {else}
                <td class="greyed" align="right"> - Create</td>
            {/if}
            {if $default_perms.accoudelete == 1}
                <td><input type="checkbox" name="accoudelete" checked> Delete</td>
            {else}
                <td class="greyed" align="right"> - Delete</td>
            {/if}
            <td></td>
        </tr>
        <tr>
            <td align="right">Domain:</td>
            {if $default_perms.domain_edit == 1}
                <td><input type="checkbox" name="domain_edit" checked> Edit</td>
            {else}
                <td class="greyed" align="right"> - Edit</td>
            {/if}
            {if $default_perms.domain_edit == 1}
                <td><input type="checkbox" name="domain_create" checked> Create</td>
            {else}
                <td class="greyed" align="right"> - Create</td>
            {/if}
            {if $default_perms.domain_delete == 1}
                <td><input type="checkbox" name="domain_delete" checked> Delete</td>
            {else}
                <td class="greyed" align="right"> - Delete</td>
            {/if}
            {if $default_perms.domain_delegate == 1}
                <td><input type="checkbox" name="domain_delegate" checked> Delegate</td>
            {else}
                <td class="greyed" align="right"> - Delegate</td>
            {/if}
        </tr>
        <tr>
            <td align="right">Domain Record:</td>
            {if $default_perms.record_edit == 1}
                <td><input type="checkbox" name="record_edit" checked> Edit</td>
            {else}
                <td class="greyed" align="right"> - Edit</td>
            {/if}
            {if $default_perms.record_create == 1}
                <td><input type="checkbox" name="record_create" checked> Create</td>
            {else}
                <td class="greyed" align="right"> - Create</td>
            {/if}
            {if $default_perms.record_delete == 1}
                <td><input type="checkbox" name="record_delete" checked> Delete</td>
            {else}
                <td class="greyed" align="right"> - Delete</td>
            {/if}
            {if $default_perms.record_delegate == 1}
                <td><input type="checkbox" name="record_delegate" checked> Delegate</td>
            {else}
                <td class="greyed" align="right"> - Delegate</td>
            {/if}
        </tr>
        <tr>
            <td align="right">Self:</td>
            {if $default_perms.self_edit == 1}
                <td><input type="checkbox" name="self_edit" checked> Edit</td>
            {else}
                <td class="greyed" align="right"> - Edit</td>
            {/if}
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>
    <input type="submit" value="create">
    </td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    </form>
