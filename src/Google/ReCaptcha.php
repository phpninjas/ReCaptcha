<?php
/**
 * This is a PHP library that handles calling reCAPTCHA.
 *    - Documentation and latest version
 *          https://github.com/phpninjas/ReCaptcha
 *    - Create a reCAPTCHA API Key
 *          https://www.google.com/recaptcha/admin
 *    - Google Discussion group
 *          http://groups.google.com/group/recaptcha
 *
 * @copyright Copyright (c) 2014, PHPNinjas Ltd.
 * @link      https://github.com/phpninjas/ReCaptcha
 *
 *    Copyright 2014 PHPNinjas Ltd.
 *
 *   Licensed under the Apache License, Version 2.0 (the "License");
 *   you may not use this file except in compliance with the License.
 *   You may obtain a copy of the License at
 *
 *       http://www.apache.org/licenses/LICENSE-2.0
 *
 *   Unless required by applicable law or agreed to in writing, software
 *   distributed under the License is distributed on an "AS IS" BASIS,
 *   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *   See the License for the specific language governing permissions and
 *   limitations under the License.
 */
namespace Google;

class ReCaptcha
{
    private $url = "https://www.google.com/recaptcha/api/siteverify?";
    /**
     * @var string
     */
    private $secret;
    /**
     * @var HttpClientGetAdapter
     */
    private $httpClient;

    /**
     * @param String $secret
     * @param HttpClientGetAdapter $httpClient - defaults to SimpleHttpGetClient
     */
    function __construct($secret, HttpClientGetAdapter $httpClient = null)
    {

        $this->secret = $secret;
        $this->httpClient = $httpClient?:new SimpleHttpGetClient();
    }

    /**
     * Calls the reCAPTCHA site verify API to verify whether the user passes
     * CAPTCHA test.
     *
     * @param string $token recaptcha response token
     * @param string $ip IP address of end user.
     *
     * @return ReCaptchaResponse
     */
    public function validate($token, $ip = null)
    {
        // Discard empty solution submissions
        $data = [
            'secret' => $this->secret,
            'response' => $token
        ];
        if ($ip) {
            $data['remoteip'] = $ip;
        }
        try {
            $response = $this->httpClient->get($this->url . http_build_query($data));
        } catch (\Exception $e) {
            // repackage into recaptcha exception.
            throw new ReCaptchaException($e->getMessage(), $e->getCode(), $e);
        }
        // verify json decoding.
        if (null !== ($json = json_decode($response, true))) {

            if(array_key_exists("success", $json) && $json['success']) {
                return new ReCaptchaResponse(true);
            }elseif(array_key_exists("error-codes", $json) && is_array($json['error-codes'])) {
                return new ReCaptchaResponse(false, $json['error-codes']);
            }elseif(array_key_exists("success", $json) && !$json['success']){
                return new ReCaptchaResponse(false, []);
            }else{
                throw new MalformedResponseException($response);
            }
        }
        throw new MalformedResponseException($response);
    }
}