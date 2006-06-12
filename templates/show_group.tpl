    <table width="90%" cellspacing=5>
    <tr>
    <td class="border">
    <table width="100%" border="0" cellspacing=5 cellpadding=0>
        <tr>
            <td alig="left">{$group_array.name}</td>
            <td alig="right" width="2%"><img src="images/newfolder.png" border="0" align="absmiddle"></td><td width="2%" nowrap><a href="{$base_url}&mode=domains&group={$group_array.group_id}">Domains</a>&nbsp |</td>
            <td alig="right" width="2%"><img src="images/user_folder.png" border="0"></td><td width="2%" nowrap><a href="{$base_url}&mode=users&group={$group_array.group_id}">Users</a>&nbsp |</td>
            <td alig="right" width="2%" nowrap><img src="images/newfolder.png" border="0"><td width="2%" align="left"><a href="{$base_url}&mode=log&group={$group_array.group_id}">Log</a></td>

        </tr>
    </table>
    <table width="100%" border="0" cellspacing=5 cellpadding=0 bgcolor="white">

        <tr>

        <td class="underline" colspan="6"><p><br>Subgroups</td>
        <td class="underline" colspan="4" align="right" nowrap><p><br>
        {if $new_sub_url != ""}<a href="{$new_sub_url}">New Sub-Group</a>{else}
        New Sub-Group
        {/if}</td>
        </tr>
            {foreach from=$group_array.subgroups item=row}
        <tr>
            <td alig="left"><a href="{$base_url}&mode=groups&group={$row.group_id}">{$row.name}</a></td>
            <td alig="right" width="2%"><img src="images/newfolder.png" border="0" align="absmiddle"></td><td width="2%" nowrap><a href="{$base_url}&mode=domains&group={$row.group_id}">Domains</a>&nbsp |</td>
            <td alig="right" width="2%"><img src="images/user_folder.png" border="0"></td><td width="2%" nowrap><a href="{$base_url}&mode=users&group={$row.group_id}">Users</a>&nbsp |</td>
            <td alig="right" width="2%" nowrap><img src="images/newfolder.png" border="0"></td><td width="2%" nowrap><a href="{$base_url}&mode=domains&group={$row.group_id}">Log</a>&nbsp |</td>
            <td alig="right" width="2%" nowrap>{if $edit_sub_url_base != ""}
                <a href="{$edit_sub_url_base}&group_to_edit={$row.group_id}">Edit</a>{else}Edit{/if} &nbsp | </td>
            <td alig="right" width="2%" nowrap><a href="{$base_url}&mode=groups&group_to_delete={$row.group_id}&group_mode=delete"><img src="images/trash.png" alt="delete" border="0"></a></td>
            <td alig="right" width="2%" nowrap><a href="{$base_url}&mode=groups&group_to_delete={$row.group_id}&group_mode=delete">Delete</a></td>
            </tr>
            {/foreach}
            </td>
        </tr>
    </table>
    </td>
    </tr>
    </table>
