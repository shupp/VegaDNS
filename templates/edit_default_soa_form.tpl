<form action="{$php_self}">
<input type="hidden" name="state" value="{$state}">
<input type="hidden" name="mode" value="default_records">
<input type="hidden" name="domain" value="{$domain|escape}">
<input type="hidden" name="{$session_name}" value="{$session_id}">
<input type="hidden" name="record_mode" value="edit_soa_now">


<table border=0 bgcolor="white">
<tr>
    <td>
        <table border=0 width="100%">
            <tr bgcolor="#cccccc">
                <td align="center">Edit SOA record for {$domain|escape}</td>
            </tr>
        </table>

        <table border=0 "width=100%">
            <tr bgcolor="#eeeeee">
                <td nowrap>Primary Name Server</td>
                <td align="left"><input type="text" name="primary_name_server" value="ns1.myserver.com"></td>
                <td>defaults:</td>
            </tr>
            <tr bgcolor="#eeeeee">
                <td nowrap>Contact Address</td>
                <td align="left"><input type=text name="contactaddr" value="hostmaster.DOMAIN"></td>
                <td align="left">hostmaster.DOMAIN</td>
            </tr>
            <tr bgcolor="#eeeeee">
                <td>TTL</td>
                <td align="left"><input type="text" name="ttl" size=10 value="{$soa_array.ttl}"></td>
                <td align="left">86400</td>
            </tr>
            <tr bgcolor="#eeeeee">
                <td>Refresh</td>
                <td align="left"><input type="text" name="refresh" size=10 value="{$soa_array.refresh}"></td>
                <td align="left">16384</td>
            </tr>
            <tr bgcolor="#eeeeee">
                <td>Retry</td>
                <td align="left"><input type="text" name="retry" size=10 value="{$soa_array.retry}"></td>
                <td align="left">2048</td>
            </tr>
            <tr bgcolor="#eeeeee">
                <td>Expire</td>
                <td align="left"><input type="text" name="expire" size=10 value="{$soa_array.expire}"></td>
                <td align="left">1048576</td>
            </tr>
            <tr bgcolor="#eeeeee">
                <td>Minimum</td>
                <td align="left"><input type="text" name="minimum" size=10 value="{$soa_array.minimum}"></td>
                <td align="left">2560</td>
            </tr>
        </table>
    </td>
</tr>
</table>
<input type="submit" value="edit">
</form>
