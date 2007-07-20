<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
    <HEAD>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
        <TITLE>VegaDNS Administration</TITLE>
        <link rel="STYLESHEET" type="text/css" href="templates/core-style.css">
        <!--[if gte IE 5.5000]>
        <script type="text/javascript" src="templates/pngfix.js"></script>
        <![endif]-->
        {if !$logged_in}
        {literal}
        <script type="text/javascript">
        function focus(){
            document.vegadns.email.focus();
        }
        </script>
        {/literal}
        {/if}
    </HEAD>
<body onload="focus()">
<table border="0" height="100%" width="100%">
    <tr valign="top">

{* Split screen if logged in - menu on left, rest on the right *}
{if $logged_in}
    {* menu type stuff *}
    <td class="border" width="20%" align="left">
    {* Display logged in/logout message *}
    &nbsp;<b>{$logged_in_email}</b> logged in<br>
    &nbsp<a href="{$logout_url}">log out</a>
    <hr>
    {include file='menu.tpl'}
    </td>
    <td align="center">
    <img src="images/vegadns-small.png" alt="VegaDNS"><p>
{else}
    {* everything else *}
    <td align="center">
    <img src="images/vegadns-big.png" alt="VegaDNS"><br>
    tinydns administration - version {$version}<p>
{/if}
    {* Display messages *}
    <br>{$message}</b><br>
    <p>

{include file="$modulePath/$tplFile"}

{if $logged_in}
    {* <p><a href="{$base_url}">Main Menu</a> *}
{else}
</td>
{/if}

</tr>
</table>
</center>
</body>
</html>
