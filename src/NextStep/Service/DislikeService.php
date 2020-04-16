<?php

namespace NextStep\Service;


class DislikeService extends AbstractLikeService {

    protected function getTable() {
        return "dislikes";
    }

    protected function getIDLabel() {
        return "disliked_id";
    }
}
