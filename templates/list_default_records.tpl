<div class="row">
    <div class="small-12 columns">
        <h3>Edit Default Records</h3><br>
        <div class="top-bar soa-properties">
            <div class="top-bar-left">
                <h4>Properties (SOA)</h4>
            </div>
            <div class="top-bar-right">
                <ul class="menu">
                    <li><a href="{$edit_soa_url}"><i class="fa fa-pencil"></i> Edit default SOA properties</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<div class="row small-up-2 medium-up-4">
    <div class="column text-center">
        <div class="callout">
            <strong>Contact address</strong><br>
            {$soa_array.tldemail|escape:'html'}
        </div>
    </div>
    <div class="column text-center">
        <div class="callout">
            <strong>Primary Nameserver</strong><br>
            {$soa_array.tldhost|escape:'html'}
        </div>
    </div>
    <div class="column text-center">
        <div class="callout">
            <strong>Refresh</strong><br>
            {$soa_array.refresh|escape:'html'}
        </div>
    </div>
    <div class="column text-center">
        <div class="callout">
            <strong>Retry</strong><br>
            {$soa_array.retry|escape:'html'}
        </div>
    </div>
    <div class="column text-center">
        <div class="callout">
            <strong>Expiration</strong><br>
            {$soa_array.expire|escape:'html'}
        </div>
    </div>
    <div class="column text-center">
        <div class="callout">
            <strong>Minimum TTL</strong><br>
            {$soa_array.minimum|escape:'html'}
        </div>
    </div>
    <div class="column text-center">
        <div class="callout">
            <strong>Default TTL</strong><br>
            {$soa_array.ttl|escape:'html'}
        </div>
    </div>
</div>

<div class="row">
    <div class="small-12 columns">
        <div class="top-bar">
            <div class="top-bar-left">
                <h4>Records</h4>
            </div>
            <div class="top-bar-right">
                <ul class="menu">
                    <li><a href="{$add_record_url}"><i class="fa fa-plus"></i> Add record</a></li>
                    <li><a href="{$view_log_url}"><i class="fa fa-search"></i> View log</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="small-12 columns">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th width="5%">Type</th>
                    <th>Address</th>
                    <th width="10%">Distance</th>
                    <th width="10%">Weight</th>
                    <th width="5%">Port</th>
                    <th width="5%">TTL</th>
                    <th width="5%">Delete</th>
                </tr>
            </thead>
            <tbody>
            {foreach from=$out_array item=row}
                <tr>
                    <td nowrap width="20%">{$row.host}</td>
                    <td width="5%" nowrap>{$row.type}</td>
                    <td nowrap>{$row.val}</td>
                    <td width="10%" nowrap>{$row.distance}</td>
                    <td width="10%" nowrap>{$row.weight}</td>
                    <td width="5%" nowrap>{$row.port}</td>
                    <td width="5%" nowrap>{$row.ttl}</td>
                    <td width="5%" align="center" nowrap><a href="{$row.delete_url}" class="button alert"><i class="fa fa-trash-o"></i></a></td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div>
