<form action="{$php_self}">
<input type="hidden" name="state" value="{$state}">
<input type="hidden" name="mode" value="default_records">
<input type="hidden" name="domain" value="{$domain|escape}">
<input type="hidden" name="{$session_name}" value="{$session_id}">
<input type="hidden" name="record_mode" value="edit_soa_now">
<div class="row">
    <div class="small-12 medium-8 small-centered columns">
        <h3>Edit SOA record for {$domain|escape}</h3>
        <label for="primary_name_server">
            Primary Name Server
            <input type="text" name="primary_name_server" value="{$soa_array.tldhost}">
        </label>
        <div class="row">
            <div class="small-12 medium-6 columns">
                <label for="contact_address">
                    Contact Address<br>(Default: hostmaster)
                    <input id="contact_address" type=text name="contactaddr" value="{$soa_array.tldemail}">
                </label>
            </div>
            <div class="small-12 medium-6 columns">
                <label for="ttl">
                    TTL<br>(Default: 86400)
                    <input id="ttl" type="text" name="ttl" size=10 value="{$soa_array.ttl}">
                </label>
            </div>
            <div class="small-12 medium-6 columns">
                <label for="refresh">
                    Refresh<br>(Default: 16384)
                    <input id="refresh" type="text" name="refresh" size=10 value="{$soa_array.refresh}">
                </label>
            </div>
            <div class="small-12 medium-6 columns">
                <label for="retry">
                    Retry<br>(Default: 2048)
                    <input id="retry" type="text" name="retry" size=10 value="{$soa_array.retry}">
                </label>
            </div>
            <div class="small-12 medium-6 columns">
                <label for="expire">
                    Expire<br>(Default: 1048576)
                    <input id="expire" type="text" name="expire" size=10 value="{$soa_array.expire}">
                </label>
            </div>
            <div class="small-12 medium-6 columns">
                <label for="minimum">
                    Minimum<br>(Default: 2560)
                    <input id="minimum" type="text" name="minimum" size=10 value="{$soa_array.minimum}">
                </label>
            </div>
        </div>
        <input type="submit" value="Save" class="button expanded">
    </div>
</div>
</form>
