<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
        $file = $this->argument('file');
        if (! file_exists($file)) {
            $this->error("$file does not exist");
            die();
        }

        DB::table('customers')->truncate();

        $orders = 0;
        $headers = $customers = [];
        $handle = fopen($file, 'r');

        if (($headerRow = fgetcsv($handle)) !== false)
            $headers = $headerRow;

        while (($line = fgetcsv($handle)) !== false) {
            $d = array_combine($headers, $line);

            if ($d['Financial Status'] == 'PAID') {
                $email = $d['Email'];
                $quantity = intval($d['Lineitem quantity']);
                @$customers[$email] += $quantity;

                $orders++;
            }
        }

        fclose($handle);

        $data = [];
        foreach ($customers as $email => $tickets) {
            $data[] = [
                'email' => $email,
                'tickets' => $tickets,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('customers')->insert($data);

        $msg = "added $orders orders and " . count($customers) . " customers";
        $this->info($msg);

        return $msg;
    }
}