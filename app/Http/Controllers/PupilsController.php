<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\ClassName;
use App\Models\LessonAnswer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PupilsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    public function getClassesList(Request $request){
        return response()->json(['status' => 'success', 'classes'=> ClassName::all()]);
    }

    public function getPupilList(Request $request){
        $validator = Validator::make($request->all(), [
            'class_name_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validator error',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $class = ClassName::find($request->class_name_id)->pupils;  // todo не получилось получить через посредника, пока нет времени
        $pupils = [];
        foreach ($class as $user){
            array_push($pupils, User::find($user->pupil_id));
        }

        return response()->json(['status' => 'success', 'pupils' => $pupils]);
    }

    public function getPupil(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validator error',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $homeworks = LessonAnswer::where('pupil_id', $request->id);

        return response()->json(['status' => 'success', 'content' => $homeworks]);
    }
    
    public function getClassList(Request $request){
        
        $classes = ClassName::with('classes')->get();
        
        $output = [];  // сам знаю, полный ужас, времени нет
        foreach ($classes as $class){
            $temp = [];
            foreach ($class->classes as $pupil){
                array_push($temp, User::find($pupil->pupil_id));
            }
            array_push($output, ["id" => $class->id, "name" => $class->name, "pupils" => $temp]);
        }
        return response()->json(['status' => 'success', 'data' => $output]);
    }
}
