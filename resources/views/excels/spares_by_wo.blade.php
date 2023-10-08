<table>
    <thead>
    <tr>
        <th>S/N</th>
        <th>WO#</th>
        <th>Trans Date</th>
        <th>Vehicle#</th>
        <th>Platform</th>
        <th>Item Details</th>
        <th>Part No</th>
        <th>Quantity</th>
        <th>Area Use</th>
        <th>Issue By</th>
        <th>Issue To</th>
        <th>Trans</th>
        <th>Expiry</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($data as $index => $value)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $value->wo }}</td>
            <td>{{ utcToClient($value->issued_date) }}</td>
            <td>{{ $value->vehicle_num }}</td>
            <td>{{ $value->platform }}</td>
            <td>{{ $value->spare_name }}</td>
            <td>{{ $value->part_no }}</td>
            <td>{{ $value->quantity }}</td>
            <td>{{ $value->torque_area }}</td>
            <td>{{ $value->issued_by }}</td>
            <td>{{ $value->issued_to }}</td>
            <td>{{ $value->tnx }}</td>
            <td>N/A</td>
        </tr>
    @endforeach
    </tbody>
</table>
