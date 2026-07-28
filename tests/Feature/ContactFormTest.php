<?php

namespace Tests\Feature;

use App\Mail\ContactMessageReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_is_visible_on_the_home_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('How can we help?')
            ->assertSee('action="'.route('contact.store').'"', false);
    }

    public function test_a_valid_message_is_stored_and_emailed(): void
    {
        Mail::fake();
        config(['mail.contact_to' => 'support@tradeyatra.test']);

        $response = $this->post(route('contact.store'), [
            'name' => 'Rohit Kumar',
            'email' => 'rohit@example.com',
            'subject' => 'broker',
            'message' => 'I need help connecting my Delta account.',
            'website' => '',
        ]);

        $response->assertRedirect(route('home').'#contact')
            ->assertSessionHas('contact_success');

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'rohit@example.com',
            'subject' => 'broker',
        ]);

        Mail::assertSent(ContactMessageReceived::class, fn ($mail) => $mail->hasTo('support@tradeyatra.test') &&
            $mail->contactMessage->email === 'rohit@example.com'
        );
    }

    public function test_invalid_and_spam_messages_are_rejected(): void
    {
        $response = $this->from(route('home'))->post(route('contact.store'), [
            'name' => '',
            'email' => 'not-an-email',
            'subject' => 'unsupported',
            'message' => 'short',
            'website' => 'spam.example',
        ]);

        $response->assertRedirect(route('home'))
            ->assertSessionHasErrors(['name', 'email', 'subject', 'message', 'website']);
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_ajax_submission_returns_json_without_a_page_reload(): void
    {
        Mail::fake();

        $this->postJson(route('contact.store'), [
            'name' => 'Rohit Kumar',
            'email' => 'rohit@example.com',
            'subject' => 'product',
            'message' => 'Please tell me more about the reports.',
            'website' => '',
        ])->assertCreated()
            ->assertJsonPath('message', 'Thanks for reaching out. We received your message and will reply soon.');

        $this->assertDatabaseCount('contact_messages', 1);
    }
}
