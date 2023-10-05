<?php

namespace App\Http\Controllers;

use App\Models\ClassName;
use Illuminate\Http\Request;

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
        return response()->json([1]);
    }
}
