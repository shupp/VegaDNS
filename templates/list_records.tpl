<div class="row">
    <div class="small-12 columns">
        <h3>Edit Domain</h3>
    </div>
</div>

{if $display_soa}
    <div class="row">
        <div class="small-12 columns">
            <div class="top-bar soa-properties">
                <div class="top-bar-left">
                    <h4>Properties (SOA)</h4>
                </div>
                <div class="top-bar-right">
                    <ul class="menu">
                        <li><a href="{$edit_soa_url}"><i class="fa fa-pencil"></i> Edit</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="row small-up-2 medium-up-4">
        <div class="column text-center">
            <div class="callout">
                <strong>Domain</strong><br>
                {$domain}
            </div>
        </div>
        <div class="column text-center">
            <div class="callout">
                <strong>Contact address</strong><br>
                {$tldemail}
            </div>
        </div>
        <div class="column text-center">
            <div class="callout">
                <strong>Primary Nameserver</strong><br>
                {$tldhost}
            </div>
        </div>
        <div class="column text-center">
            <div class="callout">
                <strong>Serial</strong><br>
                {$serial}
            </div>
        </div>
        <div class="column text-center">
            <div class="callout">
                <strong>Refresh</strong><br>
                {$refresh}
            </div>
        </div>
        <div class="column text-center">
            <div class="callout">
                <strong>Retry</strong><br>
                {$retry}
            </div>
        </div>
        <div class="column text-center">
            <div class="callout">
                <strong>Expiration</strong><br>
                {$expire}
            </div>
        </div>
        <div class="column text-center">
            <div class="callout">
                <strong>Minimum TTL</strong><br>
                {$minimum}
            </div>
        </div>
        <div class="column text-center">
            <div class="callout">
                <strong>Default TTL</strong><br>
                {$ttl}
            </div>
        </div>
    </div>
{/if}

<div class="row">
    <div class="small-12 medium-6 columns">
        <div class="expanded button-group">
            <a href="{$first_url}" class="button {if $first_url == ""}disabled{/if}">First</a>
            <a href="{$pervious_url}" class="button {if $previous_url == ""}disabled{/if}">Prev</a>
            <a href="{$all_url}" class="button">all</a>
            <a href="{$next_url}" class="button {if $next_url == ""}disabled{/if}">Next</a>
            <a href="{$last_url}" class="button {if $last_url != ""}disabled{/if}">Last</a>
        </div>
    </div>
    <div class="small-12 medium-6 columns">
        <form action="{$php_self}">
            <div class="row text-right collapse">
                <div class="small-8 large-10 columns">
                    <input type="hidden" name="state" value="{$state}">
                    <input type="hidden" name="mode" value="records">
                    <input type="hidden" name="{$session_name}" value="{$session_id}">
                    <input type="hidden" name="domain" value="{$domain}">
                    <input type="text" name="search" value="{$search}">
                </div>
                <div class="small-4 large-2 columns">
                    <input type="submit" value="search" class="button expanded">
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="small-12 medium-3 columns">
        <div class="callout secondary">
            Listing {$first_record} - {$last_record} of {$totalrecords} Records {$searchtexttag}
        </div>
    </div>
    <div class="small-12 medium-9 columns">
        <div class="callout secondary">
            <a href="{$all_url}">ALL</a> | <a href="{$all_url}&scope=num">0-9</a> | <a href="{$all_url}&scope=a">A</a> | <a href="{$all_url}&scope=b">B</a> | <a href="{$all_url}&scope=c">C</a> | <a href="{$all_url}&scope=d">D</a> | <a href="{$all_url}&scope=e">E</a> | <a href="{$all_url}&scope=f">F</a> | <a href="{$all_url}&scope=g">G</a> | <a href="{$all_url}&scope=h">H</a> | <a href="{$all_url}&scope=i">I</a> | <a href="{$all_url}&scope=j">J</a> | <a href="{$all_url}&scope=k">K</a> | <a href="{$all_url}&scope=l">L</a> | <a href="{$all_url}&scope=m">M</a> | <a href="{$all_url}&scope=n">N</a> | <a href="{$all_url}?&scope=o">O</a> | <a href="{$all_url}&scope=p">P</a> | <a href="{$all_url}&scope=q">Q</a> | <a href="{$all_url}&scope=r">R</a> | <a href="{$all_url}&scope=s">S</a> | <a href="{$all_url}&scope=t">T</a> | <a href="{$all_url}&scope=u">U</a> | <a href="{$all_url}&scope=v">V</a> | <a href="{$all_url}&scope=x">X</a> | <a href="{$all_url}&scope=w">W</a> | <a href="{$all_url}&scope=y">Y</a> | <a href="{$all_url}&scope=z">Z</a>
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
                    <th>{$Name}</th>
                    <th width="5%">{$Type}</th>
                    <th>{$Address}</th>
                    <th width="10%">{$Distance}</th>
                    <th width="10%">Weight</th>
                    <th width="5%">Port</th>
                    <th width="5%">{$TTL}</th>
                    <th width="5%">Delete</th>
                </tr>
            </thead>
            <tbody>
            {foreach from=$out_array item=row}
                <tr>
                    <td nowrap width="20%"><a href="{$row.edit_url}">{$row.host}</a></td>
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