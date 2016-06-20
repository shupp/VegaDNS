<!doctype html>
<html class="no-js" lang="en">
    <HEAD>
        <meta charset="utf-8" />
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <TITLE>VegaDNS Administration</TITLE>
        <link rel="stylesheet" href="/templates/foundation/css/foundation.css" />
        <link rel="stylesheet" href="/templates/fontawesome/css/font-awesome.min.css" />
        <link rel="stylesheet" href="/templates/foundation/css/app.css" />

    <![endif]-->

    </HEAD>
    <body>

    <div id="header" class="content-container">
        <div class="row">
            <div class="small-12 medium-6 large-4 columns">
                <a href="{$base_url}" class="brand"><img src="images/vegadns.png" alt="VegaDNS"></a>
            </div>
            <div class="small-12 medium-6 large-8 columns text-right">
                {if $state == "logged_in" && $email != ""}
                    logged in as: {$email}<br/>
                    <a href="{$base_url}&mode=users&user_mode=edit_account&cid={$cid}">Edit My Account</a><br/>
                    <a href="{$logout_url}">Log Out</a>
                {/if}
            </div>
        </div>

        {* logged in, show menu! *}
        {if $state == "logged_in" && $email != ""}
            <div class="row">
                <div class="small-12 columns">
                    <div class="top-bar">
                        <ul class="dropdown menu" data-dropdown-menu>
                            <li>
                                <a href="{$base_url}">Dashboard</a>
                            </li>
                            <li class="has-submenu">
                                <a href="{$base_url}&mode=domains">Domains</a>
                                <ul class="submenu menu vertical" data-submenu>
                                    <li><a href="{$base_url}&mode=domains&domain_mode=add">New Domain</a></li>
                                </ul>
                            </li>
                            {if $account_type == 'senior_admin' || $account_type == 'group_admin'}
                                <li class="has-submenu">
                                    <a href="{$base_url}&mode=users&user_mode=show_users">Accounts</a>
                                    <ul class="submenu menu vertical" data-submenu>
                                        <li><a href="{$base_url}&mode=users&user_mode=add_account">Add Account</a></li>
                                    </ul>
                                </li>
                            {/if}
                            <li class="has-submenu">
                                <a href="#">Tools</a>
                                <ul class="submenu menu vertical" data-submenu>
                                    <li><a href="{$base_url}&mode=default_records">Default Records</a></li>
                                    <li><a href="{$base_url}&mode=dnsquery">DNS Query</a></li>
                                    {if $account_type == 'senior_admin'}
                                    <li><a href="{$base_url}&mode=domains&domain_mode=import_domains">Import Domains via AXFR</a></li>
                                    {/if}
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        {/if}
    </div>

    {* i'm not sure where these are coming from yet! *}
    {* Display messages *}
    <div class="row">
        <div class="small-12 columns">
            {php}display_msg(){/php}
        </div>
    </div>
