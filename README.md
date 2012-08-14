# awsSesMailer - [About](http://www.verbose.com.br/2012/08/amazon-ses-simple-email-service.html)

PHPMailer like, class for send emails with Amazon AWS **Simple Email Service (SES)**

## Instantiate the class correctly

`$sesMailer = new sesPhpMailer($key, $secretKey);`

The `$key` and `$secretKey` are required for auth with AWS API

## Public Atributes

* **Sets the CharSet of the message** : `$sesMailer->CharSet = 'UTF-8';`
* **Holds the most recent mailer error message.** : `echo $sesMailer->ErrorInfo;`
* **Sets the Sender email (Return-Path) of the message: ** `$sesMailer->Sender = 'email@yourdomain.com';`
* **Sets the Body of the message: ** `$sesMailer->Body = '<b>HTML</b> or Text formats'`
* **Set the Return Path to email message: ** `$sesMailer->ReturnPath = 'return@email.com'`

## Public Methods

* **Adds a "To" address (Max 50 for each sent email): ** `$sesMailer->AddAddress($strEmail, $strName);`
* **Adds a "Cc" address (Max 50): ** `$sesMailer->AddCC($strEmail, $strName);`
* **Adds a "Bcc" address (Max 50): ** `$sesMailer->AddBCC($strEmail, $strName);`
* **Adds a "Reply-to" address: ** `$sesMailer->AddReplyTo($strEmail, $strName);`
* **Set the From and FromName properties: ** `$sesMailer->SetFrom($strEmail, $strName, $boolAuto)`
* **Clears all recipients assigned in the TO array: ** `$sesMailer->ClearAddresses();`
* **Clears all recipients assigned in the CC array: ** `$sesMailer->ClearCCs();`
* **Clears all recipients assigned in the BCC array: ** `$sesMailer->ClearBCCs();`
* **Clears all recipients assigned in the ReplyTo array: ** `$sesMailer->ClearReplyTos();`
* **Clears all recipients assigned in the TO, CC and BCC: ** `$sesMailer->ClearAllRecipients();`
* **Send Email to Amazon. (Return (bool)true or false): ** `$sesMailer->Send();`

## Minimum Requirements

* You are at least an intermediate-level PHP developer and have a basic understanding of object-oriented PHP.
* You have a valid AWS account, and you've already signed up for the services you want to use.
* The PHP interpreter, version 5.2 or newer. PHP 5.2.17 or 5.3.x is highly recommended for use with the AWS SDK for PHP.
* The cURL PHP extension (compiled with the [OpenSSL](http://openssl.org) libraries for HTTPS support).
* The ability to read from and write to the file system via [file_get_contents()](http://php.net/file_get_contents) and [file_put_contents()](http://php.net/file_put_contents).

If you're not sure whether your PHP environment meets these requirements, run the
[SDK Compatibility Test](http://github.com/amazonwebservices/aws-sdk-for-php/tree/master/_compatibility_test/) script
included in the SDK download.

