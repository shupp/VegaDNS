<div class="row">
    <div class="small-12 columns">
        <h3>System stats</h3>
    </div>
</div>

<div class="row small-up-2 medium-up-4" data-equalizer>
    <div class="column text-center">
        <div class="callout" data-equalizer-watch>
            <strong>System IP</strong><br>
            {$system.ip}
        </div>
    </div>
    <div class="column text-center">
        <div class="callout" data-equalizer-watch>
            <strong>Total Domains</strong><br>
            {$totaldomains}
        </div>
    </div>
    <div class="column text-center">
        <div class="callout" data-equalizer-watch>
            <strong>System OS</strong><br>
            {$system.os} ({$system.kernel})
        </div>
    </div>
    <div class="column text-center">
        <div class="callout" data-equalizer-watch>
            <strong>Processes ({$system.proccess.proc_total})</strong><br>
            Running: {$system.proccess.totals.running}<br />
            Zombie: {$system.proccess.totals.zombie}<br />
            Sleeping: {$system.proccess.totals.sleeping}
        </div>
    </div>
</div>

<div class="row">
    <div class="small-12 columns">
        <div class="callout">
            <h4>CPU Info</h4>
            {foreach from=$system.cpu key=coreid item=core}
                <strong>Core {$coreid+1}:</strong> {$core.Vendor} {$core.Model} @ {$core.MHz}MHz<br />
            {/foreach}
            <h5>Usage: (<span id="cpuusage">0</span>%)</h5>
            <div class="success progress" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-meter" id="cpupercent" style="width: 0%"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="small-12 columns">
        <div class="callout">
            <h4>Memory Info</h4>
            <div class="small-6 pull-left">
                <strong>Total RAM: </strong><span id="totalram">1</span> GB
            </div>
            <div class="small-6 pull-right">
                <strong>Free RAM: </strong><span id="freeram">0</span> GB
            </div>
            <h5>Usage: (<span id="ramusage">0</span>%)</h5>
            <div class="success progress" role="progressbar"  aria-valuemin="0" aria-valuemax="100">
                <div class="progress-meter" id="rampercent" style="width: 0%"></div>
            </div>
        </div>
    </div>
</div>



