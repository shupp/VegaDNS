<form action="{$php_self}">
<input type="hidden" name="state" value="{$state}">
<input type="hidden" name="mode" value="dnsquery">
<input type="hidden" name="query_mode" value="do_query">
<input type="hidden" name="{$session_name}" value="{$session_id}">

<table border="0">
<tr bgcolor="#cccccc">
  <td colspan="2" align="middle">DNS Query</td>
</tr>
<tr bgcolor="#cccccc">
  <td>Name</td>
  <td><input type="text" name="name" value="{$name}"</td>
</tr>
<tr bgcolor="#cccccc">
  <td>Query Type:</td>
  <td><select name="type">
  {html_options values=$typearray selected=$type_selected output=$typearray}
  </td>
</tr>
<tr bgcolor="#cccccc">
  <td>Recursive</td>
  <td><input type="checkbox" name="recursive"{if $recursive} checked{/if}></td>
</tr>
<tr bgcolor="#cccccc">
  <td>Host (only for non-recursive)</td>
  <td><input type="text" name="host" value="{$host}"></td>
</tr>
<tr bgcolor="#cccccc">
  <td colspan="2" align="right"><input type="submit" value="query"></td>
</tr>
{if $result != ""}
<tr bgcolor="#cccccc">
  <td colspan="2">Results of {$command}</td>
</tr>
<tr bgcolor="#cccccc">
  <td colspan="2">{$result}</td>
</tr>
{/if}
</table>
</form>
