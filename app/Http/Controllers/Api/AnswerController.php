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
        foreach ($request->answers as $index => $answer) {
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

            // Simpan proofs (image/video)
            $files = $request->file("answers.$index.proofs", []);
            foreach ($files as $file) {
                $extension = strtolower($file->getClientOriginalExtension());
                $filename  = \Str::uuid().'.'.$extension;
                $path      = $file->storeAs('answers', $filename, 'public');

                $fileType = in_array($extension, ['mp4', 'mov', 'avi']) ? 'video' : 'image';

                AnswerProof::create([
                    'question_answer_id' => $saved->id,
                    'file_path'          => 'storage/'.$path,
                    'file_type'          => $fileType,
                ]);
            }

            $savedAnswers[] = $saved->load('proofs');
        }

        // Update submitted_at session
        $session = AnswerSession::find($sessionId);
        if ($session && is_null($session->submitted_at)) {
            $session->submitted_at = now();
            $session->save();
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
        ]);

        return response()->json([
            'message' => 'Internal Server Error',
            'error'   => $e->getMessage(),
        ], 500);
    }
}


 }
