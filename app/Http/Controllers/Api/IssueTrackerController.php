<?php

// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use App\Models\IssueTracker;
// use App\Models\IssueHistory;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Storage;
// use Symfony\Component\HttpFoundation\Response;

// class IssueTrackerController extends Controller
// {
//     // LIST (ringkas): semua issue milik user / SPV, masing-masing dengan last history
//     // public function index()
//     // {
//     //     $user = Auth::user();
//     //     $tenantId = $user->tenantMappings()->first()?->tenant_id ?? null;

//     //     //anggap SPV jika ada issue where assigned_by == user_id
//     //     $isSpv = IssueTracker::where('assigned_by', $user->id)->exists();

//     //     $query = IssueTracker::query();

//     //     if ($isSpv) {
//     //         $query->where('assigned_by', $user->id);
//     //         if ($tenantId) $query->where('tenant_id', $tenantId);
//     //     } else {
//     //         $query->where('user_id', $user->id);
//     //     }

//     //      \Log::debug('IssueTracker query:', [
//     //     'sql' => $query->toSql(),
//     //     'bindings' => $query->getBindings()
//     // ]);

//     //     // gunakan relasi lastHistory untuk ambil 1 history terakhir per issue
//     //     $issues = $query->with(['lastHistory.changedByUser'])->orderBy('updated_at', 'desc')->get();

//     //     return response()->json($issues, Response::HTTP_OK);
//     // }

//     public function index()
// {
//     $user = Auth::user();

//     // Ambil semua tenant ID yang user ini miliki (mapping)
//     $tenantIds = $user->tenantMappings()->pluck('tenant_id')->toArray();

//     // Cek apakah user ini SPV (punya issue assigned_by)
//     $isSpv = IssueTracker::where('assigned_by', $user->id)->exists();

//     $query = IssueTracker::query();

//     if ($isSpv) {
//         $query->where('assigned_by', $user->id);

//         // Gunakan whereIn untuk semua tenant yang user miliki
//         if (!empty($tenantIds)) {
//             $query->whereIn('tenant_id', $tenantIds);
//         }
//     } else {
//         $query->where('user_id', $user->id);
//     }

//     \Log::debug('IssueTracker query:', [
//         'sql' => $query->toSql(),
//         'bindings' => $query->getBindings()
//     ]);

// //    $issues = $query->with(['lastHistory.changedByUser'])->orderBy('updated_at', 'desc')->get();
//     $issues = $query->with(['tenant', 'lastHistory.changedByUser'])->orderBy('updated_at', 'desc')->get();

//     return response()->json($issues, Response::HTTP_OK);
// }


//     // DETAIL: 1 issue dengan seluruh history
//     public function show($id)
//     {
//         $user = Auth::user();
//        // $issue = IssueTracker::with(['histories.changedByUser', 'histories.assignedToUser'])->findOrFail($id);
//             $issue = IssueTracker::with(['tenant', 'histories.changedByUser', 'histories.assignedToUser'])->findOrFail($id);

//         if (! $this->canView($issue, $user)) {
//             return response()->json(['message' => 'Forbidden'], Response::HTTP_FORBIDDEN);
//         }

//         return response()->json($issue, Response::HTTP_OK);
//     }

//     // USER: mulai kerja -> in_progress
//     public function startWork($id)
//     {
//         $user = Auth::user();
//         $issue = IssueTracker::findOrFail($id);

//         if ($issue->user_id != $user->id) {
//             return response()->json(['message' => 'Only assigned user can start work'], Response::HTTP_FORBIDDEN);
//         }

//         if ($issue->status === 'in_progress') {
//             return response()->json(['message' => 'Issue already in_progress'], Response::HTTP_OK);
//         }

//         $this->updateStatus($issue, 'in_progress', $user->id);

//         return response()->json(['message' => 'Issue status updated to in_progress'], Response::HTTP_OK);
//     }

//     // USER: request close (wajib upload proof(s) + optional reason)
//     public function closeRequest(Request $request, $id)
//     {
//         $request->validate([
//             'reason' => 'nullable|string',
//             'proofs' => 'required|array|min:1',
//             'proofs.*' => 'file|mimes:jpg,jpeg,png,mp4,mov,avi|max:30240'
//         ]);

//         $user = Auth::user();
//         $issue = IssueTracker::findOrFail($id);

