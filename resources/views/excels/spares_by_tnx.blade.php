<table>
    <thead>
        <tr>
            <th>S/N</th>
            <th>Trans Id</th>
            <th>Trans Date</th>
            <th>WO#</th>
            <th>Vehicle#</th>
            <th>Platform</th>
            <th>Location</th>
            <th>Item Type</th>
            <th>Item Details</th>
            <th>Part #</th>
            <th>Quantity</th>
            <th>Area Use</th>
            <th>Issue By</th>
            <th>Trans</th>
            <th>Expiry</th>
            <th>User Agent</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $index => $value)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $value['transaction'] != null ? $value['transaction']['trans_id'] : 'N/A' }}</td>
                <td>{{ utcToClient($value['created_at']) }}</td>
                <td>{{ $value['job_card'] != null ? $value['job_card']['wo'] : 'N/A' }}</td>
                <td>{{ $value['vehicle'] != null ? $value['vehicle']['vehicle_num'] : 'N/A' }} </td>
                <td>{{ $value['job_card'] != null ? $value['job_card']['platform'] : 'N/A' }} </td>
                <td>
                    {{ $value['transaction']['cluster']['name'] ?: 'N/A' }} -
                    {{ $value['shelf']['name'] ?: 'N/A' }} -
                    {{ $value['bin']['row'] ?: 'N/A' }} -
                    {{ $value['bin']['bin'] ?: 'N/A' }}
                </td>
                <td>{{ $value['spares'] != null ? $value['spares']['label'] : 'N/A' }}</td>
                <td>{{ $value['spares']['name'] }}</td>
                <td>{{ $value['spares']['part_no'] }}</td>
                <td>{{ $value['quantity'] }}</td>
                <td>{{ $value['torque_wrench_area'] != null ? $value['torque_wrench_area']['area'] : 'N/A' }}</td>
                <td>{{ $value['transaction']['user']['name'] }}</td>
                <td>
                    @if ($value['transaction']['type'] === 'issue')
                        @if ($value['spares']['type'] === 'consumable')
                            {{ 'I' }}
                        @else
                            {{ 'L' }}
                        @endif
                    @elseif ($value['transaction']['type'] === 'return')
                        {{ 'R' }}
                    @elseif ($value['transaction']['type'] === 'replenish')
                        {{ 'RP' }}
                    @endif
                </td>
                <td>
                    @if (!is_null(optional($value['configures']['expiry_date'])->format('Y-m-d H:i:s')))
                        {{ optional($value['configures']['expiry_date'])->format('d-m-Y H:i:s') }}
                    @else
                        N/A
                    @endif
                </td>
                <td>
                    @if ($value['user_agent'] != null)
                        @if (str_contains($value['user_agent'], 'Mozilla'))
                            Web
                        @elseif(str_contains($value['user_agent'], 'Postman'))
                            Postman
                        @else
                            Local
                        @endif
                    @else
                        N/A
                    @endif
                </td>

            </tr>
        @endforeach
    </tbody>
</table>
