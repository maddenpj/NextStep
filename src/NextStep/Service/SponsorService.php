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


}
