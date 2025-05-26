@extends('layouts.app')

@section('title', 'System Logs')

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow rounded">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">System Logs</h4>
        </div>

        <div class="card-body">
            @if ($logs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Details</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $index => $log)
                                <tr>
                                    <td>{{ $logs->firstItem() + $index }}</td>
                                    <td>{{ $log->user->name ?? 'Unknown' }} (ID: {{ $log->user_id }})</td>
                                    <td>{{ $log->action }}</td>
                                    <td>{{ $log->details ?? '—' }}</td>
                                    <td>{{ $log->timestamp->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $logs->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    No logs available at the moment.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
