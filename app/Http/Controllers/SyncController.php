<?php

namespace App\Http\Controllers;

use App\Jobs\BackgroundSyncJob;
use App\Models\SyncHistory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SyncController extends Controller
{
    public function startSync(Request $request)
    {
        $syncType       = $request->input('sync_type', 'General');
        $dateRangeStart = $request->input('date_range_start');
        $dateRangeEnd   = $request->input('date_range_end');

        BackgroundSyncJob::dispatch($syncType, $dateRangeStart, $dateRangeEnd);

        return redirect('/sync-dashboard')->with('success', 'Background Sync Started Successfully');
    }

    public function dashboard(Request $request)
    {
        $query = SyncHistory::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('status', 'like', '%' . $request->search . '%')
                  ->orWhere('message', 'like', '%' . $request->search . '%')
                  ->orWhere('sync_type', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->date_from) {
            $query->whereDate('started_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('started_at', '<=', $request->date_to);
        }

        $histories     = $query->latest()->paginate(10)->withQueryString();
        $totalSyncs    = SyncHistory::count();
        $completedSyncs = SyncHistory::where('status', 'Completed')->count();
        $failedSyncs   = SyncHistory::where('status', 'Failed')->count();
        $runningSyncs  = SyncHistory::where('status', 'Running')->count();

        // Chart data
        $chartData = [
            'labels' => ['Completed', 'Failed', 'Running'],
            'data'   => [$completedSyncs, $failedSyncs, $runningSyncs],
        ];

        return view('sync-dashboard', compact(
            'histories', 'totalSyncs', 'completedSyncs',
            'failedSyncs', 'runningSyncs', 'chartData'
        ));
    }

    public function destroy($id)
    {
        SyncHistory::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Record Deleted Successfully');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!empty($ids)) {
            SyncHistory::whereIn('id', $ids)->delete();
        }
        return redirect()->back()->with('success', count($ids) . ' Records Deleted Successfully');
    }

    public function pause($id)
    {
        SyncHistory::findOrFail($id)->update(['is_paused' => true, 'status' => 'Running']);
        return response()->json(['success' => true, 'message' => 'Sync Paused']);
    }

    public function resume($id)
    {
        SyncHistory::findOrFail($id)->update(['is_paused' => false]);
        return response()->json(['success' => true, 'message' => 'Sync Resumed']);
    }

    public function cancel($id)
    {
        SyncHistory::findOrFail($id)->update(['is_cancelled' => true]);
        return response()->json(['success' => true, 'message' => 'Sync Cancelled']);
    }

    public function retry($id)
    {
        $history = SyncHistory::findOrFail($id);
        BackgroundSyncJob::dispatch(
            $history->sync_type,
            $history->date_range_start,
            $history->date_range_end,
            $history->id
        );
        return redirect()->back()->with('success', 'Sync Retry Started');
    }

    public function progress($id)
    {
        $history = SyncHistory::findOrFail($id);
        return response()->json([
            'progress'     => $history->progress,
            'status'       => $history->status,
            'is_paused'    => $history->is_paused,
            'is_cancelled' => $history->is_cancelled,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $query = SyncHistory::query();

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->date_from) {
            $query->whereDate('started_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('started_at', '<=', $request->date_to);
        }

        $records = $query->latest()->get();

        return response()->streamDownload(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Status', 'Sync Type', 'Started At', 'Completed At', 'Duration (sec)', 'Progress %', 'Message']);
            foreach ($records as $r) {
                fputcsv($handle, [
                    $r->id, $r->status, $r->sync_type,
                    $r->started_at, $r->completed_at,
                    $r->duration, $r->progress, $r->message,
                ]);
            }
            fclose($handle);
        }, 'sync-history-' . now()->format('Y-m-d') . '.csv');
    }

    public function showLog($id)
    {
        $history = SyncHistory::findOrFail($id);
        return response()->json(['log_details' => $history->log_details ?: 'No logs available.']);
    }
}
