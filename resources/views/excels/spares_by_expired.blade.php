<table>
    <thead>
    <tr>
        <th>S/N</th>
        <th>Expiring Date</th>
        <th>Item Details</th>
        <th>Part No</th>
        <th>Quantity</th>
        <th>Lead Time</th>
        <th>Item Location</th>
        <th>Remarks</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($data as $index => $value)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ utcToClient($value->expiry_date) }}</td>
            <td>{{ $value->name }}</td>
            <td>{{ $value->part_no }}</td>
            <td>{{ $value->quantity_oh }}</td>
            <td>N/A</td>
            <td>{{ $value->location }}</td>
            <td>N/A</td>
        </tr>
    @endforeach
    </tbody>
</table>
