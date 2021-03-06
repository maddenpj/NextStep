<?php

namespace NextStep\Model;

use NextStep\Util\ImmutableProperties;


class Sponsor implements \JsonSerializable {

    use ImmutableProperties;

    protected $id;
    protected $geo;
    protected $name;

    protected $soberDate;
    protected $sponseeCount;
    protected $rideShare; // Not really a php boolean
    protected $phoneTime;

    protected $profileData;

    protected $images = [];

    public function __construct(
        $id,
        $geo,
        $name,
        $soberDate,
        $sponseeCount,
        $rideShare,
        $phoneTime,
        $profileData
    ) {
        $this->id = $id;
        $this->geo = $geo;
        $this->name = $name;
        $this->soberDate = $soberDate;
        $this->sponseeCount = $sponseeCount;
        $this->rideShare = $rideShare;
        $this->phoneTime = $phoneTime;
        $this->profileData = $profileData;
    }

    public function getDaysSober() {
        $n = new \DateTime();
        return $n->diff(new \DateTime($this->soberDate))->format("%a");
    }

    public function addImages(array $imgs) {
        $this->images = array_merge($this->images, $imgs);
    }

    // Probably should scrub 'id'
    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'soberDate' => $this->soberDate,
            'soberTime' => $this->getDaysSober(),
            'sponseeCount' => $this->sponseeCount,
            'rideShare' => $this->rideShare,
            'phoneTime' => $this->phoneTime,
            'geo' => $this->geo,
            'profile' => $this->profileData,
            'images' => $this->images
        ];
    }

}
