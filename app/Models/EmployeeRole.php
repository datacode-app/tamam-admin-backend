<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EmployeeRole extends Model
{
    use HasFactory;

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function getNameAttribute($value){
        if (count($this->translations ?? []) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'name') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    protected static function booted()
    {
        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function($query){
                $currentLocale = app()->getLocale();
                $kurdishLocales = ['ku', 'ckb', 'kmr', 'kurdish', 'sorani'];
                if (in_array($currentLocale, $kurdishLocales, true)) {
                    return $query->whereIn('locale', $kurdishLocales);
                }
                return $query->where('locale', $currentLocale);
            }]);
        });
    }
}
