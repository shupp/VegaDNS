<table width="70%" border=0 cellspacing=5 cellpadding=3 bgcolor="white">
<tr bgcolor="#cccccc">
    <td colspan="6" align="center">
<table border=0 width="100%">
<tr bgcolor="#cccccc">
    <td align="middle">All User Accounts
        {if $user_account_type == 'group_admin'} In Your Group{/if}
            </td>
        </tr>
</table>
    </td>
</tr>
<tr bgcolor="#cccccc">
    <td colspan="6" align="center">
        <table width="100%" border=0 bgcolor="#cccccc">
        <tr bgcolor="#cccccc">
            <td align="left" valign="top" colspan="2">
                Listing {$first_item} - {$last_item} of {$totalitems} Accounts {$searchtexttag}</td>
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
                <input type="hidden" name="mode" value="users">
                <input type="hidden" name="{$session_name}" value="{$session_id}">
                search sub groups <input type="checkbox" name="recursive" {$search}>
                <input type="text" name="search" value="{$search}">
                <input type="submit" value="search"></form>
            </td>
        </tr>
        <tr bgcolor="#cccccc">
            <td align="center" colspan="6" width=100% nowrap>
                <a href="{$all_url}">ALL</a> | <a href="{$all_url}&scope=num">0-9</a> | <a href="{$all_url}&scope=a">A</a> | <a href="{$all_url}&scope=b">B</a> | <a href="{$all_url}&scope=c">C</a> | <a href="{$all_url}&scope=d">D</a> | <a href="{$all_url}&scope=e">E</a> | <a href="{$all_url}&scope=f">F</a> | <a href="{$all_url}&scope=g">G</a> | <a href="{$all_url}&scope=h">H</a> | <a href="{$all_url}&scope=i">I</a> | <a href="{$all_url}&scope=j">J</a> | <a href="{$all_url}&scope=k">K</a> | <a href="{$all_url}&scope=l">L</a> | <a href="{$all_url}&scope=m">M</a> | <a href="{$all_url}&scope=n">N</a> | <a href="{$all_url}&scope=o">O</a> | <a href="{$all_url}&scope=p">P</a> | <a href="{$all_url}&scope=q">Q</a> | <a href="{$all_url}&scope=r">R</a> | <a href="{$all_url}&scope=s">S</a> | <a href="{$all_url}&scope=t">T</a> | <a href="{$all_url}&scope=u">U</a> | <a href="{$all_url}&scope=v">V</a> | <a href="{$all_url}&scope=w">W</a> | <a href="{$all_url}&scope=x">X</a> | <a href="{$all_url}&scope=y">Y</a> | <a href="{$all_url}&scope=z">Z</a>
            </td>
        </tr>
        <tr bgcolor="#cccccc">
            <td align="right" colspan="6" width=100% nowrap>{if $add_account_url != ""}<a href="{$add_account_url}">Add Account</a>{else}Add Account{/if}</td>
        </tr>
        </table>
    </td>
</tr>
<tr bgcolor="#cccccc">
  <td nowrap>{$name}</td>
  <td nowrap>{$email}</td>
  <td nowrap>{$account_type}</td>
  <td nowrap>{$status}</td>
  <td>edit</td>
  <td>delete</td>
</tr>

{foreach from=$out_array item=row}
<tr bgcolor="#eeeeee">
  <td>{$row.name|escape}</td>
  <td>{$row.email|escape}</td>
  <td>{$row.account_type}</td>
  <td>{$row.status}</td>
  <td>{if $row.edit_url}<a href="{$row.edit_url}">edit</a>{else}edit{/if}</td>
  <td align="center">{strip}
    {if $row.delete_url == ""}
    <img border=0 src="images/trash.png">
    {else}
    <a href="{$row.delete_url}"><img border=0 src="images/trash.png" alt="Trash"></a>
        {/if}{/strip}
    </td>
</tr>
{/foreach}

</table>
