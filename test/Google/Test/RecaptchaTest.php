<?php

namespace Google\Test;

use Google\HttpClientException;
use Google\ReCaptcha;
use Google\ReCaptchaException;
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
        $response = $recaptcha->validate("any token");

        $this->assertThat($response->isFailure(), $this->isTrue());
        $this->assertThat($response->isInvalidInputSecret(), $this->isTrue());
    }

    public function testMissingSecret()
    {
        $mockAdapter = $this->getMock('Google\HttpClientGetAdapter');
        $mockAdapter->expects($this->once())->method('get')->will($this->returnValue($this->missingSecret()));

        $recaptcha = new ReCaptcha("bad secret", $mockAdapter);
        $response = $recaptcha->validate("any token");

        $this->assertThat($response->isFailure(), $this->isTrue());
        $this->assertThat($response->isMissingInputSecret(), $this->isTrue());
    }

    public function testInvalidToken()
    {
        $mockAdapter = $this->getMock('Google\HttpClientGetAdapter');
        $mockAdapter->expects($this->once())->method('get')->will($this->returnValue($this->invalidToken()));

        $recaptcha = new ReCaptcha("bad secret", $mockAdapter);
        $response = $recaptcha->validate("any token");

        $this->assertThat($response->isFailure(), $this->isTrue());
        $this->assertThat($response->isInvalidInputResponse(), $this->isTrue());
    }


    public function testMissingToken()
    {
        $mockAdapter = $this->getMock('Google\HttpClientGetAdapter');
        $mockAdapter->expects($this->once())->method('get')->will($this->returnValue($this->missingToken()));

        $recaptcha = new ReCaptcha("some secret", $mockAdapter);

        $response = $recaptcha->validate("any token");
        $this->assertThat($response->isFailure(), $this->isTrue());
        $this->assertThat($response->isMissingInputResponse(), $this->isTrue());
    }

    public function testRealFailure()
    {
        $recaptcha = new ReCaptcha("bad secret");

        $recaptchaResponse = $recaptcha->validate("any token");
        $this->assertThat($recaptchaResponse, $this->equalTo(new ReCaptchaResponse(false, ["invalid-input-response","invalid-input-secret"])));
    }

    public function testInvalidJSON(){

        $this->setExpectedException("Google\ReCaptchaException");

        $httpAdapter = $this->getMock("Google\HttpClientGetAdapter");
        $httpAdapter->expects($this->once())->method('get')->will($this->returnValue("what no json here!"));

        $recaptcha = new ReCaptcha("some secret", $httpAdapter);
        $recaptcha->validate("any token");

    }

    public function testCustomHttpClientExceptionHandling(){

        $httpException = new \Exception();

        $httpAdapter = $this->getMock("Google\HttpClientGetAdapter");
        $httpAdapter->expects($this->once())->method('get')->will($this->throwException($httpException));

        try {

            $recaptcha = new ReCaptcha("some secret", $httpAdapter);
            $recaptcha->validate("any token");
        }catch (ReCaptchaException $e){

        }

        $this->assertThat($e, $this->isInstanceOf("Google\ReCaptchaException"));
        // must contain previously thrown exception
        $this->assertThat($e->getPrevious(), $this->equalTo($httpException));
    }

    public function testNonExpectedJSONResponse(){

        $this->setExpectedException("Google\MalformedResponseException");

        $httpAdapter = $this->getMock("Google\HttpClientGetAdapter");
        $httpAdapter->expects($this->once())->method('get')->will($this->returnValue(json_encode(["some other key" => "some other value"])));

        $recaptcha = new ReCaptcha("some secret", $httpAdapter);
        $recaptcha->validate("any token");
    }

    /**
     * This behaviour is given off by google for some reason...
     */
    public function testMissingErrorCodes(){

        $httpAdapter = $this->getMock("Google\HttpClientGetAdapter");
        $httpAdapter->expects($this->once())->method('get')->will($this->returnValue(json_encode(["success"=>false])));

        $recaptcha = new ReCaptcha("some secret", $httpAdapter);
        $response = $recaptcha->validate("any token");

        $this->assertThat($response->isFailure(), $this->isTrue());
        $this->assertThat($response->isUnknownError(), $this->isTrue());
    }

    public function testUnknownErrorCodes(){
        $httpAdapter = $this->getMock("Google\HttpClientGetAdapter");
        $httpAdapter->expects($this->once())->method('get')->will($this->returnValue(json_encode(["success"=>false,"error-codes"=>["Something I made up"]])));

        $recaptcha = new ReCaptcha("some secret", $httpAdapter);
        $response = $recaptcha->validate("any token");

        $this->assertThat($response->isFailure(), $this->isTrue());
        $this->assertThat($response->isUnknownError(), $this->isTrue());
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