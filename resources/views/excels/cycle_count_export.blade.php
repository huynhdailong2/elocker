<table>
    <thead>
    <tr>
        <th>S/N</th>
        <th>Item Type</th>
        <th>Number of Item Type</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($types as $index => $type)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ ucwords($type) }}</td>
            <td>{{ empty($data[$type]) ? 0 : count($data[$type]) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
