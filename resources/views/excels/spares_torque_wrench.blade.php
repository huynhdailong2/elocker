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
        <th>Torque Wrench No</th>
        <th>Area Use</th>
        <th>Issue To</th>
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
            <td>{{ toNumber($value->torque_value) }}</td>
            <td>{{ $value->torque_area }}</td>
            <td>{{ $value->issued_to }}</td>
            <td>{{ utcToClient($value->expiry_date) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
