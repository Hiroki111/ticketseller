<?php

namespace Tests\Feature\Backstage;

use App\AttendeeMessage;
use App\Jobs\SendAttendeeMessage;
use App\User;
use ConcertFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MessageAttendeesTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function a_promoter_can_view_the_message_form_for_their_own_concert()
    {
        $user    = factory(User::class)->create();
        $concert = ConcertFactory::createPublished([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertStatus(200);
        $response->assertViewIs('backstage.concert-messages.new');
        $this->assertTrue($response->data('concert')->is($concert));
    }

    /** @test */
    public function a_promoter_cannot_view_the_message_form_for_another_concert()
    {
        $user    = factory(User::class)->create();
        $concert = ConcertFactory::createPublished([
            'user_id' => factory(User::class)->create(),
        ]);

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertStatus(404);
    }

    /** @test */
    public function a_guest_cannot_view_the_message_form_for_any_concert()
    {
        $concert = ConcertFactory::createPublished();

        $response = $this->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertRedirect('/login');
    }

    /** @test */
    public function a_promoter_can_send_a_new_message()
    {
        Queue::fake();
        $user    = factory(User::class)->create();
        $concert = ConcertFactory::createPublished([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->post("/backstage/concerts/{$concert->id}/messages", [
            'subject' => 'My subject',
            'message' => 'My message',
        ]);

        $response->assertRedirect("/backstage/concerts/{$concert->id}/messages/new");
        $response->assertSessionHas('flash');

        $message = AttendeeMessage::first();
        $this->assertEquals($concert->id, $message->concert_id);
        $this->assertEquals('My subject', $message->subject);
        $this->assertEquals('My message', $message->message);

        Queue::assertPushed(SendAttendeeMessage::class, function ($job) use ($message) {
            return $job->attendeeMessage->is($message);
        });
    }

    /** @test */
    public function a_promoter_cannot_send_a_new_message_for_other_concerts()
    {
        Queue::fake();
        $user      = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concert   = ConcertFactory::createPublished([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)->post("/backstage/concerts/{$concert->id}/messages", [
            'subject' => 'My subject',
            'message' => 'My message',
        ]);

        $response->assertStatus(404);
        $this->assertEquals(0, AttendeeMessage::count());
        Queue::assertNotPushed(SendAttendeeMessage::class);
    }

    /** @test */
    public function a_guest_cannot_send_a_new_message_for_any_concerts()
    {
        Queue::fake();
        $concert = ConcertFactory::createPublished();

        $response = $this->post("/backstage/concerts/{$concert->id}/messages", [
            'subject' => 'My subject',
            'message' => 'My message',
        ]);

        $response->assertRedirect('/login');
        $this->assertEquals(0, AttendeeMessage::count());
        Queue::assertNotPushed(SendAttendeeMessage::class);
    }

    /** @test */
    public function subject_is_required()
    {
        Queue::fake();
        $user    = factory(User::class)->create();
        $concert = ConcertFactory::createPublished([
            'user_id' => $user->id,
        ]);

        $response = $this->from("/backstage/concerts/{$concert->id}/messages/new")
            ->actingAs($user)
            ->post("/backstage/concerts/{$concert->id}/messages", [
                'subject' => '',
                'message' => 'My message',
            ]);

        $response->assertRedirect("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertSessionHasErrors('subject');
        $this->assertEquals(0, AttendeeMessage::count());
        Queue::assertNotPushed(SendAttendeeMessage::class);
    }

    /** @test */
    public function message_is_required()
    {
        Queue::fake();
        $user    = factory(User::class)->create();
        $concert = ConcertFactory::createPublished([
            'user_id' => $user->id,
        ]);

        $response = $this->from("/backstage/concerts/{$concert->id}/messages/new")
            ->actingAs($user)
            ->post("/backstage/concerts/{$concert->id}/messages", [
                'subject' => 'My subject',
                'message' => '',
            ]);

        $response->assertRedirect("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertSessionHasErrors('message');
        $this->assertEquals(0, AttendeeMessage::count());
        Queue::assertNotPushed(SendAttendeeMessage::class);
    }
}
