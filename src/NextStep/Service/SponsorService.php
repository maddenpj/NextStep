<?php

namespace NextStep\Service;

use NextStep\Model\Sponsor;
use NextStep\Model\Geo;


class SponsorService {

    protected $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**************************
     * uses fetchAll pretty stupidly for now
     *************************/

    public function fetchAll() {
        // $sql = 'SELECT * FROM sponsors';
        $sql = 'SELECT * FROM sponsors ORDER BY id';
        $st = $this->pdo->prepare($sql);

        if($st->execute()) {
            return array_map(function ($x) {
                return $this->fromRow($x);
            }, $st->fetchAll(\PDO::FETCH_ASSOC));
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
            }, $st->fetchAll(\PDO::FETCH_ASSOC));
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
            $row['avg_phone_time']

        );


        // can order by 'popularity' later like tinder
        $st = $this->pdo->prepare('select * from pictures where sponsor_id = :id');
        $st->bindValue(':id', $sponsor->id);

        if($st->execute()) {
            $sponsor->addImages($st->fetchAll(\PDO::FETCH_ASSOC));
        }
        return $sponsor;
    }


}
