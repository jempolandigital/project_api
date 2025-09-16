<?php

namespace App\Http\Controllers\Api;

use App\Models\QuestionAnswer;
use App\Models\AnswerProof;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnswerRequest;
use App\Models\AnswerSession;
use Illuminate\Support\Carbon;

class AnswerController extends Controller
{

// public function store(StoreAnswerRequest $request)
// {
// \DB::listen(function ($query) {
//     \Log::info('📦 SQL', [
//         'sql' => $query->sql,
//         'bindings' => $query->bindings,
//     ]);
// });



//     $savedAnswers = [];
//     $sessionId = $request->answers[0]['session_id'] ?? null;


//     try {
//         foreach ($request->answers as $index => $answer) {

            
//             $data = [
//                 'tenant_id'        => $answer['tenant_id'],
//                 'user_id'          => $answer['user_id'],
//                 'question_id'      => $answer['question_id'],
//                 'question_type'    => $answer['question_type'],
//                 'answer_option_id' => $answer['answer_option_id'] ?? null,
//                 'answer_text'      => isset($answer['answer_text']) ? strval($answer['answer_text']) : null,
//                 'started_at'       => $answer['started_at'] ?? null,
//                 'submitted_at'     => now(),
//                 'is_correct'       => $answer['is_correct'] ?? null,
//                 'session_id'       => $answer['session_id'],
//             ];

//             \Log::info("Answer #$index Data:", $data);
//             \Log::info('Data to insert:', $data);
//             \Log::info('Keys in data:', array_keys($data));
//             if (count($data) !== count(array_unique(array_keys($data)))) {
//     \Log::error('Duplicate keys detected in data array', $data);
// }

//         \Log::info('Data before create:', $data);



//            $saved = QuestionAnswer::create($data);
//          //  $saved = \DB::table('question_answers')->insert($data);
//          \Log::info('✅ Inserted ID: '.$saved->id);


//             // Simpan proofs
//             $files = $request->file("answers.$index.proofs", []);
//             foreach ($files as $file) {
//                 $extension = $file->getClientOriginalExtension();
//                 $filename = Str::uuid().'.'.$extension;
//                 $path = $file->storeAs('answers', $filename, 'public');

//                 $fileType = in_array($extension, ['mp4', 'mov', 'avi']) ? 'video' : 'image';

//                 AnswerProof::create([
//                     'question_answer_id' => $saved->id,
//                     'file_path'          => 'storage/'.$path,
//                     'file_type'          => $fileType,
//                 ]);
//             }

//             $savedAnswers[] = $saved->load('proofs');
//         }
//                    //simpan submit at
//         $session = AnswerSession::find($sessionId);
// if ($session) {
//     \Log::info("🧪 Found session: id={$session->id}, before={$session->submitted_at}");
//     if (is_null($session->submitted_at)) {
//         $session->submitted_at = Carbon::now();
//         $session->save();
//         \Log::info("✅ Updated submitted_at: {$session->submitted_at}");
//     } else {
//         \Log::warning("⚠️ Session already submitted at: {$session->submitted_at}");
//     }
// } else {
//     \Log::error("❌ Session not found for ID: {$sessionId}");
// }



//         return response()->json([
//             'message' => 'All answers submitted successfully',
//             'data'    => $savedAnswers,
//         ], 200);



//     } catch (\Throwable $e) {
//         \Log::error('❌ Gagal submit answer:', [
//             'message' => $e->getMessage(),
//             'line'    => $e->getLine(),
//             'file'    => $e->getFile(),
//             'trace'   => $e->getTraceAsString(),
//         ]);

//         return response()->json([
//             'message' => 'Internal Server Error',
//             'error'   => $e->getMessage(),
//         ], 500);
//     }
// }



//}

// public function store(StoreAnswerRequest $request)
// {
//     \DB::listen(function ($query) {
//         \Log::info('📦 SQL', [
//             'sql' => $query->sql,
//             'bindings' => $query->bindings,
//         ]);
//     });

//     $savedAnswers = [];
//     $sessionId = $request->session_id;

//     try {
//         foreach ($request->answers as $index => $answer) {
//             $data = [
//                 'tenant_id'        => $request->tenant_id,
//                 'user_id'          => $request->user_id,
//                 'session_id'       => $sessionId,
//                 'question_id'      => $answer['question_id'],
//                 'question_type'    => $answer['question_type'],
//                 'answer_option_id' => $answer['answer_option_id'] ?? null,
//                 'answer_text'      => $answer['answer_text'] ?? null,
//                 'submitted_at'     => now(),
//             ];

//             $saved = QuestionAnswer::create($data);

//             // Simpan proofs
//             $files = $request->file("answers.$index.proofs", []);
//             foreach ($files as $file) {
//                 $extension = $file->getClientOriginalExtension();
//                 $filename = \Str::uuid().'.'.$extension;
//                 $path = $file->storeAs('answers', $filename, 'public');

//                 $fileType = in_array(strtolower($extension), ['mp4', 'mov', 'avi']) ? 'video' : 'image';

//                 AnswerProof::create([
//                     'question_answer_id' => $saved->id,
//                     'file_path'          => 'storage/'.$path,
//                     'file_type'          => $fileType,
//                 ]);
//             }

//             $savedAnswers[] = $saved->load('proofs');
//         }

//         // Update submitted_at session
//         $session = AnswerSession::find($sessionId);
//         if ($session && is_null($session->submitted_at)) {
//             $session->submitted_at = now();
//             $session->save();
//         }

//         return response()->json([
//             'message' => 'All answers submitted successfully',
//             'data'    => $savedAnswers,
//         ], 200);
//     } catch (\Throwable $e) {
//         \Log::error('❌ Gagal submit answer:', [
//             'message' => $e->getMessage(),
//             'line'    => $e->getLine(),
//             'file'    => $e->getFile(),
//         ]);

//         return response()->json([
//             'message' => 'Internal Server Error',
//             'error'   => $e->getMessage(),
//         ], 500);
//     }
// }

// public function store(StoreAnswerRequest $request)
// {
//     \DB::listen(function ($query) {
//         \Log::info('📦 SQL', [
//             'sql' => $query->sql,
//             'bindings' => $query->bindings,
//         ]);
//     });

//     $savedAnswers = [];
//     $tenantId  = $request->tenant_id;
//     $userId    = $request->user_id;
//     $sessionId = $request->session_id;

//     try {

//         // ✅ Simpan selfie dulu
//         $selfieFile = $request->file('selfie');
//         $selfiePath = null;
//         if ($selfieFile) {
            
//             $ext = strtolower($selfieFile->getClientOriginalExtension());
//             $name = \Str::uuid().'.'.$ext;
//             $stored = $selfieFile->storeAs('selfies', $name, 'public'); // storage/app/public/selfies/...
//             $selfiePath = 'storage/'.$stored;
//         }

//          // ✅ Update session dengan info selfie
//         $session = AnswerSession::find($sessionId);
//         if ($session) {
//             if ($selfiePath) $session->selfie_path = $selfiePath;
//             if ($request->filled('selfie_taken_at')) {
//                 $session->selfie_taken_at = Carbon::parse($request->selfie_taken_at);
//             }
//             if ($request->filled('selfie_lat')) $session->selfie_lat = $request->selfie_lat;
//             if ($request->filled('selfie_lng')) $session->selfie_lng = $request->selfie_lng;
//             $session->save();
//         }

//         // ✅ Simpan jawaban per pertanyaan (kode existingmu)
//         foreach ($request->answers as $index => $answer) {
//             $data = [
//                 'tenant_id'        => $tenantId,
//                 'user_id'          => $userId,
//                 'session_id'       => $sessionId,
//                 'question_id'      => $answer['question_id'],
//                 'question_type'    => $answer['question_type'],
//                 'answer_option_id' => $answer['answer_option_id'] ?? null,
//                 'answer_text'      => $answer['answer_text'] ?? null,
//                 'answer_reason'    => $answer['answer_reason'] ?? null,
//                 'submitted_at'     => now(),
//             ];

//             $saved = QuestionAnswer::create($data);

//             // Simpan proofs (image/video)
//             $files = $request->file("answers.$index.proofs", []);
//             foreach ($files as $file) {
//                 $extension = strtolower($file->getClientOriginalExtension());
//                 $filename  = \Str::uuid().'.'.$extension;
//                 $path      = $file->storeAs('answers', $filename, 'public');

//                 $fileType = in_array($extension, ['mp4', 'mov', 'avi']) ? 'video' : 'image';

//                 AnswerProof::create([
//                     'question_answer_id' => $saved->id,
//                     'file_path'          => 'storage/'.$path,
//                     'file_type'          => $fileType,
//                 ]);
//             }

//             $savedAnswers[] = $saved->load('proofs');
//         }

//         // Update submitted_at session
//         $session = AnswerSession::find($sessionId);
//         if ($session && is_null($session->submitted_at)) {
//             $session->submitted_at = now();
//             $session->save();
//         }

//         return response()->json([
//             'message' => 'All answers submitted successfully',
//             'data'    => $savedAnswers,
//         ], 200);

//     } catch (\Throwable $e) {
//         \Log::error('❌ Gagal submit answer:', [
//             'message' => $e->getMessage(),
//             'line'    => $e->getLine(),
//             'file'    => $e->getFile(),
//         ]);

//         return response()->json([
//             'message' => 'Internal Server Error',
//             'error'   => $e->getMessage(),
//         ], 500);
//     }
// }


//  }


public function store(StoreAnswerRequest $request)
{
    \DB::listen(function ($query) {
        \Log::info('📦 SQL', [
            'sql' => $query->sql,
            'bindings' => $query->bindings,
        ]);
    });

    $savedAnswers = [];
    $tenantId  = $request->tenant_id;
    $userId    = $request->user_id;
    $sessionId = $request->session_id;

    try {
        // --- LOG MASUKAN REQUEST UTAMA ---
        \Log::info('📥 Incoming Answer Request', [
            'tenant_id'   => $tenantId,
            'user_id'     => $userId,
            'session_id'  => $sessionId,
            // file keys (mis: 'selfie', 'answers.0.proofs' jika ada)
            'file_keys'   => array_keys($request->allFiles() ?: []),
            'answers_keys' => is_array($request->answers ?? null) ? array_keys($request->answers) : null,
        ]);

        // =========================
        // ✅ Simpan selfie dulu
        // =========================
        $selfieFile = $request->file('selfie');
        $selfiePath = null;
        if ($selfieFile) {
            // log detail file
            try {
                \Log::info('📸 Selfie diterima', [
                    'original_name' => $selfieFile->getClientOriginalName(),
                    'mime'          => $selfieFile->getMimeType(),
                    'size'          => $selfieFile->getSize(),
                ]);
            } catch (\Throwable $_) {
                // kalau ada environment yang bikin getClientOriginalName error, tetap lanjut
                \Log::warning('⚠️ Tidak dapat ambil metadata selfie (environment issue).');
            }

            // simpan file
            try {
                $ext = strtolower($selfieFile->getClientOriginalExtension());
                $name = \Str::uuid().'.'.$ext;
                $stored = $selfieFile->storeAs('selfies', $name, 'public'); // storage/app/public/selfies/...
                $selfiePath = 'storage/'.$stored;
                \Log::info('✅ Selfie berhasil disimpan ke storage', ['stored' => $stored, 'public_path' => $selfiePath]);
            } catch (\Throwable $ex) {
                \Log::error('❌ Gagal menyimpan file selfie ke storage', [
                    'message' => $ex->getMessage(),
                    'line'    => $ex->getLine(),
                ]);
                // jangan throw ulang, lanjutkan agar proses jawaban tetap berjalan (atau sesuaikan sesuai kebutuhan)
            }
        } else {
            \Log::warning('⚠️ Tidak ada file selfie di request (key: selfie)');
        }

        // =========================
        // ✅ Update session dengan info selfie (jika ada)
        // =========================
        $session = AnswerSession::find($sessionId);
        if ($session) {
            if ($selfiePath) {
                $session->selfie_path = $selfiePath;
            }
            if ($request->filled('selfie_taken_at')) {
                try {
                    $session->selfie_taken_at = Carbon::parse($request->selfie_taken_at);
                } catch (\Throwable $_) {
                    \Log::warning('⚠️ selfie_taken_at tidak dapat di-parse: ' . ($request->selfie_taken_at ?? 'null'));
                }
            }
            if ($request->filled('selfie_lat')) $session->selfie_lat = $request->selfie_lat;
            if ($request->filled('selfie_lng')) $session->selfie_lng = $request->selfie_lng;
            $session->save();

            \Log::info('📝 AnswerSession updated (selfie metadata)', [
                'session_id' => $sessionId,
                'selfie_path' => $session->selfie_path ?? null,
                'selfie_taken_at' => $session->selfie_taken_at ?? null,
                'selfie_lat' => $session->selfie_lat ?? null,
                'selfie_lng' => $session->selfie_lng ?? null,
            ]);
        } else {
            \Log::warning('⚠️ AnswerSession tidak ditemukan saat update selfie', ['session_id' => $sessionId]);
        }

        // =========================
        // ✅ Simpan jawaban per pertanyaan (kode existingmu)
        // =========================
        // Log jumlah answers yang diterima
        $answersFromRequest = $request->answers ?? [];
        \Log::info('🔁 Jumlah answers di request', ['count' => is_array($answersFromRequest) ? count($answersFromRequest) : 0]);

        foreach ($request->answers as $index => $answer) {
            \Log::info("➡️ Proses jawaban index $index", $answer);

            $data = [
                'tenant_id'        => $tenantId,
                'user_id'          => $userId,
                'session_id'       => $sessionId,
                'question_id'      => $answer['question_id'],
                'question_type'    => $answer['question_type'],
                'answer_option_id' => $answer['answer_option_id'] ?? null,
                'answer_text'      => $answer['answer_text'] ?? null,
                'answer_reason'    => $answer['answer_reason'] ?? null,
                'submitted_at'     => now(),
            ];

            $saved = QuestionAnswer::create($data);
            \Log::info('✅ QuestionAnswer created', ['id' => $saved->id, 'question_id' => $saved->question_id]);

            // Simpan proofs (image/video)
            $files = $request->file("answers.$index.proofs", []);
            if (is_array($files) && count($files) > 0) {
                \Log::info("📂 Ditemukan proofs untuk answer index $index", ['count' => count($files)]);
            } else {
                \Log::info("📂 Tidak ada proofs untuk answer index $index");
            }

            foreach ($files as $file) {
                try {
                    $extension = strtolower($file->getClientOriginalExtension());
                    $filename  = \Str::uuid().'.'.$extension;
                    $path      = $file->storeAs('answers', $filename, 'public');

                    $fileType = in_array($extension, ['mp4', 'mov', 'avi']) ? 'video' : 'image';

                    AnswerProof::create([
                        'question_answer_id' => $saved->id,
                        'file_path'          => 'storage/'.$path,
                        'file_type'          => $fileType,
                    ]);

                    \Log::info('✅ Proof saved', ['answer_id' => $saved->id, 'path' => 'storage/'.$path, 'type' => $fileType]);
                } catch (\Throwable $pfEx) {
                    \Log::error('❌ Gagal simpan proof file', [
                        'answer_index' => $index,
                        'message' => $pfEx->getMessage(),
                    ]);
                }
            }

            $savedAnswers[] = $saved->load('proofs');
        }

        // =========================
        // Update submitted_at session
        // =========================
        $session = AnswerSession::find($sessionId);
        if ($session && is_null($session->submitted_at)) {
            $session->submitted_at = now();
            $session->save();
            \Log::info("🕒 submitted_at updated untuk session {$sessionId}");
        }

        return response()->json([
            'message' => 'All answers submitted successfully',
            'data'    => $savedAnswers,
        ], 200);

    } catch (\Throwable $e) {
        \Log::error('❌ Gagal submit answer:', [
            'message' => $e->getMessage(),
            'line'    => $e->getLine(),
            'file'    => $e->getFile(),
            'trace'   => $e->getTraceAsString(),
        ]);

        return response()->json([
            'message' => 'Internal Server Error',
            'error'   => $e->getMessage(),
        ], 500);
    }
}
}