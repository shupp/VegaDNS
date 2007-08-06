            Domains in group "{$group_name}"<p>
    <table width="90%">
    <tr>
    <td class="border">
    <table width="100%" border="0" cellspacing="5" cellpadding="3" bgcolor="white">
        <tr>
            <td colspan="6" align="center">

            <table width="100%" border=0 cellspacing=0 cellpadding=3>
                <tr valign="top">
                <td align="left" colspan="2">

                Listing {$start} - {$start + $limit} of {$total} Domains {$searchtexttag}</td>
                <td align="center" colspan="2">{framework_pager}</td>
                <td align="right" colspan="2">
                    <form action="{$php_self}">
                    <input type="hidden" name="state" value="{$state}">
                    <input type="hidden" name="mode" value="domains">
                    <input type="hidden" name="{$session_name}" value="{$session_id}">
                    <input type="hidden" name="group" value="{$group}">
                    search sub domains <input type="checkbox" name="recursive" {$recursive}>
                    <input type="text" name="search" value="{$search}">
                    <input type="submit" value="search"></form>

                </td>
                </tr>
               <tr>
                <td nowrap align="center" colspan="6" width=100%>
                {include file="framework:Framework+vegadns_scope.tpl"}
                </td>
               </tr>

            </table>


            </td>
        </tr>
        <tr><td colspan="4" align="right">{if $new_domain_url}<a href="{$new_domain_url}">New Domain</a>{else}New Domain{/if}</td>
        </tr>
        <tr>
            <td class="underline" nowrap>{$Domain}</td><td class="underline" nowrap>{$Status}</td>
            <td class="underline" align="center" nowrap>{$Group}</td>
            <td class="underline" align="center" nowrap>Change Status</td>
            <td class="underline" width="1%">Delete</td>
        </tr>

        {foreach from=$out_array item=row}
        <tr bgcolor="{cycle values="#dcdcdc,#ffffff"}">
            <td><a href="{$row.edit_url}">{$row.domain}</a></td>
            <td width="1%" nowrap>{$row.status}</td>
            <td width="1%" align="center" nowrap>
            {if $row.change_owner_url}
                <a href="{$row.change_owner_url}">{$row.group_owner_name}</a>
            {else}
                {$row.group_owner_name}
            {/if}
            </td>
            <td width="1%" align="center">
            {strip}
            {if $row.status == "active"}
                {if $row.deactivate_url}
                    <a href="{$row.deactivate_url}">de-activate</a>
                {else}
                    de-activate
                {/if}
            {else if $row.state == "inactive"}
                {if $row.activate_url}
                    <a href="{$row.activate_url}">activate</a>
                {else}
                    activate
                {/if}
            {/if}
            {/strip}
            </td>
            <td align="center" width="1%"><a href="{$row.delete_url}"><img src="images/trash.png" border=0 alt="Trash"></a></td>
            </tr>
        {/foreach}
    </table>
    </td>
    </tr>
    </table>
