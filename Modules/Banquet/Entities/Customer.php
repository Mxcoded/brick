<?php
namespace Modules\Banquet\Entities;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
protected $guarded = [];

public function banquets()
{
return $this->hasMany(Banquet::class);
}
}