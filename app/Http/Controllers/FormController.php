<?php

namespace App\Http\Controllers;

use App\Models\AllowedDomain;
use App\Models\Form;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FormController extends Controller
{
    /**
     * Create a new form based on the form data from the request.
     *
     * @param Request $request The request containing the form data
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        // Validate form data
        $validator = Validator::make($request->all(), [
            'name'               => ['required'],
            'slug'               => ['required', 'alpha_dash', 'unique:forms,slug'],
            'description'        => ['required', 'string'],
            'allowed_domains'    => ['array'],
            'allowed_domains.*'  => ['string'],
            'limit_one_response' => ['boolean'],
        ]);

        // Check if validation fails
        if ($validator->fails())
        {
            // Throw validation error response
            return response()->json([
                'message' => 'Invalid field',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Create new form
        $form = Form::query()->create([
            'name'               => $request->name,
            'slug'               => $request->slug,
            'description'        => $request->description,
            'limit_one_response' => $request->limit_one_response,
            'creator_id'         => auth()->user()->id,
        ]);

        // Check if allowed domains is not empty
        if (!empty($request->allowed_domains))
        {
            // Loop all allowed domains
            foreach ($request->allowed_domains as $domain)
            {
                // Create new allowed domains based on form
                AllowedDomain::query()->create([
                    'form_id' => $form->id,
                    'domain'  => $domain,
                ]);
            }
        }

        // Return success response
        return response()->json([
            'message' => 'Create form success',
            'form'    => $form,
        ]);
    }

    /**
     * Get all forms.
     *
     * @return JsonResponse
     */
    public function getAll(): JsonResponse
    {
        $forms = Form::query()->get();

        return response()->json([
            'message' => 'Get all forms success',
            'forms'   => $forms,
        ], 200);
    }

    /**
     * Detail function to retrieve form details by slug
     *
     * @param string $slug The unique identifier for the form
     * @return JsonResponse
     */
    public function detail(string $slug): JsonResponse
    {
        $form = Form::query()
            ->with('allowedDomains', 'questions', 'creator')
            ->where('slug', $slug)
            ->first();

        // Check if form not found
        if (!$form)
        {
            return response()->json([
                'message' => 'Form not found',
            ], 404);
        }

        // Get allowed domains
        $allowedDomains = $form->allowedDomains->pluck('domain')->toArray();

        // Combine form detail with allowed domains
        $formDetail                    = $form->toArray();
        $formDetail['allowed_domains'] = $allowedDomains;

        // Return form detail 
        return response()->json([
            'message' => 'Get form success',
            'form'    => $formDetail,
        ]);
    }
}
