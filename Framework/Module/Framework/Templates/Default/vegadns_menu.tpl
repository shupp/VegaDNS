<table border="0" cellpadding="0" cellspacing="0">
<tr><td>{$menurows}</td></tr>
</table>



    {* <ul>    <li><a href="{$base_url}&mode=domains">Domains</a>
            <li><a href="{$base_url}&mode=domains&domain_mode=add">New Domain</a>
            <li><a href="{$base_url}&mode=users&user_mode=edit_account&user_id={$user_id}">Edit My Account</a>
    {if $account_type == 'senior_admin' || $account_type == 'group_admin'}
            <li><a href="{$base_url}&mode=users&user_mode=show_users">Accounts</a>
            <li><a href="{$base_url}&mode=users&user_mode=add_account">Add Account</a>
        {if $account_type == 'senior_admin'}
        {/if}
    {/if}
    </ul> *}

    <hr>
        <a href="{$base_url}&mode=default_records">Default Records</a><br>
        <a href="{$base_url}&mode=domains&domain_mode=import_domains">AXFR Import</a><br>
        <a href="{$base_url}&mode=dnsquery">DNS Query</a><br>
        <a href="{$base_url}&mode=whoisquery">Whois Query</a>
