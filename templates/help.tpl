<font color=red>Forget your password?</font>
<p>Just enter your email address below, you will be sent a new one.

<p><br>
<form action="{$php_self}">
    <input name="state" value="help" type="hidden">
    <input name="mode" value="send_pass" type="hidden">
    <input name="{$session_name}" type="hidden" value="{$session_id}">

    <table border=0>
        <tr>
            <td>Email address</td>
            <td><input name="username" type="text"></td>
        </tr>
        <tr>
            <td colspan=2 align="right"><input type="submit" value="send"></td>
        </tr>
    </table>
</form>

<p><a href="{$php_self}?{$session_name}={$session_id}">Back to login screen</a>
