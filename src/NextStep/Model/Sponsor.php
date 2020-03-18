<?php

namespace NextStep\Model;

use NextStep\Util\ImmutableProperties;


class Sponsor {

    use ImmutableProperties;

    protected $id;
    protected $geo;
    protected $name;

    public function __construct(
        $id,
        $geo,
        $name
    ) {
        $this->id = $id;
        $this->geo = $geo;
        $this->name = $name;
    }

}
