<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class ProfilesController extends Controller
{
    public function show( User $user)
    {
	    return view('profiles.show', [
    		'profileUser' => $user,
		    'activities' => $this->getActivity( $user )
	    ]);
    }

	/**
	 * @param User $user
	 *
	 * @return static
	 */
	protected function getActivity( User $user ) {
		return $user->activity()->latest()->with( 'subject' )->get()->take( 50 )->groupBy( function ( $activity ) {
			return $activity->created_at->format( 'Y-m-d' );
		} );
	}

}
