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
                        <input type="hidden" name="mode" value="domains">
                        <input type="hidden" name="{$session_name}" value="{$session_id}">
                        <input type="text" name="search" value="{$search|escape:'html'}">
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
                <p>Listing {$first_domain} - {$last_domain} of {$totaldomains} Domains {$searchtexttag|escape:'html'}</p>
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
            <table class="full-width">
                <thead>
                    <tr>
                        <th width="95%" nowrap>{$Domain}</th>
                        <th class="text-center" nowrap>{$Status}</th>
                        <th class="text-center" nowrap>{$Owner}</th>
                        <th class="text-center" nowrap>{$Group_Owner}</th>
                        <th class="text-center" nowrap>Change Status</th>
                        <th class="text-center">Delete</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$out_array item=row}
                        <tr>
                            <td><a href="{$row.edit_url}">{$row.domain}</td>
                            <td class="text-center">{$row.status}</td>
                            <td class="text-center">
                                {if $row.change_owner_url != ""}
                                    <a href="{$row.change_owner_url}">{$row.owner_name}</a>
                                {else}
                                    {$row.owner_name}
                                {/if}
                            </td>
                            <td class="text-center">
                                {if $row.change_owner_url != ""}
                                    <a href="{$row.change_owner_url}">{$row.group_owner_name}</a>
                                {else}
                                    {$row.group_owner_name}
                                {/if}
                            </td>
                            <td class="text-center">
                                {strip}
                                    {if $row.status == "active"}
                                        {if $row.deactivate_url != ""}
                                            <a href="{$row.deactivate_url}" class="button warning">de-activate</a>
                                        {else}
                                            de-activate
                                        {/if}
                                    {else if $row.state == "inactive"}
                                        {if $row.activate_url != ""}
                                            <a href="{$row.activate_url}" class="button success">activate</a>
                                        {else}
                                            activate
                                        {/if}
                                    {/if}
                                {/strip}
                            </td>
                            <td class="text-center">
                                <a href="{$row.delete_url}" class="button alert"><i class="fa fa-trash-o"></i></a>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>

