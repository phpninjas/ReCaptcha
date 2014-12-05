I'M NOT A ROBOT
===============

ReCaptcha is a free CAPTCHA service that protect websites from spam and abuse.
This provides plugins for third-party integration with ReCAPTCHA.

https://developers.google.com/recaptcha/intro

This supports the latest Google ReCaptcha with the "I'm not a robot" tickbox.

Installation
============

Composer is used for installation.

```bash
curl -s https://getcomposer.org/installer | php
php composer.phar install
```

Your composer.json file should look like this:

```javascript
{
  require: {
    "phpninjas/recaptcha": "0.1.0"
  }
}
```

Example
=======

To load the ReCaptcha on your page.
```html
<html>
  <body>
    <div class="g-recaptcha" data-sitekey="your_site_key"></div>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  </body>
</html>
```

To verify the captcha at server side.
```php
<?php
require_once "vendor/autoload.php"

use Google\ReCaptcha;

$recaptcha = new ReCaptcha($secret);
$resp = $recaptcha->validate($_POST['g-recaptcha-response']);

assert($resp->isSuccess());
```

Stubbing for tests
==================

As most people want to be able to stub out the behaviour of a valid/invalid recaptcha it makes sense to be able to do this.
Simply replace the http adapter with a mocked/stubbed one and you can validate the functionality of the the recaptcha library
in the context of your code.

```php
<?php

require_once "vendor/autoload.php"

class MyTest extends \PHPUnit_Framework_TestCase {

    public function testValidRecaptcha(){
    
        $goodResponse = [
          "success" => true
        ];
    
        $mockAdapter = $this->getMock('Google\HttpClientGetAdapter');
        $mockAdapter->expects($this->once())->method('get')->will($this->returnValue(json_encode($goodResponse)));

        $recaptcha = new ReCaptcha("secret", $mockAdapter);
        $this->assertThat($recaptcha->validate("my token thing"), $this->equalTo(new ReCaptchaResponse(true)));
    }
    
    public function testInvalidRecaptcha(){
        $badResponse = [
          "success" => false,
          "error-codes" => ["error1"]
        ];
    
        $mockAdapter = $this->getMock('Google\HttpClientGetAdapter');
        $mockAdapter->expects($this->once())->method('get')->will($this->returnValue(json_encode($badResponse)));

        $recaptcha = new ReCaptcha("secret", $mockAdapter);
        $this->assertThat($recaptcha->validate("my token thing"), $this->equalTo(new ReCaptchaResponse(false, ["error1"])));
    }
}  
    
```
    
    
Rest Handlers
=============

By default this library ships with a very skinny http client. This client CAN throws warnings due to network errors.
If you want to use something more professional I would recommend looking at Guzzle https://github.com/guzzle/guzzle or another HTTP Client that provides
better request/response handling. Until that time the default adapter should suffice.

If you want to provide a separate http adapter then simply implement the HttpClientGetAdapter interface and the only required method.

```php
class MyHttpClient implements Google\HttpClientGetAdapter {
    public function get($uri){
        // do http magics here 
        // fsockopen?
        // guzzle->get()
        // curl_exec()
        return $body
    }
}

$recaptcha = new ReCaptcha("secret", new MyHttpClient());
$recaptcha->validate("response token");

```

Exception Handling
==================

By default the ReCaptcha library handles any exceptions thrown by the http client that it utilises.
These get wrapped into ReCaptchaException objects (which contain the inner exception). Thereby you only need to 
handle 1 exception when utilising the recaptcha.

```php
try {
    $recaptcha = new ReCaptcha("secret", new Guzzle());
    $recaptcha->validate("response token");
} catch(ReCaptchaException $e){
    // peek at the inner exception
    $e->getPrevious();
}

```

