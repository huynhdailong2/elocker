<table>
    <thead>
    <tr>
        <th>S/N</th>
        <th>Item Name</th>
        <th>Type</th>
        <th>P/N</th>
        <th>Mat’l No</th>
        <th>Description</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($data as $index => $value)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $value->name }}</td>
            <td>{{ ucfirst($value->type) }}</td>
            <td>{{ $value->part_no }}</td>
            <td>{{ $value->material_no }}</td>
            <td>{{ $value->description }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
