<table>
    <thead>
    <tr>
        <th>No.</th>
        <th>Item Details</th>
        <th>Part No</th>
        <th>Quantity</th>
        <th>Item Location</th>
        <th>Reason</th>
        <th>Write Off By</th>
        <th>Edited Time</th>
        <th>Item Type</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($data as $index => $value)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $value['spares']['name'] }}</td>
            <td>{{ $value['spares']['part_no'] }}</td>
            <td>{{ $value['quantity']}}</td>
            <td>   
                {{ $value['cluster_name' ]}} -
                {{ $value['cabinet_name'] }} -
                {{ $value['bin']['row'] }} -
                {{ $value['bin_name'] }}
            </td>
            <td>{{ $value['reason'] }}</td>
            <td>{{ $value['user']['name'] }}</td>
            <td>{{ utcToClient($value['created_at']) }}</td>
            <td>{{ $value['spares']['label'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
