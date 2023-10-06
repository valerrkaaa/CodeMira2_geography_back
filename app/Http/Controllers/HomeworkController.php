<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonAnswer;
use App\Services\Distances\CalculateDistanceService;
use App\Services\Files\FileService;
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

    public function getHomeworkListApi(Request $request)
    {
        $lessons = Lesson::all();
        $fileService = new FileService();

        $output = [];
        foreach ($lessons as $lesson) {
            $path = $fileService->buildPath('lessons', $lesson->teacherId, $lesson->fileId, 'json');
            $rawFile = $fileService->getFile($path);
            if (!$rawFile)
                return response()->json(['status' => 'file not found'], 404);

            $file = json_decode($rawFile);
            $photo = $file->map;
            array_push($output, [
                'id' => $lesson->id,
                'type' => $lesson->type,
                'name' => $lesson->name,
                'photo' => $photo
            ]);
        }

        return response()->json(['status' => 'success', 'lessons' => $output]);
    }

    public function getHomeworkItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:lessons,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validator error',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $lesson = Lesson::find($request->id);

        $fileService = new FileService();
        $path = $fileService->buildPath('lessons', $lesson->teacherId, $lesson->fileId, 'json');
        $rawFile = $fileService->getFile($path);

        if (!$rawFile)
            return response()->json(['status' => 'file not found'], 404);

        $file = json_decode($rawFile);

        $output = [
            "map" => $file->map,
            "pieces" => $file->pieces,
            "name" => $lesson->name,
            "description" => $lesson->description
        ];

        return response()->json(['status' => 'success', 'content' => $output]);
    }
    public function sendHomeworkAnswer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'map' => 'required|string',
            'pieces' => 'required|array',
            'lesson_id' => 'required|integer',
            'fileId' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validator error',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $file = json_encode([
            'map' => $request->map,
            'pieces' => $request->pieces,
        ]);


        $fileService = new FileService();
        $path = $fileService->buildPath('lessons', auth()->id(), $request->fileId, 'json');
        $fileService->saveFile($path, $file);

        $lesson = Lesson::find($request->lesson_id);
        $path = $fileService->buildPath('lessons', $lesson->teacherId, $lesson->fileId, 'json');
        $rawFile = $fileService->getFile($path);
        $file = json_decode($rawFile);

        $calculateDistanceService = new CalculateDistanceService();
        $option = ['maxScore' => 100, 'minScore' => 0];
        $coefficients = $calculateDistanceService->calculateSimilarityCoefficient($file, json_decode(json_encode($request->all())), $option);
        $mark = $calculateDistanceService->convertToMark($coefficients);
        // return response()->json(['status' => 'success', "answer" => [[$coefficients, $mark],[$file, json_decode(json_encode($request->all()))]]]);

        LessonAnswer::create([
            'lesson_id' => $request->lesson_id,
            'pupil_id' => auth()->id(),
            'fileId' => $request->fileId,
            'mark' => $mark
        ]);

        return response()->json(['status' => 'success', "mark" => $mark]);
    }
}
