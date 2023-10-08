<table>
    <thead>
    <tr>
        <th>Username</th>
        <th>Card ID</th>
        <th>Employee ID</th>
        <th>Role</th>
        <th>Dept</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($data as $index => $value)
        <tr>
            <td>{{ $value->login_name }}</td>
            <td>{{ $value->card_id }}</td>
            <td>{{ $value->employee_id }}</td>
            <td>{{ $value->role_name }}</td>
            <td>{{ $value->dept }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
