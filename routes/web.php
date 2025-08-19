<?php

use Illuminate\Support\Facades\Route;

// // Route::get('/', function () {
// //     return view('welcome');
// // });

// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Log;


// Route::get('/test-insert', function() {
//     $data = [
//         'tenant_id' => 4,
//         'user_id' => 3,
//         'question_id' => 8,
//         'question_type' => 'text',
//         'answer_option_id' => null,
//         'answer_text' => 'bismillah',
//         'started_at' => null,
//         'submitted_at' => now(),
//         'is_correct' => null,
//         'session_id' => 9,
//         'created_at' => now(),
//         'updated_at' => now(),
//     ];

//     try {
//         DB::table('question_answers')->insert($data);
//         Log::info('Insert manual berhasil');
//         return 'Insert manual berhasil';
//     } catch (\Exception $e) {
//         Log::error('Insert manual gagal: ' . $e->getMessage());
//         return 'Insert manual gagal: ' . $e->getMessage();
//     }
// });
