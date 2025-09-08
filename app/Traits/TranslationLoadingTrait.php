<?php

namespace App\Traits;

use App\Models\Translation;

/**
 * BULLETPROOF TRANSLATION LOADING TRAIT
 * 
 * Prevents translation display issues by ensuring translations
 * are ALWAYS loaded, even when eager loading fails.
 * 
 * Usage in any Controller:
 * 1. use TranslationLoadingTrait;
 * 2. $model = $this->loadWithTranslations(Model::class, $id);
 */
trait TranslationLoadingTrait
{
    /**
     * Load model with bulletproof translation loading
     * 
     * @param string $modelClass Full model class name (e.g., 'App\Models\Store')
     * @param int $id Model ID
     * @param array $with Additional relationships to load
     * @return mixed Model instance with guaranteed translation loading
     */
    public function loadWithTranslations(string $modelClass, int $id, array $with = [])
    {
        // Add translations to the relationships to load
        $relationships = array_merge($with, ['translations']);
        
        // Load model with translations and other relationships
        // 🚨 FORCE EXPLICIT TRANSLATION LOADING to bypass all global scopes
        $model = $modelClass::withoutGlobalScopes()
            ->findOrFail($id);
        
        // Manually load all translations without any filtering
        $allTranslations = Translation::where('translationable_type', $modelClass)
            ->where('translationable_id', $id)
            ->get();
        $model->setRelation('translations', $allTranslations);
        
        // 🛡️ BULLETPROOF FALLBACK: Manually load translations if eager loading failed
        if ($model->translations->isEmpty()) {
            $translations = Translation::where('translationable_type', $modelClass)
                ->where('translationable_id', $id)
                ->get();
            
            if ($translations->count() > 0) {
                $model->setRelation('translations', $translations);
                
                // Log warning for monitoring
                \Log::warning("Translation eager loading failed for {$modelClass} ID {$id}, used fallback", [
                    'model_class' => $modelClass,
                    'model_id' => $id,
                    'translations_count' => $translations->count()
                ]);
            }
        }
        
        return $model;
    }
    
    /**
     * Load multiple models with bulletproof translation loading
     * 
     * @param string $modelClass Full model class name
     * @param array $ids Array of model IDs
     * @param array $with Additional relationships to load
     * @return \Illuminate\Support\Collection Collection of models with translations
     */
    public function loadMultipleWithTranslations(string $modelClass, array $ids, array $with = [])
    {
        $relationships = array_merge($with, ['translations']);
        
        $models = $modelClass::withoutGlobalScope('translate')
            ->with($relationships)
            ->whereIn('id', $ids)
            ->get();
        
        // Check each model for translation loading issues
        foreach ($models as $model) {
            if ($model->translations->isEmpty()) {
                $translations = Translation::where('translationable_type', $modelClass)
                    ->where('translationable_id', $model->id)
                    ->get();
                
                if ($translations->count() > 0) {
                    $model->setRelation('translations', $translations);
                    
                    \Log::warning("Translation eager loading failed for {$modelClass} ID {$model->id}, used fallback");
                }
            }
        }
        
        return $models;
    }
    
    /**
     * Verify translation loading for debugging
     * 
     * @param mixed $model Model instance to verify
     * @return array Debug information about translations
     */
    public function verifyTranslationLoading($model): array
    {
        $modelClass = get_class($model);
        
        // Count translations in relationship
        $relationshipCount = $model->translations->count();
        
        // Count translations in database
        $databaseCount = Translation::where('translationable_type', $modelClass)
            ->where('translationable_id', $model->id)
            ->count();
        
        return [
            'model_class' => $modelClass,
            'model_id' => $model->id,
            'relationship_count' => $relationshipCount,
            'database_count' => $databaseCount,
            'loading_successful' => $relationshipCount === $databaseCount && $databaseCount > 0,
            'has_translations' => $databaseCount > 0,
            'eager_loading_failed' => $databaseCount > 0 && $relationshipCount === 0
        ];
    }
}