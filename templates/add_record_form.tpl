<form action="{$php_self}">
<input type="hidden" name="state" value="{$state}">
<input type="hidden" name="mode" value="{$mode}">
{if $mode == 'records'}
<input type="hidden" name="domain" value="{$domain}">
{/if}
<input type="hidden" name="{$session_name}" value="{$session_id}">
<input type="hidden" name="record_mode" value="add_record_now">


        {if $mode == 'default_records'}
        <font size=-1>use DOMAIN where you want the domain name to appear<br>
        i.e. "www.DOMAIN" will expand to "www.example.com" <br>
        when you add domain "example.com"<br>
        (case sensitive)</font><p>
        {/if}
<table border=0 bgcolor="white">
<tr><td>

    <table border=0 width="100%">
    <tr bgcolor="#cccccc">
        <td align="center" colspan=2>
        {if $mode == 'default_records'}
            Add Default Resource Record for New Domains<p>
        {else}
            Add Resource Record to {$domain}
        {/if}
        </td>
    <tr>
    <tr bgcolor="#eeeeee">
        <td>Hostname</td>
        <td><input type="text" name="name" value="{$name|escape:'html'}"></td>
    </tr>
    <tr bgcolor=#eeeeee>
        <td>Type</td>
        <td><select name="type">
    {html_options values=$typearray selected=$type_selected output=$typearray}
            </select></td>
    </tr>
    <tr bgcolor="#eeeeee">
        <td>Address</td>
        <td><input type="text" name="address" value="{$address|escape:'html'}"></td>
    </tr>
    <tr bgcolor="#eeeeee">
        <td>Distance (MX and SRV only)</td>
        <td><input type="text" name="distance" value="{$distance|escape:'html'}" size=5 maxlength=10></td>
    </tr>
    <tr bgcolor="#eeeeee">
        <td>Weight (SRV only)</td>
        <td><input type="text" name="weight" value="{$weight|escape:'html'}" size=5 maxlength=10></td>
    </tr>
    <tr bgcolor="#eeeeee">
        <td>Port (SRV only)</td>
        <td><input type="text" name="port" value="{$port|escape:'html'}" size=5 maxlength=10></td>
    </tr>

    <tr bgcolor="#eeeeee">
        <td>TTL</td>
        <td><input size=7 maxlenth=20 type="text" name="ttl" value="{$ttl|escape:'html'}">
    </tr>
    </table>
</td></tr>
</table>

<input type="submit" value="add">

</form>
