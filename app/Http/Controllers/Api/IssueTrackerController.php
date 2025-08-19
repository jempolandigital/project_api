<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IssueTracker;
use App\Models\IssueHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class IssueTrackerController extends Controller
{
    // LIST (ringkas): semua issue milik user / SPV, masing-masing dengan last history
    // public function index()
    // {
    //     $user = Auth::user();
    //     $tenantId = $user->tenantMappings()->first()?->tenant_id ?? null;

    //     //anggap SPV jika ada issue where assigned_by == user_id
    //     $isSpv = IssueTracker::where('assigned_by', $user->id)->exists();

    //     $query = IssueTracker::query();

    //     if ($isSpv) {
    //         $query->where('assigned_by', $user->id);
    //         if ($tenantId) $query->where('tenant_id', $tenantId);
    //     } else {
    //         $query->where('user_id', $user->id);
    //     }

    //      \Log::debug('IssueTracker query:', [
    //     'sql' => $query->toSql(),
    //     'bindings' => $query->getBindings()
    // ]);

    //     // gunakan relasi lastHistory untuk ambil 1 history terakhir per issue
    //     $issues = $query->with(['lastHistory.changedByUser'])->orderBy('updated_at', 'desc')->get();

    //     return response()->json($issues, Response::HTTP_OK);
    // }

    public function index()
{
    $user = Auth::user();

    // Ambil semua tenant ID yang user ini miliki (mapping)
    $tenantIds = $user->tenantMappings()->pluck('tenant_id')->toArray();

    // Cek apakah user ini SPV (punya issue assigned_by)
    $isSpv = IssueTracker::where('assigned_by', $user->id)->exists();

    $query = IssueTracker::query();

    if ($isSpv) {
        $query->where('assigned_by', $user->id);

        // Gunakan whereIn untuk semua tenant yang user miliki
        if (!empty($tenantIds)) {
            $query->whereIn('tenant_id', $tenantIds);
        }
    } else {
        $query->where('user_id', $user->id);
    }

    \Log::debug('IssueTracker query:', [
        'sql' => $query->toSql(),
        'bindings' => $query->getBindings()
    ]);

    $issues = $query->with(['lastHistory.changedByUser'])->orderBy('updated_at', 'desc')->get();

    return response()->json($issues, Response::HTTP_OK);
}


    // DETAIL: 1 issue dengan seluruh history
    public function show($id)
    {
        $user = Auth::user();
        $issue = IssueTracker::with(['histories.changedByUser', 'histories.assignedToUser'])->findOrFail($id);

        if (! $this->canView($issue, $user)) {
            return response()->json(['message' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        return response()->json($issue, Response::HTTP_OK);
    }

    // USER: mulai kerja -> in_progress
    public function startWork($id)
    {
        $user = Auth::user();
        $issue = IssueTracker::findOrFail($id);

        if ($issue->user_id != $user->id) {
            return response()->json(['message' => 'Only assigned user can start work'], Response::HTTP_FORBIDDEN);
        }

        if ($issue->status === 'in_progress') {
            return response()->json(['message' => 'Issue already in_progress'], Response::HTTP_OK);
        }

        $this->updateStatus($issue, 'in_progress', $user->id);

        return response()->json(['message' => 'Issue status updated to in_progress'], Response::HTTP_OK);
    }

    // USER: request close (wajib upload proof(s) + optional reason)
    public function closeRequest(Request $request, $id)
    {
        $request->validate([
            'reason' => 'nullable|string',
            'proofs' => 'required|array|min:1',
            'proofs.*' => 'file|mimes:jpg,jpeg,png,mp4,mov,avi|max:30240'
        ]);

        $user = Auth::user();
        $issue = IssueTracker::findOrFail($id);

        if ($issue->user_id != $user->id) {
            return response()->json(['message' => 'Only assigned user can request close'], Response::HTTP_FORBIDDEN);
        }

        $paths = $this->storeFiles($request->file('proofs'));

        $this->updateStatus($issue, 'waiting_approval', $user->id, $request->input('reason'), $paths);

        return response()->json(['message' => 'Close request submitted'], Response::HTTP_OK);
    }

    // USER: upload proof tambahan saat in_progress (tidak ubah status)
    public function addProof(Request $request, $id)
    {
        $request->validate([
            'proofs' => 'required|array|min:1',
            'proofs.*' => 'file|mimes:jpg,jpeg,png,mp4,mov,avi|max:30240'
        ]);

        $user = Auth::user();
        $issue = IssueTracker::findOrFail($id);

        if ($issue->user_id != $user->id) {
            return response()->json(['message' => 'Only assigned user can add proof'], Response::HTTP_FORBIDDEN);
        }

        if ($issue->status !== 'in_progress') {
            return response()->json(['message' => 'Can only add proof when issue is in_progress'], Response::HTTP_BAD_REQUEST);
        }

        $paths = $this->storeFiles($request->file('proofs'));

        IssueHistory::create([
            'issue_tracker_id' => $issue->id,
            'status' => $issue->status,
            'changed_by' => $user->id,
            'assigned_to' => $issue->assigned_by,
            'note' => null,
            'attachment_paths' => $paths
        ]);

        // update updated_at supaya list terurut sesuai aktivitas
        $issue->touch();

        return response()->json(['message' => 'Proof(s) uploaded'], Response::HTTP_OK);
    }

    // SPV: approve -> closed
    public function approve(Request $request, $id)
    {
        $request->validate(['note' => 'nullable|string']);

        $user = Auth::user();
        $issue = IssueTracker::findOrFail($id);

        if ($issue->assigned_by != $user->id) {
            return response()->json(['message' => 'Only SPV can approve'], Response::HTTP_FORBIDDEN);
        }

        $this->updateStatus($issue, 'closed', $user->id, $request->input('note'));

        return response()->json(['message' => 'Issue approved and closed'], Response::HTTP_OK);
    }

    // SPV: reject -> in_progress (alasan required)
    public function reject(Request $request, $id)
    {
        $request->validate(['note' => 'required|string']);

        $user = Auth::user();
        $issue = IssueTracker::findOrFail($id);

        if ($issue->assigned_by != $user->id) {
            return response()->json(['message' => 'Only SPV can reject'], Response::HTTP_FORBIDDEN);
        }

        $this->updateStatus($issue, 'in_progress', $user->id, $request->input('note'));

        return response()->json(['message' => 'Issue rejected and set to in_progress'], Response::HTTP_OK);
    }

    // -------- helper methods --------

    private function updateStatus(IssueTracker $issue, $status, $changedBy, $note = null, $attachments = [])
    {
        $issue->status = $status;
        $issue->save();

        IssueHistory::create([
            'issue_tracker_id' => $issue->id,
            'status' => $status,
            'changed_by' => $changedBy,
            'assigned_to' => $issue->assigned_by,
            'note' => $note,
            'attachment_paths' => $attachments
        ]);
    }

    private function storeFiles($files)
    {
        $paths = [];
        foreach ($files as $file) {
            $paths[] = $file->store('issue_proofs', 'public');
        }
        return $paths;
    }

    private function canView(IssueTracker $issue, $user)
    {
        // assigned user always bisa lihat
        if ($issue->user_id == $user->id) return true;

        // SPV (assigned_by) bisa lihat issue yang dia assign dan tenant sama
        if ($issue->assigned_by == $user->id && $issue->tenant_id == $user->tenantMappings()->first()?->tenant_id) {
            return true;
        }

        return false;
    }
}
