<form action="{$php_self}">
<input type="hidden" name="state" value="{$state}">
<input type="hidden" name="mode" value="records">
<input type="hidden" name="domain" value="{$domain|escape}">
<input type="hidden" name="{$session_name}" value="{$session_id}">
<input type="hidden" name="record_mode" value="edit_soa_now">


<table border=0 bgcolor="white">
<tr><td>
    <table border=0 width="100%">
    <tr bgcolor="#cccccc">
        <td align="center">Edit SOA record for {$domain|escape}</td>
    </tr>
    </table>

    <table border=0 "width=100%">
    <tr bgcolor="#eeeeee">
        <td nowrap>Primary Name Server</td>
        <td align="left"><input type="text" name="primary_name_server" value="{$primary_name_server|escape}"></td>
        <td>defaults:</td>
    </tr>
    <tr bgcolor="#eeeeee">
        <td nowrap>Contact Address</td>
        <td align="left"><input type=text name="contactaddr" value="{$contactaddr|escape}"></td>
        <td align="left">hostmaster.{$domain|escape}</td>
    </tr>
    <tr bgcolor="#eeeeee">
        <td>Serial Number</td>
        <td align="left"><input type="text" name="serial" size=10 value="{$serial}"></td>
        <td align="left">(leave blank for djbdns default)</td>
    </tr>
    <tr bgcolor="#eeeeee">
        <td>TTL</td>
        <td align="left"><input type="text" name="ttl" size=10 value="{$ttl}"></td>
        <td align="left">86400</td>
    </tr>
    <tr bgcolor="#eeeeee">
        <td>Refresh</td>
        <td align="left"><input type="text" name="refresh" size=10 value="{$refresh}"></td>
        <td align="left">16384</td>
    </tr>
    <tr bgcolor="#eeeeee">
        <td>Retry</td>
        <td align="left"><input type="text" name="retry" size=10 value="{$retry}"></td>
        <td align="left">2048</td>
    </tr>
    <tr bgcolor="#eeeeee">
        <td>Expire</td>
        <td align="left"><input type="text" name="expire" size=10 value="{$expire}"></td>
        <td align="left">1048576</td>
    </tr>
    <tr bgcolor="#eeeeee">
        <td>Minimum</td>
        <td align="left"><input type="text" name="minimum" size=10 value="{$minimum}"></td>
        <td align="left">2560</td>
    </tr>
    </table>

</td></tr>
</table>
<input type="submit" value="edit">
</form>
