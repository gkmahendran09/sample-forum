<?php

namespace App\Http\Controllers;

use App\Inspections\Spam;
use App\Reply;
use App\Thread;

use Illuminate\Http\Request;

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
	 * @param Spam $spam
	 *
	 * @return $this|\Illuminate\Http\RedirectResponse
	 */
	public function store($channelId, Thread $thread, Spam $spam) {

		$this->validate(request(), ['body' => 'required']);

		$spam->detect(request('body'));

    	$reply = $thread->addReply([
    		'body' => request('body'),
		    'user_id' => auth()->id()
	    ]);

    	if(request()->expectsJson()) {
    		return $reply->load('owner');
	    }

    	return back()->with('flash', 'Your reply has been left.');
    }

	/**
	 * @param Reply $reply
	 *
	 * @throws \Illuminate\Auth\Access\AuthorizationException
	 */
	public function update(Reply $reply)
    {
    	$this->authorize('update', $reply);

    	$reply->update(request(['body']));
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
