<table>
    <thead>
    <tr>
        <th>S/N</th>
        <th>MPN</th>
        <th>SSN</th>
        <th>Description</th>
        <th>Bin#</th>
        <th>Item Type</th>
        <th>Qty OH</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($data as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->material_no }}</td>
            <td>{{ $item->part_no }}</td>
            <td>{{ $item->description }}</td>
            <td>{{ $item->bin }}</td>
            <td>{{ ucwords($item->type) }}</td>
            <td>{{ $item->quantity }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
