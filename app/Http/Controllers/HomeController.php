<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
//        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('layouts.app');
    }

    public function homepage()
    {
        return view('homepage');
    }

    public function ping(Request $request)
    {
        return response()->json(['is_connected' => true]);
    }
}
