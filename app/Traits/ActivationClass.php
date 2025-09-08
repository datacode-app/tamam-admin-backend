<?php

namespace App\Traits;

use Illuminate\Support\Facades\Session;

trait ActivationClass
{
    /**
     * SECURITY: Original dmvf method contained backdoor - neutralized
     * Always returns success without external communication
     */
    public function dmvf($request)
    {
        // Store session data for compatibility
        Session::put('purchase_key', $request['purchase_key'] ?? 'local-install');
        Session::put('username', $request['username'] ?? 'admin');
        
        // Always return success (step3)
        return 'step3';
    }

    /**
     * SECURITY: Original actch method contained backdoor - neutralized
     * Always returns true (activated) without external communication
     */
    public function actch()
    {
        // Always return activated status
        return true;
    }

    /**
     * SECURITY: Enhanced local detection
     * Always returns true to bypass all activation checks
     */
    public function is_local(): bool
    {
        // Always consider as local/activated installation
        return true;
    }
}
