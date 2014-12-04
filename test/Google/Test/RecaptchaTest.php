<?php

namespace Google\Test;

use Google\HttpClientException;
use Google\ReCaptcha;
use Google\ReCaptchaResponse;

final class RecaptchaTest extends \PHPUnit_Framework_TestCase
{

    public function testSuccessfulCaptcha()
    {

        $mockAdapter = $this->getMock('Google\HttpClientGetAdapter');
        $mockAdapter->expects($this->once())->method('get')->with($this->validUrl())->will($this->returnValue($this->goodResponse()));

        $recaptcha = new ReCaptcha("secret", $mockAdapter);
        $this->assertThat($recaptcha->validate("my token thing"), $this->equalTo(new ReCaptchaResponse(true)));

    }

    public function testSuccessWithIP()
    {
        $mockAdapter = $this->getMock('Google\HttpClientGetAdapter');
        $mockAdapter->expects($this->once())->method('get')->with($this->validUrl() . "&remoteip=1.2.3.4")->will($this->returnValue($this->goodResponse()));

        $recaptcha = new ReCaptcha("secret", $mockAdapter);
        $this->assertThat($recaptcha->validate("my token thing", "1.2.3.4"), $this->equalTo(new ReCaptchaResponse(true)));

    }


    public function testHttpException()
    {
        // expect transformation
        $this->setExpectedException("Google\RecaptchaException");
        $mockAdapter = $this->getMock('Google\HttpClientGetAdapter');
        $mockAdapter->expects($this->once())->method('get')->will($this->throwException(new HttpClientException()));

        $recaptcha = new ReCaptcha("any secret", $mockAdapter);
        $recaptcha->validate("any token");

    }

    public function testInvalidSecret()
    {
        $mockAdapter = $this->getMock('Google\HttpClientGetAdapter');
        $mockAdapter->expects($this->once())->method('get')->will($this->returnValue($this->invalidSecret()));

        $recaptcha = new ReCaptcha("bad secret", $mockAdapter);

        $this->assertThat($recaptcha->validate("any token"), $this->equalTo(new ReCaptchaResponse(false, ["invalid-input-secret"])));
    }

    public function testMissingSecret()
    {
        $mockAdapter = $this->getMock('Google\HttpClientGetAdapter');
        $mockAdapter->expects($this->once())->method('get')->will($this->returnValue($this->missingSecret()));

        $recaptcha = new ReCaptcha("bad secret", $mockAdapter);

        $this->assertThat($recaptcha->validate("any token"), $this->equalTo(new ReCaptchaResponse(false, ["missing-input-secret"])));
    }

    public function testInvalidToken()
    {
        $mockAdapter = $this->getMock('Google\HttpClientGetAdapter');
        $mockAdapter->expects($this->once())->method('get')->will($this->returnValue($this->invalidToken()));

        $recaptcha = new ReCaptcha("bad secret", $mockAdapter);

        $this->assertThat($recaptcha->validate("any token"), $this->equalTo(new ReCaptchaResponse(false, ["invalid-input-response"])));
    }


    public function testMissingToken()
    {
        $mockAdapter = $this->getMock('Google\HttpClientGetAdapter');
        $mockAdapter->expects($this->once())->method('get')->will($this->returnValue($this->missingToken()));

        $recaptcha = new ReCaptcha("bad secret", $mockAdapter);

        $this->assertThat($recaptcha->validate("any token"), $this->equalTo(new ReCaptchaResponse(false, ["missing-input-response"])));
    }

    public function testRealFailure()
    {
        $recaptcha = new ReCaptcha("bad secret");

        $recaptchaResponse = $recaptcha->validate("any token");
        $this->assertThat($recaptchaResponse, $this->equalTo(new ReCaptchaResponse(false, ["invalid-input-response","invalid-input-secret"])));
    }


    private function validUrl()
    {
        return "https://www.google.com/recaptcha/api/siteverify?secret=secret&response=my+token+thing";
    }

    private function goodResponse()
    {
        return <<<EOF
{"success": true}
EOF;

    }

    private function missingSecret()
    {
        return <<<EOF
{"success": false, "error-codes": ["missing-input-secret"]}
EOF;
    }

    private function invalidSecret()
    {
        return <<<EOF
{"success": false, "error-codes": ["invalid-input-secret"]}
EOF;
    }

    private function missingToken()
    {
        return <<<EOF
{"success": false, "error-codes": ["missing-input-response"]}
EOF;
    }

    private function invalidToken()
    {
        return <<<EOF
{"success": false, "error-codes": ["invalid-input-response"]}
EOF;
    }

}