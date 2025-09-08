<?php

// FLEET MANAGEMENT MODEL - TEMPORARILY DISABLED FOR STABLE RELEASE
// TODO: Re-enable after completing database migrations and proper testing
// The database table 'business_delivery_fleet' does not exist yet and would cause errors in production

/*
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class BusinessDeliveryFleet
 *
 * @property int $id
 * @property int $store_id
 * @property string $driver_name
 * @property string $driver_phone
 * @property string|null $driver_email
 * @property string|null $vehicle_type
 * @property string|null $vehicle_number
 * @property string|null $license_number
 * @property bool $is_active
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read Store $store
 * @property-read \Illuminate\Database\Eloquent\Collection|Order[] $orders
 * @property-read \Illuminate\Database\Eloquent\Collection|Order[] $completedOrders
 */
class BusinessDeliveryFleet extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'business_delivery_fleet';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'store_id',
        'driver_name',
        'driver_phone',
        'driver_email',
        'vehicle_type',
        'vehicle_number',
        'license_number',
        'is_active',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the store that owns this business driver.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get all orders assigned to this business driver.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'business_driver_id');
    }

    /**
     * Get completed orders for this business driver.
     */
    public function completedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'business_driver_id')
                    ->where('order_status', 'delivered');
    }

    /**
     * Scope a query to only include active drivers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include drivers for a specific store.
     */
    public function scopeForStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    /**
     * Get the driver's full display name with vehicle info.
     */
    public function getDisplayNameAttribute(): string
    {
        $name = $this->driver_name;
        if ($this->vehicle_type) {
            $name .= " ({$this->vehicle_type})";
        }
        return $name;
    }

    /**
     * Get the total number of completed orders for this driver.
     */
    public function getCompletedOrdersCountAttribute(): int
    {
        return $this->completedOrders()->count();
    }

    /**
     * Check if this driver is currently available (active and no ongoing orders).
     */
    public function getIsAvailableAttribute(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Check if driver has any ongoing orders
        $ongoingOrders = $this->orders()
            ->whereIn('order_status', ['confirmed', 'processing', 'handover', 'picked_up'])
            ->count();

        return $ongoingOrders === 0;
    }

    /**
     * Get formatted phone number for display.
     */
    public function getFormattedPhoneAttribute(): string
    {
        // Simple formatting - can be enhanced based on requirements
        return $this->driver_phone;
    }

    /**
     * Get vehicle information as a formatted string.
     */
    public function getVehicleInfoAttribute(): ?string
    {
        if (!$this->vehicle_type) {
            return null;
        }

        $info = $this->vehicle_type;
        if ($this->vehicle_number) {
            $info .= " - {$this->vehicle_number}";
        }

        return $info;
    }
}