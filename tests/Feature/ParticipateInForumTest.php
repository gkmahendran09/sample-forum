<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ParticipateInForumTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	public function unauthenticated_users_may_not_add_replies()
	{
		$this->withExceptionHandling()
			->post('/threads/channel/1/replies', [])
			->assertRedirect('login');
	}

    /** @test  */
    public function an_authenticated_user_may_participate_in_forum_threads()
    {
		// Given we have a authenticated user
	    $this->be($user = create('App\User'));

	    // And an existing thread
	    $thread = create('App\Thread');

	    // When the user adds a reply to the thread
	    $reply = make('App\Reply');
	    $this->post($thread->path() . '/replies', $reply->toArray());

	    // Then their reply should be visible on the page
	    $this->assertDatabaseHas('replies', [ 'body' => $reply->body]);
	    $this->assertEquals(1, $thread->fresh()->replies_count);
    }

    /** @test */
    public function a_reply_requires_a_body() {
	    $this->withExceptionHandling();
	    $this->signIn();

	    // And an existing thread
	    $thread = create( 'App\Thread' );

	    // When the user adds a reply to the thread
	    $reply = make( 'App\Reply', [ 'body' => null ] );
	    $this->json('post', $thread->path() . '/replies', $reply->toArray() )
		    ->assertStatus(422);
//	         ->assertSessionHasErrors( 'body' );

    }

    /** @test */
    public function  unauthorized_users_can_not_delete_replies()
    {
    	$this->withExceptionHandling();

        $reply = create('App\Reply');

        $this->delete("/replies/{$reply->id}")
            ->assertRedirect('/login');

	    $this->signIn()->delete("/replies/{$reply->id}")
	         ->assertStatus(403);

    }
    
    /** @test */
    public function  authorized_users_can_delete_replies()
    {
	    $this->signIn();
	    $reply = create('App\Reply', [ 'user_id' => auth()->id()]);
	    $this->delete("/replies/{$reply->id}")->assertStatus(302);
	    $this->assertDatabaseMissing('replies', [ 'id' => $reply->id ]);
	    $this->assertEquals(0, $reply->thread->fresh()->replies_count);
    }

	/** @test */
	public function  unauthorized_users_can_not_update_replies()
	{
		$this->withExceptionHandling();

		$reply = create('App\Reply');

		$this->patch("/replies/{$reply->id}")
		     ->assertRedirect('/login');

		$this->signIn()->patch("/replies/{$reply->id}")
		     ->assertStatus(403);

	}
    
    /** @test */
    public function  authorized_users_can_update_replies()
    {
    	$this->signIn();
	    $reply = create('App\Reply', [ 'user_id' => auth()->id()]);
	    $updatedReply = 'You been changed.';
	    $this->patch("/replies/{$reply->id}", [ 'body' => $updatedReply ]);

	    $this->assertDatabaseHas('replies', [ 'id' => $reply->id, 'body' => $updatedReply ]);
    }
    
    /** @test */
    public function  replies_that_contain_spam_may_not_be_created()
    {
    	    $this->withExceptionHandling();
	    // Given we have a authenticated user
	    $this->be($user = create('App\User'));

	    // And an existing thread
	    $thread = create('App\Thread');

	    // When the user adds a reply to the thread
	    $reply = make('App\Reply', [
	    	'body' => 'Yahoo Customer Support'
	    ]);

	    $this->json('post',$thread->path() . '/replies', $reply->toArray())
	        ->assertStatus(422);
    
    }
    
    /** @test */
    public function  users_may_only_reply_a_maximum_of_once_per_minute()
    {
    	    $this->withExceptionHandling();
	    $this->be($user = create('App\User'));

	    // And an existing thread
	    $thread = create('App\Thread');

	    // When the user adds a reply to the thread
	    $reply = make('App\Reply', [
		    'body' => 'My simple reply'
	    ]);

	    $this->post($thread->path() . '/replies', $reply->toArray())
	         ->assertStatus(200);

	    $this->post($thread->path() . '/replies', $reply->toArray())
	         ->assertStatus(429);
    }
    
    
    
    
    
}
