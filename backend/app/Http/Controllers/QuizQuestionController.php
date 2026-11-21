<?php

namespace App\Http\Controllers;

use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class QuizQuestionController extends Controller
{
    public const CACHE_KEY = 'quiz_assessment_data';

    public function index()
    {
        $questions = QuizQuestion::orderBy('id', 'desc')->get()->map(function ($q) {
            return [
                'id' => $q->id,
                'questionText' => $q->question_text ?? $q->questionText,
                'type' => $q->type ?? 'Multiple Choice',
                'category' => $q->category ?? 'Technical Skills',
                'weight' => $q->weight ?? 'Medium',
                'status' => $q->status ?? 'Active',
                'options' => is_array($q->options) ? $q->options : json_decode($q->options, true) ?? []
            ];
        });

        return response()->json($questions);
    }

    public function assessment()
    {
        $payload = Cache::remember(self::CACHE_KEY, 86400, function () {
            $normalized = QuizQuestion::orderBy('id')
                ->get()
                ->filter(function ($q) {
                    $status = $q->status ?: 'Active';
                    return strtolower($status) === 'active';
                })
                ->map(function ($q) {
                    $options = $q->options;
                    if (is_string($options)) {
                        $options = json_decode($options, true);
                    }
                    if (!is_array($options)) {
                        $options = [];
                    }
                    $options = array_values(array_filter(
                        array_map(fn ($o) => is_string($o) ? trim($o) : $o, $options),
                        fn ($o) => $o !== null && $o !== ''
                    ));

                    return [
                        'id'           => $q->id,
                        'questionText' => $q->question_text,
                        'type'         => $q->type ?: 'Multiple Choice',
                        'category'     => $q->category ?: 'General',
                        'weight'       => $q->weight ?: 'Medium',
                        'options'      => $options,
                    ];
                })
                ->filter(fn ($q) => count($q['options']) > 0)
                ->values();

            $grouped = $normalized->groupBy('category');

            $steps = $grouped->keys()
                ->sortBy(fn ($category) => $grouped[$category]->min('id'))
                ->values()
                ->map(function ($category, $index) use ($grouped) {
                    return [
                        'step'      => $index + 1,
                        'category'  => $category,
                        'questions' => $grouped[$category]->sortBy('id')->values()->all(),
                    ];
                })
                ->all();

            return [
                'totalQuestions' => $normalized->count(),
                'totalSteps'     => count($steps),
                'steps'          => $steps,
            ];
        });

        return response()->json($payload);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'questionText' => 'required|string',
            'type' => 'nullable|string',
            'category' => 'required|string',
            'weight' => 'required|string',
            'status' => 'nullable|string',
            'options' => 'required|array'
        ]);

        $question = QuizQuestion::create([
            'question_text' => $validatedData['questionText'],
            'type' => $validatedData['type'] ?? 'Multiple Choice',
            'category' => $validatedData['category'],
            'weight' => $validatedData['weight'],
            'status' => $validatedData['status'] ?? 'Active',
            'options' => $validatedData['options']
        ]);

        Cache::forget(self::CACHE_KEY);

        return response()->json([
            'message' => 'Quiz question created successfully',
            'data' => $question
        ], 201);
    }

    public function show($id)
    {
        $q = QuizQuestion::findOrFail($id);

        return response()->json([
            'id' => $q->id,
            'questionText' => $q->question_text ?? $q->questionText,
            'type' => $q->type ?? 'Multiple Choice',
            'category' => $q->category ?? 'Technical Skills',
            'weight' => $q->weight ?? 'Medium',
            'status' => $q->status ?? 'Active',
            'options' => is_array($q->options) ? $q->options : json_decode($q->options, true) ?? []
        ]);
    }

    public function update(Request $request, $id)
    {
        $question = QuizQuestion::findOrFail($id);

        $validatedData = $request->validate([
            'questionText' => 'required|string',
            'type' => 'nullable|string',
            'category' => 'required|string',
            'weight' => 'required|string',
            'status' => 'nullable|string',
            'options' => 'required|array'
        ]);

        $question->update([
            'question_text' => $validatedData['questionText'],
            'type' => $validatedData['type'] ?? 'Multiple Choice',
            'category' => $validatedData['category'],
            'weight' => $validatedData['weight'],
            'status' => $validatedData['status'] ?? 'Active',
            'options' => $validatedData['options']
        ]);

        Cache::forget(self::CACHE_KEY);

        return response()->json([
            'message' => 'Quiz question updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $question = QuizQuestion::findOrFail($id);
        $question->delete();

        Cache::forget(self::CACHE_KEY);

        return response()->json([
            'message' => 'Quiz question deleted successfully'
        ]);
    }
}