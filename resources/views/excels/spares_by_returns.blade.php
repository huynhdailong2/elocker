<table>
    <thead>
        <tr>
            <th>S/N</th>
            <th>Trans Date</th>
            <th>Item Details</th>
            <th>Part #</th>
            <th>Quantity</th>
            <th>Location</th>
            <th>Item type</th>
            <th>Load/Hydrostatic Test Due</th>
            <th>Expiry Date</th>
            <th>Calibration Date</th>
            <th>Return By</th>
            <th>Trans</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $index => $value)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ utcToClient($value['created_at']) }}</td>
                <td>{{ $value['spares']['name'] }}</td>
                <td>{{ $value['spares']['part_no'] }}</td>
                <td>{{ $value['quantity'] }}</td>
                <td>
                    {{ $value['transaction']['cluster']['name'] ?: 'N/A' }} -
                    {{ $value['shelf']['name'] ?: 'N/A' }} -
                    {{ $value['bin']['row'] ?: 'N/A' }} -
                    {{ $value['bin']['bin'] ?: 'N/A' }}
                </td>
                <td>{{ $value['spares'] != null ? $value['spares']['label'] : 'N/A' }}</td>
                <td>
                    @if (!is_null(optional($value['configures']['load_hydrostatic_test_due'])->format('Y-m-d H:i:s')))
                        {{ optional($value['configures']['load_hydrostatic_test_due'])->format('d-m-Y H:i:s') }}
                    @else
                        N/A
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
                    @if (!is_null(optional($value['configures']['calibration_due'])->format('Y-m-d H:i:s')))
                        {{ optional($value['configures']['calibration_due'])->format('d-m-Y H:i:s') }}
                    @else
                        N/A
                    @endif
                </td>
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
                    {{ $value['conditions'] != null ? $value['conditions'] : 'N/A' }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
