<form action="{$php_self}">
    <input type="hidden" name="state" value="{$state}">
    <input type="hidden" name="mode" value="{$mode}">
    {if $mode == 'records'}
        <input type="hidden" name="domain" value="{$domain}">
    {/if}
    <input type="hidden" name="{$session_name}" value="{$session_id}">
    <input type="hidden" name="record_mode" value="add_record_now">
    <div class="row">
        <div class="small-12 medium-8 small-centered columns">
            {if $mode == 'default_records'}
                <div class="callout warning">
                    use DOMAIN where you want the domain name to appear<br>
                    i.e. "www.DOMAIN" will expand to "www.example.com" when you add domain "example.com" (case sensitive)
                </div>
            {/if}
            <div class="row">
                <div class="small-12 columns">
                    <div class="callout warning">
                        {if $mode == 'default_records'}
                            Add Default Resource Record for New Domains
                        {else}
                            Add Resource Record to {$domain}
                        {/if}
                    </div>
                </div>
                <div class="small-12 columns">
                    <label for="hostname">
                        Hostname
                        <input id="hostname" type="text" name="name" value="{$name|escape:'html'}">
                    </label>
                    <label for="type">
                        Type
                        <select id="type" name="type">
                            {html_options values=$typearray selected=$type_selected output=$typearray}
                        </select>
                    </label>
                    <label for="address">
                        Address
                        <input id="address" type="text" name="address" value="{$address|escape:'html'}">
                    </label>
                </div>
                <div class="small-12 medium-6 columns">
                    <label for="distance">
                        Distance (MX and SRV only)
                        <input id="distance" type="text" name="distance" value="{$distance|escape:'html'}" size=5 maxlength=10>
                    </label>
                </div>
                <div class="small-12 medium-6 columns">
                    <label for="weight">
                        Weight (SRV only)
                        <input id="weight" type="text" name="weight" value="{$weight|escape:'html'}" size=5 maxlength=10>
                    </label>
                </div>
                <div class="small-12 medium-6 columns">
                    <label for="port">
                        Port (SRV only)
                        <input id="port" type="text" name="port" value="{$port|escape:'html'}" size=5 maxlength=10>
                    </label>
                </div>
                <div class="small-12 medium-6 columns">
                    <label for="ttl">
                        TTL
                        <input size=7 maxlenth=20 type="text" name="ttl" value="{$ttl|escape:'html'}">
                    </label>
                </div>
                <div class="small-12 columns">
                    <input type="submit" value="add record" class="button float-right">
                </div>
            </div>
        </div>
    </div>
</form>
