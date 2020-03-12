<?php

namespace NextStep\Model;


class Sponsor {

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
