<?php

namespace NextStep\Service;


class LikeService extends AbstractLikeService {

    protected function getTable() {
        return "likes";
    }

    protected function getIDLabel() {
        return "liked_id";
    }
}
