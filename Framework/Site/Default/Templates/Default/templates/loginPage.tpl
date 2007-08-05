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

    {* everything else *}
    <td align="center">
    <img src="images/vegadns-big.png" alt="VegaDNS"><br>
    tinydns administration - version {$version}<p>
    {* Display messages *}
    <br>{$message}</b><br>
    <p>

{include file="$modulePath/$tplFile"}
</td>
</tr>
</table>
</center>
</body>
</html>
