<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SquarespaceService;

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

    public function __construct(
        private SquarespaceService $squarespace
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        
        try {
            $result = $this->squarespace->importCustomers($file);
            $this->info($result);
            return $result;
        } catch (\Exception $e) {
            $this->error("Import failed: " . $e->getMessage());
            return false;
        }
    }
}