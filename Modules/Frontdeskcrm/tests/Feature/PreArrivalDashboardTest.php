<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\Property;
use App\Models\RoomType;
use App\Models\RoomUnit;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\GuestDocument;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Tests\ModuleTestCase;

class PreArrivalDashboardTest extends ModuleTestCase
{
    private Property $property;

    private RoomType $roomType;

    private Guest $guest;

    private Registration $registration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->property = Property::factory()->create(['is_active' => true]);
        $this->roomType = RoomType::factory()->create([
            'property_id' => $this->property->id,
            'is_active' => true,
        ]);
        RoomUnit::factory()->create([
            'room_type_id' => $this->roomType->id,
            'property_id' => $this->property->id,
        ]);

        $this->guest = Guest::create([
            'full_name' => 'Jane Guest',
            'email' => 'jane@example.com',
            'contact_number' => '+234800000000',
            'nationality' => 'NG',
        ]);

        $this->registration = Registration::create([
            'guest_id' => $this->guest->id,
            'room_type_id' => $this->roomType->id,
            'stay_status' => 'reserved',
            'reservation_code' => 'PRARR-TEST-001',
            'check_in' => Carbon::tomorrow()->format('Y-m-d'),
            'check_out' => Carbon::tomorrow()->addDays(2)->format('Y-m-d'),
            'property_id' => $this->property->id,
            'full_name' => 'Jane Guest',
            'contact_number' => '+234800000000',
            'email' => 'jane@example.com',
            'no_of_guests' => 2,
            'no_of_nights' => 2,
            'room_rate' => 25000,
            'total_amount' => 50000,
            'agreed_to_policies' => true,
            'registration_date' => now()->format('Y-m-d'),
            'pre_arrival_token' => 'test-token-'.str()->random(20),
        ]);

