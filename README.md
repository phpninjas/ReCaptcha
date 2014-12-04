ReCaptcha is a free CAPTCHA service that protect websites from spam and abuse.
This provides plugins for third-party integration with ReCAPTCHA.

Installation
============

Composer is used for installation.

    curl -s https://getcomposer.org/installer | php
    php composer.phar install

Your composer.json file should look like this:

    {
      require: {
        "phpninjas/recaptcha": "dev-master"
      }
    }

Example
=======

    <?php
    require_once "vendor/autoload.php"
    
    use Google\ReCaptcha;
    
    $recaptcha = new ReCaptcha($secret);
    $resp = $recaptcha->validate($_POST['g-recaptcha-response']);
    
    assert($resp->isSuccess());
    
    
