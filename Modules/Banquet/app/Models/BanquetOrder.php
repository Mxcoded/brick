<?php

namespace Modules\Banquet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BanquetOrder extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'order_id',
        'preparation_date',
        'customer_id',
        'contact_person_name',
        'department',
        'contact_person_phone',
        'contact_person_email',
        'referred_by',
        'contact_person_name_ii',
        'contact_person_phone_ii',
        'contact_person_email_ii',
        'total_revenue',
        'expenses',
        'profit_margin',
        'status',
        'hall_rental_fees',
    ];
    protected $casts = [
        'preparation_date' => 'date',
    ];
    /**
     * Get the customer that owns the banquet order.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the event days for the banquet order.
     */
    public function eventDays()
    {
        return $this->hasMany(BanquetOrderDay::class);
    }
    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'order_id'; // Use order_id instead of id for route binding
    }
}
