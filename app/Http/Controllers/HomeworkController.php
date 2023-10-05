<?php

namespace App\Http\Controllers;

use App\Models\LessonAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HomeworkController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }
    
    public function getHomework(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:lesson_answers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validator error',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $homework = LessonAnswer::find($request->id);

        return response()->json(['status' => 'success', 'data' => $homework]);
    }
}
