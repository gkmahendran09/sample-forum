<?php
namespace App;

use Carbon\Carbon;

trait LocalTime {

	public function getCreatedAtAttribute( $value ) {
		return $this->getCarbonInstanceByTimezone( $value );
	}

	public function getUpdatedAtAttribute( $value ) {
		return $this->getCarbonInstanceByTimezone( $value );
	}

	/**
	 * @param $value
	 *
	 * @return mixed
	 */
	protected function getCarbonInstanceByTimezone( $value ) {
		if(request()->wantsJson()) {
			return Carbon::parse( $value )->timezone( 'Asia/Kolkata' )->toDateTimeString();
		}

		return Carbon::parse( Carbon::parse( $value )->timezone( 'Asia/Kolkata' )->toDateTimeString() );
	}
}