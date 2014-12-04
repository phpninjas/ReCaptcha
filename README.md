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
    "phpninjas/recaptcha": "dev-master"
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
    
