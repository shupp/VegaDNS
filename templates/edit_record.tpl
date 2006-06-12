<form action="{$php_self}">
<input type="hidden" name="state" value="{$state}">
<input type="hidden" name="mode" value="{$mode}">
<input type="hidden" name="domain" value="{$domain}">
<input type="hidden" name="{$session_name}" value="{$session_id}">
<input type="hidden" name="record_id" value="{$record_id}">
<input type="hidden" name="type" value="{$type}">
<input type="hidden" name="record_mode" value="edit_record_now">


<table border=0 bgcolor="white">
<tr><td>

    <table border=0 width="100%">
    <tr bgcolor="#cccccc">
        <td align="center" colspan=2>
        Edit Resource Record for {$domain}
        </td>
    <tr>
    <tr bgcolor="#eeeeee">
        <td>Hostname</td>
        <td><input type="text" name="name" value="{$name}"></td>
    </tr>
    <tr bgcolor=#eeeeee>
        <td>Type</td>
        <td>{$type}</td>
    </tr>
    <tr bgcolor="#eeeeee">
        <td>Address</td>
        <td><input type="text" name="address" value="{$address}"></td>
    </tr>
    <tr bgcolor="#eeeeee">
        <td>Distance (MX and SRV only)</td>
        <td><input type="text" name="distance" value="{$distance}" size=5 maxlength=10></td>
    </tr>
    <tr bgcolor="#eeeeee">
        <td>Weight (SRV only)</td>
        <td><input type="text" name="weight" value="{$weight}" size=5 maxlength=10></td>
    </tr>
    <tr bgcolor="#eeeeee">
        <td>Port (SRV only)</td>
        <td><input type="text" name="port" value="{$port}" size=5 maxlength=10></td>
    </tr>

    <tr bgcolor="#eeeeee">
        <td>TTL</td>
        <td><input size=7 maxlenth=20 type="text" name="ttl" value="{$ttl}">
    </tr>
    </table>
</td></tr>
</table>

<input type="submit" value="edit">

</form>