//         if ($issue->user_id != $user->id) {
//             return response()->json(['message' => 'Only assigned user can request close'], Response::HTTP_FORBIDDEN);
//         }

//         $paths = $this->storeFiles($request->file('proofs'));

//         $this->updateStatus($issue, 'waiting_approval', $user->id, $request->input('reason'), $paths);

//         return response()->json(['message' => 'Close request submitted'], Response::HTTP_OK);
//     }

//     // USER: upload proof tambahan saat in_progress (tidak ubah status)
//     public function addProof(Request $request, $id)
//     {
//         $request->validate([
//             'proofs' => 'required|array|min:1',
//             'proofs.*' => 'file|mimes:jpg,jpeg,png,mp4,mov,avi|max:30240'
//         ]);

//         $user = Auth::user();
//         $issue = IssueTracker::findOrFail($id);

//         if ($issue->user_id != $user->id) {
//             return response()->json(['message' => 'Only assigned user can add proof'], Response::HTTP_FORBIDDEN);
//         }

//         if ($issue->status !== 'in_progress') {
//             return response()->json(['message' => 'Can only add proof when issue is in_progress'], Response::HTTP_BAD_REQUEST);
//         }

//         $paths = $this->storeFiles($request->file('proofs'));

//         IssueHistory::create([
//             'issue_tracker_id' => $issue->id,
//             'status' => $issue->status,
//             'changed_by' => $user->id,
//             'assigned_to' => $issue->assigned_by,
//             'note' => null,
//             'attachment_paths' => $paths
//         ]);

//         // update updated_at supaya list terurut sesuai aktivitas
//         $issue->touch();

//         return response()->json(['message' => 'Proof(s) uploaded'], Response::HTTP_OK);
//     }

//     // SPV: approve -> closed
//     public function approve(Request $request, $id)
//     {
//         $request->validate(['note' => 'nullable|string']);

//         $user = Auth::user();
//         $issue = IssueTracker::findOrFail($id);

//         if ($issue->assigned_by != $user->id) {
//             return response()->json(['message' => 'Only SPV can approve'], Response::HTTP_FORBIDDEN);
//         }

//         $this->updateStatus($issue, 'closed', $user->id, $request->input('note'));

//         return response()->json(['message' => 'Issue approved and closed'], Response::HTTP_OK);
//     }

//     // SPV: reject -> in_progress (alasan required)
//     public function reject(Request $request, $id)
//     {
//         $request->validate(['note' => 'required|string']);

//         $user = Auth::user();
//         $issue = IssueTracker::findOrFail($id);

//         if ($issue->assigned_by != $user->id) {
//             return response()->json(['message' => 'Only SPV can reject'], Response::HTTP_FORBIDDEN);
//         }

//         $this->updateStatus($issue, 'in_progress', $user->id, $request->input('note'));

//         return response()->json(['message' => 'Issue rejected and set to in_progress'], Response::HTTP_OK);
//     }

//     // -------- helper methods --------

//     private function updateStatus(IssueTracker $issue, $status, $changedBy, $note = null, $attachments = [])
//     {
//         $issue->status = $status;
//         $issue->save();

//         IssueHistory::create([
//             'issue_tracker_id' => $issue->id,
//             'status' => $status,
//             'changed_by' => $changedBy,
//             'assigned_to' => $issue->assigned_by,
//             'note' => $note,
//             'attachment_paths' => $attachments
//         ]);
//     }

//     private function storeFiles($files)
//     {
//         $paths = [];
//         foreach ($files as $file) {
//             $paths[] = $file->store('issue_proofs', 'public');
//         }
//         return $paths;
//     }

//     private function canView(IssueTracker $issue, $user)
//     {
//         // assigned user always bisa lihat
//         if ($issue->user_id == $user->id) return true;

//         // SPV (assigned_by) bisa lihat issue yang dia assign dan tenant sama
//         if ($issue->assigned_by == $user->id && $issue->tenant_id == $user->tenantMappings()->first()?->tenant_id) {
//             return true;
//         }

//         return false;
//     }
// }

//edit spv 
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

