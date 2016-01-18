<form action="{$php_self}">
<input type="hidden" name="state" value="{$state}">
<input type="hidden" name="mode" value="dnsquery">
<input type="hidden" name="query_mode" value="do_query">
<input type="hidden" name="{$session_name}" value="{$session_id}">
    <div class="row">
        <div class="small-12 medium-8 small-centered columns">
            <h3>DNS Query</h3>
            <label for="name">
                Name
                <input type="text" name="name" value="{$name}">
            </label>
            <label for="query_type">
                Query Type:
                <select name="type">
                    {html_options values=$typearray selected=$type_selected output=$typearray}
                </select>
            </label>
            <label for="recursive">
                Recursive
                <input type="checkbox" name="recursive"{if $recursive} checked{/if}>
            </label>
            <label for="host">
                Host (only for non-recursive)
                <input type="text" name="host" value="{$host}">
            </label>
            <input type="submit" value="query" class="button expanded">

            {if $result != ""}
                <div class="callout secondary">
                    Results of {$command}
                </div>
                {$result}
            {/if}

        </div>
    </div>
</form>
