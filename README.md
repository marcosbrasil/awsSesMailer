# awsSesMailer

PHPMailer like, class for send emails with Amazon AWS **Simple Email Service (SES)**

## Instantiate the class correctly

`$sesMailer = new sesPhpMailer($key, $secretKey);`

The `$key` and `$secretKey` are required for auth with AWS API

## Public Methods

* **Sets the CharSet of the message** : `$sesMailer->CharSet = 'UTF-8';`
* **Holds the most recent mailer error message.** : `echo $sesMailer->ErrorInfo;`

## Minimum Requirements

* You are at least an intermediate-level PHP developer and have a basic understanding of object-oriented PHP.
* You have a valid AWS account, and you've already signed up for the services you want to use.
* The PHP interpreter, version 5.2 or newer. PHP 5.2.17 or 5.3.x is highly recommended for use with the AWS SDK for PHP.
* The cURL PHP extension (compiled with the [OpenSSL](http://openssl.org) libraries for HTTPS support).
* The ability to read from and write to the file system via [file_get_contents()](http://php.net/file_get_contents) and [file_put_contents()](http://php.net/file_put_contents).

If you're not sure whether your PHP environment meets these requirements, run the
[SDK Compatibility Test](http://github.com/amazonwebservices/aws-sdk-for-php/tree/master/_compatibility_test/) script
included in the SDK download.

