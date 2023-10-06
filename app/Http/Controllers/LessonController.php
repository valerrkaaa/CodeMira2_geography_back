<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonAnswer;
use App\Services\Files\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LessonController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    public function getLessons(Request $request)
    {
        $lessons = Lesson::all();
        $fileService = new FileService();

        $output = [];
        foreach ($lessons as $lesson) {
            $path = $fileService->buildPath('lessons', $lesson->teacherId, $lesson->fileId, 'json');
            $file = json_decode($fileService->getFile($path));
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

    public function getTeacherLessons(Request $request)
    {
        $lessons = Lesson::all()->where('teacherId', auth()->id());
        $fileService = new FileService();

        $output = [];
        foreach ($lessons as $lesson) {
            $path = $fileService->buildPath('lessons', $lesson->teacherId, $lesson->fileId, 'json');
            $file = json_decode($fileService->getFile($path));
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

    public function getLesson(Request $request)
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

    public function createLesson(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string',
            'fileId' => 'required|string',
            'name' => 'required|string',
            'content' => 'required|json',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validator error',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $newLesson = Lesson::create([
            'teacherId' => auth()->id(),
            'type' => $request->type,
            'name' => $request->name,
            'description' => $request->description ? $request->description : "",
            'fileId' => $request->fileId
        ]);

        $fileService = new FileService();
        $path = $fileService->buildPath('lessons', auth()->id(), $request->fileId, 'json');
        $fileService->saveFile($path, $request->content);

        return response()->json(['status' => 'success']);
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

        $newAnswer = LessonAnswer::create([
            'lesson_id' => $request->lesson_id,
            'pupil_id' => auth()->id(),
            'fileId' => $request->fileId,
        ]);

        return response()->json(['status' => 'success']);
    }
}