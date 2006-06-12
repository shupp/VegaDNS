
Log entries for domain {$domain}<p>
<table border=0 width="70%">
  <tr bgcolor="#cccccc">
      <td>Name</td>
      <td>Customer ID</td>
      <td>Email</td>
      <td>Log Entry</td>
      <td>Date / Time</td></td>
  </tr>

{foreach from=$logs item=row}
    <tr bgcolor="#eeeeee">
        <td nowrap>{$row.name}</td>
        <td nowrap>{$row.cid}</td>
        <td nowrap>{$row.email}</td>
        <td nowrap>{$row.entry}</td>
        <td nowrap>{$row.time}</td>
    </tr>
{/foreach}
</table>

</table>
