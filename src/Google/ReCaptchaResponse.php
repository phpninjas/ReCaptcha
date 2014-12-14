<?php

namespace Google;
/**
 * @immutable
 * @final
 */
final class ReCaptchaResponse
{

    const MISSING_INPUT_SECRET = "missing-input-secret";
    const INVALID_INPUT_SECRET = "invalid-input-secret";
    const MISSING_INPUT_RESPONSE = "missing-input-response";
    const INVALID_INPUT_RESPONSE = "invalid-input-response";

    private $success;
    private $errors;

    /**
     * @param bool $success
     * @param array $errors
     */
    public function __construct($success = false, $errors = [])
    {
        $this->success = !!$success;
        $this->errors = $errors;
    }

    /**
     * @return bool
     */
    public function isSuccess(){
        return $this->success;
    }

    /**
     * @return bool
     */
    public function isFailure(){
        return !$this->isSuccess();
    }

    public function isMissingInputSecret(){
        return array_search(static::MISSING_INPUT_SECRET, $this->errors)!==false;
    }

    public function isMissingInputResponse(){
        return array_search(static::MISSING_INPUT_RESPONSE, $this->errors)!==false;
    }

    public function isInvalidInputSecret(){
        return array_search(static::INVALID_INPUT_SECRET, $this->errors)!==false;
    }

    public function isInvalidInputResponse(){
        return array_search(static::INVALID_INPUT_RESPONSE, $this->errors)!==false;
    }

}