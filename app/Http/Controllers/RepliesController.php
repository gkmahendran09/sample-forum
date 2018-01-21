<?php

namespace App\Http\Controllers;

use App\Reply;
use App\Thread;

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
	 * @return $this|\Illuminate\Http\RedirectResponse
	 */
	public function store($channelId, Thread $thread) {

		try {
			$this->validate(request(), ['body' => 'required|spamfree']);

			$reply = $thread->addReply([
				'body' => request('body'),
				'user_id' => auth()->id()
			]);


		} catch(\Exception $e) {
			return response('Sorry, your reply could not be saved at this time.', 422);
		}

		if(request()->expectsJson()) {
			return $reply->load('owner');
		}

		return back()->with('flash', 'Your reply has been left.');
    }

	/**
	 * @param Reply $reply
	 *
	 * @param Spam $spam
	 *
	 * @throws \Illuminate\Auth\Access\AuthorizationException
	 */
	public function update(Reply $reply, Spam $spam)
    {
    	$this->authorize('update', $reply);

    	try {
		    $this->validate(request(), ['body' => 'required|spamfree']);

		    if(request()->expectsJson()) {
			    return $reply->load('owner');
		    }

		    return back()->with('flash', 'Your reply has been left.');
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
