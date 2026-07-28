<?php

namespace Tests\Feature;

use App\Models\Trade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CalendarNoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_note_box_is_empty_and_accepts_screenshots(): void
    {
        $user = User::factory()->create();
        $trade = Trade::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'pair' => 'BTCINR',
            'trade_type' => 'Long',
            'status' => 'Closed',
            'notes' => '{"api":"response that must not fill the editor"}',
            'profit' => 100,
        ]);

        $this->actingAs($user)->get(route('trades.calendar'))
            ->assertOk()
            ->assertSee('placeholder="Write a new trade note here..."></textarea>', false)
            ->assertSee('name="screenshot[]"', false);

        $response = $this->actingAs($user)->withHeader('Accept', 'application/json')->post(route('trades.notes.update', $trade), [
            'notes' => 'My calendar review note.',
            'screenshot' => [UploadedFile::fake()->image('chart.png', 800, 600)],
        ]);

        $response->assertOk()
            ->assertJsonPath('notes', 'My calendar review note.')
            ->assertJsonCount(1, 'screenshots');

        $filename = json_decode($trade->fresh()->screenshot, true)[0];
        Storage::disk('local')->assertExists('trade-screenshots/'.$filename);
        Storage::disk('local')->delete('trade-screenshots/'.$filename);
    }
}
