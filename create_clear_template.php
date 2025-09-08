<?php
/**
 * Create the clearest possible multilingual item template
 */

$template_content = [
    // Header row with required field indicators
    [
        'Id*',
        'Name*',
        'name_ku',
        'name_ar',
        'Description',
        'description_ku',
        'description_ar',
        'CategoryId*',
        'SubCategoryId*',
        'Price*',
        'Discount*',
        'DiscountType*',
        'StoreId*',
        'ModuleId*',
        'Image',
        'Images',
        'UnitId',
        'Stock',
        'AvailableTimeStarts',
        'AvailableTimeEnds',
        'Variations',
        'ChoiceOptions',
        'AddOns',
        'Attributes',
        'Status',
        'Veg',
        'Recommended'
    ],
    // Instructions row
    [
        '(Required: Unique ID number)',
        '(Required: Item name in English)',
        '(Kurdish name - optional)',
        '(Arabic name - optional)',
        '(English description - optional)',
        '(Kurdish description - optional)',
        '(Arabic description - optional)',
        '(Required: Main category ID)',
        '(Required: Subcategory ID)', 
        '(Required: Price in currency)',
        '(Required: Discount amount, use 0 for no discount)',
        '(Required: percent or fixed)',
        '(Required: Store/Restaurant ID)',
        '(Required: Module ID - 1=Food, 2=Grocery, etc)',
        '(Image filename - optional)',
        '(JSON array of images - optional)',
        '(Unit ID - optional)',
        '(Stock quantity - optional)',
        '(Start time HH:MM:SS - optional)',
        '(End time HH:MM:SS - optional)',
        '(JSON variations - optional)',
        '(JSON choice options - optional)',
        '(JSON addon IDs [1,2,3] - optional)',
        '(JSON attributes - optional)',
        '(active or inactive - optional, default: active)',
        '(yes or no - optional, default: no)',
        '(yes or no - optional, default: no)'
    ],
    // Example 1 - Kurdish dish
    [
        '1',
        'Kurdish Kabab Special',
        'کەباب تایبەتی کوردی',
        'كباب كردي خاص',
        'Traditional Kurdish grilled meat kabab with rice and vegetables',
        'کەباب گوشتی کوردی نەریتی لەگەل برنج و سەوزیجات',
        'كباب اللحم الكردي التقليدي مع الأرز والخضروات',
        '1',
        '2',
        '25000',
        '10',
        'percent',
        '1',
        '1',
        'kurdish_kabab.jpg',
        '[]',
        '1',
        '50',
        '09:00:00',
        '23:00:00',
        '[]',
        '[]',
        '[1,2,3]',
        '[]',
        'active',
        'no',
        'yes'
    ],
    // Example 2 - Simple item with minimal fields
    [
        '2',
        'Arabic Tea',
        'چایی عەرەبی',
        'الشاي العربي',
        'Traditional Arabic tea served hot',
        'چایی عەرەبی نەریتی گەرم',
        'الشاي العربي التقليدي يقدم ساخناً',
        '3',
        '4',
        '5000',
        '0',
        'percent',
        '1',
        '1',
        '',
        '[]',
        '',
        '',
        '',
        '',
        '[]',
        '[]',
        '[]',
        '[]',
        'active',
        'yes',
        'no'
    ]
];

// Create CSV content
$csvContent = '';
foreach ($template_content as $row) {
    $csvContent .= implode(',', array_map(function($field) {
        // Wrap fields containing commas, quotes, or newlines in quotes
        if (strpos($field, ',') !== false || strpos($field, '"') !== false || strpos($field, "\n") !== false) {
            return '"' . str_replace('"', '""', $field) . '"';
        }
        return $field;
    }, $row)) . "\n";
}

// Write to file
file_put_contents('/Users/hooshyar/Desktop/development/tamam-workspace/Admin-with-Rental/public/assets/items_multilang_template_simple.csv', $csvContent);

echo "✅ SUPER CLEAR TEMPLATE CREATED\n";
echo "📝 Location: public/assets/items_multilang_template_simple.csv\n";
echo "\n";
echo "🔥 KEY FEATURES:\n";
echo "   ⭐ Required fields marked with * in header\n";
echo "   📖 Full instruction row explaining each field\n";
echo "   ✨ Two complete examples with all translations\n";
echo "   🎯 One complex example, one simple example\n";
echo "   💡 Clear guidance on optional vs required fields\n";
echo "\n";
echo "📋 REQUIRED FIELDS SUMMARY:\n";
echo "   1. Id* - Unique identifier number\n";
echo "   2. Name* - Item name in English\n";
echo "   3. CategoryId* - Main category ID\n";
echo "   4. SubCategoryId* - Subcategory ID\n";
echo "   5. Price* - Price in local currency\n";
echo "   6. Discount* - Discount amount (use 0 for no discount)\n";
echo "   7. DiscountType* - 'percent' or 'fixed'\n";
echo "   8. StoreId* - Store/Restaurant ID\n";
echo "   9. ModuleId* - Module ID (1=Food, 2=Grocery, etc)\n";
echo "\n";
echo "🌍 TRANSLATION FIELDS (Optional):\n";
echo "   - name_ku: Kurdish name\n";
echo "   - name_ar: Arabic name\n";
echo "   - description_ku: Kurdish description\n";
echo "   - description_ar: Arabic description\n";