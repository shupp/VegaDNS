<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <title>VegaDNS Administration</title>
        <link rel="STYLESHEET" type="text/css" href="templates/core-style.css" />
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
    </head>
<body onload="focus()">
<table border="0" width="100%">
    <tr valign="top">

    {* everything else *}
    <td align="center">
    <img src="images/vegadns-big.png" alt="VegaDNS" /><br />
    tinydns administration - version {$version}<p />
    {* Display messages *}
    <br /><b>{$message}</b><br />
    <p />

{include file="$modulePath/$tplFile"}
</td>
</tr>
</table>
</body>
</html>
