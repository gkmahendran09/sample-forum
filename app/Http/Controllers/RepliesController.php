<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePostRequest;
use App\Notifications\YouWereMentioned;
use App\Reply;
use App\Thread;
use App\User;
use Illuminate\Support\Facades\Gate;

class RepliesController extends Controller
{

	public function __construct() {
		$this->middleware('auth')->except('index');
	}

	/**
	 * @param $channelId
	 * @param Thread $thread
	 *
	 * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
	 */
	public function index($channelId, Thread $thread)
	{
		return $thread->replies()->paginate(20);
	}

	/**
	 * @param $channelId
	 * @param Thread $thread
	 *
	 *
	 * @param CreatePostRequest $form
	 *
	 * @return $this|\Illuminate\Http\RedirectResponse
	 */
	public function store($channelId, Thread $thread, CreatePostRequest $form) {
		$reply = $thread->addReply([
			'body' => request('body'),
			'user_id' => auth()->id()
		]);

		// Inspect the body of the reply for username mentions
		preg_match_all('/\@([^\s\.]+)/', $reply->body, $matches);
		// And then for each mentioned user, notify them.
		foreach( $matches[1] as $name) {
			$user = User::whereName($name)->first();

			if($user) {
				$user->notify(new YouWereMentioned($reply));
			}
		}

		return $reply->load('owner');
    }

	/**
	 * @param Reply $reply
	 *
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
	 * @throws \Illuminate\Auth\Access\AuthorizationException
	 */
	public function update(Reply $reply)
    {
    	$this->authorize('update', $reply);

    	try {
		    $this->validate(request(), ['body' => 'required|spamfree']);

		    $reply->update(request(['body']));
	    } catch(\Exception $e) {
		    return response('Sorry, your reply could not be saved at this time.', 422);
	    }
    }

	/**
	 * @param Reply $reply
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 * @throws \Exception
	 * @throws \Illuminate\Auth\Access\AuthorizationException
	 */
	public function destroy(Reply $reply)
    {
		$this->authorize('update', $reply);

    	$reply->delete();

    	if(request()->expectsJson()) {
    		return response([ 'status' => 'Reply deleted.']);
	    }

    	return back();

    }
}
