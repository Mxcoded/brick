<?php

namespace Modules\Staff\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Tasks\Models\Task;

// use Modules\Staff\Database\Factories\EmployeeFactory;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Employee extends Model implements AuditableContract
{
    use HasFactory, Auditable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'place_of_birth',
        'state_of_origin',
        'lga',
        'nationality',
        'gender',
        'date_of_birth',
        'marital_status',
        'blood_group',
        'genotype',
        'phone_number',
        'position',
        'residential_address',
        'next_of_kin_name',
        'next_of_kin_phone',
        'ice_contact_name',
        'ice_contact_phone',
        'profile_image',
        'cv_path',
        'status',
        'start_date',          // Date employed
        'end_date',            // Date departed (nullable if still active)
        'note_for_leaving',    // Reason/note for leaving
        'leaving_reason',      // Enum: resignation, sack, transfer
        'branch_name',         // Branch name
        'resignation_letter',  // Path to uploaded resignation letter
        'nin',                 // National identification number
        'bvn',                 // Bank Verification number
        'department',           // Department
        'staff_code',
        'biometric_pin',
        'user_id',
    ];

    public function employmentHistories()
    {
        return $this->hasMany(EmploymentHistory::class);
    }

    public function educationalBackgrounds()
    {
        return $this->hasMany(EducationalBackground::class);
    }

    // Helper method to check if employee is active
    public function isActive()
    {
        return is_null($this->end_date);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_assignments', 'employee_id', 'task_id');
    }

    public function shiftAssignments()
    {
        return $this->hasMany(ShiftAssignment::class);
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function performanceReviews()
    {
        return $this->hasMany(PerformanceReview::class);
    }

    public function reviewsGiven()
    {
        return $this->hasMany(PerformanceReview::class, 'reviewer_id');
    }

    public function trainingRecords()
    {
        return $this->hasMany(TrainingRecord::class);
    }

    public function skills()
    {
        return $this->hasMany(EmployeeSkill::class);
    }
    // protected static function newFactory(): EmployeeFactory
    // {
    //     // return EmployeeFactory::new();
    // }
}