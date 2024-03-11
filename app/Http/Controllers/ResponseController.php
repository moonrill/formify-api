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

        // Create validaton rules
        $validationRules = [
            'answers'               => ['required', 'array'],
            'answers.*.question_id' => ['required', 'integer', 'exists:questions,id'],
        ];

        // Check if questions is required
        for ($i = 0; $i < count($form->questions); $i++)
        {
            if ($form->questions[$i]->is_required)
            {
                $validationRules["answers.{$i}.value"] = ['required'];
            }
        }

        // Validate request
        $validator = Validator::make($request->all(), $validationRules);

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

    /**
     * Get all responses for a form with the given slug
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function getAll(string $slug): JsonResponse
    {
        // Find the form by its slug
        $form = Form::query()->where('slug', $slug)->first();

        // If form not found, return error response
        if (!$form)
        {
            return response()->json([
                'message' => 'Form not found',
            ], 404);
        }

        // If the user is not the creator, return forbidden access error response
        if ($form->creator_id != auth()->user()->id)
        {
            return response()->json([
                'message' => 'Forbidden access',
            ], 403);
        }

        // Get all responses for the form
        $responses = Response::query()->with('user', 'answers')->where('form_id', $form->id)->get();

        // Initialize array to store new response data
        $newResponses = [];

        // Iterate over each response to create new response data
        foreach ($responses as $response)
        {
            // Initialize array to store new answers
            $answers = [];

            // Iterate over each answer to create new answer data
            foreach ($response->answers as $answer)
            {
                // Assign each answer to the new answer array
                $answers[$answer->question->name] = $answer->value;
            }

            // Add new response data to the new responses array
            $newResponses[] = [
                'date'    => $response->date,
                'user'    => $response->user,
                'answers' => $answers,
            ];
        }

        // Return success response with the new responses
        return response()->json([
            'message'   => 'Get responses success',
            'responses' => $newResponses,
        ]);
    }
}
