<?php

namespace App\Http\Controllers;

use App\Jobs\BackgroundSyncJob;
use App\Models\SyncHistory;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    /**
     * Start Background Sync
     */
    public function startSync()
    {
        BackgroundSyncJob::dispatch();

        return redirect('/sync-dashboard')
            ->with('success', 'Background Sync Started Successfully');
    }

    /**
     * Sync Dashboard
     */
    public function dashboard(Request $request)
    {
        $query = SyncHistory::query();

        // Search
        if ($request->search) {
            $query->where('status', 'like', '%' . $request->search . '%')
                  ->orWhere('message', 'like', '%' . $request->search . '%');
        }

        // Status Filter
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $histories = $query->oldest()->paginate(5);

        $totalSyncs = SyncHistory::count();
        $completedSyncs = SyncHistory::where('status', 'Completed')->count();
        $failedSyncs = SyncHistory::where('status', 'Failed')->count();
        $runningSyncs = SyncHistory::where('status', 'Running')->count();

        return view('sync-dashboard', compact(
            'histories',
            'totalSyncs',
            'completedSyncs',
            'failedSyncs',
            'runningSyncs'
        ));
    }

    /**
     * Delete Sync History
     */
    public function destroy($id)
    {
        SyncHistory::findOrFail($id)->delete();

        return redirect()
            ->back()
            ->with('success', 'Sync Record Deleted Successfully');
    }
}