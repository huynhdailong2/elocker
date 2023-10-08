<table>
    <thead>
    <tr>
        <th>S/N</th>
        <th>Loan Date</th>
        <th>Yet to Return</th>
        <th>WO#</th>
        <th>Vehicle#</th>
        <th>Platform</th>
        <th>Item Details</th>
        <th>Part No</th>
        <th>Quantity</th>
        <th>Loan By</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($data as $index => $value)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ utcToClient($value->issued_date) }}</td>
            <td>{{ $value->yet_to_return }}</td>
            <td>{{ $value->wo }}</td>
            <td>{{ $value->vehicle_num }}</td>
            <td>{{ $value->platform }}</td>
            <td>{{ $value->spare_name }}</td>
            <td>{{ $value->part_no }}</td>
            <td>{{ $value->issued_quantity }}</td>
            <td>{{ $value->issued_by }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
