<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\BusinessSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update business_settings system_language from 'ku' to 'ckb' for Sorani Kurdish
        $systemLanguage = BusinessSetting::where('key', 'system_language')->first();
        
        if ($systemLanguage) {
            $languages = json_decode($systemLanguage->value, true);
            
            // Find and update Kurdish language code
            foreach ($languages as &$language) {
                if (isset($language['code']) && $language['code'] === 'ku') {
                    $language['code'] = 'ckb';
                    // Ensure it's marked as RTL
                    $language['direction'] = 'rtl';
                    // Update name to be more specific
                    if (!isset($language['name']) || $language['name'] === 'Kurdish') {
                        $language['name'] = 'Kurdish Sorani';
                    }
                }
            }
            
            $systemLanguage->update(['value' => json_encode($languages)]);
        }
        
        // Update business_settings language array
        $languageList = BusinessSetting::where('key', 'language')->first();
        
        if ($languageList) {
            $languages = json_decode($languageList->value, true);
            
            // Replace 'ku' with 'ckb' in the language array
            $updatedLanguages = array_map(function($lang) {
                return $lang === 'ku' ? 'ckb' : $lang;
            }, $languages);
            
            $languageList->update(['value' => json_encode($updatedLanguages)]);
        }
        
        // Update any translation records that might use 'ku'
        if (Schema::hasTable('translations')) {
            \DB::table('translations')
                ->where('locale', 'ku')
                ->update(['locale' => 'ckb']);
        }
        
        // Update user language preferences
        if (Schema::hasColumn('users', 'current_language_key')) {
            \DB::table('users')
                ->where('current_language_key', 'ku')
                ->update(['current_language_key' => 'ckb']);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert business_settings system_language from 'ckb' back to 'ku'
        $systemLanguage = BusinessSetting::where('key', 'system_language')->first();
        
        if ($systemLanguage) {
            $languages = json_decode($systemLanguage->value, true);
            
            // Find and revert Kurdish language code
            foreach ($languages as &$language) {
                if (isset($language['code']) && $language['code'] === 'ckb') {
                    $language['code'] = 'ku';
                }
            }
            
            $systemLanguage->update(['value' => json_encode($languages)]);
        }
        
        // Revert business_settings language array
        $languageList = BusinessSetting::where('key', 'language')->first();
        
        if ($languageList) {
            $languages = json_decode($languageList->value, true);
            
            // Replace 'ckb' with 'ku' in the language array
            $updatedLanguages = array_map(function($lang) {
                return $lang === 'ckb' ? 'ku' : $lang;
            }, $languages);
            
            $languageList->update(['value' => json_encode($updatedLanguages)]);
        }
        
        // Revert translation records
        if (Schema::hasTable('translations')) {
            \DB::table('translations')
                ->where('locale', 'ckb')
                ->update(['locale' => 'ku']);
        }
        
        // Revert user language preferences
        if (Schema::hasColumn('users', 'current_language_key')) {
            \DB::table('users')
                ->where('current_language_key', 'ckb')
                ->update(['current_language_key' => 'ku']);
        }
    }
};