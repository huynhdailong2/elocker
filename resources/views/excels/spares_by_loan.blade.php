<table>
    <thead>
        <tr>
            <th>No.</th>
            <th>WO/Svc#</th>
            <th>Loan Date</th>
            <th>Return Date</th>
            <th>Vehicle</th>
            <th>Platform</th>
            <th>Location</th>
            <th>Item type</th>
            <th>Item Details</th>
            <th>Part No</th>
            <th>Qty</th>
            <th>By</th>
            <th>Load/Hydrostatic Test Due</th>
            <th>Calibration/Inspection Due</th>
            <th>Trans</th>
            <th>Expiry Date</th>
        </tr>
    </thead>
    <tbody> 
    @foreach ($data as $index => $value) 
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $value['job_card']['wo'] }}</td>
            <td>{{ utcToClient($value['transaction']['created_at']) }}</td>
            <td>{{ utcToClient($value['transaction']['updated_at']) }}</td>
            <td>{{ $value['vehicle']['vehicle_num'] }}</td>
            <td>{{ $value['job_card']['platform'] }}</td>
            <td>  
                {{ $value['transaction']['cluster']['name'] }} -
                {{ $value['shelf']['name'] }} -
                {{ $value['bin']['row'] }} -
                {{ $value['bin']['bin'] }}
            </td>
            <td>{{ $value['spares']['label'] }}</td>
            <td>{{ $value['spares']['name'] }}</td>
            <td>{{ $value['spares']['part_no'] }}</td>
            <td>{{ $value['quantity'] }}</td>
            <td>{{ $value['transaction']['user']['name'] }}</td>
            <td>{{ utcToClient($value['configures']['load_hydrostatic_test_due'])}}</td>
            <td>{{ utcToClient($value['configures']['calibration_due']) }}</td>
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
            <td>{{ utcToClient($value['configures']['expiry_date']) }}</td>
        </tr>
    @endforeach 
    </tbody>
</table>