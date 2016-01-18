<form action="{$php_self}">
<input type="hidden" name="state" value="{$state}">
<input type="hidden" name="mode" value="records">
<input type="hidden" name="domain" value="{$domain|escape}">
<input type="hidden" name="{$session_name}" value="{$session_id}">
<input type="hidden" name="record_mode" value="edit_soa_now">
<div class="row">
    <div class="small-12 medium-8 small-centered columns">
        <h3>Edit SOA record for {$domain|escape}</h3>
        <label for="primary_name_server">
            Primary Name Server
            <input type="text" name="primary_name_server" value="{$primary_name_server|escape}">
        </label>
        <div class="row">
            <div class="small-12 columns">
                <label for="serial">
                    Serial Number<br>
                    (leave blank for djbdns default)
                    <input type="text" name="serial" size=10 value="{$serial}">
                </label>
            </div>
            <div class="small-12 medium-6 columns">
                <label for="contact_address">
                    Contact Address<br>(Default: hostmaster.{$domain|escape})
                    <input id="contact_address" type=text name="contactaddr" value="{$contactaddr|escape}">
                </label>
            </div>
            <div class="small-12 medium-6 columns">
                <label for="ttl">
                    TTL<br>(Default: 86400)
                    <input id="ttl" type="text" name="ttl" size=10 value="{$ttl}">
                </label>
            </div>
            <div class="small-12 medium-6 columns">
                <label for="refresh">
                    Refresh<br>(Default: 16384)
                    <input id="refresh" type="text" name="refresh" size=10 value="{$refresh}">
                </label>
            </div>
            <div class="small-12 medium-6 columns">
                <label for="retry">
                    Retry<br>(Default: 2048)
                    <input id="retry" type="text" name="retry" size=10 value="{$retry}">
                </label>
            </div>
            <div class="small-12 medium-6 columns">
                <label for="expire">
                    Expire<br>(Default: 1048576)
                    <input id="expire" type="text" name="expire" size=10 value="{$expire}">
                </label>
            </div>
            <div class="small-12 medium-6 columns">
                <label for="minimum">
                    Minimum<br>(Default: 2560)
                    <input id="minimum" type="text" name="minimum" size=10 value="{$minimum}">
                </label>
            </div>
        </div>
        <input type="submit" value="Save" class="button expanded">
    </div>
</div>
</form>