        $this->createAuthenticatedUser();
    }

    public function test_index_page_loads(): void
    {
        $response = $this->get(route('frontdesk.pre-arrivals.index'));

        $response->assertOk();
        $response->assertSee('Digital Guest Journey');
        $response->assertSee('Pending Pre-Arrivals');
    }

    public function test_datatable_returns_json(): void
    {
        $response = $this->get(route('frontdesk.pre-arrivals.datatable'));

        $response->assertOk();
        $response->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
    }

    public function test_datatable_includes_guest(): void
    {
        $response = $this->get(route('frontdesk.pre-arrivals.datatable'));

        $response->assertOk();
        $json = $response->json();
        $this->assertGreaterThanOrEqual(1, $json['recordsTotal']);
        $this->assertStringContainsString('Jane Guest', $json['data'][0]['guest_name']);
    }

    public function test_datatable_excludes_draft_registrations(): void
    {
        Registration::create([
            'guest_id' => $this->guest->id,
            'room_type_id' => $this->roomType->id,
            'stay_status' => 'draft',
            'reservation_code' => 'DRAFT-001',
            'check_in' => Carbon::tomorrow()->format('Y-m-d'),
            'check_out' => Carbon::tomorrow()->addDays(1)->format('Y-m-d'),
            'property_id' => $this->property->id,
            'full_name' => 'Draft User',
            'contact_number' => '+234800000001',
            'email' => 'draft@example.com',
            'no_of_guests' => 1,
            'no_of_nights' => 1,
            'room_rate' => 10000,
            'total_amount' => 10000,
            'agreed_to_policies' => true,
            'registration_date' => now()->format('Y-m-d'),
        ]);

        $response = $this->get(route('frontdesk.pre-arrivals.datatable'));

        $response->assertOk();
        $json = $response->json();
        $this->assertEquals(1, $json['recordsTotal']);
    }

    public function test_show_page_displays_registration(): void
    {
        $response = $this->get(route('frontdesk.pre-arrivals.show', $this->registration));

        $response->assertOk();
        $response->assertSee('Jane Guest');
        $response->assertSee('PRARR-TEST-001');
    }

    public function test_show_rejects_non_pre_arrival(): void
    {
        $draft = Registration::create([
            'guest_id' => $this->guest->id,
            'room_type_id' => $this->roomType->id,
            'stay_status' => 'draft',
            'reservation_code' => 'DRAFT-002',
            'check_in' => Carbon::tomorrow()->format('Y-m-d'),
            'check_out' => Carbon::tomorrow()->addDays(1)->format('Y-m-d'),
            'property_id' => $this->property->id,
            'full_name' => 'No Token',
            'contact_number' => '+234800000002',
            'email' => 'notoken@example.com',
            'no_of_guests' => 1,
            'no_of_nights' => 1,
            'room_rate' => 10000,
            'total_amount' => 10000,
            'agreed_to_policies' => true,
            'registration_date' => now()->format('Y-m-d'),
        ]);

        $response = $this->get(route('frontdesk.pre-arrivals.show', $draft));

        $response->assertRedirect(route('frontdesk.pre-arrivals.index'));
        $response->assertSessionHas('error');
    }

    public function test_verify_document(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('passport.jpg');
        $path = $file->store('guest-documents/'.$this->registration->id, 'public');

        $document = GuestDocument::create([
            'registration_id' => $this->registration->id,
            'guest_id' => $this->guest->id,
            'type' => 'passport',
            'file_path' => $path,
            'original_name' => 'passport.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 1024,
            'status' => 'pending',
        ]);

        $response = $this->post(route('frontdesk.pre-arrivals.documents.verify', [
            $this->registration,
            $document,
        ]));

        $response->assertSessionHas('success');
        $this->assertNotNull($document->fresh()->verified_at);
        $this->assertEquals('pending', $document->fresh()->status);
    }

    public function test_reject_document(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('bad_id.jpg');
        $path = $file->store('guest-documents/'.$this->registration->id, 'public');

        $document = GuestDocument::create([
            'registration_id' => $this->registration->id,
            'guest_id' => $this->guest->id,
            'type' => 'national_id',
            'file_path' => $path,
            'original_name' => 'bad_id.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 2048,
            'status' => 'pending',
        ]);

        $response = $this->post(route('frontdesk.pre-arrivals.documents.reject', [
            $this->registration,
            $document,
        ]), [
            'rejection_reason' => 'Image is too blurry to verify identity.',
        ]);

        $response->assertSessionHas('success');
        $this->assertNotNull($document->fresh()->rejected_at);
        $this->assertEquals('Image is too blurry to verify identity.', $document->fresh()->rejection_reason);
    }

    public function test_reject_document_requires_reason(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('id.jpg');
        $path = $file->store('guest-documents/'.$this->registration->id, 'public');

        $document = GuestDocument::create([
            'registration_id' => $this->registration->id,
            'guest_id' => $this->guest->id,
            'type' => 'visa',
            'file_path' => $path,
            'original_name' => 'id.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 512,
            'status' => 'pending',
        ]);

        $response = $this->post(route('frontdesk.pre-arrivals.documents.reject', [
            $this->registration,
            $document,
        ]), [
            'rejection_reason' => '',
        ]);

        $response->assertSessionHasErrors('rejection_reason');
    }

    public function test_approve_pre_arrival(): void
    {
        $this->assertNull($this->registration->pre_arrival_completed_at);

        $response = $this->post(route('frontdesk.pre-arrivals.approve', $this->registration));

        $response->assertSessionHas('success');
        $this->assertNotNull($this->registration->fresh()->pre_arrival_completed_at);
    }

    public function test_approve_already_completed(): void
    {
        $this->registration->update(['pre_arrival_completed_at' => now()->subHour()]);

        $response = $this->post(route('frontdesk.pre-arrivals.approve', $this->registration));

        $response->assertSessionHas('success');
    }

    public function test_send_reminder_without_template(): void
    {
        $response = $this->post(route('frontdesk.pre-arrivals.send-reminder', $this->registration));

        $response->assertSessionHas('error');
        $response->assertSessionHas('error', fn ($msg) => str_contains($msg, 'No pre-arrival reminder template'));
    }

    public function test_send_reminder_invalid_registration(): void
    {
        $draft = Registration::create([
            'guest_id' => $this->guest->id,
            'room_type_id' => $this->roomType->id,
            'stay_status' => 'draft',
            'reservation_code' => 'DRAFT-003',
            'check_in' => Carbon::tomorrow()->format('Y-m-d'),
            'check_out' => Carbon::tomorrow()->addDays(1)->format('Y-m-d'),
            'property_id' => $this->property->id,
            'full_name' => 'Invalid',
            'contact_number' => '+234800000003',
            'email' => 'invalid@example.com',
            'no_of_guests' => 1,
            'no_of_nights' => 1,
            'room_rate' => 10000,
            'total_amount' => 10000,
            'agreed_to_policies' => true,
            'registration_date' => now()->format('Y-m-d'),
        ]);

        $response = $this->post(route('frontdesk.pre-arrivals.send-reminder', $draft));

        $response->assertSessionHas('error');
    }

    public function test_unauthenticated_access_redirects(): void
    {
        auth()->logout();

        $response = $this->get(route('frontdesk.pre-arrivals.index'));

        $response->assertRedirect(route('login'));
    }
}
