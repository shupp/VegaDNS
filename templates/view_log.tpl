<div class="row">
    <div class="small-12 columns">
        <table class="full-width">
            <thead>
                <tr>
                    <th>Name</th>
                    <th nowrap class="text-center">CustomerID</th>
                    <th>Email</th>
                    <th>Log Entry</th>
                    <th>Date / Time</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$logs item=row}
                    <tr>
                        <td nowrap>{$row.name}</td>
                        <td class="text-center">{$row.cid}</td>
                        <td nowrap>{$row.email}</td>
                        <td>{$row.entry}</td>
                        <td nowrap>{$row.time}</td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</div>