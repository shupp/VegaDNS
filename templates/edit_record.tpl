<form action="{$php_self}">
    <input type="hidden" name="state" value="{$state}">
    <input type="hidden" name="mode" value="{$mode}">
    <input type="hidden" name="domain" value="{$domain}">
    <input type="hidden" name="{$session_name}" value="{$session_id}">
    <input type="hidden" name="record_id" value="{$record_id}">
    <input type="hidden" name="type" value="{$type}">
    <input type="hidden" name="record_mode" value="edit_record_now">

    <div class="row">
        <div class="small-12 medium-8 small-centered columns">
            <h3>Edit Resource Record for {$domain}</h3>
            <label for="hostname">
                Hostname
                <input type="text" name="name" value="{$name}">
            </label>
            <div class="row">
                <div class="small-12 medium-6 columns">
                    <label for="type">
                        Type
                        <input type="text" disabled value="{$type}">
                    </label>
                </div>
                <div class="small-12 medium-6 columns">
                    <label for="address">
                        Address
                        <input type="text" name="address" value="{$address}">
                    </label>
                </div>
                <div class="small-12 medium-6 columns">
                    <label for="distance">
                        Distance (MX and SRV only)
                        <input type="text" name="distance" value="{$distance}" size=5 maxlength=10>
                    </label>
                </div>
                <div class="small-12 medium-6 columns">
                    <label for="weight">
                        Weight (SRV only)
                        <input type="text" name="weight" value="{$weight}" size=5 maxlength=10>
                    </label>
                </div>
                <div class="small-12 medium-6 columns">
                    <label for="port">
                        Port (SRV only)
                        <input type="text" name="port" value="{$port}" size=5 maxlength=10>
                    </label>
                </div>
                <div class="small-12 medium-6 columns">
                    <label for="ttl">
                        TTL
                        <input size=7 maxlenth=20 type="text" name="ttl" value="{$ttl}">
                    </label>
                </div>
            </div>
            <input type="submit" value="Save" class="button expanded">
        </div>
    </div>

</form>
