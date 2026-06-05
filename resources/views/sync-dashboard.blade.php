<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Background Sync Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
        }

        .card {
            border-radius: 15px;
        }

        .stats-card {
            transition: 0.3s;
        }

        .stats-card:hover {
            transform: translateY(-4px);
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .dashboard-header {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <div class="container py-5">

        <div class="dashboard-header shadow-sm">

            <div class="d-flex justify-content-between align-items-center">

                <h2 class="fw-bold mb-0">
                    🚀 Background Sync Dashboard
                </h2>

                <a href="/start-sync" class="btn btn-primary">
                    Run Sync
                </a>

            </div>

        </div>

        {{-- Success Message --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">

            {{ session('success') }}

            <button type="button"
                class="btn-close"
                data-bs-dismiss="alert"></button>

        </div>
        @endif

        {{-- Statistics --}}
        <div class="row mb-4">

            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0 stats-card">
                    <div class="card-body text-center">
                        <h6>Total Syncs</h6>
                        <h2>{{ $totalSyncs }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0 stats-card">
                    <div class="card-body text-center">
                        <h6>Completed</h6>
                        <h2 class="text-success">
                            {{ $completedSyncs }}
                        </h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0 stats-card">
                    <div class="card-body text-center">
                        <h6>Failed</h6>
                        <h2 class="text-danger">
                            {{ $failedSyncs }}
                        </h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0 stats-card">
                    <div class="card-body text-center">
                        <h6>Running</h6>
                        <h2 class="text-warning">
                            {{ $runningSyncs }}
                        </h2>
                    </div>
                </div>
            </div>

        </div>

        {{-- Search & Filter --}}
        <div class="card shadow-sm border-0 mb-4">

            <div class="card-body">

                <form method="GET" action="/sync-dashboard">

                    <div class="row g-3">

                        <div class="col-md-5">

                            <input
                                type="text"
                                name="search"
                                class="form-control"
                                placeholder="Search status or message..."
                                value="{{ request('search') }}">

                        </div>

                        <div class="col-md-4">

                            <select name="status" class="form-select">

                                <option value="">
                                    All Status
                                </option>

                                <option value="Completed"
                                    {{ request('status') == 'Completed' ? 'selected' : '' }}>
                                    Completed
                                </option>

                                <option value="Failed"
                                    {{ request('status') == 'Failed' ? 'selected' : '' }}>
                                    Failed
                                </option>

                                <option value="Running"
                                    {{ request('status') == 'Running' ? 'selected' : '' }}>
                                    Running
                                </option>

                            </select>

                        </div>

                        <div class="col-md-3">

                            <button class="btn btn-success w-100">
                                Search / Filter
                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>

        {{-- Table --}}
        <div class="card shadow-sm border-0">

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered table-hover align-middle">

                        <thead class="table-dark">

                            <tr>

                                <th>ID</th>
                                <th>Status</th>
                                <th>Started At</th>
                                <th>Completed At</th>
                                <th>Duration</th>
                                <th>Message</th>
                                <th width="120">Action</th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($histories as $history)

                            <tr>

                                <td>{{ $history->id }}</td>

                                <td>

                                    @if($history->status == 'Completed')

                                    <span class="badge bg-success">
                                        Completed
                                    </span>

                                    @elseif($history->status == 'Failed')

                                    <span class="badge bg-danger">
                                        Failed
                                    </span>

                                    @else

                                    <span class="badge bg-warning text-dark">
                                        Running
                                    </span>

                                    @endif

                                </td>

                                <td>
                                    {{ $history->started_at }}
                                </td>

                                <td>
                                    {{ $history->completed_at }}
                                </td>

                                <td>

                                    @if($history->duration)

                                    {{ $history->duration }} sec

                                    @else

                                    --

                                    @endif

                                </td>

                                <td>
                                    {{ $history->message }}
                                </td>

                                <td>

                                    <form
                                        action="{{ route('sync.destroy',$history->id) }}"
                                        method="POST">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Delete this record?')">

                                            Delete

                                        </button>

                                    </form>

                                </td>

                            </tr>

                            @empty

                            <tr>

                                <td colspan="7" class="text-center">

                                    No Sync Records Found

                                </td>

                            </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

                <div class="d-flex justify-content-end mt-3">

                    {{ $histories->withQueryString()->links('pagination::bootstrap-5') }}

                </div>

            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
