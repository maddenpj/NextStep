<?php

namespace NextStep\Model;

use NextStep\Util\ImmutableProperties;


class Geo {

    use ImmutableProperties;

    protected $latitude;
    protected $longitude;

    public function __construct(
        float $latitude,
        float $longitude
    ) {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

}
