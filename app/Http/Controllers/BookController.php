<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        return $request->all();
    }

    public function show($id)
    {
        return ['id' => $id];
    }

    public function store(Request $request)
    {
        return ['name' => $request->input('name')];
    }

    public function upload(Request $request)
    {
        $file = $request->file('file1');
        $file->move('/tmp/a.png');
    }
}
