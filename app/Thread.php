<?php

namespace App;

use App\Events\ThreadReceivedNewReply;
use Illuminate\Database\Eloquent\Model;

class Thread extends Model {
	use RecordsActivity, LocalTime;

	protected $guarded = [];

	protected $with = [ 'creator', 'channel' ];

	protected $appends = [ 'isSubscribedTo' ];

	protected static function boot() {
		parent::boot();

		static::deleting( function ( $thread ) {
			$thread->replies->each->delete();
		} );
	}

	public function path() {
		return "/threads/{$this->channel->slug}/{$this->id}";
	}

	public function replies() {
		return $this->hasMany( Reply::class );
	}

	public function creator() {
		return $this->belongsTo( User::class, 'user_id' );
	}

	public function channel() {
		return $this->belongsTo( Channel::class );
	}

	public function addReply( $reply ) {
		$reply = $this->replies()->create( $reply );

		event(new ThreadReceivedNewReply($reply));

		return $reply;
	}

	public function scopeFilter( $query, $filters ) {
		return $filters->apply( $query );
	}

	public function subscribe( $userId = null ) {
		$this->subscriptions()->create( [
			'user_id' => $userId ?: auth()->id()
		] );

		return $this;
	}

	public function unsubscribe( $userId = null ) {
		$this->subscriptions()
		     ->where( 'user_id', $userId ?: auth()->id() )
		     ->delete();
	}

	public function subscriptions() {
		return $this->hasMany( ThreadSubscription::class );
	}

	public function getIsSubscribedToAttribute() {
		return $this->subscriptions()
		            ->where( 'user_id', auth()->id() )
		            ->exists();
	}

	public function hasUpdatesFor($user)
	{
		// Look in the cache for the proper key
		$key = $user->visitedThreadCacheKey($this);

		// compare that carbon instance with the $thread->updated_at
		return $this->updated_at > cache($key);
	}
}
