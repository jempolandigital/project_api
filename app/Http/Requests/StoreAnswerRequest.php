<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// class StoreAnswerRequest extends FormRequest
// {
    // public function rules(): array
    // {
    //     return [
    //         'tenant_id' => 'required|exists:tenants,id',
    //         'user_id' => 'required|exists:users,id',
    //         'question_id' => 'required|exists:questions,id',
    //         'question_type' => 'required|in:radio,checkbox,text,image,video',
    //         'answer_option_id' => 'nullable|exists:question_options,id',
    //         'answer_text' => 'nullable|string',
    //         'started_at' => 'nullable|date',
    //         'submitted_at' => 'nullable|date',
    //         'is_correct' => 'nullable|boolean',

    //         // Proofs (optional)
    //         'proofs' => 'nullable|array',
    //         'proofs.*' => 'file|mimes:jpg,jpeg,png,mp4,mov,avi|max:20480', // max 20MB
    //     ];
    // }
// public function rules(): array
// {
//     return [
//         'answers' => 'required|array',
//         'answers.*.tenant_id' => 'required|exists:tenants,id',
//         'answers.*.user_id' => 'required|exists:users,id',
//         'answers.*.question_id' => 'required|exists:questions,id',
//         'answers.*.question_type' => 'required|in:radio,checkbox,text,image,video',
//         'answers.*.answer_option_id' => 'nullable|exists:question_options,id',
//         'answers.*.answer_text' => 'nullable|string',
//         'answers.*.started_at' => 'nullable|date',
//         'answers.*.submitted_at' => 'nullable|date',
//         'answers.*.is_correct' => 'nullable|boolean',
//         'answers.*.session_id' => 'required|exists:answer_sessions,id',

//         // proofs per answer (optional)
//         'answers.*.proofs' => 'nullable|array',
//         'answers.*.proofs.*' => 'file|mimes:jpg,jpeg,png,mp4,mov,avi|max:20480',
//     ];
// }

//     public function authorize(): bool
//     {
//         return true;
//     }




class StoreAnswerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Root-level data
            'tenant_id'  => 'required|exists:tenants,id',
            'user_id'    => 'required|exists:users,id',
            'session_id' => 'required|exists:answer_sessions,id',

            // Answers array
            'answers' => 'required|array',
            'answers.*.question_id'      => 'required|exists:questions,id',
            'answers.*.question_type'    => 'required|in:radio,checkbox,text,image,video',
            'answers.*.answer_option_id' => 'nullable|exists:question_options,id',
            'answers.*.answer_text'      => 'nullable|string',
            'answers.*.answer_reason'    => 'nullable|string',
            'answers.*.started_at'       => 'nullable|date',
            'answers.*.submitted_at'     => 'nullable|date',
            'answers.*.is_correct'       => 'nullable|boolean',

            // Proofs per answer (optional)
            'answers.*.proofs'   => 'nullable|array',
            'answers.*.proofs.*' => 'file|mimes:jpg,jpeg,png,mp4,mov,avi|max:102400',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}



 
