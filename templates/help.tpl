<div class="row">
    <div class="small-12 medium-8 small-centered columns">
        <h3>Forgot your password?</h3>
        <p>Just enter your email address below, you will be sent a new one.</p>

        <form action="{$php_self}">
            <input name="state" value="help" type="hidden">
            <input name="mode" value="send_pass" type="hidden">
            <input name="{$session_name}" type="hidden" value="{$session_id}">
            <label for="email_address">
                Email address
                <input id="email_ddress" name="username" type="text">
            </label>
                <a class="button secondary float-left" href="{$php_self}?{$session_name}={$session_id}">Back to login screen</a>
                <input type="submit" value="Send me a new password" class="button float-right">
        </form>
    </div>
</div>
