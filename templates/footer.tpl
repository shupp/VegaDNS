    <div id="footer" class="content-container">
        <div class="row">
            <div class="small-12 columns text-center">
                <p>tinydns administration - version {$version}</p>
            </div>
        </div>
    </div>

    {literal}
        <script src="/templates/foundation/js/vendor/jquery.min.js"></script>
        <script src="/templates/foundation/js/vendor/what-input.min.js"></script>
        <script src="/templates/foundation/js/foundation.min.js"></script>
        <script src="/templates/foundation/js/app.js"></script>
    {/literal}
    {if $build_config}
        {literal}
            <script src="/templates/config-validation.js"></script>
        {/literal}
    {/if}

    {if $dashboardajax == true }
        {literal}
            <script src="/templates/foundation/js/dashboard.js"></script>
        {/literal}
    {/if}

</body>
</html>
