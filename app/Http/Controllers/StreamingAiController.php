<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StreamingAiController extends Controller
{
    public function index()
    {
        return view('streaming-ai.index');
    }
}
