<table border=0 width="70%" bgcolor="white">
<tr><td>
<table border=0 width="100%">
<tr bgcolor="#cccccc">
    <td align="middle">All User Accounts
        {if $user_account_type == 'group_admin'} In Your Group{/if}
</td></tr>
</table>

<table border=0 width="100%">
<tr bgcolor="#cccccc">
  <td nowrap>{$Name}</td>
  <td nowrap>{$Email}</td>
  <td nowrap>{$Account_Type}</td>
  <td nowrap>{$Group_Owner}</td>
  <td nowrap>{$Status}</td>
  <td>Edit</td>
  <td>Delete</td>
</tr>

{foreach from=$out_array item=row}
<tr bgcolor="#eeeeee">
  <td>{$row.name|escape}</td>
  <td>{$row.email|escape}</td>
  <td>{$row.account_type}</td>
  <td>{$row.group_owner_name|escape}</td>
  <td>{$row.status}</td>
  <td><a href="{$row.edit_url}">edit</a></td>
  <td align="center">{strip}
    {if $row.delete_url == ""}
    <img border=0 src="images/trash.png">
    {else}
    <a href="{$row.delete_url}"><img border=0 src="images/trash.png" alt="Trash"></a>
    {/if}{/strip}</td>
</tr>
{/foreach}

</table>
</td></tr>
</table>
