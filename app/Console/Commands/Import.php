<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;

class Import extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import all orders from a csv into the customer table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = storage_path('app/' . $this->argument('file'));
        if (! file_exists($file)) {
            $this->error("$file does not exist");
            die();
        }

        DB::table('customers')->truncate();

        $added = 0;
        $headers = [];
        $handle = fopen($file, 'r');

        if (($headerRow = fgetcsv($handle)) !== false)
            $headers = $headerRow;

        while (($line = fgetcsv($handle)) !== false) {
            $d = array_combine($headers, $line);

            if ($d['Financial Status'] == 'PAID') {
                $email = $d['Email'];
                $quantity = intval($d['Lineitem quantity']);
                $customer = Customer::where('email', $email)->first();

                if ($customer && $customer->exists()) {
                    $customer->tickets += $quantity;
                    $customer->save();
                } else {
                    $customer = Customer::create([
                        'email' => $email,
                        'tickets' => $quantity
                    ]);
                }

                $added++;
            }
        }

        fclose($handle);

        $this->info("added $added customers");
    }
}