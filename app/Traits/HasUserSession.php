<?php

namespace App\Traits;

trait HasUserSession
{
    /**
     * Initialize user session data safely
     *
     * @return bool Returns true if user session is valid, false otherwise
     */
    protected function initializeUserSession(): bool
    {
        $this->user = session('user', []);
        
        if (!isset($this->user['office']['id'])) {
            $this->office = null;
            return false;
        }
        
        $this->office = $this->user['office']['id'];
        return true;
    }
    
    /**
     * Get user office ID safely
     *
     * @return int|null
     */
    protected function getUserOfficeId(): ?int
    {
        return $this->user['office']['id'] ?? null;
    }
    
    /**
     * Check if user has valid office data
     *
     * @return bool
     */
    protected function hasValidOfficeData(): bool
    {
        return isset($this->user['office']['id']);
    }
}