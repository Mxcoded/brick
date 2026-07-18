<?php

namespace Modules\Website\Tests\Feature;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\GuestDocument;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Services\PreArrivalService;
use Modules\Website\Tests\WebsiteModuleTestCase;

class PreArrivalFlowTest extends WebsiteModuleTestCase
{
    private Guest $guest;

    private Registration $registration;

    private PreArrivalService $preArrival;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guest = Guest::create([
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'contact_number' => '+234800000000',
            'nationality' => 'NG',
        ]);

        $this->registration = Registration::create([
            'guest_id' => $this->guest->id,
            'room_type_id' => $this->roomType->id,
            'stay_status' => 'draft',
            'reservation_code' => 'RES-001',
            'check_in' => '2026-07-10',
            'check_out' => '2026-07-12',
            'property_id' => $this->property->id,
            'full_name' => 'John Doe',
            'contact_number' => '+234800000000',
            'email' => 'john@example.com',
            'no_of_guests' => 1,
            'no_of_nights' => 2,
            'room_rate' => 20000,
            'total_amount' => 40000,
            'agreed_to_policies' => true,
            'registration_date' => '2026-07-01',
        ]);

        $this->preArrival = app(PreArrivalService::class);
    }

    public function test_index_page_loads(): void
    {
        $response = $this->get(route('guest.pre-arrival'));
        $response->assertOk();
        $response->assertViewIs('website::guest.pre-arrival.index');
    }

    public function test_invalid_lookup_returns_back(): void
    {
        $this->get(route('guest.pre-arrival'));

        $response = $this->post(route('guest.pre-arrival.lookup'), [
            'reservation_code' => 'NONEXISTENT',
            'contact' => 'noone@example.com',
        ]);

        $response->assertRedirect(route('guest.pre-arrival'));
    }

    public function test_lookup_by_code_and_email(): void
    {
        $this->registration->update(['stay_status' => 'reserved']);

        $response = $this->post(route('guest.pre-arrival.lookup'), [
            'reservation_code' => 'RES-001',
            'contact' => 'john@example.com',
        ]);

        $response->assertRedirect();
        $this->assertNotNull($this->registration->fresh()->pre_arrival_token);
    }

    public function test_lookup_with_wrong_email_fails(): void
    {
        $this->registration->update(['stay_status' => 'reserved']);
        $this->get(route('guest.pre-arrival'));

        $response = $this->post(route('guest.pre-arrival.lookup'), [
            'reservation_code' => 'RES-001',
            'contact' => 'wrong@example.com',
        ]);

        $response->assertRedirect(route('guest.pre-arrival'));
    }

    public function test_lookup_by_token_redirects(): void
    {
        $this->registration->update(['stay_status' => 'reserved']);
        $this->registration->update(['pre_arrival_token' => null]);
        $token = $this->preArrival->generateToken($this->registration);

        $response = $this->get(route('guest.pre-arrival.token', $token));

        $response->assertRedirect(route('guest.pre-arrival.details', $this->registration));
    }

    public function test_invalid_token_returns_to_index(): void
    {
        $this->get(route('guest.pre-arrival'));

        $response = $this->get(route('guest.pre-arrival.token', 'invalid-token-12345'));

        $response->assertRedirect(route('guest.pre-arrival'));
    }

    public function test_details_page_requires_auth(): void
    {
        $response = $this->get(route('guest.pre-arrival.details', $this->registration));

        $response->assertStatus(403);
    }

    public function test_details_page_shows_guest_info_when_authenticated(): void
    {
        $this->registration->update(['stay_status' => 'reserved']);
        $this->preArrival->generateToken($this->registration);
        $this->withSession([
            'pre_arrival_token_'.$this->registration->id => $this->registration->pre_arrival_token,
        ]);

        $response = $this->get(route('guest.pre-arrival.details', $this->registration));

        $response->assertOk();
        $response->assertViewHas('registration');
    }

    public function test_can_update_guest_details(): void
    {
        $this->registration->update(['stay_status' => 'reserved']);
        $this->preArrival->generateToken($this->registration);
        $this->withSession([
            'pre_arrival_token_'.$this->registration->id => $this->registration->pre_arrival_token,
        ]);

        $response = $this->put(route('guest.pre-arrival.update-details', $this->registration), [
            'full_name' => 'John Updated',
            'no_of_guests' => 2,
            'emergency_name' => 'Jane Doe',
            'emergency_contact' => '+234800000001',
            'contact_number' => '+234800000000',
            'estimated_arrival_at' => Carbon::tomorrow()->setHour(14)->format('Y-m-d\TH:i'),
        ]);

        $response->assertRedirect(route('guest.pre-arrival.documents', $this->registration));
        $this->assertEquals('John Updated', $this->guest->fresh()->full_name);
    }

    public function test_documents_upload_and_delete(): void
    {
        $this->registration->update(['stay_status' => 'reserved']);
        $this->preArrival->generateToken($this->registration);
        $this->withSession([
            'pre_arrival_token_'.$this->registration->id => $this->registration->pre_arrival_token,
        ]);

        $file = UploadedFile::fake()->image('passport.jpg');
        $response = $this->post(route('guest.pre-arrival.upload-document', $this->registration), [
            'type' => 'passport',
            'document' => $file,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('guest_documents', [
            'registration_id' => $this->registration->id,
            'type' => 'passport',
        ]);

        $document = GuestDocument::where('registration_id', $this->registration->id)->first();

        $deleteResponse = $this->delete(route('guest.pre-arrival.delete-document', [
            $this->registration->id,
            $document->id,
        ]));
        $deleteResponse->assertRedirect();
        $this->assertDatabaseMissing('guest_documents', ['id' => $document->id]);
    }

    public function test_complete_flow(): void
    {
        $this->registration->update(['stay_status' => 'reserved']);
        $this->preArrival->generateToken($this->registration);
        $this->withSession([
            'pre_arrival_token_'.$this->registration->id => $this->registration->pre_arrival_token,
        ]);

        $this->put(route('guest.pre-arrival.update-details', $this->registration), [
            'full_name' => 'John Completed',
            'no_of_guests' => 1,
            'special_requests' => 'Extra towels please',
            'emergency_name' => 'Jane Doe',
            'emergency_contact' => '+234800000002',
            'contact_number' => '+234800000000',
            'estimated_arrival_at' => Carbon::tomorrow()->setHour(14)->format('Y-m-d\TH:i'),
        ]);

        $file = UploadedFile::fake()->image('passport.jpg');
        $this->post(route('guest.pre-arrival.upload-document', $this->registration), [
            'type' => 'passport',
            'document' => $file,
        ]);

        $response = $this->post(route('guest.pre-arrival.submit-signature', $this->registration), [
            'signature' => 'data:image/png;base64,'.base64_encode('fake-signature-content'),
        ]);

        $response->assertRedirect(route('guest.pre-arrival.confirmation', $this->registration));
        $this->registration->refresh();
        $this->assertNotNull($this->registration->guest_signature);
    }
}
