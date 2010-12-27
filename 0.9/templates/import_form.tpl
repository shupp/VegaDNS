<form action="{$php_self}">
<input type="hidden" name="state" value="{$state}">
<input type="hidden" name="mode" value="{$mode}">
<input type="hidden" name="domain_mode" value="import_domains_now">
<input type="hidden" name="{$session_name}" value="{$session_id}">


<table border=0 bgcolor="white">
<tr><td>
    <table border=0 width="100%">
    <tr bgcolor="#cccccc"><td align="center">Import Domains via AXFR</td></tr>
    </table>

    <table border=0 width="100%">
    <tr bgcolor="#eeeeee">
        <td>Hostname or IP address:</td>
        <td><input type="text" name="hostname" value="{$hostname}"></td>
    </tr>
    <tr valign="top" bgcolor="#eeeeee">
        <td>List of Domains:<br>(one per line)</td>
        <td><textarea name="domains" rows=10>{$domains}</textarea></td>
    </tr>
    </table>
</td></tr>
</table>
    Rewrite SOA to default value?<input type="checkbox" name="default_soa">
    <br>Rewrite NS servers to defalt values?<input type="checkbox" name="default_ns">
    <br><br><input type="submit" value="get domains">
</form>
