<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    /**
     * Create a new question for the form.
     *
     * @param Request $request The request data
     * @param string $slug The slug of the form
     * @return JsonResponse
     */
    public function create(Request $request, string $slug): JsonResponse
    {
        // Find form by slug
        $form = Form::query()->where('slug', $slug)->first();

        // Check if form not found
        if (!$form)
        {
            return response()->json([
                'message' => 'Form not found',
            ], 404);
        }

        // Check if user is creator of the form
        if ($form->creator_id !== auth()->user()->id)
        {
            return response()->json([
                'message' => 'Forbidden access',
            ], 403);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'form_id'     => ['required', 'exists:forms,id'],
            'name'        => ['required'],
            'choice_type' => ['required', 'in:short answer,paragraph,date,time,multiple choice,dropdown,checkboxes'],
            'choices'     => ['required_if:choice_type,multiple choice,dropdown,checkboxes'],
            'is_required' => ['boolean'],
        ]);

        // Check if validations failed
        if ($validator->fails())
        {
            return response()->json([
                'message' => 'Invalid field',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Create question
        $question = $form->questions()->create([
            'name'        => $request->name,
            'choice_type' => $request->choice_type,
            'choices'     => $request->choices,
            'is_required' => $request->is_required,
        ]);

        // Return success response
        return response()->json([
            'message'  => 'Add question success',
            'question' => $question,
        ]);
    }
}
