<?php

namespace Modules\Gym\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Modules\Gym\Models\Membership;
use Modules\Gym\Models\Member;
use Modules\Gym\Models\Payment;
use Modules\Gym\Models\TrainerPayment;
use Modules\Gym\Models\Trainer;
use Modules\Gym\Models\SubscriptionConfig;
use Illuminate\Support\Facades\Log;

class GymController extends Controller
{
    /**
     * Display a listing of the resource(Gym membership Data).
     */
    public function index()
    {
        $memberships = Membership::with([
            'members',
            'createdBy',
            'payments' => function ($query) {
                $query->latest('payment_date')->take(2);
            }
        ])->get();

        return view('gym::index', compact('memberships'));
    }


    /**
     * Show the form for creating a new Membership.
     */
    public function create()
    {
        return view('gym::memberships.create');
    }

    /**
     * Store a newly created resource(membership registration) in DB.
     */
    public function store(Request $request)
    {
        Log::info('Starting gym membership registration', [
            'package_type' => $request->input('package_type'),
            'subscription_plan' => $request->input('subscription_plan'),
            'start_date' => $request->input('start_date'),
            'user_id' => Auth::id(),
        ]);

        try {
            if (!Auth::check()) {
                Log::warning('Unauthenticated user attempted to register membership');
                return redirect()->back()->withErrors(['auth' => 'You must be logged in to register a membership.']);
            }

            $rules = [
                'package_type' => ['required', 'in:individual,couple'],
                'subscription_plan' => ['required', 'in:monthly,quarterly,6months,yearly'],
                'personal_trainer' => ['required', 'in:yes,no'],
                'sessions' => ['required_if:personal_trainer,yes', 'integer', 'min:1'],
                'start_date' => ['required', 'date'],
                'payment_amount' => ['required', 'numeric', 'min:0'],
                'payment_date' => ['required', 'date', 'before_or_equal:now'],
                'payment_status' => ['required', 'in:paid,partial,pending,overdue'],
                'payment_mode' => ['required', 'in:cash,bank_transfer,pos,crypto'],
                'terms_agreed' => ['accepted'],
                'full_name_1' => ['required', 'string', 'max:255'],
                'date_of_birth_1' => ['required', 'date'],
                'gender_1' => ['required', 'in:male,female,other'],
                'phone_number_1' => ['required', 'string', 'max:20'],
                'email_address_1' => ['nullable', 'email', 'max:255'],
                'home_address_1' => ['required', 'string'],
                'emergency_contact_name_1' => ['required', 'string', 'max:255'],
                'emergency_contact_relationship_1' => ['required', 'string', 'max:100'],
                'emergency_contact_number_1' => ['required', 'string', 'max:20'],
                'has_medical_conditions_1' => 'required_if:package_type,couple|in:yes,no|nullable',
                'medical_conditions_1' => 'required_if:has_medical_conditions_2,yes|nullable|string|max:1000',
                'fitness_goals_1' => 'required_if:package_type,couple|array|min:1|nullable',
                'fitness_goals_1.*' => 'in:Weight Loss,Muscle Gain,General Fitness,Other|nullable',
                'fitness_goals_other_1' => 'required_if:fitness_goals_1.*,Other|nullable|string|max:255',
            ];

            if ($request->input('package_type') === 'couple') {
                $rules = array_merge($rules, [
                    'full_name_2' => ['required', 'string', 'max:255'],
                    'date_of_birth_2' => ['required', 'date'],
                    'gender_2' => ['required', 'in:male,female,other'],
                    'phone_number_2' => ['required', 'string', 'max:20'],
                    'email_address_2' => ['nullable', 'email', 'max:255'],
                    'home_address_2' => ['required', 'string'],
                    'emergency_contact_name_2' => ['required', 'string', 'max:255'],
                    'emergency_contact_relationship_2' => ['required', 'string', 'max:100'],
                    'emergency_contact_number_2' => ['required', 'string', 'max:20'],
                    'has_medical_conditions_2' => 'required_if:package_type,couple|in:yes,no|nullable',
                    'medical_conditions_2' => 'required_if:has_medical_conditions_2,yes|nullable|string|max:1000',
                    'fitness_goals_2' => 'required_if:package_type,couple|array|min:1|nullable',
                    'fitness_goals_2.*' => 'in:Weight Loss,Muscle Gain,General Fitness,Other|nullable',
                    'fitness_goals_other_2' => 'required_if:fitness_goals_2.*,Other|nullable|string|max:255',
                ]);
            }

            $validated = $request->validate($rules);

            $config = SubscriptionConfig::first();
            if (!$config) {
                Log::error('Subscription config not found');
                throw new \Exception('Subscription configuration is missing. Please run the appropriate seeder or configuration route to set the fees.');
            }

            $baseFee = match ($validated['subscription_plan']) {
                'monthly' => $config->monthly_fee,
                'quarterly' => $config->quarterly_fee,
                '6months' => $config->six_months_fee,
                'yearly' => $config->yearly_fee,
                default => 0,
            };
            $sessionCost = $validated['personal_trainer'] === 'yes' ? $config->session_fee * $validated['sessions'] : 0;
            $total_cost = $baseFee + $sessionCost;

            $existingMembership = Membership::where([
                'package_type' => $validated['package_type'],
                'start_date' => $validated['start_date'],
                'created_by' => Auth::id(),
            ])->first();

            if ($existingMembership) {
                Log::warning('Duplicate membership detected', [
                    'package_type' => $validated['package_type'],
                    'start_date' => $validated['start_date'],
                    'created_by' => Auth::id(),
                ]);
                return redirect()->back()->withErrors(['package_type' => 'A membership with this package type and start date already exists for this staff member.'])->withInput();
            }

            $existingMember1 = Member::where([
                'full_name' => $validated['full_name_1'],
                'date_of_birth' => $validated['date_of_birth_1'],
            ])->orWhere(function ($query) use ($validated) {
                if (!empty($validated['email_address_1'])) {
                    $query->where('email_address', $validated['email_address_1']);
                }
            })->first();

            if ($existingMember1) {
                Log::warning('Duplicate member 1 detected', [
                    'full_name' => $validated['full_name_1'],
                    'date_of_birth' => $validated['date_of_birth_1'],
                    'email_address' => $validated['email_address_1'],
                ]);
                return redirect()->back()->withErrors(['full_name_1' => 'A member with this name and date of birth or email address already exists.'])->withInput();
            }

            if ($validated['package_type'] === 'couple') {
                $existingMember2 = Member::where([
                    'full_name' => $validated['full_name_2'],
                    'date_of_birth' => $validated['date_of_birth_2'],
                ])->orWhere(function ($query) use ($validated) {
                    if (!empty($validated['email_address_2'])) {
                        $query->where('email_address', $validated['email_address_2']);
                    }
                })->first();

                if ($existingMember2) {
                    Log::warning('Duplicate member 2 detected', [
                        'full_name' => $validated['full_name_2'],
                        'date_of_birth' => $validated['date_of_birth_2'],
                        'email_address' => $validated['email_address_2'],
                    ]);
                    return redirect()->back()->withErrors(['full_name_2' => 'A member with this name and date of birth or email address already exists.'])->withInput();
                }
            }

            $membership = Membership::create([
                'package_type' => $validated['package_type'],
                'subscription_plan' => $validated['subscription_plan'],
                'personal_trainer' => $validated['personal_trainer'],
                'sessions' => $validated['sessions'] ?? null,
                'total_cost' => $total_cost,
                'start_date' => $validated['start_date'],
                'next_billing_date' => $this->calculateNextBillingDate($validated['start_date'], $validated['subscription_plan']),
                'created_by' => Auth::id(),
                'registration_date' => now()->toDateString(),
                'terms_agreed' => true,
            ]);

            Log::info('Membership created successfully', [
                'membership_id' => $membership->id,
                'package_type' => $membership->package_type,
                'created_by' => Auth::id(),
            ]);

            // Combine fitness goals with "Other" details for Member 1
            $fitnessGoals1 = $validated['fitness_goals_1'];
            if (in_array('Other', $fitnessGoals1) && !empty($validated['fitness_goals_other_1'])) {
                $fitnessGoals1[array_search('Other', $fitnessGoals1)] = $validated['fitness_goals_other_1'];
            }

            $member1 = Member::create([
                'membership_id' => $membership->id,
                'full_name' => $validated['full_name_1'],
                'date_of_birth' => $validated['date_of_birth_1'],
                'gender' => $validated['gender_1'],
                'phone_number' => $validated['phone_number_1'],
                'email_address' => $validated['email_address_1'],
                'home_address' => $validated['home_address_1'],
                'emergency_contact_name' => $validated['emergency_contact_name_1'],
                'emergency_contact_relationship' => $validated['emergency_contact_relationship_1'],
                'emergency_contact_number' => $validated['emergency_contact_number_1'],
                'medical_conditions' => $validated['has_medical_conditions_1'] === 'yes' ? $validated['medical_conditions_1'] : null,
                'fitness_goals' => json_encode($fitnessGoals1),
            ]);

            Log::info('Member 1 created successfully', [
                'member_id' => $member1->id,
                'membership_id' => $membership->id,
                'full_name' => $member1->full_name,
            ]);

            if ($validated['package_type'] === 'couple') {
                // Combine fitness goals with "Other" details for Member 2
                $fitnessGoals2 = $validated['fitness_goals_2'];
                if (in_array('Other', $fitnessGoals2) && !empty($validated['fitness_goals_other_2'])) {
                    $fitnessGoals2[array_search('Other', $fitnessGoals2)] = $validated['fitness_goals_other_2'];
                }

                $member2 = Member::create([
                    'membership_id' => $membership->id,
                    'full_name' => $validated['full_name_2'],
                    'date_of_birth' => $validated['date_of_birth_2'],
                    'gender' => $validated['gender_2'],
                    'phone_number' => $validated['phone_number_2'],
                    'email_address' => $validated['email_address_2'],
                    'home_address' => $validated['home_address_2'],
                    'emergency_contact_name' => $validated['emergency_contact_name_2'],
                    'emergency_contact_relationship' => $validated['emergency_contact_relationship_2'],
                    'emergency_contact_number' => $validated['emergency_contact_number_2'],
                    'medical_conditions' => $validated['has_medical_conditions_2'] === 'yes' ? $validated['medical_conditions_2'] : null,
                    'fitness_goals' => json_encode($fitnessGoals2),
                ]);

                Log::info('Member 2 created successfully', [
                    'member_id' => $member2->id,
                    'membership_id' => $membership->id,
                    'full_name' => $member2->full_name,
                ]);
            }

            $remaining_balance = 0;
            if ($validated['payment_status'] === 'partial') {
                $remaining_balance = $validated['payment_amount'] - $total_cost;
            }

            $existingPayment = Payment::where([
                'membership_id' => $membership->id,
                'payment_date' => $validated['payment_date'],
            ])->first();

            if ($existingPayment) {
                Log::warning('Duplicate payment detected', [
                    'membership_id' => $membership->id,
                    'payment_date' => $validated['payment_date'],
                ]);
                return redirect()->back()->withErrors(['payment_date' => 'A payment for this membership on this date already exists.'])->withInput();
            }

            $payment = Payment::create([
                'membership_id' => $membership->id,
                'payment_amount' => $validated['payment_amount'],
                'payment_date' => $validated['payment_date'],
                'payment_status' => $validated['payment_status'],
                'payment_mode' => $validated['payment_mode'],
                'payment_type' => $validated['payment_status'] === 'partial' ? 'partial' : 'full',
                'remaining_balance' => $remaining_balance,
            ]);

            Log::info('Payment created successfully', [
                'payment_id' => $payment->id,
                'membership_id' => $membership->id,
                'payment_amount' => $payment->payment_amount,
                'payment_status' => $payment->payment_status,
            ]);

            return redirect()->route('gym.index')->with('success', 'Membership registered successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation failed during membership registration', [
                'errors' => $e->errors(),
                'user_id' => Auth::id(),
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error during membership registration', [
                'message' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'An error occurred while registering the membership. Please try again.')->withInput();
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        try {
            $membership = Membership::with(['members', 'payments', 'trainerPayments', 'createdBy'])->findOrFail($id);
            $trainers = Trainer::all();
            // Calculate total paid
            $totalPaid = $membership->payments->sum('payment_amount');

            // Calculate remaining balance
            $remainingBalance = $totalPaid - $membership->total_cost;

            // Determine status based on payment and billing
            $status = 'Active';
            if ($remainingBalance > 0) {
                $status = 'Partially Paid';
            }
            if ($membership->next_billing_date->isPast()) {
                $status = 'Overdue';
            }
            if ($remainingBalance <= 0 && $membership->next_billing_date->isFuture()) {
                $status = 'Fully Paid';
            }

            return view('gym::memberships.show', compact('membership', 'trainers', 'totalPaid', 'remainingBalance', 'status'));
        } catch (\Exception $e) {
            Log::error('Error displaying membership', [
                'membership_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('gym.index')->with('error', 'Membership not found or an error occurred.');
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $membership = Membership::with(['members'])->findOrFail($id);
            return view('gym::memberships.edit', compact('membership'));
        } catch (\Exception $e) {
            Log::error('Error loading membership for edit', [
                'membership_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('gym.index')->with('error', 'Membership not found or an error occurred.');
        }
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        Log::info('Starting gym membership update', [
            'membership_id' => $id,
            'package_type' => $request->input('package_type'),
            'subscription_plan' => $request->input('subscription_plan'),
            'user_id' => Auth::id(),
        ]);

        try {
            if (!Auth::check()) {
                Log::warning('Unauthenticated user attempted to update membership');
                return redirect()->back()->withErrors(['auth' => 'You must be logged in to update a membership.']);
            }

            $membership = Membership::with(['members'])->findOrFail($id);

            $rules = [
                'package_type' => ['required', 'in:individual,couple'],
                'subscription_plan' => ['required', 'in:monthly,quarterly,6months,yearly'],
                'personal_trainer' => ['required', 'in:yes,no'],
                'sessions' => ['required_if:personal_trainer,yes', 'integer', 'min:1'],
                'start_date' => ['required', 'date'],
                'terms_agreed' => ['accepted'],
                'full_name_1' => ['required', 'string', 'max:255'],
                'date_of_birth_1' => ['required', 'date'],
                'gender_1' => ['required', 'in:male,female,other'],
                'phone_number_1' => ['required', 'string', 'max:20'],
                'email_address_1' => ['nullable', 'email', 'max:255'],
                'home_address_1' => ['required', 'string'],
                'emergency_contact_name_1' => ['required', 'string', 'max:255'],
                'emergency_contact_relationship_1' => ['required', 'string', 'max:100'],
                'emergency_contact_number_1' => ['required', 'string', 'max:20'],
                'has_medical_conditions_1' => 'required|in:yes,no',
                'medical_conditions_1' => 'required_if:has_medical_conditions_1,yes|nullable|string|max:1000',
                'fitness_goals_1' => 'required|array|min:1',
                'fitness_goals_1.*' => 'in:Weight Loss,Muscle Gain,General Fitness,Other',
                'fitness_goals_other_1' => 'required_if:fitness_goals_1.*,Other|nullable|string|max:255',
            ];

            if ($request->input('package_type') === 'couple') {
                $rules = array_merge($rules, [
                    'full_name_2' => ['required', 'string', 'max:255'],
                    'date_of_birth_2' => ['required', 'date'],
                    'gender_2' => ['required', 'in:male,female,other'],
                    'phone_number_2' => ['required', 'string', 'max:20'],
                    'email_address_2' => ['nullable', 'email', 'max:255'],
                    'home_address_2' => ['required', 'string'],
                    'emergency_contact_name_2' => ['required', 'string', 'max:255'],
                    'emergency_contact_relationship_2' => ['required', 'string', 'max:100'],
                    'emergency_contact_number_2' => ['required', 'string', 'max:20'],
                    'has_medical_conditions_2' => 'required_if:package_type,couple|in:yes,no|nullable',
                    'medical_conditions_2' => 'required_if:has_medical_conditions_2,yes|nullable|string|max:1000',
                    'fitness_goals_2' => 'required_if:package_type,couple|array|min:1|nullable',
                    'fitness_goals_2.*' => 'in:Weight Loss,Muscle Gain,General Fitness,Other|nullable',
                    'fitness_goals_other_2' => 'required_if:fitness_goals_2.*,Other|nullable|string|max:255',
                ]);
            }

            $validated = $request->validate($rules);

            $config = SubscriptionConfig::first();
            if (!$config) {
                Log::error('Subscription config not found');
                throw new \Exception('Subscription configuration is missing. Please run the appropriate seeder or configuration route to set the fees.');
            }

            $baseFee = match ($validated['subscription_plan']) {
                'monthly' => $config->monthly_fee,
                'quarterly' => $config->quarterly_fee,
                '6months' => $config->six_months_fee,
                'yearly' => $config->yearly_fee,
                default => 0,
            };
            $sessionCost = $validated['personal_trainer'] === 'yes' ? $config->session_fee * $validated['sessions'] : 0;
            $total_cost = $baseFee + $sessionCost;

            // Update Membership
            $membership->update([
                'package_type' => $validated['package_type'],
                'subscription_plan' => $validated['subscription_plan'],
                'personal_trainer' => $validated['personal_trainer'],
                'sessions' => $validated['personal_trainer'] === 'yes' ? $validated['sessions'] : null,
                'total_cost' => $total_cost,
                'start_date' => $validated['start_date'],
                'next_billing_date' => $this->calculateNextBillingDate($validated['start_date'], $validated['subscription_plan']),
                'terms_agreed' => true,
            ]);

            Log::info('Membership updated successfully', [
                'membership_id' => $membership->id,
                'package_type' => $membership->package_type,
                'updated_by' => Auth::id(),
            ]);

            // Combine fitness goals with "Other" details for Member 1
            $fitnessGoals1 = $validated['fitness_goals_1'];
            if (in_array('Other', $fitnessGoals1) && !empty($validated['fitness_goals_other_1'])) {
                $fitnessGoals1[array_search('Other', $fitnessGoals1)] = $validated['fitness_goals_other_1'];
            }

            // Update Member 1
            $member1 = $membership->members->first();
            if ($member1) {
                $member1->update([
                    'full_name' => $validated['full_name_1'],
                    'date_of_birth' => $validated['date_of_birth_1'],
                    'gender' => $validated['gender_1'],
                    'phone_number' => $validated['phone_number_1'],
                    'email_address' => $validated['email_address_1'],
                    'home_address' => $validated['home_address_1'],
                    'emergency_contact_name' => $validated['emergency_contact_name_1'],
                    'emergency_contact_relationship' => $validated['emergency_contact_relationship_1'],
                    'emergency_contact_number' => $validated['emergency_contact_number_1'],
                    'medical_conditions' => $validated['has_medical_conditions_1'] === 'yes' ? $validated['medical_conditions_1'] : null,
                    'fitness_goals' => json_encode($fitnessGoals1),
                ]);

                Log::info('Member 1 updated successfully', [
                    'member_id' => $member1->id,
                    'membership_id' => $membership->id,
                    'full_name' => $member1->full_name,
                ]);
            } else {
                Log::error('Primary member not found for membership', [
                    'membership_id' => $membership->id,
                ]);
                return redirect()->back()->withErrors(['full_name_1' => 'Primary member not found.'])->withInput();
            }

            // Handle Member 2
            $member2 = $membership->members->skip(1)->first();
            if ($validated['package_type'] === 'couple') {
                // Combine fitness goals with "Other" details for Member 2
                $fitnessGoals2 = $validated['fitness_goals_2'];
                if (in_array('Other', $fitnessGoals2) && !empty($validated['fitness_goals_other_2'])) {
                    $fitnessGoals2[array_search('Other', $fitnessGoals2)] = $validated['fitness_goals_other_2'];
                }

                if ($member2) {
                    $member2->update([
                        'full_name' => $validated['full_name_2'],
                        'date_of_birth' => $validated['date_of_birth_2'],
                        'gender' => $validated['gender_2'],
                        'phone_number' => $validated['phone_number_2'],
                        'email_address' => $validated['email_address_2'],
                        'home_address' => $validated['home_address_2'],
                        'emergency_contact_name' => $validated['emergency_contact_name_2'],
                        'emergency_contact_relationship' => $validated['emergency_contact_relationship_2'],
                        'emergency_contact_number' => $validated['emergency_contact_number_2'],
                        'medical_conditions' => $validated['has_medical_conditions_2'] === 'yes' ? $validated['medical_conditions_2'] : null,
                        'fitness_goals' => json_encode($fitnessGoals2),
                    ]);
                    Log::info('Member 2 updated successfully', [
                        'member_id' => $member2->id,
                        'membership_id' => $membership->id,
                        'full_name' => $member2->full_name,
                    ]);
                } else {
                    $newMember2 = Member::create([
                        'membership_id' => $membership->id,
                        'full_name' => $validated['full_name_2'],
                        'date_of_birth' => $validated['date_of_birth_2'],
                        'gender' => $validated['gender_2'],
                        'phone_number' => $validated['phone_number_2'],
                        'email_address' => $validated['email_address_2'],
                        'home_address' => $validated['home_address_2'],
                        'emergency_contact_name' => $validated['emergency_contact_name_2'],
                        'emergency_contact_relationship' => $validated['emergency_contact_relationship_2'],
                        'emergency_contact_number' => $validated['emergency_contact_number_2'],
                        'medical_conditions' => $validated['has_medical_conditions_2'] === 'yes' ? $validated['medical_conditions_2'] : null,
                        'fitness_goals' => json_encode($fitnessGoals2),
                    ]);
                    Log::info('Member 2 created successfully', [
                        'member_id' => $newMember2->id,
                        'membership_id' => $membership->id,
                        'full_name' => $newMember2->full_name,
                    ]);
                }
            } elseif ($member2) {
                $member2->delete();
                Log::info('Member 2 deleted due to package type change to individual', [
                    'member_id' => $member2->id,
                    'membership_id' => $membership->id,
                ]);
            }

            return redirect()->route('gym.index')->with('success', 'Membership updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation failed during membership update', [
                'errors' => $e->errors(),
                'user_id' => Auth::id(),
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error during membership update', [
                'membership_id' => $id,
                'message' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'An error occurred while updating the membership. Please try again.')->withInput();
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Log::info('Starting gym membership deletion', [
            'membership_id' => $id,
            'user_id' => Auth::id(),
        ]);

        try {
            if (!Auth::check()) {
                Log::warning('Unauthenticated user attempted to delete membership');
                return redirect()->back()->withErrors(['auth' => 'You must be logged in to delete a membership.']);
            }

            $membership = Membership::with(['members', 'payments', 'trainerPayments'])->findOrFail($id);

            // Delete related records
            $membership->members()->delete();
            Log::info('Members deleted for membership', [
                'membership_id' => $id,
                'count' => $membership->members->count(),
            ]);

            $membership->payments()->delete();
            Log::info('Payments deleted for membership', [
                'membership_id' => $id,
                'count' => $membership->payments->count(),
            ]);

            $membership->trainerPayments()->delete();
            Log::info('Trainer payments deleted for membership', [
                'membership_id' => $id,
                'count' => $membership->trainerPayments->count(),
            ]);

            // Delete the membership
            $membership->delete();
            Log::info('Membership deleted successfully', [
                'membership_id' => $id,
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('gym.index')->with('success', 'Membership deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error during membership deletion', [
                'membership_id' => $id,
                'message' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('gym.index')->with('error', 'An error occurred while deleting the membership. Please try again.');
        }
    }
    // Member payment creation
    public function createMemberPayment(Membership $membership)
    {
        return view('gym::payments.create', compact('membership'));
    }

    public function storeMemberPayment(Request $request, Membership $membership)
    {
        $validated = $request->validate([
            'payment_amount' => ['required', 'numeric', 'min:0'],
            'payment_date' => ['required', 'date', 'before_or_equal:now'],
            'payment_status' => ['required', 'in:paid,partial,pending,overdue'],
            'payment_mode' => ['required', 'in:cash,bank_transfer,pos,crypto'],
        ]);

        $payment = Payment::create([
            'membership_id' => $membership->id,
            'payment_amount' => $validated['payment_amount'],
            'payment_date' => now(),
            'payment_status' => $validated['payment_status'],
            'payment_mode' => $validated['payment_mode'],
            'payment_type' => $validated['payment_status'] === 'partial' ? 'partial' : 'full',
        ]);

        Log::info('Member payment created successfully', [
            'payment_id' => $payment->id,
            'membership_id' => $membership->id,
            'payment_amount' => $payment->payment_amount,
        ]);

        return redirect()->route('gym.memberships.show', $membership->id)->with('success', 'Member payment added successfully.');
    }

    // Trainer payment creation
    public function createTrainerPayment(Membership $membership)
    {
        $trainers = Trainer::all();
        return view('gym::trainer-payments.create', compact('membership', 'trainers'));
    }

    public function storeTrainerPayment(Request $request, Membership $membership)
    {
        $validated = $request->validate([
            'trainer_id' => ['required', 'exists:trainers,id'],
            'payment_amount' => ['required', 'numeric', 'min:0'],
            'payment_date' => ['required', 'date', 'before_or_equal:now'],
            'payment_type' => ['required', 'in:full,partial'],
            'payment_mode' => ['required', 'in:cash,bank_transfer,pos,crypto'],
        ]);

        $trainerPayment = TrainerPayment::create([
            'trainer_id' => $validated['trainer_id'],
            'membership_id' => $membership->id,
            'payment_amount' => $validated['payment_amount'],
            'payment_date' => now(),
            'payment_type' => $validated['payment_type'],
            'payment_mode' => $validated['payment_mode'],
        ]);

        Log::info('Trainer payment created successfully', [
            'trainer_payment_id' => $trainerPayment->id,
            'membership_id' => $membership->id,
            'trainer_id' => $validated['trainer_id'],
            'payment_amount' => $trainerPayment->payment_amount,
        ]);

        return redirect()->route('gym.memberships.show', $membership->id)->with('success', 'Trainer payment added successfully.');
    }
    //Trainer Controller
    public function indexTrainer()
    {
        $trainers = Trainer::all();
        return view('gym::trainers.index', compact('trainers'));
    }
    public function createTrainer()
    {
        return view('gym::trainers.create');
    }
    public function storeTrainer(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email_address' => 'required|email|unique:trainers,email_address',
            'phone_number' => 'required|string|max:20',
            'specialization' => 'required|string|max:255',
        ]);

        Trainer::create([
            'full_name' => $request->full_name,
            'email_address' => $request->email_address,
            'phone_number' => $request->phone_number,
            'specialization' => $request->specialization,
        ]);

        return redirect()->route('gym.trainers.index')->with('success', 'Trainer registered successfully!');
    }
    /**
     * Display the specified resource.
     */
    public function showTrainer($id)
    {
        try {
            $trainer = Trainer::findOrFail($id);
            return view('gym::trainers.show', compact('trainer'));
        } catch (\Exception $e) {
            Log::error('Error displaying trainer', [
                'trainer_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('gym.trainers.index')->with('error', 'Trainer not found or an error occurred.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editTrainer($id)
    {
        try {
            $trainer = Trainer::findOrFail($id);
            return view('gym::trainers.edit', compact('trainer'));
        } catch (\Exception $e) {
            Log::error('Error loading trainer for edit', [
                'trainer_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('gym.trainers.index')->with('error', 'Trainer not found or an error occurred.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateTrainer(Request $request, $id)
    {
        try {
            $trainer = Trainer::findOrFail($id);
            $request->validate([
                'full_name' => 'required|string|max:255',
                'email_address' => 'required|email|unique:trainers,email_address,' . $trainer->id,
                'phone_number' => 'required|string|max:20',
                'specialization' => 'required|string|max:255',
            ]);

            $trainer->update([
                'full_name' => $request->full_name,
                'email_address' => $request->email_address,
                'phone_number' => $request->phone_number,
                'specialization' => $request->specialization,
            ]);

            Log::info('Trainer updated successfully', [
                'trainer_id' => $trainer->id,
                'full_name' => $request->full_name,
            ]);

            return redirect()->route('gym.trainers.index')->with('success', 'Trainer updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation failed during trainer update', [
                'trainer_id' => $id,
                'errors' => $e->errors(),
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error during trainer update', [
                'trainer_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'An error occurred while updating the trainer. Please try again.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyTrainer($id)
    {
        try {
            $trainer = Trainer::findOrFail($id);
            $trainer->delete();

            Log::info('Trainer deleted successfully', [
                'trainer_id' => $id,
                'full_name' => $trainer->full_name,
            ]);

            return redirect()->route('gym.trainers.index')->with('success', 'Trainer deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Error during trainer deletion', [
                'trainer_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('trainers.index')->with('error', 'An error occurred while deleting the trainer. Please try again.');
        }
    }

    /**
     * Subscription price configuration settings
     */
    public function editSubscriptionConfig()
    {
        $config = SubscriptionConfig::first(); // Retrieve the subscription config (assumes a single config record)
        return view('gym::subscription_config', compact('config'));
    }

    public function updateSubscriptionConfig(Request $request)
    {
        // Validate the incoming data
        $validated = $request->validate([
            'monthly_fee' => 'required|numeric|min:0',
            'quarterly_fee' => 'required|numeric|min:0',
            'six_months_fee' => 'required|numeric|min:0',
            'yearly_fee' => 'required|numeric|min:0',
            'session_fee' => 'required|numeric|min:0',
        ]);

        // Update the configuration
        $config = SubscriptionConfig::first();
        $config->update($validated);

        // Redirect with success message
        return redirect()->route('gym.subscription-config.edit')->with('success', 'Subscription configurations updated successfully!');
    }

    private function calculateNextBillingDate(string $startDate, string $paymentMethod): string
    {
        $date = Carbon::parse($startDate);
        return match ($paymentMethod) {
            'monthly' => $date->addMonth()->toDateString(),
            'quarterly' => $date->addMonths(3)->toDateString(),
            '6months' => $date->addMonths(6)->toDateString(),
            'yearly' => $date->addYear()->toDateString(),
            default => $date->toDateString(),
        };
    }
}
