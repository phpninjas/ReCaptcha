<?php

namespace Google;

interface HttpClientGetAdapter {
    /**
     * @param $uri
     * @return String
     */
    public function get($uri);
}