<table>
    <thead>
    <tr>
        <th>S/N</th>
        <th>WO#</th>
        <th>Trans Date</th>
        <th>Vehicle#</th>
        <th>Platform</th>
        <th>Item Details</th>
        <th>Part #</th>
        <th>Area Use</th>
        <th>Quantity</th>
        <th>Loan By</th>
        <th>Loan To</th>
        <th>Trans</th>
        <th>Load/Hydrostatic Test Due</th>
        <th>Inspection / Calibration Due</th>
        <th>Expiring Date</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($data as $index => $value)
        <tr>
            <td>{{ $index + 1 }}</td>
            {{-- <td>{{ $value->wo }}</td> --}}
            {{-- <td>{{ utcToClient($value->issued_date) }}</td>
            <td>{{ $value->vehicle_num }}</td>
            <td>{{ $value->platform }}</td>
            <td>{{ $value->spare_name }}</td>
            <td>{{ $value->part_no }}</td>
            <td>{{ $value->torque_area }}</td>
            <td>{{ $value->quantity }}</td>
            <td>{{ $value->issued_by }}</td>
            <td>{{ $value->issued_to }}</td>
            <td>{{ $value->tnx }}</td>
            <td>{{ $value->load_hydrostatic_test_due ? \Carbon\Carbon::createFromFormat('Y-m-d', $value->load_hydrostatic_test_due)->format('d-m-Y') : '' }}</td>
            <td>{{ $value->calibration_due ? \Carbon\Carbon::createFromFormat('Y-m-d', $value->calibration_due)->format('d-m-Y') : '' }}</td>
            <td>{{ $value->expiry_date ? \Carbon\Carbon::createFromFormat('Y-m-d', $value->expiry_date)->format('d-m-Y') : '' }}</td> --}}
        </tr>
    @endforeach
    </tbody>
</table>
