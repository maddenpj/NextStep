<?php

namespace NextStep\Model;

use NextStep\Util\ImmutableProperties;


class Sponsor {

    use ImmutableProperties;

    protected $id;
    protected $soberTime;

    public function __construct(
        $id,
        $soberTime
    ) {
        $this->id = $id;
        $this->soberTime = $soberTime;
    }

}
