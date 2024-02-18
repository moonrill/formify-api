<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    public function create(Request $request, string $slug)
    {
        $validator = Validator::make($request->all(), [
            'name'        => ['required'],
            'choice_type' => ['required', 'in:short answer, paragraph, date, time, multiple choice, dropdown, checkboxes'],
            'choices'     => ['required_if:choice_type, multiple choice, dropdown, checkboxes'],
        ]);

        // Find form by slug
        $form = Form::query()->where('slug', $slug)->first();

        // Check if form not found
        if (!$form)
        {
            return response()->json([
                'message' => 'Form not found',
            ], 404);
        }
    }
}
