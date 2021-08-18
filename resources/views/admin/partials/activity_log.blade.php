<table class="table is-striped is-fullwidth">
    <thead>
        <tr>
            <th>Date</th>
            <th>User</th>
            <th>Event</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($logs as $log)
            <tr>
                <td>
                    {{ $log->created_at->format('d/m/Y H:i') }}
                </td>
                <td>
                    @if ($log->causer)
                        {{ $log->causer->full_name }}
                    @endif
                </td>
                <td>
                    {{ $log->description }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
