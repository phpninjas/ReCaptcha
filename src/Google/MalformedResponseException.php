<?php

namespace Google;

class MalformedResponseException extends ReCaptchaException {
    public function __construct($response, $code = 0, $previous = null){
        parent::__construct("Malformed response: '$response'", $code, $previous);
    }
}