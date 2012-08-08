<?php
ini_set('allow_url_fopen', 1); // - Enable 'allow_url_fopen' in php.ini

require_once('sdk-1.5.10/sdk.class.php');//load RUNTIME sdk class

class sesPhpMailer extends AmazonSES {
    
    /////////////////////////////////////////////////
    // PROPERTIES, PUBLIC
    /////////////////////////////////////////////////

    /**
    * Sets the CharSet of the message.
    * @var string
    */
    public $CharSet           = 'UTF-8';

    /**
    * Holds the most recent mailer error message.
    * @var string
    */
    public $ErrorInfo         = '';

    /**
    * Sets the From email address for the message.
    * @var string
    */
    public $From              = '';

    /**
    * Sets the Sender email (Return-Path) of the message.  If not empty,
    * will be sent via -f to sendmail or as 'MAIL FROM' in smtp mode.
    * @var string
    */
    public $Sender            = '';

    /**
    * Sets the Subject of the message.
    * @var string
    */
    public $Subject           = '';

    /**
    * Sets the Body of the message.  This can be either an HTML or text body.
    * If HTML then run IsHTML(true).
    * @var string
    */
    public $Body              = '';

    /**
    * Sets the text-only body of the message.  This automatically sets the
    * email to multipart/alternative.  This body can be read by mail
    * clients that do not have HTML email capability such as mutt. Clients
    * that can read HTML will view the normal Body.
    * @var string
    */
    public $AltBody           = '';
    
    /**
     * Set the Return Path to email message 
     */
    public $ReturnPath = '';


    /////////////////////////////////////////////////
    // PROPERTIES, PRIVATE AND PROTECTED
    /////////////////////////////////////////////////
    
    protected   $to             = array();
    protected   $cc             = array();
    protected   $bcc            = array();
    protected   $ReplyTo        = array();
    protected   $error_count    = 0;
    protected   $arrConfig      = array(); //set array config with amazon credentials
    protected   $ContentType    = 'Html';
    protected   $debug          = true;

    /////////////////////////////////////////////////
    // METHODS, RECIPIENTS
    /////////////////////////////////////////////////

    public function __construct($strKey, $strSecret){
        $this->amazonPass($strKey, $strSecret);
        parent::__construct($this->arrConfig);
    }

    /**
    * Sets Credentials for login into amazonSES.
    * @param string $strKey // Amazon Key for aws account
    * @param string $strSecret // Secret Key for aws account
    * @return void
    */
    public function amazonPass($strKey, $strSecret){
        $this->arrConfig = array(
            'key' => $strKey,
            'secret' => $strSecret
        );
    }
    
    /**
     * Set debug type
     * @param int $enable // only 0 or 1 
     * Set 1 for display send errors or 0 for hide it
     */
    public function setDebug($enable = 0){
        $this->debug = ($enable === 0) ? false : true;
    }

    /**
    * Sets message type to HTML.
    * @param bool $ishtml
    * @return void
    */
    public function IsHTML($ishtml = true) {
        if ($ishtml) {
            $this->ContentType = 'Html';
        } else {
            $this->ContentType = 'Text';
        }
    }
    
    /**
    * Adds a "To" address.
    * @param string $address
    * @param string $name
    * @return boolean true on success, false if address already used
    */
    public function AddAddress($address, $name = '') {
        return $this->AddAnAddress('to', $address, $name);
    }

    /**
    * Adds a "Cc" address.
    * 
    * @param string $address
    * @param string $name
    * @return boolean true on success, false if address already used
    */
    public function AddCC($address, $name = '') {
        return $this->AddAnAddress('cc', $address, $name);
    }

    /**
    * Adds a "Bcc" address.
    * 
    * @param string $address
    * @param string $name
    * @return boolean true on success, false if address already used
    */
    public function AddBCC($address, $name = '') {
        return $this->AddAnAddress('bcc', $address, $name);
    }

    /**
    * Adds a "Reply-to" address.
    * @param string $address
    * @param string $name
    * @return boolean
    */
    public function AddReplyTo($address, $name = '') {
        return $this->AddAnAddress('Reply-To', $address, $name);
    }
    
    /**
    * Adds an address to one of the recipient arrays
    * Addresses that have been added already return false, but do not throw exceptions
    * @param string $kind One of 'to', 'cc', 'bcc', 'ReplyTo'
    * @param string $address The email address to send to
    * @param string $name
    * @return boolean true on success, false if address already used or invalid in some way
    * @access protected
    */
    protected function AddAnAddress($kind, $address, $name = '') {
        
        if (!preg_match('/^(to|cc|bcc|Reply-To)$/', $kind)) {
            $this->SetError('Invalid recipient array => '.$kind);
            return false;
        }
        
        $address = trim($address);
        $name = trim(preg_replace('/[\r\n]+/', '', $name)); //Strip breaks and trim
        
        if (!self::ValidateAddress($address)) {
            $this->SetError('invalid_address => '.$address);
            return false;
        }
        
        $formated = trim($name . ' <' . $address . '>');
        
        switch ($kind){
            case 'to': $this->to[] = $formated; break;
            case 'cc': $this->cc[] = $formated; break;
            case 'bcc': $this->bcc[] = $formated; break;
            case 'Reply-To': $this->ReplyTo[] = $formated; break;
            default: $this->SetError('Invalid recipient array => '.$kind); break;
        }
        return false;
    }

