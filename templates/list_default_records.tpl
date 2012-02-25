<b>Edit Default Records</b><br>
<table border=0 width=90% bgcolor=white>
<tr><td>

<table border=0 width=100%>
<tr bgcolor="#cccccc">
  <td>Properties (SOA)</td>
  <td align="right" width="5%"><a href="{$edit_soa_url}">edit</a></td>
</table>


<table border=0 width="100%">
  <tr bgcolor="#eeeeee">
      <td width="10%" nowrap>Contact Address:</td>
      <td width="40%" nowrap>{$soa_array.tldemail|escape:'html'}</td>
      <td width="10%" nowrap>Primary Nameserver:&nbsp</td>
      <td width="40%" nowrap>{$soa_array.tldhost|escape:'html'}</td>
  </tr>
  <tr bgcolor="#eeeeee">
      <td width="10%" nowrap>Refresh:</td>
      <td width="40%" nowrap>{$soa_array.refresh|escape:'html'}</td>
      <td width="10%" nowrap>Retry:</td>
      <td width="40%" nowrap>{$soa_array.retry|escape:'html'}</td>
  </tr>
  <tr bgcolor="#eeeeee">
      <td width="10%" nowrap>Expiration:</td>
      <td width="40%" nowrap>{$soa_array.expire|escape:'html'}</td>
      <td width="10%" nowrap>Minimum TTL:&nbsp</td>
      <td width="40%" nowrap>{$soa_array.minimum|escape:'html'}</td>
  </tr>
  <tr bgcolor="#eeeeee">
      <td width="10%" nowrap>Default TTL:</td>
      <td width="40%" nowrap>{$soa_array.ttl|escape:'html'}</td>
      <td width="10%" nowrap>&nbsp</td>
      <td width="40%" nowrap>&nbsp</td>
  </tr>
  <tr bgcolor="#eeeeee">
  </tr>
</table>

<br>
<table border=0 width="100%">
<tr bgcolor="#cccccc">
  <td>Records</td>
  <td align="right" width="5%" nowrap><a href="{$add_record_url}">add record</a></td>
  <td align="right" width="5%" nowrap><a href="{$view_log_url}">view log</a></td>
  </tr>
</table>

<table border=0 width="100%">
  <tr bgcolor="#cccccc">
      <td>Name</td>
      <td width="5%">Type</td>
      <td>Address</td>
      <td width="10%">Distance</td>
      <td width="10%">Weight</td>
      <td width="5%">Port</td>
      <td width="5%">TTL</td>
      <td width="5%">Delete</td>
  </tr>

{foreach from=$out_array item=row}
    <tr bgcolor="{cycle values="#ffffff,#dcdcdc"}">
        <td nowrap width="20%">{$row.host}</td>
        <td width="5%" nowrap>{$row.type}</td>
        <td nowrap>{$row.val}</td>
        <td width="10%" nowrap>{$row.distance}</td>
        <td width="10%" nowrap>{$row.weight}</td>
        <td width="5%" nowrap>{$row.port}</td>
        <td width="5%" nowrap>{$row.ttl}</td>
        <td width="5%" align="center" nowrap><a href="{$row.delete_url}"><img border=0 src="images/trash.png" alt="Trash"></a></td>
    </tr>
{/foreach}

</table>
</td></tr>
</table>
