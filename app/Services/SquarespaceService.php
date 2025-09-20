<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;

class SquarespaceService
{
    /**
     * Process Squarespace CSV for real-time check-in interface
     */
    public function processForCheckin(UploadedFile $file): array
    {
        $orders = $this->processCsvFile($file->getRealPath());
        
        $checkinRows = [];
        $event = null;
        $cost = null;
        
        foreach ($orders as $orderData) {
            // Extract event details from first order
            if (!$event) {
                $event = $orderData['Lineitem name'];
                $cost = $orderData['Total'] / intval($orderData['Lineitem quantity']);
            }
            
            // Create checkin row with customer tracking
            $checkinRows[] = $this->createCheckinRow($orderData);
        }
        
        // Sort by last name
        usort($checkinRows, function ($a, $b) {
            return strcmp(strtolower($a['last']), strtolower($b['last']));
        });
        
        $sold = array_sum(array_column($checkinRows, 'quantity'));
        
        return [
            'event' => $event,
            'cost' => $cost,
            'sold' => $sold,
            'rows' => $checkinRows
        ];
    }
    
    /**
     * Import customers from Squarespace CSV into database
     */
    public function importCustomers(string $filePath): string
    {
        $orders = $this->processCsvFile($filePath);
        
        // Aggregate customers by email
        $customers = [];
        foreach ($orders as $orderData) {
            $email = $orderData['Email'];
            $customers[$email] = ($customers[$email] ?? 0) + intval($orderData['Lineitem quantity']);
        }
        
        // Clear existing data and bulk insert
        DB::table('customers')->truncate();
        
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
        
        return "added " . count($orders) . " orders and " . count($customers) . " customers";
    }
    
    /**
     * Read and filter CSV file for PAID orders
     */
    private function processCsvFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("CSV file does not exist: {$filePath}");
        }
        
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \Exception("Could not open CSV file: {$filePath}");
        }
        
        $headers = fgetcsv($handle, 0, ',', '"', '\\');
        if ($headers === false) {
            fclose($handle);
            throw new \Exception("Could not read CSV headers");
        }
        
        // Validate required headers exist
        $requiredHeaders = ['Financial Status', 'Email', 'Lineitem quantity', 'Order ID', 'Billing Name', 'Lineitem name', 'Total', 'Discount Code'];
        $missingHeaders = array_diff($requiredHeaders, $headers);
        if (!empty($missingHeaders)) {
            fclose($handle);
            throw new \Exception("Missing required CSV headers: " . implode(', ', $missingHeaders));
        }
        
        $orders = [];
        while (($line = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            $orderData = array_combine($headers, $line);
            
            if ($orderData && $orderData['Financial Status'] === 'PAID') {
                $orders[] = $orderData;
            }
        }
        
        fclose($handle);
        return $orders;
    }
    
    /**
     * Create a checkin row from order data with customer tracking
     */
    private function createCheckinRow(array $orderData): array
    {
        $email = $orderData['Email'];
        $quantity = intval($orderData['Lineitem quantity']);
        $discountCode = $orderData['Discount Code'] === 'null' ? null : $orderData['Discount Code'];
        
        // Parse customer name
        $nameParts = $this->parseCustomerName($orderData['Billing Name']);
        
        // Handle customer tracking
        $customer = $this->handleCustomerTracking($email, $quantity);
        
        return [
            'done' => false,
            'checked_in' => 0,
            'id' => $orderData['Order ID'],
            'where' => $orderData['Checkout Form: How did you hear about this event or workshop?'] ?? '',
            'welcome' => $customer ? $customer->created_at->isToday() : true,
            'quantity' => $quantity,
            'first' => $nameParts['first'],
            'last' => $nameParts['last'],
            'discount_code' => $discountCode
        ];
    }
    
    /**
     * Parse full name into first and last name components
     */
    private function parseCustomerName(string $fullName): array
    {
        $nameParts = explode(' ', trim($fullName));
        $last = end($nameParts);
        $first = str_replace(" {$last}", '', $fullName);
        
        return compact('first', 'last');
    }
    
    /**
     * Handle customer lookup and creation with optional updates
     */
    private function handleCustomerTracking(string $email, int $quantity): ?Customer
    {
        $update = env('UPDATE_CUSTOMERS', true);
        $customer = Customer::where('email', $email)->first();
        
        if ($customer && $customer->exists()) {
            if ($update) {
                $customer->tickets += $quantity;
                $customer->save();
            }
            return $customer;
        }
        
        if ($update) {
            return Customer::create([
                'email' => $email,
                'tickets' => $quantity
            ]);
        }
        
        return null;
    }
}