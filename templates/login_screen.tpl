<div class="row">
    <div class="small-12 medium-8 small-centered columns">
        <h3>Log in</h3>
        <form action="{$php_self}">
            <input type="hidden" name="state" value="login">
            <input type="hidden" name="{$session_name}" value="{$session_id}">
            <label for="email_address">
                Email Address
                <input type="text" name="email">
            </label>
            <label for="password">
                Password
                <input type="password" name="password">
            </label>
            <input type="submit" value="Login" class="button expanded">
        </form>
    </div>
</div>