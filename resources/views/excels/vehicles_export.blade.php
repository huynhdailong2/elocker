<table>
    <thead>
    <tr>
        <th>S/N</th>
        <th>Veh no.</th>
        <th>Variant</th>
        <th>Unit</th>
        <th>Last O Point Servicing</th>
        <th>6 mth Plan</th>
        <th>12 mth Plan</th>
        <th>18 mth Plan</th>
        <th>24 mth Plan</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($data as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item['vehicle_num'] }}</td>
            <td>{{ $item['variant'] }}</td>
            <td>{{ strtoupper($item['unit']) }}</td>
            <td>{{ \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $item['last_point_servicing'])->format('d-m-Y') }}</td>
            <td>{{ \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $item['schedule_6_months'])->format('d-m-Y') }}</td>
            <td>{{ \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $item['schedule_12_months'])->format('d-m-Y') }}</td>
            <td>{{ \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $item['schedule_18_months'])->format('d-m-Y') }}</td>
            <td>{{ \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $item['schedule_24_months'])->format('d-m-Y') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
