<?php
namespace App;

use Carbon\Carbon;

trait LocalTime {

	public function getCreatedAtAttribute( $value ) {
		return $this->convertToProperTimezone( $value );
	}

	public function getUpdatedAtAttribute( $value ) {
		return $this->convertToProperTimezone( $value );
	}

	/**
	 * @param $value
	 *
	 * @return mixed
	 */
	protected function convertToProperTimezone( $value ) {
		if(request()->wantsJson()) {
			return Carbon::parse( $value )->timezone( 'Asia/Kolkata' )->toDateTimeString();
		}

		return Carbon::parse( $value )->timezone( 'Asia/Kolkata' );
	}
}