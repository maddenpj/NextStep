<?php

namespace NextStep\Service;

use PDO;
use NextStep\Model\Sponsor;

abstract class AbstractLikeService {

    abstract protected function getTable();
    abstract protected function getIDLabel();

    protected $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function fetch(Sponsor $s) {
        $sql = "SELECT * FROM {$this->getTable()} WHERE base_id = :id";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(':id', $s->id);
        if($st->execute()) {
            return $st->fetchAll();
        }
    }

    public function insert(Sponsor $s, $id) {
        $sql = "INSERT INTO {$this->getTable()} (base_id, {$this->getIDLabel()}) VALUES ";
        $sql .= "({$s->id}, {$id}) RETURNING id"; // Not using bindValues
        $st = $this->pdo->prepare($sql);
        $st->execute();
        return $st->fetch()["id"];
    }

}
