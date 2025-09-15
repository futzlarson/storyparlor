<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Mail\Markdown;
use App\Models\Customer;

const UPDATE_CUSTOMERS = true;

Route::get('readme', function () {
    $content = File::get(base_path('README.md'));
    $html = Markdown::parse($content);

    return response($html, 200)
        ->header('Content-Type', 'text/html');
});

Route::get('/', function () {

    $errors = Customer::exists() ? [] : [
        'No customer data available. Run import.'
    ];

    return view('checkin')->with('rows', false)->withErrors($errors);
});

Route::post('/', function(Request $req) {
    $req->validate([
        'file' => 'required|mimes:csv',
    ]);

    $data = $headers = [];
    $event = $cost = null;
    $path = $req->file('file')->getRealPath();
    $handle = fopen($path, 'r');

    if (($headerRow = fgetcsv($handle)) !== false)
        $headers = $headerRow;

    while (($line = fgetcsv($handle)) !== false) {
        $d = array_combine($headers, $line);

        if ($d['Financial Status'] != 'PAID')
            continue;

        $row = [
            'done' => false,
            'checked_in' => 0,
            'id' => $d['Order ID'],
            'where' => $d['Checkout Form: How did you hear about this event or workshop?'],
            'welcome' => false
        ];

        $email = $d['Email'];
        $quantity = $row['quantity'] = intval($d['Lineitem quantity']);
        $row['last'] = getLastName($d);
        $row['first'] = getFirstName($d, $row['last']);
        $row['discount_code'] = ($d['Discount Code'] == 'null') ? null : $d['Discount Code'];

        if (! $event) {
            $event = $d['Lineitem name'];
            $cost = $d['Total'] / $quantity;
        }

        $customer = Customer::where('email', $email)->first();
        if ($customer && $customer->exists()) {
            if (UPDATE_CUSTOMERS) {
                $customer->tickets += $quantity;
                $customer->save();
            }

            $row['welcome'] = $customer->created_at->isToday();
        } else {
            if (UPDATE_CUSTOMERS) {
                $customer = Customer::create([
                    'email' => $email,
                    'tickets' => $quantity
                ]);
            }

            $row['welcome'] = true;
        }
        
        $data[] = $row;
    }

    fclose($handle);

    usort($data, function ($a, $b) {
        return strcmp(strtolower($a['last']), strtolower($b['last']));
    });

    $sold = array_sum(array_column($data, 'quantity'));

    return view('checkin', compact('event', 'cost', 'sold'))->with('rows', $data);
});

function getFirstName($field, $last) {
    return str_replace(" $last", '', $field['Billing Name']);
}
function getLastName($field) {
    $fullName = $field['Billing Name'];
    $nameParts = explode(' ', trim($fullName));

    return end($nameParts);
}
