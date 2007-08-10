    Domains in group {$group_name}
    {if $searchstring} with search string {$searchstring}{/if}
    <p />
    <table width="90%">
    <tr>
    <td class="border">
    <table width="100%" border="0" cellspacing="5" cellpadding="3" bgcolor="white">
        <tr>
            <td colspan="6" align="center">

            <table width="100%" border='0' cellspacing='0' cellpadding='3'>
                <tr valign="top">
                <td align="left" colspan="2"></td>
                <td align="center" colspan="2">{framework_pager start=$start limit=$limit total=$total pages=6}</td>
                <td align="right" colspan="2">
                    <form action="./?module=Domains">
                    search sub domains <input type="checkbox" name="recursive" {$recursive} />
                    <input type="text" name="search" value="{$search}" />
                    <input type="submit" value="search" /></form>

                </td>
                </tr>
               <tr>
                <td align="center" colspan="6" width="100%" nowrap="nowrap">
                {include file="framework:Framework+vegadns_scope.tpl"}
                </td>
               </tr>

            </table>


            </td>
        </tr>
        <!-- <tr><td colspan="4" align="right">{if $new_domain_url}<a href="{$new_domain_url}&amp;height=300&amp;width=300&amp;TB_iframe=true" class="thickbox">New Domain</a>{/if}-->
        <tr><td colspan="4" align="right">{if $new_domain_url}<a href="{$new_domain_url}">New Domain</a>
            {else}New Domain{/if}</td>
        </tr>
        <tr>
            <td class="underline" nowrap="nowrap">{$Domain}</td><td class="underline" nowrap="nowrap">{$Status}</td>
            <td class="underline" align="center" nowrap="nowrap">{$Group}</td>
            <td class="underline" align="center" nowrap="nowrap">Change Status</td>
            <td class="underline" width="1%">Delete</td>
        </tr>

        {foreach from=$out_array item=row}
        <tr bgcolor="{cycle values="#dddddd,#ffffff"}">
            <td><a href="{$row.edit_url}">{$row.domain}</a></td>
            <td width="1%" nowrap="nowrap">{$row.status}</td>
            <td width="1%" align="center" nowrap="nowrap">
            {if $row.change_owner_url}
                <a href="{$row.change_owner_url}">{$row.group_name}</a>
            {else}
                {$row.group_name}
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
            <td align="center" width="1%"><a href="{$row.delete_url}"><img src="images/trash.png" border='0' alt="Trash" /></a></td>
            </tr>
        {/foreach}
    </table>
    </td>
    </tr>
    </table>
