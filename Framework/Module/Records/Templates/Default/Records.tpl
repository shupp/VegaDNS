<b>Edit Domain</b><br>
<table border=0 width="90%" bgcolor="white">
<tr><td>

{if $soa}
<table border=0 width="100%">
<tr bgcolor="#cccccc">
  <td>Properties (SOA)</td>
  <td align="right" width="5%">{if $soa.edit_soa_url}<a href="{$soa.edit_soa_url}">edit</a>{else}edit{/if}</td>
</table>


<table border=0 width="100%">
  <tr bgcolor="#eeeeee">
      <td width="10%">Domain:</td>
      <td width="40%" nowrap>{$domain.domain}</td>
      <td width="10%" nowrap>Refresh:</td>
      <td width="40%" nowrap>{$soa.refresh}</td>
  </tr>
  <tr bgcolor="#eeeeee">
      <td width="10%" nowrap>Contact Address:</td>
      <td width="40%" nowrap>{$soa.tldemail}</td>
      <td width="10%" nowrap>Retry:</td>
      <td width="40%" nowrap>{$soa.retry}</td>
  </tr>
  <tr bgcolor="#eeeeee">
      <td width="10%" nowrap>Primary Nameserver:&nbsp</td>
      <td width="40%" nowrap>{$soa.tldhost}</td>
      <td width="10%" nowrap>Expiration:</td>
      <td width="40%" nowrap>{$soa.expire}</td>
  </tr>
  <tr bgcolor="#eeeeee">
      <td width="10%" nowrap>Serial Number:</td>
      <td width="40%" nowrap>Default</td>
      <td width="10%" nowrap>Minimum TTL:&nbsp</td>
      <td width="40%" nowrap>{$soa.minimum}</td>
  </tr>
  <tr bgcolor="#eeeeee">
      <td width="10%" nowrap>Default TTL:</td>
      <td width="40%" nowrap>{$soa.ttl}</td>
      <td width="10%" nowrap>&nbsp</td>
      <td width="40%" nowrap>&nbsp</td>
  </tr>
</table>
<br>
{/if}

<table border=0 width="100%">
<tr bgcolor="#cccccc">
    <td colspan=6 align="center">
            <table width="100%" border=0 cellspacing=0 cellpadding=3 bgcolor="#cccccc">
                <tr valign="top" bgcolor="#cccccc">
                <td align="left" colspan="2">{framework_pager start=$start limit=$limit total=$total pages=6}</td>
                <td align="right" colspan="2">
                    <form action="./?module=Records&event=search&domain_id={$domain.domain_id}">
                    <input type="text" name="search" value="{$search}">
                    <input type="submit" value="search"></form>
                </td>
                </tr>
            </table>
    </td>
</tr>
<tr bgcolor="#cccccc">
  <td>Records</td>
  <td align="right" width="5%" nowrap>{if $add_record_url != ""}<a href="{$add_record_url}">add record</a>{else}add record{/if}</td>
  <td align="right" width="5%" nowrap><a href="{$view_log_url}">view log</a></td>
  </tr>
</table>

<table border=0 width="100%">
  <tr bgcolor="#cccccc">
      <td nowrap>{$Name}</td>
      <td width="5%" nowrap>{$Type}</td>
      <td nowrap>{$Address}</td>
      <td nowrap width="10%">{$Distance}</td>
      <td width="10%">{$Weight}</td>
      <td width="5%">{$Port}</td>
      <td nowrap width="5%">{$TTL}</td></td>
      <td width="5%">Delete</td>
  </tr>

{foreach from=$records_array item=row}
    <tr bgcolor="{cycle values="#ffffff,#dcdcdc"}">
        <td nowrap width="20%">{if $row.edit_url}<a href="{$row.edit_url}">{$row.host}</a>{else}{$row.host}{/if}</td>
        <td width="5%" nowrap>{$row.type}</td>
        <td nowrap>{$row.val}</td>
        <td width="10%" nowrap>{$row.distance}</td>
     	<td width="10%" nowrap>{$row.weight}</td>
        <td width="5%" nowrap>{$row.port}</td>
        <td width="5%" nowrap>{$row.ttl}</td>
        <td width="5%" align="center" nowrap>{if $row.delete_url}<a href="{$row.delete_url}"><img border=0 src="images/trash.png" alt="Trash">{else}<img border=0 src="images/trash.png" alt="Trash">{/if}</a></td>
    </tr>
{/foreach}

</table>
</td></tr>
</table>
