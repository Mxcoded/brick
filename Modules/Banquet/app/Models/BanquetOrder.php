<?php

namespace Modules\Banquet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

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
    /**
     * Scope to fetch upcoming banquet events.
     */
    public function scopeUpcoming($query)
    {
        return $query->select(
            'banquet_orders.id',
            'banquet_orders.order_id',
            'banquet_orders.customer_id',
            'banquet_orders.status',
            DB::raw('MIN(banquet_order_days.event_date) as earliest_event_date')
        )
            ->join('banquet_order_days', 'banquet_orders.id', '=', 'banquet_order_days.banquet_order_id')
            ->whereNotIn('banquet_orders.status', ['Completed', 'Cancelled'])
            ->groupBy('banquet_orders.id', 'banquet_orders.order_id', 'banquet_orders.customer_id', 'banquet_orders.status')
            ->having('earliest_event_date', '>=', now()->toDateString())
            ->orderBy('earliest_event_date', 'asc')
            ->with('customer');
    }
}
