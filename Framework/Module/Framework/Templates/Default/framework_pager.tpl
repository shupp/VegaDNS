<div class="framework_pager"{if strlen($params.id)} id="{$params.id}"{/if}>
{if $nav->start > 0}
    <a class="begin" href="{$url}{$s}start={$nav->getBeginning()}">&laquo;</a>
    <a class="prev" href="{$url}{$s}start={$nav->getPrevPage()}">&laquo; Prev</a>
{else}
    <span class="begin disabled">&laquo;</span>
    <span class="prev disabled">&laquo; Prev</span>
{/if}
{foreach key=key item=val from=$nav->getPageList()}
    {if $nav->start == $val}
        <a class="page current" href="{$url}{$s}start={$val}">{$key}</a>
    {else}
        <a class="page" href="{$url}{$s}start={$val}">{$key}</a>
    {/if}
{/foreach}
{if $nav->getNextPage() > 0}
    <a class="next" href="{$url}{$s}start={$nav->getNextPage()}">Next &raquo;</a>
{else}
    <span class="next disabled">Next &raquo;</span>
{/if}
{if $nav->start < $nav->getEnd()}
    <a class="end" href="{$url}{$s}start={$nav->getEnd()}">&raquo;</a>
{else}
    <span class="end disabled">&raquo;</span>
{/if}
<div class="framework_pager_results">
    ({$nav->start+1}-{$stop} of {$nav->total})
</div>
</div>
