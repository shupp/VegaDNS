<div class="row">
    <div class="small-12 columns">
        <h3>All User Accounts{if $user_account_type == 'group_admin'} In Your Group{/if}</h3>
        <table class="full-width">
            <thead>
                <tr>
                    <th nowrap>{$Name}</th>
                    <th nowrap>{$Email}</th>
                    <th nowrap class="text-center">{$Account_Type}</th>
                    <th nowrap class="text-center">{$Group_Owner}</th>
                    <th nowrap class="text-center">{$Status}</th>
                    <th class="text-center">Edit</th>
                    <th class="text-center">Delete</th>
                </tr>
            </thead>
            <tbody>
            {foreach from=$out_array item=row}
                <tr>
                    <td>{$row.name|escape}</td>
                    <td>{$row.email|escape}</td>
                    <td class="text-center">{$row.account_type}</td>
                    <td class="text-center">{$row.group_owner_name|escape}</td>
                    <td class="text-center">{$row.status}</td>
                    <td class="text-center"><a href="{$row.edit_url}" class="button success"><i class="fa fa-pencil"></i> edit</a></td>
                    <td class="text-center">
                        {strip}
                            {if $row.delete_url == ""}
                                <a class="button disabled"><i class="fa fa-trash-o"></i></a>
                            {else}
                                <a class="button alert" href="{$row.delete_url}"><i class="fa fa-trash-o"></i></a>
                            {/if}
                        {/strip}
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div>