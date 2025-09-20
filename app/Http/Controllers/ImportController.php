<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ImportController extends Controller
{
    public function show()
    {
        return view('import');
    }

    public function process(Request $req)
    {
        ini_set('upload_max_filesize', '10M');
        ini_set('post_max_size', '10M');

        $file = $req->file('file');
        if (! $file->isValid())
            die($file->getErrorMessage());

        $req->validate([
            'file' => 'required|mimes:csv',
        ]);

        $path = $file->getRealPath();
        Artisan::call('app:import', [
            'file' => $path
        ]);
        $output = Artisan::output();

        return back()->with('success', $output);
    }
}