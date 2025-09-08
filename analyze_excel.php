<?php
require_once 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$file = '/Users/hooshyar/Downloads/test_multilingual_food_FIXED_module2_store26.xlsx';

try {
    $spreadsheet = IOFactory::load($file);
    $worksheet = $spreadsheet->getActiveSheet();

    echo "File analysis:\n";
    echo "Worksheet title: " . $worksheet->getTitle() . "\n";

    // Get headers (first row)
    $headers = [];
    $row = 1;
    $col = 1;
    while($worksheet->getCell([$col, $row])->getValue() !== null) {
        $headers[] = $worksheet->getCell([$col, $row])->getValue();
        $col++;
        if($col > 30) break; // safety break
    }

    echo "Column headers (" . count($headers) . " columns):\n";
    foreach($headers as $index => $header) {
        echo "  " . ($index + 1) . ": [" . $header . "]\n";
    }

    echo "\nFirst 2 data rows:\n";
    for($dataRow = 2; $dataRow <= 3; $dataRow++) {
        if($worksheet->getCell([1, $dataRow])->getValue() === null) break;
        
        echo "Row $dataRow:\n";
        for($col = 1; $col <= count($headers); $col++) {
            $value = $worksheet->getCell([$col, $dataRow])->getValue();
            if($value !== null) {
                echo "  " . $headers[$col-1] . ": [" . $value . "]\n";
            }
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}