//    $issues = $query->with(['lastHistory.changedByUser'])->orderBy('updated_at', 'desc')->get();
    $issues = $query->with(['tenant', 'lastHistory.changedByUser'])->orderBy('updated_at', 'desc')->get();

    return response()->json($issues, Response::HTTP_OK);
}


    // DETAIL: 1 issue dengan seluruh history
    public function show($id)
    {
        $user = Auth::user();
       // $issue = IssueTracker::with(['histories.changedByUser', 'histories.assignedToUser'])->findOrFail($id);
            $issue = IssueTracker::with(['tenant', 'histories.changedByUser', 'histories.assignedToUser'])->findOrFail($id);

        if (! $this->canView($issue, $user)) {
            return response()->json(['message' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        return response()->json($issue, Response::HTTP_OK);
    }

    // // USER: mulai kerja -> in_progress
    // public function startWork($id)
    // {
    //     $user = Auth::user();
    //     $issue = IssueTracker::findOrFail($id);

    //     if ($issue->user_id != $user->id) {
    //         return response()->json(['message' => 'Only assigned user can start work'], Response::HTTP_FORBIDDEN);
    //     }

    //     if ($issue->status === 'in_progress') {
    //         return response()->json(['message' => 'Issue already in_progress'], Response::HTTP_OK);
    //     }

    //     $this->updateStatus($issue, 'in_progress', $user->id);

    //     return response()->json(['message' => 'Issue status updated to in_progress'], Response::HTTP_OK);
    // }

    // // USER: request close (wajib upload proof(s) + optional reason)
    // public function closeRequest(Request $request, $id)
    // {
    //     $request->validate([
    //         'reason' => 'nullable|string',
    //         'proofs' => 'required|array|min:1',
    //         'proofs.*' => 'file|mimes:jpg,jpeg,png,mp4,mov,avi|max:30240'
    //     ]);

    //     $user = Auth::user();
    //     $issue = IssueTracker::findOrFail($id);

    //     if ($issue->user_id != $user->id) {
    //         return response()->json(['message' => 'Only assigned user can request close'], Response::HTTP_FORBIDDEN);
    //     }

    //     $paths = $this->storeFiles($request->file('proofs'));

    //     $this->updateStatus($issue, 'waiting_approval', $user->id, $request->input('reason'), $paths);

    //     return response()->json(['message' => 'Close request submitted'], Response::HTTP_OK);
    // }

    // // USER: upload proof tambahan saat in_progress (tidak ubah status)
    // public function addProof(Request $request, $id)
    // {
    //     $request->validate([
    //         'proofs' => 'required|array|min:1',
    //         'proofs.*' => 'file|mimes:jpg,jpeg,png,mp4,mov,avi|max:30240'
    //     ]);

    //     $user = Auth::user();
    //     $issue = IssueTracker::findOrFail($id);

    //     if ($issue->user_id != $user->id) {
    //         return response()->json(['message' => 'Only assigned user can add proof'], Response::HTTP_FORBIDDEN);
    //     }

    //     if ($issue->status !== 'in_progress') {
    //         return response()->json(['message' => 'Can only add proof when issue is in_progress'], Response::HTTP_BAD_REQUEST);
    //     }

    //     $paths = $this->storeFiles($request->file('proofs'));

    //     IssueHistory::create([
    //         'issue_tracker_id' => $issue->id,
    //         'status' => $issue->status,
    //         'changed_by' => $user->id,
    //         'assigned_to' => $issue->assigned_by,
    //         'note' => null,
    //         'attachment_paths' => $paths
    //     ]);

    //     // update updated_at supaya list terurut sesuai aktivitas
    //     $issue->touch();

    //     return response()->json(['message' => 'Proof(s) uploaded'], Response::HTTP_OK);
    // }

    // USER/SPV: mulai kerja -> in_progress
public function startWork($id)
{
    $user = Auth::user();
    $issue = IssueTracker::findOrFail($id);

    if ($issue->user_id != $user->id && $issue->assigned_by != $user->id) {
        return response()->json(['message' => 'Only assigned user or SPV can start work'], Response::HTTP_FORBIDDEN);
    }

    if ($issue->status === 'in_progress') {
        return response()->json(['message' => 'Issue already in_progress'], Response::HTTP_OK);
    }

    $this->updateStatus($issue, 'in_progress', $user->id);

    return response()->json(['message' => 'Issue status updated to in_progress'], Response::HTTP_OK);
}

// USER/SPV: request close (wajib upload proof(s) + optional reason)
public function closeRequest(Request $request, $id)
{
    $request->validate([
        'reason' => 'nullable|string',
        'proofs' => 'required|array|min:1',
        'proofs.*' => 'file|mimes:jpg,jpeg,png,mp4,mov,avi|max:30240'
    ]);

    $user = Auth::user();
    $issue = IssueTracker::findOrFail($id);

    if ($issue->user_id != $user->id && $issue->assigned_by != $user->id) {
        return response()->json(['message' => 'Only assigned user or SPV can request close'], Response::HTTP_FORBIDDEN);
    }

    $paths = $this->storeFiles($request->file('proofs'));

    $this->updateStatus($issue, 'waiting_approval', $user->id, $request->input('reason'), $paths);

    return response()->json(['message' => 'Close request submitted'], Response::HTTP_OK);
}

// USER/SPV: upload proof tambahan saat in_progress (tidak ubah status)
public function addProof(Request $request, $id)
{
    $request->validate([
        'proofs' => 'required|array|min:1',
        'proofs.*' => 'file|mimes:jpg,jpeg,png,mp4,mov,avi|max:30240'
    ]);

    $user = Auth::user();
    $issue = IssueTracker::findOrFail($id);

    if ($issue->user_id != $user->id && $issue->assigned_by != $user->id) {
        return response()->json(['message' => 'Only assigned user or SPV can add proof'], Response::HTTP_FORBIDDEN);
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

//EDIT SPV END
// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use App\Models\IssueTracker;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Log;

// class IssueTrackerController extends Controller
// {
//     public function index(Request $request)
// {
//     $user = Auth::user();

//     if ($user->roles->contains('name', 'spv')) {
//         // Ambil tenant_id dari tenant_mappings
//         $tenantIds = \App\Models\TenantMapping::where('user_id', $user->id)
//             ->pluck('tenant_id');

//         $issues = IssueTracker::with('tenant')
//             ->whereIn('tenant_id', $tenantIds)
//             ->get();
//     } else {
//         // Kalau outlet/user biasa → issue dia sendiri
//         $issues = IssueTracker::with('tenant')
//             ->where('user_id', $user->id)
//             ->get();
//     }

//     return response()->json($issues);
// }


//     public function show($id)
//     {
//         $issue = IssueTracker::with(['tenant', 'proofs', 'user'])->findOrFail($id);
//         return response()->json($issue);
//     }

//     public function startWork($id)
//     {
//         $issue = IssueTracker::findOrFail($id);

//         if ($issue->status !== 'open') {
//             return response()->json(['message' => 'Issue sudah diproses'], 400);
//         }

//         $issue->status = 'in_progress';
//         $issue->started_by = Auth::id();
//         $issue->started_at = now();
//         $issue->save();

//         Log::info('Issue started', ['issue_id' => $issue->id, 'started_by' => Auth::id()]);

//         return response()->json(['message' => 'Issue dimulai', 'issue' => $issue]);
//     }

//     public function addProof(Request $request, $id)
//     {
//         $issue = IssueTracker::findOrFail($id);

//         if (!$request->hasFile('proof')) {
//             return response()->json(['message' => 'Proof file wajib diupload'], 400);
//         }

//         $path = $request->file('proof')->store('proofs', 'public');

//         $issue->proofs()->create([
//             'file_path' => $path,
//             'uploaded_by' => Auth::id(),
//         ]);

//         Log::info('Proof ditambahkan', ['issue_id' => $issue->id, 'by' => Auth::id()]);

//         return response()->json(['message' => 'Proof ditambahkan']);
//     }

//     public function requestClose(Request $request, $id)
//     {
//         $issue = IssueTracker::findOrFail($id);

//         if ($issue->status !== 'in_progress') {
//             return response()->json(['message' => 'Issue belum dalam progress'], 400);
//         }

//         $issue->status = 'waiting_approval';
//         $issue->closed_by = Auth::id();
//         $issue->closed_at = now();
//         $issue->save();

//         Log::info('Request close issue', ['issue_id' => $issue->id, 'by' => Auth::id()]);

//         return response()->json(['message' => 'Request close dikirim', 'issue' => $issue]);
//     }

//     public function approve(Request $request, $id)
//     {
//         $issue = IssueTracker::findOrFail($id);

//         if ($issue->status !== 'waiting_approval') {
//             return response()->json(['message' => 'Issue tidak menunggu approval'], 400);
//         }

//         $issue->status = 'closed';
//         $issue->approved_by = Auth::id();
//         $issue->approved_at = now();
//         $issue->save();

//         Log::info('Issue approved', ['issue_id' => $issue->id, 'approved_by' => Auth::id()]);

//         return response()->json(['message' => 'Issue disetujui', 'issue' => $issue]);
//     }

//     public function reject(Request $request, $id)
//     {
//         $issue = IssueTracker::findOrFail($id);

//         if ($issue->status !== 'waiting_approval') {
//             return response()->json(['message' => 'Issue tidak menunggu approval'], 400);
//         }

//         $issue->status = 'rejected';
//         $issue->answer_reason = $request->reason ?? null;
//         $issue->approved_by = Auth::id();
//         $issue->approved_at = now();
//         $issue->save();

//         Log::info('Issue rejected', ['issue_id' => $issue->id, 'rejected_by' => Auth::id()]);

//         return response()->json(['message' => 'Issue ditolak', 'issue' => $issue]);
//     }
// }


//end edit spv


// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use App\Models\IssueTracker;
// use App\Models\IssueHistory;
// use Illuminate\Support\Facades\Auth;
// use Symfony\Component\HttpFoundation\Response;

// class IssueTrackerController extends Controller
// {
//     public function __construct()
//     {
//         $this->middleware('auth:sanctum');
//     }

//     // LIST: semua issue milik user/SPV, masing-masing dengan last history
//     public function index()
//     {
//         $user = Auth::user();
//         $tenantIds = $user->tenantMappings()->pluck('tenant_id')->toArray();
//         $isSpv = IssueTracker::where('assigned_by', $user->id)->exists();

//         $query = IssueTracker::query();

//         if ($isSpv) {
//             $query->where('assigned_by', $user->id);
//             if (!empty($tenantIds)) {
//                 $query->whereIn('tenant_id', $tenantIds);
//             }
//         } else {
//             $query->where('user_id', $user->id);
//         }

//         $issues = $query
//             ->with(['tenant', 'lastHistory.changedByUser'])
//             ->orderBy('updated_at', 'desc')
//             ->get();

//         return response()->json($issues, Response::HTTP_OK);
//     }

//     // DETAIL: 1 issue dengan seluruh history
//     public function show($id)
//     {
//         $issue = IssueTracker::with([
//             'tenant',
//             'histories.changedByUser',
//             'histories.assignedToUser'
//         ])->findOrFail($id);

//         $this->authorize('view', $issue);

//         return response()->json($issue, Response::HTTP_OK);
//     }

//     // USER: mulai kerja
//     public function startWork($id)
//     {
//         $user = Auth::user();
//         $issue = IssueTracker::findOrFail($id);

//         if ($issue->user_id !== $user->id) {
//             return response()->json(['message' => 'Forbidden'], Response::HTTP_FORBIDDEN);
//         }

//         if ($issue->status === 'in_progress') {
//             return response()->json(['message' => 'Already in progress'], Response::HTTP_OK);
//         }

//         $this->updateStatus($issue, 'in_progress', $user->id);

//         return response()->json(['message' => 'Status updated to in_progress'], Response::HTTP_OK);
//     }

//     // USER: request close
//     public function closeRequest(Request $request, $id)
//     {
//         $request->validate([
//             'reason' => 'nullable|string',
//             'proofs' => 'required|array|min:1',
//             'proofs.*' => 'file|mimes:jpg,jpeg,png,mp4,mov,avi|max:30240'
//         ]);

//         $user = Auth::user();
//         $issue = IssueTracker::findOrFail($id);

//         if ($issue->user_id !== $user->id) {
//             return response()->json(['message' => 'Forbidden'], Response::HTTP_FORBIDDEN);
//         }

//         $paths = $this->storeFiles($request->file('proofs'));
//         $this->updateStatus($issue, 'waiting_approval', $user->id, $request->input('reason'), $paths);

//         return response()->json(['message' => 'Close request submitted'], Response::HTTP_OK);
//     }

//     // USER: tambah proof
//     public function addProof(Request $request, $id)
//     {
//         $request->validate([
//             'proofs' => 'required|array|min:1',
//             'proofs.*' => 'file|mimes:jpg,jpeg,png,mp4,mov,avi|max:30240'
//         ]);

//         $user = Auth::user();
//         $issue = IssueTracker::findOrFail($id);

//         if ($issue->user_id !== $user->id) {
//             return response()->json(['message' => 'Forbidden'], Response::HTTP_FORBIDDEN);
//         }

//         if ($issue->status !== 'in_progress') {
//             return response()->json(['message' => 'Not in progress'], Response::HTTP_BAD_REQUEST);
//         }

//         $paths = $this->storeFiles($request->file('proofs'));

//         IssueHistory::create([
//             'issue_tracker_id' => $issue->id,
//             'status' => $issue->status,
//             'changed_by' => $user->id,
//             'assigned_to' => $issue->assigned_by,
//             'note' => null,
//             'attachment_paths' => $paths
//         ]);

//         $issue->touch();

//         return response()->json(['message' => 'Proof uploaded'], Response::HTTP_OK);
//     }

//     // SPV: approve
//     public function approve(Request $request, $id)
//     {
//         $request->validate(['note' => 'nullable|string']);

//         $issue = IssueTracker::findOrFail($id);
//         $this->authorize('update', $issue);

//         $this->updateStatus($issue, 'closed', Auth::id(), $request->input('note'));

//         return response()->json(['message' => 'Issue closed'], Response::HTTP_OK);
//     }

//     // SPV: reject
//     public function reject(Request $request, $id)
//     {
//         $request->validate(['note' => 'required|string']);

//         $issue = IssueTracker::findOrFail($id);
//         $this->authorize('update', $issue);

//         $this->updateStatus($issue, 'in_progress', Auth::id(), $request->input('note'));

//         return response()->json(['message' => 'Issue rejected'], Response::HTTP_OK);
//     }

//     // -------- Helpers --------
//     private function updateStatus(IssueTracker $issue, $status, $changedBy, $note = null, $attachments = [])
//     {
//         $issue->status = $status;
//         $issue->save();

//         IssueHistory::create([
//             'issue_tracker_id' => $issue->id,
//             'status' => $status,
//             'changed_by' => $changedBy,
//             'assigned_to' => $issue->assigned_by,
//             'note' => $note,
//             'attachment_paths' => $attachments
//         ]);
//     }

//     private function storeFiles($files)
//     {
//         $paths = [];
//         foreach ($files as $file) {
//             $paths[] = $file->store('issue_proofs', 'public');
//         }
//         return $paths;
//     }
// }





// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use App\Models\IssueTracker;
// use App\Models\IssueHistory;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Http\Response;
// use Illuminate\Support\Facades\Storage;

// class IssueTrackerController extends Controller
// {
//     /**
//      * List issue sesuai role.
//      */
//     public function index(Request $request)
//     {
//         $user = Auth::user();

//         // SPV -> lihat issue sesuai outlet yang dia assign
//         if ($user->hasRole('spv')) {
//             $issues = IssueTracker::where('assigned_by', $user->id)->latest()->get();
//         }
//         // Outlet -> lihat issue yang assigned ke dia
//         else {
//             $issues = IssueTracker::where('user_id', $user->id)->latest()->get();
//         }

//         return response()->json($issues, Response::HTTP_OK);
//     }

//     /**
//      * Start Work (open -> in_progress).
//      */
//     public function startWork($id)
//     {
//         $user = Auth::user();
//         $issue = IssueTracker::findOrFail($id);

//         if (!$this->canActOnIssue($issue, $user)) {
//             return response()->json(['message' => 'Not authorized to start work'], Response::HTTP_FORBIDDEN);
//         }

//         if ($issue->status === 'in_progress') {
//             return response()->json(['message' => 'Issue already in_progress'], Response::HTTP_OK);
//         }

//         $this->updateStatus($issue, 'in_progress', $user->id);

//         return response()->json(['message' => 'Issue status updated to in_progress'], Response::HTTP_OK);
//     }

//     /**
//      * Add Proof (hanya ketika in_progress).
//      */
//     public function addProof(Request $request, $id)
//     {
//         $request->validate([
//             'proofs' => 'required|array|min:1',
//             'proofs.*' => 'file|mimes:jpg,jpeg,png,mp4,mov,avi|max:30240'
//         ]);

//         $user = Auth::user();
//         $issue = IssueTracker::findOrFail($id);

//         if (!$this->canActOnIssue($issue, $user)) {
//             return response()->json(['message' => 'Not authorized to add proof'], Response::HTTP_FORBIDDEN);
//         }

//         if ($issue->status !== 'in_progress') {
//             return response()->json(['message' => 'Can only add proof when issue is in_progress'], Response::HTTP_BAD_REQUEST);
//         }

//         $paths = $this->storeFiles($request->file('proofs'));

//         IssueHistory::create([
//             'issue_tracker_id' => $issue->id,
//             'status' => $issue->status,
//             'changed_by' => $user->id,
//             'assigned_to' => $issue->assigned_by,
//             'note' => null,
//             'attachment_paths' => $paths
//         ]);

//         $issue->touch();

//         return response()->json(['message' => 'Proof(s) uploaded'], Response::HTTP_OK);
//     }

//     /**
//      * Request Close (in_progress -> waiting_approval).
//      */
//     public function closeRequest(Request $request, $id)
//     {
//         $request->validate([
//             'reason' => 'nullable|string',
//             'proofs' => 'required|array|min:1',
//             'proofs.*' => 'file|mimes:jpg,jpeg,png,mp4,mov,avi|max:30240'
//         ]);

//         $user = Auth::user();
//         $issue = IssueTracker::findOrFail($id);

//         if (!$this->canActOnIssue($issue, $user)) {
//             return response()->json(['message' => 'Not authorized to request close'], Response::HTTP_FORBIDDEN);
//         }

//         $paths = $this->storeFiles($request->file('proofs'));

//         $this->updateStatus($issue, 'waiting_approval', $user->id, $request->input('reason'), $paths);

//         return response()->json(['message' => 'Close request submitted'], Response::HTTP_OK);
//     }

//     /**
//      * Approve (waiting_approval -> closed).
//      */
//     public function approve($id)
//     {
//         $user = Auth::user();
//         $issue = IssueTracker::findOrFail($id);

//         // hanya SPV yang boleh approve
//         if ($issue->assigned_by != $user->id) {
//             return response()->json(['message' => 'Only SPV can approve'], Response::HTTP_FORBIDDEN);
//         }

//         if ($issue->status !== 'waiting_approval') {
//             return response()->json(['message' => 'Issue must be in waiting_approval to approve'], Response::HTTP_BAD_REQUEST);
//         }

//         $this->updateStatus($issue, 'closed', $user->id);

//         return response()->json(['message' => 'Issue approved and closed'], Response::HTTP_OK);
//     }

//     /**
//      * Reject (waiting_approval -> in_progress).
//      */
//     public function reject(Request $request, $id)
//     {
//         $request->validate([
//             'reason' => 'nullable|string'
//         ]);

//         $user = Auth::user();
//         $issue = IssueTracker::findOrFail($id);

//         if ($issue->assigned_by != $user->id) {
//             return response()->json(['message' => 'Only SPV can reject'], Response::HTTP_FORBIDDEN);
//         }

//         if ($issue->status !== 'waiting_approval') {
//             return response()->json(['message' => 'Issue must be in waiting_approval to reject'], Response::HTTP_BAD_REQUEST);
//         }

//         $this->updateStatus($issue, 'in_progress', $user->id, $request->input('reason'));

//         return response()->json(['message' => 'Issue rejected and moved back to in_progress'], Response::HTTP_OK);
//     }

//     /**
//      * Cek apakah user boleh bertindak pada issue.
//      */
//     private function canActOnIssue(IssueTracker $issue, $user): bool
//     {
//         return $issue->user_id == $user->id || $issue->assigned_by == $user->id;
//     }

//     /**
//      * Simpan file upload.
//      */
//     private function storeFiles($files)
//     {
//         $paths = [];
//         foreach ($files as $file) {
//             $paths[] = $file->store('proofs', 'public');
//         }
//         return $paths;
//     }

//     /**
//      * Update status issue + catat history.
//      */
//     private function updateStatus(IssueTracker $issue, $status, $changedBy, $note = null, $attachments = null)
//     {
//         $issue->status = $status;
//         $issue->save();

//         IssueHistory::create([
//             'issue_tracker_id' => $issue->id,
//             'status' => $status,
//             'changed_by' => $changedBy,
//             'assigned_to' => $issue->assigned_by,
//             'note' => $note,
//             'attachment_paths' => $attachments
//         ]);
//     }
// }
