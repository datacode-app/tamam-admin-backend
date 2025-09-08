<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParcelDeliveryInstruction extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'id' => 'integer',
        'status' => 'integer',
    ];

    public function getInstructionAttribute($value){
        if (count($this->translations ?? []) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'instruction') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    protected static function booted()
    {
        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
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
