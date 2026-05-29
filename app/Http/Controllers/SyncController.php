<?php

namespace App\Http\Controllers;

use App\Jobs\BackgroundSyncJob;
use App\Models\SyncHistory;

class SyncController extends Controller
{
    /**
     * Start Background Sync
     */
    public function startSync()
    {
        BackgroundSyncJob::dispatch();

        return response()->json([

            'success' => true,

            'message' => 'Background Sync Started Successfully',
        ]);
    }

    /**
     * Sync Dashboard
     */
    public function dashboard()
    {
        $histories = SyncHistory::orderBy('id', 'asc')->paginate(10);

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
}