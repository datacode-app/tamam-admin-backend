<?php

namespace Modules\Rental\Entities;

use App\CentralLogics\Helpers;
use App\Models\Storage;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RentalEmailTemplate extends Model
{
    use HasFactory;
    protected $appends = ['image_full_url','logo_full_url','icon_full_url'];

    public function getImageFullUrlAttribute(){
        $value = $this->image;
        if (count($this->storage) > 0) {
            foreach ($this->storage as $storage) {
                if ($storage['key'] == 'image') {
                    return Helpers::get_full_url('email_template',$value,$storage['value']);
                }
            }
        }

        return Helpers::get_full_url('email_template',$value,'public');
    }

    public function getLogoFullUrlAttribute(){
        $value = $this->logo;
        if (count($this->storage) > 0) {
            foreach ($this->storage as $storage) {
                if ($storage['key'] == 'logo') {
                    return Helpers::get_full_url('email_template',$value,$storage['value']);
                }
            }
        }

        return Helpers::get_full_url('email_template',$value,'public');
    }

    public function getIconFullUrlAttribute(){
        $value = $this->icon;
        if (count($this->storage) > 0) {
            foreach ($this->storage as $storage) {
                if ($storage['key'] == 'icon') {
                    return Helpers::get_full_url('email_template',$value,$storage['value']);
                }
            }
        }

        return Helpers::get_full_url('email_template',$value,'public');
    }
    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function getTitleAttribute($value){
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'title') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    public function getBodyAttribute($value){
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'body') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    public function getButtonNameAttribute($value){
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'button_name') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    public function getFooterTextAttribute($value){
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'footer_text') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    public function getCopyrightTextAttribute($value){
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'copyright_text') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    public function storage()
    {
        return $this->morphMany(Storage::class, 'data');
    }
    protected static function booted()
    {
        static::addGlobalScope('storage', function ($builder) {
            $builder->with('storage');
        });
        // TRANSLATION FIX: Global scope removed - use TranslationLoadingTrait in controllers instead
        // Original global scope caused translation filtering issues for Kurdish/Arabic languages
    }

    protected static function boot()
    {
        parent::boot();
        static::saved(function ($model) {
            if($model->isDirty('image')){
                $value = Helpers::getDisk();

                DB::table('storages')->updateOrInsert([
                    'data_type' => get_class($model),
                    'data_id' => $model->id,
                    'key' => 'image',
                ], [
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            if($model->isDirty('logo')){
                $value = Helpers::getDisk();

                DB::table('storages')->updateOrInsert([
                    'data_type' => get_class($model),
                    'data_id' => $model->id,
                    'key' => 'logo',
                ], [
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            if($model->isDirty('icon')){
                $value = Helpers::getDisk();

                DB::table('storages')->updateOrInsert([
                    'data_type' => get_class($model),
                    'data_id' => $model->id,
                    'key' => 'icon',
                ], [
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

    }
}
