<form action="{$php_self}">
<input type="hidden" name="state" value="{$state}">
<input type="hidden" name="mode" value="whoisquery">
<input type="hidden" name="query_mode" value="do_query">
<input type="hidden" name="{$session_name}" value="{$session_id}">

<table border="0">
<tr bgcolor="#cccccc">
  <td colspan="2" align="middle">Whois Query</td>
</tr>
<tr bgcolor="#cccccc">
  <td colspan="2" align="middle">Enter the domain name to run whois on</td>
</tr>
<tr bgcolor="#cccccc">
  <td>Name</td>
  <td><input type="text" name="name" value="{$name}"</td>
</tr>
<tr bgcolor="#cccccc">
  <td colspan="2" align="right"><input type="submit" value="query whois"></td>
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
