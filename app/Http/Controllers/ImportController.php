<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SquarespaceService;

class ImportController extends Controller
{
    public function __construct(
        private SquarespaceService $squarespace
    ) {}

    public function show()
    {
        return view('import');
    }

    public function process(Request $request)
    {
        ini_set('upload_max_filesize', '10M');
        ini_set('post_max_size', '10M');

        $file = $request->file('file');
        if (!$file->isValid()) {
            return back()->withErrors([$file->getErrorMessage()]);
        }

        $request->validate([
            'file' => 'required|mimes:csv',
        ]);

        try {
            $result = $this->squarespace->importCustomers($file->getRealPath());
            return back()->with('success', $result);
        } catch (\Exception $e) {
            return back()->withErrors(['Import failed: ' . $e->getMessage()]);
        }
    }
}