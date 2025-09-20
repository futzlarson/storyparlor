<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Services\SquarespaceService;

class CheckinController extends Controller
{
    public function __construct(
        private SquarespaceService $squarespace
    ) {}

    public function show()
    {
        return view('checkin')->with('rows', false);
    }

    public function process(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv',
        ]);

        if (!Customer::exists()) {
            return view('checkin')
                ->with('rows', false)
                ->withErrors(['No customer data available. Run import first.']);
        }

        $result = $this->squarespace->processForCheckin($request->file('file'));

        return view('checkin', $result);
    }
}