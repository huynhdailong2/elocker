<table>
    <thead>
    <tr>
        <th>S/N</th>
        <th>Expiring Date</th>
        <th>Item Details</th>
        <th>Item Type</th>
        <th>Part No</th>
        <th>Quantity</th>
        <th>Item Location</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($data as $index => $value)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ utcToClient($value->expiry_date) }}</td>
            <td>{{ $value->name }}</td>
            <td>{{ ucfirst($value->item_type) }}</td>
            <td>{{ $value->part_no }}</td>
            <td>{{ $value->quantity_oh }}</td>
            <td>{{ $value->location }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
