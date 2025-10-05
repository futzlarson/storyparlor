<?php

use App\Services\SquarespaceService;
use App\Models\Customer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    // Only run the specific migration we need
    if (!Schema::hasTable('customers')) {
        Schema::create('customers', function ($table) {
            $table->id();
            $table->string('email');
            $table->integer('tickets');
            $table->timestamps();
        });
    }
    
    $this->service = new SquarespaceService();
});

afterEach(function () {
    Schema::dropIfExists('customers');
});

function createCsv(array $rows): UploadedFile {
    $headers = 'Financial Status,Email,Lineitem quantity,Order ID,Billing Name,Lineitem name,Total,Discount Code';
    $content = $headers . "\n" . implode("\n", $rows);
    
    $tempPath = tempnam(sys_get_temp_dir(), 'test_csv_');
    file_put_contents($tempPath, $content);
    
    return new UploadedFile($tempPath, 'test.csv', 'text/csv', null, true);
}

describe('processForCheckin', function () {
    
    it('processes valid CSV correctly', function () {
        $result = $this->service->processForCheckin(createCsv([
            'PAID,john@test.com,2,12345,John Doe,Test Event,50.00,',
            'PAID,jane@test.com,1,12346,Jane Smith,Test Event,25.00,EARLY10'
        ]));
        
        expect($result)
            ->toHaveKeys(['event', 'cost', 'sold', 'rows'])
            ->event->toBe('Test Event')
            ->cost->toBe(25.0)
            ->sold->toBe(3)
            ->rows->toHaveCount(2);
            
        // Verify customers were created in database
        expect(Customer::count())->toBe(2);
    });
    
    it('filters out non-PAID orders', function () {
        $result = $this->service->processForCheckin(createCsv([
            'PAID,john@test.com,2,12345,John Doe,Test Event,50.00,',
            'PENDING,jane@test.com,1,12346,Jane Smith,Test Event,25.00,',
        ]));
        
        expect($result['rows'])->toHaveCount(1);
        expect($result['sold'])->toBe(2);
    });
    
    it('throws exception for missing headers', function () {
        $tempPath = tempnam(sys_get_temp_dir(), 'test_csv_');
        file_put_contents($tempPath, "Email,Name\njohn@test.com,John Doe");
        $csv = new UploadedFile($tempPath, 'test.csv', 'text/csv', null, true);
        
        expect(fn() => $this->service->processForCheckin($csv))
            ->toThrow(Exception::class, 'Missing required CSV headers');
    });
    
    it('sorts customers by last name', function () {
        $result = $this->service->processForCheckin(createCsv([
            'PAID,alice@test.com,1,12345,Alice Zebra,Test Event,25.00,',
            'PAID,bob@test.com,1,12346,Bob Adams,Test Event,25.00,',
        ]));
        
        expect($result['rows'][0]['last'])->toBe('Adams');
        expect($result['rows'][1]['last'])->toBe('Zebra');
    });
});