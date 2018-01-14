<?php

namespace Tests\Feature;

use App\Activity;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateThreadsTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	public function guests_may_not_create_threads()
	{
		$this->withExceptionHandling();

		$this->get('/threads/create')
		     ->assertRedirect('/login');

		$this->post('/threads')
			->assertRedirect('login');
	}

	/** @test */
	public function guests_cannot_see_the_create_thread_page()
	{
		$this->withExceptionHandling();

		$this->get('/threads/create')
			->assertRedirect('/login');
	}

    /** @test */
    public function an_authenticated_user_can_create_new_forum_threads()
    {
    	// Given we have a signed in user
	    $this->signIn();

	    // When we hit the endpoint to create a new thread
	    $thread = make('App\Thread');

	    $response = $this->post('/threads', $thread->toArray());

	    // Then, when we visit the thread page
	    // We should see the new thread
	    $this->get($response->headers->get('Location'))
	         ->assertSee($thread->title)
		    ->assertSee($thread->body);
    }

    /** @test */
    public function a_thread_requires_title()
    {
    	$this->publishThreads(['title' => null])
		    ->assertSessionHasErrors('title');
    }

	/** @test */
	public function a_thread_requires_body()
	{
		$this->publishThreads(['body' => null])
		     ->assertSessionHasErrors('body');
	}

	/** @test */
	public function a_thread_requires_a_valid_channel()
	{
		factory('App\Channel', 2)->create();

		$this->publishThreads(['channel_id' => null])
		     ->assertSessionHasErrors('channel_id');

		$this->publishThreads(['channel_id' => 999])
		     ->assertSessionHasErrors('channel_id');
	}

	/** @test */
	public function unauthorized_users_may_not_delete_threads()
	{
		$this->withExceptionHandling();

		$thread = create('App\Thread');

		$this->delete($thread->path())
		     ->assertRedirect('/login');

		$this->signIn();

		$this->delete($thread->path())
		     ->assertStatus(403);

	}


	/** @test */
	public function authorized_users_can_delete_threads()
	{
		$this->signIn();

		$thread = create('App\Thread', [ 'user_id' => auth()->id()]);
		$reply = create('App\Reply', [ 'thread_id' => $thread->id ]);

		$response = $this->json('DELETE', $thread->path());

		$response->assertStatus(204);

		$this->assertDatabaseMissing('threads', [ 'id' => $thread->id ]);
		$this->assertDatabaseMissing('replies', [ 'id' => $reply->id ]);

		$this->assertEquals(0, Activity::count());
	}



    public function publishThreads($overrides = [])
    {
	    $this->withExceptionHandling()->signIn();

	    $thread = make('App\Thread', $overrides);

	    return $this->post('/threads', $thread->toArray());
    }
}
