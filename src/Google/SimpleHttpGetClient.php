<?php

namespace Google;

class SimpleHttpGetClient implements HttpClientGetAdapter
{

    /**
     * @var int
     */
    private $timeout;

    public function __construct($timeout = 5)
    {
        $this->timeout = $timeout;
    }

    /**
     * Fetch a server page.
     * @param $uri
     * @return String
     */
    public function get($uri)
    {
        // warning will probably happen.
        $response = file_get_contents($uri, null, stream_context_create(["http" => ["timeout" => $this->timeout]]));
        if ($response !== false) {
            return $response;
        } else {
            throw new HttpClientException("GET $uri failed.");
        }
    }
}
