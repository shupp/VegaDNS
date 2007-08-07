<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
    <HEAD>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
        <TITLE>VegaDNS Administration</TITLE>
        <link rel="STYLESHEET" type="text/css" href="vegadns-style.css">
        <link rel="STYLESHEET" type="text/css" href="tree.css">
        <!--[if gte IE 5.5000]>
        <script type="text/javascript" src="templates/pngfix.js"></script>
        <![endif]-->
        {literal}
        <script src="jquery.pack.js" type="text/javascript"></script>
        <script src="jquery.treeview.pack.js" type="text/javascript"></script>
        <script type="text/javascript">
        $(document).ready(function(){
            $("ul").Treeview({
                speed: "fast",
                store: true,
                unique: true,
                collapsed: true
            });
        });
        </script>
        {/literal}
    </HEAD>
<body>
<table border="0" width="100%">
    <tr valign="top">

{* menu type stuff *}
<td class="border" width="20%" align="left">
{* Display logged in/logout message *}
&nbsp;<b>{$email}</b><br>
&nbsp<a href="./?module=Login&amp;event=logoutNow">log out</a>
<hr>
{include file='framework:Framework+vegadns_menu.tpl'}
</td>
<td align="center">
<img src="images/vegadns-small.png" alt="VegaDNS"><p>
{* Display messages *}
{if $message}
<br><b>{$message}</b><br>
<p>
{/if}

{include file="$modulePath/$tplFile"}

</tr>
</table>
</center>
</body>
</html>
