<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
    <HEAD>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
        <TITLE>VegaDNS Administration</TITLE>
        <link rel="STYLESHEET" type="text/css" href="vegadns-style.css">
        <link rel="stylesheet" href="thickbox.css" type="text/css" media="screen" />
        <!--[if gte IE 5.5000]>
        <script type="text/javascript" src="templates/pngfix.js"></script>
        <![endif]-->
        {literal}
        <script src="jquery.pack.js" type="text/javascript"></script>
        <script src="thickbox-compressed.js" type="text/javascript"></script>
        {/literal}
    </HEAD>
<body>
<table border="0" height="100%" width="100%">
    <tr valign="top">

    {* everything else *}
    <td align="center">
    <img src="images/vegadns-small.png" alt="VegaDNS"><br>
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
