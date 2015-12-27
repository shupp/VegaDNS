<form action="{$php_self}">
<input type="hidden" name="state" value="{$state}">
<input type="hidden" name="mode" value="{$mode}">
{if $mode == 'records'}
<input type="hidden" name="locations" value="{$location}">
{/if}
<input type="hidden" name="{$session_name}" value="{$session_id}">
<input type="hidden" name="location_mode" value="add_location_now">



<table border=0 bgcolor="white">
<tr><td>

    <table border=0 width="100%">
    <tr bgcolor="#cccccc">
        <td align="center" colspan=2>
        Add Location
        </td>
    <tr>
    <tr bgcolor="#eeeeee">
        <td>Location</td>
        <td><input type="text" name="location" value="{$location|escape:'html'}"></td>
    </tr>
    <tr bgcolor="#eeeeee">
        <td>Prefix</td>
        <td><input type="text" name="prefix" value="{$address|escape:'html'}"></td>
    </tr>
    </table>
</td></tr>
</table>

<input type="submit" value="add">

</form>
