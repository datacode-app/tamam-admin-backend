<?php

/**
 * Real Translation Quality Checker
 * Check actual translation content vs just file sizes
 */

echo "🔍 REAL Translation Content Analysis\n";
echo "====================================\n\n";

$en = include('resources/lang/en/messages.php');
$ckb = include('resources/lang/ckb/messages.php'); 
$ar = include('resources/lang/ar/messages.php');

echo "File sizes:\n";
echo "English: " . count($en) . " keys\n";
echo "Kurdish: " . count($ckb) . " keys\n";
echo "Arabic: " . count($ar) . " keys\n\n";

// Check how many are actually translated vs just English fallbacks
$ckb_english_fallbacks = 0;
$ar_english_fallbacks = 0;
$ckb_actually_translated = 0;
$ar_actually_translated = 0;
$ckb_empty = 0;
$ar_empty = 0;

echo "SAMPLE CHECK (first 30 translations):\n";
echo "=====================================\n";
printf("%-30s | %-25s | %-25s | %-25s\n", "KEY", "ENGLISH", "KURDISH", "ARABIC");
echo str_repeat("-", 110) . "\n";

// Sample check - compare first 30 translations
$sample_keys = array_slice(array_keys($en), 0, 30);

foreach($sample_keys as $key) {
    $en_val = $en[$key] ?? '';
    $ckb_val = $ckb[$key] ?? '';
    $ar_val = $ar[$key] ?? '';
    
    printf("%-30s | %-25s | %-25s | %-25s\n", 
        substr($key, 0, 29), 
        substr($en_val, 0, 24), 
        substr($ckb_val, 0, 24), 
        substr($ar_val, 0, 24)
    );
    
    // Kurdish analysis
    if (empty($ckb_val)) {
        $ckb_empty++;
    } elseif ($ckb_val === $en_val) {
        $ckb_english_fallbacks++;
    } else {
        $ckb_actually_translated++;
    }
    
    // Arabic analysis
    if (empty($ar_val)) {
        $ar_empty++;
    } elseif ($ar_val === $en_val) {
        $ar_english_fallbacks++;
    } else {
        $ar_actually_translated++;
    }
}

echo "\n📊 SAMPLE ANALYSIS (first 30 keys):\n";
echo "====================================\n";
echo "KURDISH:\n";
echo "  ✅ Actually translated: $ckb_actually_translated/30 (" . round($ckb_actually_translated/30*100, 1) . "%)\n";
echo "  🔄 English fallbacks: $ckb_english_fallbacks/30 (" . round($ckb_english_fallbacks/30*100, 1) . "%)\n";
echo "  ❌ Missing/Empty: $ckb_empty/30 (" . round($ckb_empty/30*100, 1) . "%)\n\n";

echo "ARABIC:\n";
echo "  ✅ Actually translated: $ar_actually_translated/30 (" . round($ar_actually_translated/30*100, 1) . "%)\n";
echo "  🔄 English fallbacks: $ar_english_fallbacks/30 (" . round($ar_english_fallbacks/30*100, 1) . "%)\n";
echo "  ❌ Missing/Empty: $ar_empty/30 (" . round($ar_empty/30*100, 1) . "%)\n\n";

// Now do a full analysis
echo "🔍 FULL FILE ANALYSIS:\n";
echo "======================\n";

$ckb_total_translated = 0;
$ckb_total_fallbacks = 0;
$ckb_total_empty = 0;

$ar_total_translated = 0;
$ar_total_fallbacks = 0;
$ar_total_empty = 0;

foreach($en as $key => $en_value) {
    $ckb_val = $ckb[$key] ?? '';
    $ar_val = $ar[$key] ?? '';
    
    // Kurdish analysis
    if (empty($ckb_val)) {
        $ckb_total_empty++;
    } elseif ($ckb_val === $en_value) {
        $ckb_total_fallbacks++;
    } else {
        $ckb_total_translated++;
    }
    
    // Arabic analysis  
    if (empty($ar_val)) {
        $ar_total_empty++;
    } elseif ($ar_val === $en_value) {
        $ar_total_fallbacks++;
    } else {
        $ar_total_translated++;
    }
}

$total_keys = count($en);

echo "KURDISH FULL ANALYSIS:\n";
echo "  ✅ Actually translated: $ckb_total_translated/$total_keys (" . round($ckb_total_translated/$total_keys*100, 2) . "%)\n";
echo "  🔄 English fallbacks: $ckb_total_fallbacks/$total_keys (" . round($ckb_total_fallbacks/$total_keys*100, 2) . "%)\n";
echo "  ❌ Missing/Empty: $ckb_total_empty/$total_keys (" . round($ckb_total_empty/$total_keys*100, 2) . "%)\n\n";

echo "ARABIC FULL ANALYSIS:\n";
echo "  ✅ Actually translated: $ar_total_translated/$total_keys (" . round($ar_total_translated/$total_keys*100, 2) . "%)\n";
echo "  🔄 English fallbacks: $ar_total_fallbacks/$total_keys (" . round($ar_total_fallbacks/$total_keys*100, 2) . "%)\n";
echo "  ❌ Missing/Empty: $ar_total_empty/$total_keys (" . round($ar_total_empty/$total_keys*100, 2) . "%)\n\n";

// Show some examples of what we consider "actually translated"
echo "🌟 EXAMPLES OF ACTUAL TRANSLATIONS:\n";
echo "===================================\n";

$translation_examples = 0;
foreach($en as $key => $en_value) {
    $ckb_val = $ckb[$key] ?? '';
    $ar_val = $ar[$key] ?? '';
    
    if ($translation_examples >= 5) break;
    
    if ($ckb_val !== $en_value && !empty($ckb_val) && $ar_val !== $en_value && !empty($ar_val)) {
        echo "Key: $key\n";
        echo "  EN: $en_value\n";
        echo "  KU: $ckb_val\n";
        echo "  AR: $ar_val\n\n";
        $translation_examples++;
    }
}

if ($translation_examples == 0) {
    echo "❌ No examples found where both languages are actually translated!\n\n";
}

// Final verdict
echo "🎯 FINAL VERDICT:\n";
echo "=================\n";

if ($ckb_total_translated < ($total_keys * 0.5)) {
    echo "❌ KURDISH: Seriously under-translated! Only " . round($ckb_total_translated/$total_keys*100, 1) . "% actually translated\n";
} elseif ($ckb_total_translated < ($total_keys * 0.8)) {
    echo "⚠️ KURDISH: Partially translated. " . round($ckb_total_translated/$total_keys*100, 1) . "% actually translated\n";
} else {
    echo "✅ KURDISH: Well translated! " . round($ckb_total_translated/$total_keys*100, 1) . "% actually translated\n";
}

if ($ar_total_translated < ($total_keys * 0.5)) {
    echo "❌ ARABIC: Seriously under-translated! Only " . round($ar_total_translated/$total_keys*100, 1) . "% actually translated\n";
} elseif ($ar_total_translated < ($total_keys * 0.8)) {
    echo "⚠️ ARABIC: Partially translated. " . round($ar_total_translated/$total_keys*100, 1) . "% actually translated\n";
} else {
    echo "✅ ARABIC: Well translated! " . round($ar_total_translated/$total_keys*100, 1) . "% actually translated\n";
}