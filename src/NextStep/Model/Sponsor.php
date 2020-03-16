<?php

namespace NextStep\Model;

use NextStep\Util\ImmutableProperties;


class Sponsor {

    use ImmutableProperties;

    protected $id;
    protected $geo;
    protected $soberTime;

    public function __construct(
        $id,
        $geo,
        $soberTime
    ) {
        $this->id = $id;
        $this->geo = $geo;
        $this->soberTime = $soberTime;
    }

}
