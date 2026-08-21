<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Background Sync Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #f4f6f9; }
        .card { border-radius: 15px; }
        .stats-card { transition: 0.3s; }
        .stats-card:hover { transform: translateY(-4px); }
        .table th, .table td { vertical-align: middle; }
        .dashboard-header { background: white; padding: 20px; border-radius: 15px; margin-bottom: 20px; }
        .progress { height: 20px; border-radius: 10px; }
        #autoRefreshTimer { font-size: 13px; color: #6c757d; }
    </style>
</head>

<body>
<div class="container py-4">

    {{-- Header --}}
    <div class="dashboard-header shadow-sm">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="fw-bold mb-0">🚀 Background Sync Dashboard</h2>
            <div class="d-flex align-items-center gap-3">
                <span id="autoRefreshTimer">Auto-refresh in <b id="countdown">30</b>s</span>
                <button class="btn btn-outline-secondary btn-sm" id="toggleRefresh">Pause Auto-Refresh</button>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#syncModal">
                    ▶ Run Sync
                </button>
            </div>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Stats --}}
    <div class="row mb-4">
        @foreach([['Total Syncs', $totalSyncs, 'primary'], ['Completed', $completedSyncs, 'success'], ['Failed', $failedSyncs, 'danger'], ['Running', $runningSyncs, 'warning']] as [$label, $count, $color])
        <div class="col-6 col-md-3 mb-3">
            <div class="card shadow-sm border-0 stats-card">
                <div class="card-body text-center">
                    <h6 class="text-muted">{{ $label }}</h6>
                    <h2 class="text-{{ $color }}">{{ $count }}</h2>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Chart + Export Row --}}
    <div class="row mb-4">
        <div class="col-md-5 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <h6 class="fw-bold mb-3">📊 Sync Status Chart</h6>
                    <canvas id="syncChart" style="max-height:220px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-7 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">🔍 Search, Filter & Export</h6>
                    <form method="GET" action="{{ route('sync.dashboard') }}" id="filterForm">
                        <div class="row g-2">
                            <div class="col-md-5">
                                <input type="text" name="search" class="form-control form-control-sm"
                                    placeholder="Search status, type, message..."
                                    value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Status</option>
                                    @foreach(['Completed','Failed','Running'] as $s)
                                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-primary btn-sm w-100">Search / Filter</button>
                            </div>
                            <div class="col-md-4">
                                <input type="date" name="date_from" class="form-control form-control-sm"
                                    value="{{ request('date_from') }}" placeholder="From Date">
                            </div>
                            <div class="col-md-4">
                                <input type="date" name="date_to" class="form-control form-control-sm"
                                    value="{{ request('date_to') }}" placeholder="To Date">
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-outline-secondary btn-sm w-100"
                                    onclick="window.location='{{ route('sync.dashboard') }}'">Clear</button>
                            </div>
                        </div>
                    </form>
                    <hr>
                    <a href="{{ route('sync.exportCsv', request()->only(['status','date_from','date_to'])) }}"
                        class="btn btn-outline-success btn-sm">
                        ⬇ Export CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden Bulk Delete Form (outside table) --}}
    <form method="POST" action="{{ route('sync.bulkDelete') }}" id="bulkForm">
        @csrf
        <div id="bulkCheckboxes"></div>
    </form>

    {{-- Table Card --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <input type="checkbox" id="selectAll" class="form-check-input me-1">
                    <label for="selectAll" class="form-check-label fw-semibold">Select All</label>
                </div>
                <button type="button" class="btn btn-danger btn-sm" onclick="submitBulkDelete()">
                    🗑 Bulk Delete
                </button>
            </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th width="40"><input type="checkbox" id="selectAllHeader" class="form-check-input"></th>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Progress</th>
                                <th>Started At</th>
                                <th>Completed At</th>
                                <th>Duration</th>
                                <th>Message</th>
                                <th width="200">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($histories as $history)
                            <tr>
                                <td>
                                    <input type="checkbox" value="{{ $history->id }}"
                                        class="form-check-input row-check">
                                </td>
                                <td>{{ $history->id }}</td>
                                <td><span class="badge bg-secondary">{{ $history->sync_type }}</span></td>
                                <td>
                                    @php
                                        $badgeMap = ['Completed' => 'success', 'Failed' => 'danger', 'Running' => 'warning'];
                                        $badge = $badgeMap[$history->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $badge }}">
                                        {{ $history->status }}
                                        @if($history->is_paused) ⏸ @endif
                                    </span>
                                </td>
                                <td style="min-width:120px;">
                                    <div class="progress" id="progress-bar-{{ $history->id }}">
                                        <div class="progress-bar bg-{{ $badge }}"
                                            style="width:{{ $history->progress }}%">
                                            {{ $history->progress }}%
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $history->started_at }}</td>
                                <td>{{ $history->completed_at ?? '--' }}</td>
                                <td>{{ $history->duration ? $history->duration . 's' : '--' }}</td>
                                <td>{{ Str::limit($history->message, 40) }}</td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">

                                        {{-- Log Viewer --}}
                                        <button type="button" class="btn btn-info btn-sm"
                                            onclick="viewLog({{ $history->id }})">
                                            📋 Log
                                        </button>

                                        {{-- Pause / Resume --}}
                                        @if($history->status === 'Running' && !$history->is_paused)
                                        <button type="button" class="btn btn-warning btn-sm"
                                            onclick="pauseSync({{ $history->id }})">
                                            ⏸ Pause
                                        </button>
                                        @elseif($history->status === 'Running' && $history->is_paused)
                                        <button type="button" class="btn btn-success btn-sm"
                                            onclick="resumeSync({{ $history->id }})">
                                            ▶ Resume
                                        </button>
                                        @endif

                                        {{-- Cancel --}}
                                        @if($history->status === 'Running')
                                        <button type="button" class="btn btn-dark btn-sm"
                                            onclick="cancelSync({{ $history->id }})">
                                            ✖ Cancel
                                        </button>
                                        @endif

                                        {{-- Retry --}}
                                        @if($history->status === 'Failed')
                                        <form action="{{ route('sync.retry', $history->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                🔄 Retry
                                            </button>
                                        </form>
                                        @endif

                                        {{-- Delete --}}
                                        <form action="{{ route('sync.destroy', $history->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Delete this record?')">
                                                🗑
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">No Sync Records Found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">
                        Showing {{ $histories->firstItem() ?? 0 }} - {{ $histories->lastItem() ?? 0 }}
                        of {{ $histories->total() }} records
                    </small>
                    {{ $histories->links() }}
                </div>

            </div>
        </div>

