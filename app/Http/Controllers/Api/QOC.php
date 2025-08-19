<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QuestionOption;
use Illuminate\Support\Facades\Storage;

class QOC extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'question_id' => 'required|exists:questions,id',
            'option_text' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $option = new QuestionOption();
        $option->question_id = $request->question_id;
        $option->option_text = $request->option_text;
        $option->is_correct = false; // default, karena ini jawaban user

        // Simpan gambar jika ada
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('question_options', 'public');
            $option->option_image_path = $imagePath;
        }

        $option->save();

        return response()->json([
            'message' => 'Jawaban berhasil disimpan',
            'data' => $option
        ], 201);
    }
}
