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
        $path = $fileService->buildPath('lessons', $lesson->teacherId, $lesson->filePath, 'json');
        $file = $fileService->getFile($path);

        if (!$file)
            return response()->json(['status' => 'file not found'], 404);

        return response()->json(['status' => 'success', 'content' => $file]);
    }

    public function createLesson(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string',
            'fileId' => 'required|string',
            'name' => 'required|string',
            'description' => 'required|string',
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
            'description' => $request->description,
            'fileId' => $request->fileId
        ]);

        $fileService = new FileService();
        $path = $fileService->buildPath('lessons', auth()->id(), $request->fileId, 'json');
        $fileService->saveFile($path, $request->content);

        return response()->json(['status' => 'success']);
    }
}