</div>

{{-- Manual Sync Modal --}}
<div class="modal fade" id="syncModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('sync.start') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">▶ Run Background Sync</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sync Type</label>
                        <select name="sync_type" class="form-select">
                            <option value="General">General</option>
                            <option value="User Sync">User Sync</option>
                            <option value="Product Sync">Product Sync</option>
                            <option value="Order Sync">Order Sync</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date Range Start</label>
                        <input type="date" name="date_range_start" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date Range End</label>
                        <input type="date" name="date_range_end" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Start Sync</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Log Viewer Modal --}}
<div class="modal fade" id="logModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📋 Sync Log Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <pre id="logContent" class="bg-dark text-light p-3 rounded"
                    style="max-height:400px;overflow-y:auto;font-size:13px;">Loading...</pre>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // ── Chart.js Pie Chart ──────────────────────────────────────────────────
    const ctx = document.getElementById('syncChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($chartData['labels']) !!},
            datasets: [{
                data: {!! json_encode($chartData['data']) !!},
                backgroundColor: ['#198754', '#dc3545', '#ffc107'],
                borderWidth: 2
            }]
        },
        options: { plugins: { legend: { position: 'bottom' } }, cutout: '60%' }
    });

    // ── Auto Refresh ────────────────────────────────────────────────────────
    let countdown = 30;
    let refreshPaused = false;
    const countdownEl = document.getElementById('countdown');
    const toggleBtn   = document.getElementById('toggleRefresh');

    const timer = setInterval(() => {
        if (refreshPaused) return;
        countdown--;
        countdownEl.textContent = countdown;
        if (countdown <= 0) location.reload();
    }, 1000);

    toggleBtn.addEventListener('click', () => {
        refreshPaused = !refreshPaused;
        toggleBtn.textContent = refreshPaused ? 'Resume Auto-Refresh' : 'Pause Auto-Refresh';
        toggleBtn.className = refreshPaused ? 'btn btn-outline-success btn-sm' : 'btn btn-outline-secondary btn-sm';
    });

    // ── Real-time Progress Polling ──────────────────────────────────────────
    function pollProgress() {
        document.querySelectorAll('[id^="progress-bar-"]').forEach(el => {
            const id = el.id.replace('progress-bar-', '');
            fetch(`/sync-progress/${id}`)
                .then(r => r.json())
                .then(data => {
                    const bar = el.querySelector('.progress-bar');
                    if (bar) {
                        bar.style.width = data.progress + '%';
                        bar.textContent = data.progress + '%';
                    }
                });
        });
    }
    setInterval(pollProgress, 3000);

    // ── Select All Checkboxes ───────────────────────────────────────────────
    ['selectAll', 'selectAllHeader'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', function () {
            document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
        });
    });

    // ── Bulk Delete ─────────────────────────────────────────────────────────
    function submitBulkDelete() {
        const checked = document.querySelectorAll('.row-check:checked');
        if (checked.length === 0) { alert('Koi record select nathi karyo!'); return; }
        if (!confirm(checked.length + ' records delete karva che?')) return;

        const container = document.getElementById('bulkCheckboxes');
        container.innerHTML = '';
        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = cb.value;
            container.appendChild(input);
        });
        document.getElementById('bulkForm').submit();
    }

    // ── Pause / Resume / Cancel ─────────────────────────────────────────────
    function syncAction(url, msg) {
        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' }
        })
        .then(r => r.json())
        .then(data => { alert(data.message); location.reload(); });
    }

    function pauseSync(id)  { syncAction(`/sync-pause/${id}`,  'Pausing...'); }
    function resumeSync(id) { syncAction(`/sync-resume/${id}`, 'Resuming...'); }
    function cancelSync(id) {
        if (confirm('Cancel this sync?')) syncAction(`/sync-cancel/${id}`, 'Cancelling...');
    }

    // ── Log Viewer ──────────────────────────────────────────────────────────
    function viewLog(id) {
        document.getElementById('logContent').textContent = 'Loading...';
        new bootstrap.Modal(document.getElementById('logModal')).show();
        fetch(`/sync-log/${id}`)
            .then(r => r.json())
            .then(data => {
                document.getElementById('logContent').textContent = data.log_details;
            });
    }
</script>
</body>
</html>
