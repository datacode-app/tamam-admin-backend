<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Exception;

/**
 * TRANSACTION MANAGER
 * 
 * Ensures atomic operations - either everything succeeds or everything rolls back.
 * Prevents partial imports that leave the database in an inconsistent state.
 */
class TransactionManager
{
    private $backupData = [];

    /**
     * EXECUTE ATOMIC IMPORT
     * All operations happen within a single database transaction
     */
    public function executeAtomicImport(array $data, callable $importFunction): array
    {
        // Create backup point
        $this->createBackupPoint();

        try {
            return DB::transaction(function() use ($data, $importFunction) {
                // Execute the import function within transaction
                return $importFunction($data);
            });

        } catch (Exception $e) {
            // Transaction automatically rolls back on exception
            $this->logRollback($e);
            
            // Verify rollback completed
            $this->verifyRollback();
            
            throw $e;
        }
    }

    /**
     * CREATE BACKUP POINT
     * Record current state for verification
     */
    private function createBackupPoint(): void
    {
        $this->backupData = [
            'timestamp' => now()->toDateTimeString(),
            'vendor_count' => DB::table('vendors')->count(),
            'store_count' => DB::table('stores')->count(),
            'translation_count' => DB::table('translations')->count(),
            'store_config_count' => DB::table('store_configs')->count(),
        ];
    }

    /**
     * VERIFY ROLLBACK
     * Ensure database is in the same state as before the failed import
     */
    private function verifyRollback(): void
    {
        $currentCounts = [
            'vendor_count' => DB::table('vendors')->count(),
            'store_count' => DB::table('stores')->count(),
            'translation_count' => DB::table('translations')->count(),
            'store_config_count' => DB::table('store_configs')->count(),
        ];

        $discrepancies = [];
        
        foreach ($currentCounts as $table => $count) {
            if ($count != $this->backupData[$table]) {
                $discrepancies[] = "{$table}: expected {$this->backupData[$table]}, found {$count}";
            }
        }

        if (!empty($discrepancies)) {
            \Log::error('Transaction rollback verification failed', [
                'discrepancies' => $discrepancies,
                'backup' => $this->backupData,
                'current' => $currentCounts
            ]);
            
            throw new Exception('Transaction rollback failed - database may be in inconsistent state: ' . implode(', ', $discrepancies));
        }
    }

    /**
     * LOG ROLLBACK
     */
    private function logRollback(Exception $e): void
    {
        \Log::warning('Import transaction rolled back', [
            'error' => $e->getMessage(),
            'backup_point' => $this->backupData,
            'rollback_time' => now()->toDateTimeString()
        ]);
    }

    /**
     * EXECUTE WITH SAVEPOINT
     * For nested transactions within the main transaction
     */
    public function executeWithSavepoint(string $savepointName, callable $operation)
    {
        DB::statement("SAVEPOINT {$savepointName}");
        
        try {
            return $operation();
        } catch (Exception $e) {
            DB::statement("ROLLBACK TO SAVEPOINT {$savepointName}");
            throw $e;
        } finally {
            DB::statement("RELEASE SAVEPOINT {$savepointName}");
        }
    }

    /**
     * BATCH OPERATION HELPER
     * Process large datasets in smaller batches within transaction
     */
    public function executeBatchOperation(array $data, callable $batchProcessor, int $batchSize = 100): array
    {
        $results = [];
        $batches = array_chunk($data, $batchSize);
        
        foreach ($batches as $batchIndex => $batch) {
            $savepointName = "batch_" . $batchIndex;
            
            $batchResult = $this->executeWithSavepoint($savepointName, function() use ($batch, $batchProcessor) {
                return $batchProcessor($batch);
            });
            
            $results[] = $batchResult;
        }
        
        return $results;
    }
}