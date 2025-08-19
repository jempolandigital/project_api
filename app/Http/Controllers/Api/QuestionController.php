<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Modul;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function getQuestionsByModul($modulId)
{
    $modul = Modul::with(['questions.options'])->find($modulId);

    if (!$modul) {
        return response()->json(['message' => 'Modul tidak ditemukan'], 404);
    }

    return response()->json([
        'modul_id' => $modul->id,
        'modul_title' => $modul->name, // fix here
        'questions' => $modul->questions->map(function ($question) {
            return [
                'id' => $question->id,
                'text' => $question->question, // fix here
                 'desc' => $question->desc,
                'type' => $question->question_type, // optional: or $question->type if you want accessor
                'min_proofs_required' => $question->min_proofs_required,
                'options' => $question->options->map(function ($opt) {
                    return [
                        'id' => $opt->id,
                        'text' => $opt->option_text,
                        'is_correct' => $opt->is_correct,
                    ];
                }),
            ];
        }),
    ]);
}
}