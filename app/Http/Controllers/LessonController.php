<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Services\Files\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LessonController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    public function getTeacherLessons(Request $request)
    {
        $lessons = Lesson::all()->where('teacherId', auth()->id());
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

    public function getOwnLesson(Request $request)
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
        if ($lesson->teacherId != auth()->id()) {
            return response()->json(['status' => 'permission denied'], 403);
        }

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
            "description" => $lesson->description,
            "fileId" => $lesson->fileId
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

    public function updateLesson(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
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

        $lesson = Lesson::find($request->id);
        if (!$lesson) {
            return response()->json(['status' => 'lesson not found'], 404);
        }
        $lesson->type = $request->type;
        $lesson->fileId = $request->fileId;
        $lesson->name = $request->name;
        $lesson->save();

        $fileService = new FileService();
        $path = $fileService->buildPath('lessons', auth()->id(), $request->fileId, 'json');
        $fileService->saveFile($path, $request->content);

        return response()->json(['status' => 'success']);
    }

    public function deleteLesson(Request $request)
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
        if ($lesson->teacherId !== auth()->id()) {
            return response()->json(['status' => 'permission denied'], 403);
        }

        $fileService = new FileService();
        $path = $fileService->buildPath('lessons', $lesson->teacherId, $lesson->fileId, 'json');
        $fileService->deleteFile($path);

        $lesson->delete();

        return response()->json(['status' => 'success']);
    }
}