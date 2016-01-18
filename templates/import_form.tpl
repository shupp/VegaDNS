<form action="{$php_self}">
<input type="hidden" name="state" value="{$state|escape:'html'}">
<input type="hidden" name="mode" value="{$mode|escape:'html'}">
<input type="hidden" name="domain_mode" value="import_domains_now">
<input type="hidden" name="{$session_name|escape:'html'}" value="{$session_id|escape:'html'}">
    <div class="row">
        <div class="small-12 medium-8 small-centered columns">
            <h3>Import Domains via AXFR</h3>
            <label for="hostname">
                Hostname or IP address
                <input type="text" name="hostname" value="{$hostname|escape:'html'}">
            </label>
            <label for="domains">
                List of Domains:<br>(one per line)
                <textarea name="domains" rows="6">{$domains|escape:'html'}</textarea>
            </label>
            <div class="row">
                <div class="small-12 medium-6 columns">
                    <label for="default_soa">
                        <input type="checkbox" name="default_soa">
                        Rewrite SOA to default value?
                    </label>
                </div>
                <div class="small-12 medium-6 columns">
                    <label for="default_ns">
                        <input type="checkbox" name="default_ns">
                        Rewrite NS servers to defalt values?
                    </label>
                </div>
            </div>
            <input type="submit" value="get domains" class="button expanded">
        </div>
    </div>
</form>
