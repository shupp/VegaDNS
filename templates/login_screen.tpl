<br>please log in:<br>

<form action="{$php_self}">
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

</form>
