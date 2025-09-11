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
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

/**
 * Enhanced Item List Export with Multilingual Support
 * 
 * Exports items with translations in Arabic (ar), Kurdish Sorani (ckb), and English (en)
 * Utilizes the existing MultilingualExportService for consistent translation handling
 */
class ItemListMultilingualExport implements FromView, ShouldAutoSize, WithStyles, WithColumnWidths, WithHeadings, WithEvents
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
        // Import template columns: 29 columns (A to AC)
        // Includes all import fields + multilingual extensions (removed name_en, description_en)
        $this->columnCount = 29;
    }

    public function view(): View
    {
        // Enhance the data with multilingual information
        $enhancedData = $this->data;
        $enhancedData['multilingual_items'] = $this->extractMultilingualData();
        $enhancedData['supported_languages'] = $this->supportedLanguages;
        
        return view('file-exports.item-list-multilingual-import-compatible', [
            'data' => $enhancedData,
        ]);
    }

    /**
     * Extract multilingual data for all items with import-compatible format
     */
    protected function extractMultilingualData()
    {
        return $this->data['data']->map(function($item) {
            // Get multilingual data using the service
            $multilingual = $this->multilingualService->extractMultilingualData($item, 'Item');
            
            // Merge item data with multilingual fields
            $itemArray = $item->toArray();
            
            // Add multilingual fields (import-compatible format)
            $itemArray['name_en'] = $multilingual['name_en'] ?? $item->name;
            $itemArray['name_ar'] = $multilingual['name_ar'] ?? '';
            $itemArray['name_ckb'] = $multilingual['name_ckb'] ?? '';
            $itemArray['description_en'] = $multilingual['description_en'] ?? $item->description;
            $itemArray['description_ar'] = $multilingual['description_ar'] ?? '';
            $itemArray['description_ckb'] = $multilingual['description_ckb'] ?? '';
            
            // Add import-compatible fields that might be missing
            $itemArray['sub_category_id'] = $item->sub_category_id ?? '';
            $itemArray['maximum_cart_quantity'] = $item->maximum_cart_quantity ?? 999;
            $itemArray['unit_id'] = $item->unit_id ?? '';
            
            // Include the original item object for relationships
            $itemArray['original_item'] = $item;
            
            return $itemArray;
        });
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10, // Id
            'B' => 25, // Name
            'C' => 40, // Description
            'D' => 15, // CategoryId
            'E' => 15, // SubCategoryId
            'F' => 15, // StoreId
            'G' => 15, // Price
            'H' => 15, // DiscountType
            'I' => 15, // Discount
            'J' => 15, // Unit
            'K' => 15, // Stock
            'L' => 20, // MaxOrderQuantity
            'M' => 15, // VegNonVeg
            'N' => 20, // Image
            'O' => 20, // AvailableTimeStarts
            'P' => 20, // AvailableTimeEnds
            'Q' => 15, // Status
            'R' => 15, // RecommendedFlag
            'S' => 15, // PopularFlag
            'T' => 15, // UnitId
            'U' => 30, // Variations
            'V' => 30, // ChoiceOptions
            'W' => 30, // AddOns
            'X' => 30, // Attributes
            'Y' => 15, // ModuleId
            'Z' => 25, // name_ar
            'AA' => 25, // name_ckb
            'AB' => 40, // description_ar
            'AC' => 40, // description_ckb
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

    /**
     * Set images in the export
     */
    public function setImage($workSheet) 
    {
        $this->data['data']->each(function($item, $index) use($workSheet) {
            $drawing = new Drawing();
            $drawing->setName($item->name);
            $drawing->setDescription($item->name);
            $imagePath = is_file(storage_path('app/public/product/'.$item->image)) 
                ? storage_path('app/public/product/'.$item->image)
                : public_path('/assets/admin/img/160x160/img2.jpg');
            $drawing->setPath($imagePath);
            $drawing->setHeight(25);
            $index += 4;
            $drawing->setCoordinates("B$index");
            $drawing->setWorksheet($workSheet);
        });
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
                
                // Add images
                $workSheet = $event->sheet->getDelegate();
                $this->setImage($workSheet);
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