<?php
// Remove ku from system_language
$systemLang = \App\Models\BusinessSetting::where('key', 'system_language')->first();
if ($systemLang) {
    $languages = json_decode($systemLang->value, true);
    $languages = array_filter($languages, function($lang) {
        return \!isset($lang['code']) || $lang['code'] \!== 'ku';
    });
    $languages = array_values($languages);
    $systemLang->update(['value' => json_encode($languages)]);
    echo 'Removed ku from admin system_language display' . PHP_EOL;
}

// Remove ku from language list
$langList = \App\Models\BusinessSetting::where('key', 'language')->first();
if ($langList) {
    $languages = json_decode($langList->value, true);
    $languages = array_filter($languages, function($lang) {
        return $lang \!== 'ku';
    });
    $languages = array_values($languages);
    $langList->update(['value' => json_encode($languages)]);
    echo 'Cleaned language list: ' . json_encode($languages) . PHP_EOL;
}
echo 'Admin cleanup complete\!' . PHP_EOL;
