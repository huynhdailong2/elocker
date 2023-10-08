<table>
    <thead>
    <tr>
        <th>S/N</th>
        <th>User Name</th>
        <th>Card ID</th>
        <th>Trans Date</th>
        <th>Item Name</th>
        <th>Device ID</th>
        <th>Qty Change</th>
        <th>OH Change</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($data as $index => $value)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $value->weighingHistory->name }}</td>
            <td>{{ $value->weighingHistory->card_id }}</td>
            <td>{{ utcToClient($value->created_at) }}</td>
            <td>{{ $value->name }}</td>
            <td>{{ $value->bin_id }}</td>
            <td>{{ $value->change_quantity }}</td>
            <td>{{ $value->quantity }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
