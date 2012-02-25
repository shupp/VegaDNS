<b>Edit Domain</b><br>
<table border=0 width="90%" bgcolor="white">
<tr><td>

{if $display_soa}
<table border=0 width="100%">
<tr bgcolor="#cccccc">
  <td>Properties (SOA)</td>
  <td align="right" width="5%"><a href="{$edit_soa_url}">edit</a></td>
</table>


<table border=0 width="100%">
  <tr bgcolor="#eeeeee">
      <td width="10%">Domain:</td>
      <td width="40%" nowrap>{$domain}</td>
      <td width="10%" nowrap>Refresh:</td>
      <td width="40%" nowrap>{$refresh}</td>
  </tr>
  <tr bgcolor="#eeeeee">
      <td width="10%" nowrap>Contact Address:</td>
      <td width="40%" nowrap>{$tldemail}</td>
      <td width="10%" nowrap>Retry:</td>
      <td width="40%" nowrap>{$retry}</td>
  </tr>
  <tr bgcolor="#eeeeee">
      <td width="10%" nowrap>Primary Nameserver:&nbsp</td>
      <td width="40%" nowrap>{$tldhost}</td>
      <td width="10%" nowrap>Expiration:</td>
      <td width="40%" nowrap>{$expire}</td>
  </tr>
  <tr bgcolor="#eeeeee">
      <td width="10%" nowrap>Serial Number:</td>
      <td width="40%" nowrap>{$serial}</td>
      <td width="10%" nowrap>Minimum TTL:&nbsp</td>
      <td width="40%" nowrap>{$minimum}</td>
  </tr>
  <tr bgcolor="#eeeeee">
      <td width="10%" nowrap>Default TTL:</td>
      <td width="40%" nowrap>{$ttl}</td>
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
                <td align="left" colspan="2">

                Listing {$first_record} - {$last_record} of {$totalrecords} Records {$searchtexttag}</td>
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
                    <input type="hidden" name="mode" value="records">
                    <input type="hidden" name="{$session_name}" value="{$session_id}">
                    <input type="hidden" name="domain" value="{$domain}">
                    <input type="text" name="search" value="{$search}">
                    <input type="submit" value="search"></form>

                </td>
                </tr>
            </table>
    </td>
</tr>
<tr bgcolor="#cccccc">
  <td>Records</td>
  <td align="right" width="5%" nowrap><a href="{$add_record_url}">add record</a></td>
  <td align="right" width="5%" nowrap><a href="{$view_log_url}">view log</a></td>
  </tr>
</table>

<table border=0 width="100%">
  <tr bgcolor="#cccccc">
      <td nowrap>{$Name}</td>
      <td width="5%" nowrap>{$Type}</td>
      <td nowrap>{$Address}</td>
      <td nowrap width="10%">{$Distance}</td>
      <td nowrap width="10%">Weight</td>
      <td nowrap width="5%">Port</td>
      <td nowrap width="5%">{$TTL}</td>
      <td width="5%">Delete</td>
  </tr>

{foreach from=$out_array item=row}
    <tr bgcolor="{cycle values="#ffffff,#dcdcdc"}">
        <td nowrap width="20%"><a href="{$row.edit_url}">{$row.host}</a></td>
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
