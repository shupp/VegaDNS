<div style="border: solid black; border-width: .01em">
    <div style="background-color: #DCCFFF;">
        <div style="float: left;">{$curGroup->name}</div>
        <div style="float: right;">
            <a href="./?module=Domains&amp;group_id={$curGroup->id}">Domains</a>
            <a href="./?module=Users&amp;group_id={$curGroup->id}">Users</a>
            <a href="./?module=Log&amp;group_id={$curGroup->id}">Log</a>
        </div>
    </div>
    <div>
    <br />
    <br />
    <div style="float: left;">Sub-Groups</div>
    <br />
    <hr width="100%"/>
    {if $subGroups}
        <div>
        {foreach from=$subGroups item=sub}
            <div style="float: left;">{$sub->name}</div>
            <div style="float: right;">
                <a href="./?module=Domains&amp;group_id={$sub->id}">Domains</a> | 
                <a href="./?module=Domains&amp;group_id={$sub->id}">Domains</a> | 
                <a href="./?module=Users&amp;group_id={$sub->id}">Users</a> | 
                <a href="./?module=Log&amp;group_id={$sub->id}">Log</a> | 
                <a href="./?module=Groups&amp;event=delete&amp;group_id={$sub->id}">Delete</a>
            </div>
            <br />
        {/foreach}
        </div>
    {/if}
    </div>
</div>
