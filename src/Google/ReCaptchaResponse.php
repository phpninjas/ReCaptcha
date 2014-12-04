<?php

namespace Google;
/**
 * @immutable
 * @final
 */
final class ReCaptchaResponse
{
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
}