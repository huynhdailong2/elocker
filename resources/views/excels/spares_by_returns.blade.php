<table>
    <thead>
    <tr>
        <th>S/N</th>
        {{-- <th>WO#</th> --}}
        <th>Trans Date</th>
        {{-- <th>Vehicle#</th>
        <th>Platform</th> --}}
        <th>Item Details</th>
        <th>Part No</th>
        <th>Quantity</th>
        {{-- <th>Area Use</th> --}}
        <th>Return By</th>
        <th>Trans</th>
        <th>Expiry Date</th>
        <th>Calibration Date</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($data as $index => $value)
        <tr>
            <td>{{ $index + 1 }}</td>
            {{-- <td>{{ $value->wo }}</td> --}}
            {{-- <td>{{ utcToClient($value->returned_date) }}</td> --}}
            {{-- <td>{{ $value->vehicle_num }}</td>
            <td>{{ $value->platform }}</td> --}}
            {{-- <td>{{ $value->spare_name }}</td>
            <td>{{ $value->part_no }}</td>
            <td>{{ $value->quantity }}</td> --}}
            {{-- <td>{{ $value->torque_area }}</td> --}}
            {{-- <td>{{ $value->returned_by }}</td>
            <td>{{ $value->tnx }}</td>
            <td>{{ utcToClient($value->expiry_date, 'd/m/Y') }}</td>
            <td>{{ utcToClient($value->calibration_due, 'd/m/Y') }}</td> --}}
        </tr>
    @endforeach
    </tbody>
</table>
