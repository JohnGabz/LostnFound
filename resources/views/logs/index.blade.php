@extends('layouts.app')

@section('title', 'System Logs')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">System Logs</h2>
        </div>

        <div class="card shadow-sm rounded">
            <div class="card-body">
                @if ($logs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr class="text-center text-uppercase small fw-semibold">
                                    <th style="width: 5%">#</th>
                                    <th style="width: 20%">User</th>
                                    <th style="width: 15%">Action</th>
                                    <th style="width: 40%">Details</th>
                                    <th style="width: 20%">Timestamp</th>
                                </tr>
                            </thead>
                            <tbody class="fs-6">
                                @foreach ($logs as $index => $log)
                                    <tr>
                                        <td class="text-center">{{ $logs->firstItem() + $index }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $log->user->name ?? 'Unknown' }}</div>
                                            <div class="text-muted small">ID: {{ $log->user_id }}</div>
                                        </td>
                                        <td class="text-center text-capitalize">{{ $log->action }}</td>
                                        <td>{{ $log->details ?? '—' }}</td>
                                        <td class="text-muted small text-center">{{ $log->timestamp->format('M d, Y h:i A') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $logs->links() }}
                    </div>
                @else
                    <div class="alert alert-info text-center">
                        <h5 class="fw-semibold">No logs available</h5>
                        <p class="mb-0">There are no system logs to display at the moment.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
