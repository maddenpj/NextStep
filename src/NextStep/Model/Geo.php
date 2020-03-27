<?php

namespace NextStep\Model;

use NextStep\Util\ImmutableProperties;


class Geo implements \JsonSerializable {

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

    public function jsonSerialize () {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude
        ];
    }

}