    /**
    * Set the From and FromName properties
    * @param string $address
    * @param string $name
    * @return boolean
    */
    public function SetFrom($address, $name = '', $auto = true) {
        $address = trim($address);
        $name = trim(preg_replace('/[\r\n]+/', '', $name)); //Strip breaks and trim
        
        if (!self::ValidateAddress($address)) {
            $this->SetError('invalid_address => '.$address);
            return false;
        }
        
        $this->From =  $name . ' <' . $address . '>';
        
        if ($auto) {
            if (empty($this->ReplyTo)) {
                $this->AddAnAddress('Reply-To', $address, $name);
            }
            if (empty($this->Sender)) {
                $this->Sender = $address;   
            }
            if ($this->ReturnPath == '') {
                $this->ReturnPath = $address;
            }
        }
        return true;
    }
    
    /**
    * Check that a string looks roughly like an email address should
    * Static so it can be used without instantiation
    * Tries to use PHP built-in validator in the filter extension (from PHP 5.2), falls back to a reasonably competent regex validator
    * Conforms approximately to RFC2822
    * @link http://www.hexillion.com/samples/#Regex Original pattern found here
    * @param string $address The email address to check
    * @return boolean
    * @static
    * @access public
    */
    public static function ValidateAddress($address) {
        if (function_exists('filter_var')) { //Introduced in PHP 5.2
            if(filter_var($address, FILTER_VALIDATE_EMAIL) === FALSE) {
                return false;
            } else {
                return true;
            }
        } else {
            return preg_match('/^(?:[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+\.)*[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+@(?:(?:(?:[a-zA-Z0-9_](?:[a-zA-Z0-9_\-](?!\.)){0,61}[a-zA-Z0-9_-]?\.)+[a-zA-Z0-9_](?:[a-zA-Z0-9_\-](?!$)){0,61}[a-zA-Z0-9_]?)|(?:\[(?:(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\.){3}(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\]))$/', $address);
        }
    }
    
    /**
    * Adds the error message to the error container.
    * @access protected
    * @return void
    */
    protected function SetError($msg) {
        $this->error_count++;
        $this->ErrorInfo = $msg;
    }
    
    /////////////////////////////////////////////////
    // CLASS METHODS, MESSAGE RESET
    /////////////////////////////////////////////////

    /**
    * Clears all recipients assigned in the TO array.  Returns void.
    * @return void
    */
    public function ClearAddresses() {
        $this->to = array();
    }

    /**
    * Clears all recipients assigned in the CC array.  Returns void.
    * @return void
    */
    public function ClearCCs() {
        $this->cc = array();
    }

    /**
    * Clears all recipients assigned in the BCC array.  Returns void.
    * @return void
    */
    public function ClearBCCs() {
        $this->bcc = array();
    }

    /**
    * Clears all recipients assigned in the ReplyTo array.  Returns void.
    * @return void
    */
    public function ClearReplyTos() {
        $this->ReplyTo = array();
    }

    /**
    * Clears all recipients assigned in the TO, CC and BCC
    * array.  Returns void.
    * @return void
    */
    public function ClearAllRecipients() {
        $this->to = array();
        $this->cc = array();
        $this->bcc = array();
    }

    ///////////////////////////////////////////////
    // SEND METHOD
    ///////////////////////////////////////////////
    
    protected function PreSend() {
   
        if (count($this->to) < 1) {
            $this->SetError('provide_address');
            return false;
        }

        //Refuse to send an empty message
        if (empty($this->Body)) {
            $this->SetError('empty_message');
            return false;
        }
      
        return true;
    }
    
    public function Send(){

        if(!$this->PreSend()){
            return false;
        }
        
        $destination = array(
            'ToAddresses' => $this->to,
            'CcAddresses' => $this->cc,
            'BccAddresses' => $this->bcc
        );
        
        $message = array(
            'Subject.Data' => $this->Subject,
            'Body.'. $this->ContentType .'.Data' => $this->Body,
            'Body.'. $this->ContentType .'.Charset' => $this->CharSet
        );
        
        $opt = array(
            'ReplyToAddresses' => $this->ReplyTo,
            'ReturnPath' => $this->ReturnPath
        );
        
        $response = $this->send_email($this->From,$destination,$message,$opt);
        if (!$response->isOK()){
            return ($this->debug) ? var_dump($response) : false;
        }else{
            return true;
        }
    }
       
}