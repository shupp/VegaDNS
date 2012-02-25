    <table width="70%" border=0 cellspacing=5 cellpadding=3 bgcolor="white">
        <tr bgcolor="#cccccc">
            <td colspan="6" align="center">

            <table width="100%" border=0 cellspacing=0 cellpadding=3 bgcolor="#cccccc">
                <tr valign="top" bgcolor="#cccccc">
                <td align="left" colspan="2">

                Listing {$first_domain} - {$last_domain} of {$totaldomains} Domains {$searchtexttag|escape:'html'}</td>
                <td align="center" colspan="2">
                {if $previous_url != ""} <a href={$previous_url}>previous</a>
                {else}previous{/if}
                {if $next_url != ""} <a href={$next_url}>next</a>
                {else}next{/if}
                {if $first_url != ""} <a href={$first_url}>first</a>
                {else}first{/if}
                {if $last_url != ""} <a href={$last_url}>last</a>
                {else}last{/if}
                <a href={$all_url}>all</a>
                </td>
                <td align="right" colspan="2">
                    <form action="{$php_self}">
                    <input type="hidden" name="state" value="{$state}">
                    <input type="hidden" name="mode" value="domains">
                    <input type="hidden" name="{$session_name}" value="{$session_id}">
                    <input type="text" name="search" value="{$search|escape:'html'}">
                    <input type="submit" value="search"></form>

                </td>
                </tr>
               <tr>
                <td align="center" colspan="6" width=100%>
<a href="{$all_url}">ALL</a> | <a href="{$all_url}&scope=num">0-9</a> | <a href="{$all_url}&scope=a">A</a> | <a href="{$all_url}&scope=b">B</a> | <a href="{$all_url}&scope=c">C</a> | <a href="{$all_url}&scope=d">D</a> | <a href="{$all_url}&scope=e">E</a> | <a href="{$all_url}&scope=f">F</a> | <a href="{$all_url}&scope=g">G</a> | <a href="{$all_url}&scope=h">H</a> | <a href="{$all_url}&scope=i">I</a> | <a href="{$all_url}&scope=j">J</a> | <a href="{$all_url}&scope=k">K</a> | <a href="{$all_url}&scope=l">L</a> | <a href="{$all_url}&scope=m">M</a> | <a href="{$all_url}&scope=n">N</a> | <a href="{$all_url}?&scope=o">O</a> | <a href="{$all_url}&scope=p">P</a> | <a href="{$all_url}&scope=q">Q</a> | <a href="{$all_url}&scope=r">R</a> | <a href="{$all_url}&scope=s">S</a> | <a href="{$all_url}&scope=t">T</a> | <a href="{$all_url}&scope=u">U</a> | <a href="{$all_url}&scope=v">V</a> | <a href="{$all_url}&scope=x">X</a> | <a href="{$all_url}&scope=w">W</a> | <a href="{$all_url}&scope=y">Y</a> | <a href="{$all_url}&scope=z">Z</a>
                </td>
               </tr>

            </table>


            </td>
        </tr>
        <tr bgcolor="#cccccc">
            <td nowrap>{$Domain}</td><td nowrap>{$Status}</td>
            <td align="center" nowrap>{$Owner}</td>
            <td align="center" nowrap>{$Group_Owner}</td>
            <td align="center" nowrap>Change Status</td>
            <td width="1%">Delete</td>
        </tr>

        {foreach from=$out_array item=row}
        <tr bgcolor="{cycle values="#ffffff,#dcdcdc"}">
            <td><a href="{$row.edit_url}">{$row.domain}</td>
            <td width="1%" nowrap>{$row.status}</td>
            <td width="1%" align="center" nowrap>
            {if $row.change_owner_url != ""}
                <a href="{$row.change_owner_url}">{$row.owner_name}</a>
            {else}
                {$row.owner_name}
            {/if}
            </td>
            <td width="1%" align="center" nowrap>
            {if $row.change_owner_url != ""}
                <a href="{$row.change_owner_url}">{$row.group_owner_name}</a>
            {else}
                {$row.group_owner_name}
            {/if}
            </td>
            <td width="1%" align="center">
            {strip}
            {if $row.status == "active"}
                {if $row.deactivate_url != ""}
                    <a href="{$row.deactivate_url}">de-activate</a>
                {else}
                    de-activate
                {/if}
            {else if $row.state == "inactive"}
                {if $row.activate_url != ""}
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
