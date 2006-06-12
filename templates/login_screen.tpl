<table border=0 cellpadding="5">

<tr>
<td class="border" align="center">
please log in:<p>

<form name="vegadns" action="{$php_self}">
<input type="hidden" name="state" value="login">
<input type="hidden" name="{$session_name}" value="{$session_id}">

<table border=0>
    <tr>
        <td>Email Address</td>
        <td><input type="text" name="email"></td>
    </tr>
    <tr>
        <td>Password</td>
        <td><input type="password" name="password"></td>
    </tr>
    <tr>
        <td colspan=2 align="right"><input type="submit" value="Login"></td>
    </tr>
</table>
</td>
</tr>
</table>

</form>
