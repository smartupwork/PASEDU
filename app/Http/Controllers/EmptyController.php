<?php
namespace App\Http\Controllers;

class EmptyController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return response()->json(['status' => 'success']);
    }
}
