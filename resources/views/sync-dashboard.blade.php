<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Background Sync Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

    <div class="container py-5">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h2>Background Sync Dashboard</h2>

            <a href="/start-sync" class="btn btn-primary">
                Run Sync
            </a>

        </div>

        <!-- Statistics -->

        <div class="row mb-4">

            <div class="col-md-3">

                <div class="card shadow-sm border-0">

                    <div class="card-body">

                        <h6>Total Syncs</h6>

                        <h3>{{ $totalSyncs }}</h3>

                    </div>

                </div>

            </div>

            <div class="col-md-3">

                <div class="card shadow-sm border-0">

                    <div class="card-body">

                        <h6>Completed</h6>

                        <h3>{{ $completedSyncs }}</h3>

                    </div>

                </div>

            </div>

            <div class="col-md-3">

                <div class="card shadow-sm border-0">

                    <div class="card-body">

                        <h6>Failed</h6>

                        <h3>{{ $failedSyncs }}</h3>

                    </div>

                </div>

            </div>

            <div class="col-md-3">

                <div class="card shadow-sm border-0">

                    <div class="card-body">

                        <h6>Running</h6>

                        <h3>{{ $runningSyncs }}</h3>

                    </div>

                </div>

            </div>

        </div>

        <!-- Table -->

        <div class="card shadow-sm border-0">

            <div class="card-body">

                <table class="table table-bordered align-middle">

                    <thead class="table-dark">

                        <tr>

                            <th>ID</th>

                            <th>Status</th>

                            <th>Started At</th>

                            <th>Completed At</th>

                            <th>Duration</th>

                            <th>Message</th>

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

                        </tr>

                        @empty

                        <tr>

                            <td colspan="6" class="text-center">

                                No Sync Records Found

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

                <!-- Right Side Pagination -->

                <div class="d-flex justify-content-end mt-4">

                    {{ $histories->onEachSide(1)->links('pagination::bootstrap-5') }}

                </div>

            </div>

        </div>

    </div>

</body>

</html>