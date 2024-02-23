<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Validator;

class ResponseController extends Controller
{
    /**
     * Create a new response for the given form slug.
     *
     * @param string $slug The slug of the form
     * @param Request $request The HTTP request containing form answers
     * @throws \Illuminate\Validation\ValidationException If the request data is invalid
     * @return \Illuminate\Http\JsonResponse The JSON response indicating the success or failure of the form submission
     */
    public function create(string $slug, Request $request): JsonResponse
    {
        // Find form by slug
        $form = Form::query()->with('allowedDomains')->where('slug', $slug)->first();

        // Check if form not found
        if (!$form)
        {
            return response()->json([
                'message' => 'Form not found',
            ], 404);
        }

        // Get user 
        $user = auth()->user();

        // Check if form has limit one response
        if ($form->limit_one_response)
        {
            // Find user response by form
            $userResponse = Response::query()->where('form_id', $form->id)->where('user_id', $user->id)->first();

            // Check if user response not found
            if ($userResponse)
            {
                return response()->json([
                    'message' => 'You can not submit form twice',
                ], 422);
            }
        }

        // Get form allowed domain
        $allowedDomains = $form->allowedDomains->pluck('domain')->toArray();

        // Check if the form has allowed domain
        if (!empty($allowedDomains))
        {
            // Get user email
            $userEmail = $user->email;

            // Find the position of the last occurrence of '@' symbol
            $position = strrpos($userEmail, '@');

            // Extract the substring after the '@' symbol
            $userDomain = substr($userEmail, $position + 1);

            // Check if user domain not in allowed domain
            if (!in_array($userDomain, $allowedDomains))
            {
                return response()->json([
                    'message' => 'Forbidden access',
                ], 403);
            }
        }

        // Validate request data
        $validator = Validator::make($request->all(), [
            'answers'               => ['required', 'array'],
            'answers.*.question_id' => ['required', 'integer', 'exists:questions,id'],
            'answers.*.value'       => ['nullable'],
        ]);

        // Check if validation fails
        if ($validator->fails())
        {
            return response()->json([
                'message' => 'Invalid field',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Create new Response
        $response = Response::query()->create([
            'form_id' => $form->id,
            'user_id' => $user->id,
            'date'    => now(),
        ]);

        // Get all answers
        $answers = $request->input('answers');

        // Loop for create every answer
        foreach ($answers as $answer)
        {
            // Create new answer
            $response->answers()->create([
                'question_id' => $answer['question_id'],
                'value'       => $answer['value'],
            ]);
        }

        return response()->json([
            'message' => 'Submit response success',
        ]);
    }
}
