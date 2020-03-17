<?php

namespace NextStep\Service;


class SponsorService {

    protected $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function fetchAll() {
        $sql = 'SELECT * FROM sponsors';
        $st = $this->pdo->prepare($sql);

        if($st->execute()) {
            return $st->fetchAll(\PDO::FETCH_ASSOC);
        }
    }

    public function fetchByDistance($geo, $max_distance = null) {
        $sql = 'select name, ( point(a.longitude, a.latitude)<@>point('. $geo->longitude . ',' . $geo->latitude .') ) as distance from sponsors a order by distance';
        $st = $this->pdo->prepare($sql);

        if($st->execute()) {
            return $st->fetchAll(\PDO::FETCH_ASSOC);
        }
    }

    public function fromRow($row) {

    }


}
