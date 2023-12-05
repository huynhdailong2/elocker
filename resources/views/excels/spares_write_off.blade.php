<table>
    <thead>
    <tr>
        <th>S/N</th>
        <th>Item Details</th>
        <th>Part No</th>
        <th>Quantity</th>
        <th>Location</th>
        <th>Reason</th>
        <th>Write Off By</th>
        <th>Edited Time</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($data as $index => $value)
        <tr>
            <td>{{ $index + 1 }}</td>
            {{-- <td>{{ $value->name }}</td>
            <td>{{ $value->part_no }}</td>
            <td>{{ $value->quantity }}</td>
            <td>{{ $value->location }}</td>
            <td>{{ $value->reason }}</td>
            <td>{{ $value->write_off_name }}</td>
            <td>{{ utcToClient($value->created_at) }}</td> --}}
        </tr>
    @endforeach
    </tbody>
</table>
