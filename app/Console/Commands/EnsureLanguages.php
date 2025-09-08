<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BusinessSetting;

class EnsureLanguages extends Command
{
    protected $signature = 'tamam:ensure-languages {--locales=ckb,ar}';
    protected $description = 'Ensure BusinessSetting language includes required locales for vendor translations';

    public function handle(): int
    {
        $locales = array_values(array_filter(array_map('trim', explode(',', (string)$this->option('locales')))));
        $row = BusinessSetting::firstOrNew(['key' => 'language']);
        $current = [];
        if ($row->exists && is_string($row->value)) {
            $decoded = json_decode($row->value, true);
            if (is_array($decoded)) {
                $current = $decoded;
            }
        }
        $merged = array_values(array_unique(array_merge($current, $locales)));
        $row->value = json_encode($merged, JSON_UNESCAPED_UNICODE);
        $row->save();
        $this->info('language set to: '.json_encode($merged));
        return Command::SUCCESS;
    }
}


