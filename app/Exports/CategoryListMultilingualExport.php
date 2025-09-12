<?php

namespace App\Exports;

use App\Services\MultilingualExportService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Config;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Enhanced Category List Export with Multilingual Support
 * 
 * Exports categories with translations in Arabic (ar), Kurdish Sorani (ckb), and English (en)
 * Utilizes the existing MultilingualExportService for consistent translation handling
 */
class CategoryListMultilingualExport implements FromView, ShouldAutoSize, WithStyles, WithColumnWidths, WithHeadings, WithEvents
{
    use Exportable;

    protected $data;
    protected $multilingualService;
    protected $supportedLanguages;
    protected $columnCount;

    public function __construct($data) 
    {
        $this->data = $data;
        $this->multilingualService = app(MultilingualExportService::class);
        $this->supportedLanguages = $this->multilingualService->getSupportedLanguages();
        
        // Calculate total columns for import-compatible format
        // Categories: id, name, parent_id, position, status, priority + multilingual (name_ar, name_ckb)
        // Import-compatible: 8 columns (A to H)
        $this->columnCount = 8;
    }

    public function view(): View
    {
        // Enhance the data with multilingual information
        $enhancedData = $this->data;
        $enhancedData['multilingual_categories'] = $this->extractMultilingualData();
        $enhancedData['supported_languages'] = $this->supportedLanguages;
        
        return view('file-exports.category-list-multilingual-import-compatible', [
            'data' => $enhancedData,
        ]);
    }

    /**
     * Extract multilingual data for all categories with import-compatible format
     */
    protected function extractMultilingualData()
    {
        return $this->data['data']->map(function($category) {
            // Get multilingual data using the service
            $multilingual = $this->multilingualService->extractMultilingualData($category, 'Category');
            
            // Merge category data with multilingual fields
            $categoryArray = $category->toArray();
            
            // Add multilingual fields (import-compatible format)
            $categoryArray['name_ar'] = $multilingual['name_ar'] ?? '';
            $categoryArray['name_ckb'] = $multilingual['name_ckb'] ?? '';
            
            // Add import-compatible fields that might be missing
            $categoryArray['parent_id'] = $category->parent_id ?? 0;
            $categoryArray['position'] = $category->position ?? 0;
            $categoryArray['priority'] = $category->priority ?? 0;
            
            // Include the original category object for relationships
            $categoryArray['original_category'] = $category;
            
            return $categoryArray;
        });
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10, // Id
            'B' => 25, // Name
            'C' => 15, // Parent Id
            'D' => 15, // Position
            'E' => 15, // Status
            'F' => 15, // Priority
            'G' => 25, // name_ar
            'H' => 25, // name_ckb
        ];
    }

    public function styles(Worksheet $sheet) 
    {
        // Header styling
        $headerRange = 'A2:' . $this->getColumnLetter($this->columnCount) . '2';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        
        $filterRange = 'A3:' . $this->getColumnLetter($this->columnCount) . '3';
        $sheet->getStyle($filterRange)->getFill()->applyFromArray([
            'fillType' => 'solid',
            'rotation' => 0,
            'color' => ['rgb' => '9F9F9F'],
        ]);

        $sheet->setShowGridlines(false);
        
        // Main content styling
        $dataRange = 'A1:' . $this->getColumnLetter($this->columnCount) . ($this->data['data']->count() + 3);
        return [
            $dataRange => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => '000000'],
                    ],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $columnLetter = $this->getColumnLetter($this->columnCount);
                
                // Center alignment for headers
                $event->sheet->getStyle('A1:' . $columnLetter . '1')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                    
                $event->sheet->getStyle('A2:C2')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                    
                $event->sheet->getStyle('A3:C3')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Center alignment for data
                $event->sheet->getStyle('A3:' . $columnLetter . ($this->data['data']->count() + 3))
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                    
                // Left alignment for description columns
                $event->sheet->getStyle('D2:' . $columnLetter . '2')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Merge cells for header
                $event->sheet->mergeCells('A1:' . $columnLetter . '1');
                $event->sheet->mergeCells('A2:C2');
                $event->sheet->mergeCells('D2:' . $columnLetter . '2');

                // Set row heights
                $event->sheet->getDefaultRowDimension()->setRowHeight(30);
                $event->sheet->getRowDimension(1)->setRowHeight(50);
                $event->sheet->getRowDimension(2)->setRowHeight(80);
            },
        ];
    }

    public function headings(): array
    {
        return ['1'];
    }

    /**
     * Convert column number to Excel column letter
     */
    protected function getColumnLetter($columnNumber): string
    {
        $letter = '';
        while ($columnNumber > 0) {
            $columnNumber--;
            $letter = chr(65 + ($columnNumber % 26)) . $letter;
            $columnNumber = intval($columnNumber / 26);
        }
        return $letter;
    }
}