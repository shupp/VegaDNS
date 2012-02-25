<form action="{$php_self}">
<input type="hidden" name="state" value="{$state|escape:'html'}">
<input type="hidden" name="mode" value="domains">
<input type="hidden" name="{$session_name|escape:'html'}" value="{$session_id|escape:'html'}">
<input type="hidden" name="domain_mode" value="add_now">


<table border=0 width="70%" bgcolor="white">
<tr><td>
    <table border=0 width="100%">
    <tr bgcolor="#cccccc"><td align="center">Add Domain</td></tr>
    </table>

    <table border=0 width="100%">
    <tr bgcolor="#eeeeee">
        <td>Domain Name</td>
        <td align="left" colspan=2><input type="text" name="domain" value="{$domain|escape:'html'}">
        </td>
    </tr>
    </table>
    </td>
</tr>
</table>
<input type="submit" value="add">
</form>
