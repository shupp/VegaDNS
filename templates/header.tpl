<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
    <HEAD>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
        <TITLE>VegaDNS Administration</TITLE>
        <link rel="STYLESHEET" type="text/css" href="templates/core-style.css">
    <!--[if gte IE 5.5000]>
    <script type="text/javascript" src="templates/pngfix.js"></script>
    <![endif]-->

    </HEAD>
    <body background="images/background.jpg">

<center>

<table border=0 width="100%">
    <tr valign="top">

{* Display logged in/logout message *}
{if $state == "logged_in" && $email != ""}
    <td width="25%">
    &nbsp;logged in as:<br>
    &nbsp;<b>{$email}</b><br>
    &nbsp<a href="{$logout_url}">log out</a><br>
    </td>
{/if}
    <td align="center">
    <img src="images/vegadns.png" alt="VegaDNS">
    <br>tinydns administration - version {$version}
    </td>
{if $state == "logged_in" && $email != ""}
    <td width="25%">
    </td>
{/if}
</tr>
</table>

<p>

{* Display menu at top at all times now *}
{if $state == "logged_in" && $email != ""}

<table border=0>
    <tr>
        <td align="center">
            <a href="{$base_url}&mode=domains">Domains</a>&nbsp | &nbsp
            <a href="{$base_url}&mode=domains&domain_mode=add">New Domain</a>&nbsp | &nbsp
            <a href="{$base_url}&mode=users&user_mode=edit_account&cid={$cid}">Edit My Account</a> | &nbsp
            <a href="{$base_url}&mode=dnsquery">DNS Query</a>
        </td>
    </tr>

    {if $account_type == 'senior_admin' || $account_type == 'group_admin'}
    <tr>
        <td align="center">
            <a href="{$base_url}&mode=users&user_mode=show_users">Accounts</a>&nbsp | &nbsp
            <a href="{$base_url}&mode=users&user_mode=add_account">Add Account</a>&nbsp | &nbsp
            <a href="{$base_url}&mode=default_records">Default Records</a>&nbsp

        {if $account_type == 'senior_admin'}
            | &nbsp <a href="{$base_url}&mode=domains&domain_mode=import_domains">Import Domains via AXFR</a>&nbsp
        {/if}
        </td>
    </tr>
    {/if}
</table>
        </td>
    </tr>

</table>
{/if}
<p>


    {* Display messages *}
    <br>{php}display_msg(){/php}</b><br>
    <p>
