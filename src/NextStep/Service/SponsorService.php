<?php

namespace NextStep\Service;

use NextStep\Model\Sponsor;
use NextStep\Model\Geo;


class SponsorService {

    protected $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function fetch($id): Sponsor {
        $st = $this->pdo->prepare('SELECT * FROM sponsors WHERE id = :id');
        $st->bindValue(':id', $id);

        if($st->execute()) {
            return $this->fromRow($st->fetch());
        }
    }

    /**************************
     * uses fetchAll pretty stupidly for now
     *
     *  Also should 'LIMIT' everything eventually
     *************************/

    public function fetchAll() {
        // $sql = 'SELECT * FROM sponsors';
        $sql = 'SELECT * FROM sponsors ORDER BY id DESC';
        $st = $this->pdo->prepare($sql);

        if($st->execute()) {
            return array_map(function ($x) {
                return $this->fromRow($x);
            }, $st->fetchAll());
        }
    }

    /**************************
     * Other Crucial Filters
     * - soberdate
     * - sponsee_count
     * - rideshare
     * - avg_phone_time
     *************************/

    public function fetchByDistance($geo, $max_distance = null, $filters = array()) {
        // Should use bind values?
        $sql  = 'select *, ( point(a.longitude, a.latitude)<@>point('. $geo->longitude . ',' . $geo->latitude .') ) as distance ';
        $sql .= 'from sponsors a';

        if (isset($max_distance)) {
            $sql = 'select * from (' . $sql . ') as x where distance < '.$max_distance;
        }
        // Other filters can go here

        $sql .= ' order by distance';
        $st = $this->pdo->prepare($sql);
        if($st->execute()) {
            return array_map(function ($x) {
                return [
                    'sponsor' => $this->fromRow($x),
                    'distance' => $x['distance']
                ];
            }, $st->fetchAll());
        }
    }

    public function fetchImages(Sponsor $sponsor) {
        // can order by 'popularity' later like tinder
        $st = $this->pdo->prepare('select * from pictures where sponsor_id = :id');
        $st->bindValue(':id', $sponsor->id);

        if($st->execute()) {
            $imgs = array_map(function ($x) {
                // Shitty way to convert path to URL
                return "http://" . $_SERVER['HTTP_HOST'] . "/static/" . $x['filepath'];
            }, $st->fetchAll());

            $sponsor->addImages($imgs);
            return $sponsor;
        }
    }

    // This is gonna be really stupid but fetching images here
    public function fromRow($row) {
        $sponsor = new Sponsor(
            $row['id'],
            new Geo($row['latitude'], $row['longitude']),
            $row['name'],
            $row['soberdate'],
            $row['sponsee_count'],
            $row['rideshare'],
            $row['avg_phone_time'],
            json_decode($row['profile_data'], true)

        );

        $sponsor = $this->fetchImages($sponsor);
        return $sponsor;
    }

    public function insert($arr) {
        $sql = <<<SQL
        INSERT INTO sponsors (name, latitude, longitude, soberdate, sponsee_count, rideshare, avg_phone_time)
        VALUES (:name, :lat, :long, :soberDate, :sponseeCount, :rideShare, :phoneTime)
        RETURNING id
SQL;
        if (empty($arr['rideShare'])) {
            $arr['rideShare'] = 0;
        }

        if (isset($arr['image'])) {
            unset($arr['image']);
        }

        $st = $this->pdo->prepare($sql);
        if ($st->execute($arr)) {
            return $this->pdo->lastInsertId();
        }
    }

    public function insertImage(Sponsor $s, $img) {
        $st = $this->pdo->prepare('INSERT INTO pictures (sponsor_id, filepath) VALUES (:sid, :img)');
        $st->bindValue(':sid', $s->id);
        $st->bindValue(':img', $img);

        if ($st->execute()) {
            $s->addImages([$img]);
            return $s;
        }
    }

}
