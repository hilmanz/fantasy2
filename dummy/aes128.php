<?php
    // Class: AES-128 Implementation Utilizing Mcrypt's Rijndael-128 Algorithm
    // File: AES128.class.php
    // Author: Jack Servedio - jservedio.com
    // Published: July 2, 2010
    // Current: v1.00 (2010-07-02)
    /* About This Class
       Requirements:
            - PHP 4 >= 4.3.0, PHP 5
            - PHP Mcrypt library
            
        About:
        This class implements AES-128 Encryption utilizing the PHP Mcrypt Library with the Rijndael-128 Algorithm. This encryption method uses an Intialization Vector as "seed" along with a 128-bit key. The encrypted data is stored in Base 64 prepended by the Initialization Vector also in Base 64.
        
        Copyright 2010 Jack Servedio
    */
    
    class AES128 {
        //Private Member Variables
        private $key, $iv, $data, $mode, $encrypted_data, $encrypted_string;
        
        //Constructor
        //Instantiates the Object and allows the 128-bit Encryption Key to be passed as a parameter. If a 128-bit key (32 Hex Characters) isn't supplied, or is too short, it will be padded '\0' to fill 128-bits.
        //Mode can also be set by passing ECB, CFB, or CBC
        public function AES128( $key = "", $mode = "ECB" ) {
            //Sets key member variable from string passed through the parameter key
            $this->key = $key;
            
            //Sets Encryption Mode from mode parameter
            switch( $mode ) {
                case "ECB":
                    $this->mode = MCRYPT_MODE_ECB;
                break;
                case "CFB":
                    $this->mode = MCRYPT_MODE_CFB;
                break;
                case "CBC":
                    $this->mode = MCRYPT_MODE_CBC;
                break;
                default:
                    $this->mode = MCRYPT_MODE_ECB;
            }
        }
        
        //Method to Generate and Set the Initialization Vector
        private function generate_iv() {
            //Generates a Random Initialization Vector for the Rijndael Cipher using the System Random Generator
            $this->iv = mcrypt_create_iv( mcrypt_get_block_size( MCRYPT_RIJNDAEL_128, $this->mode ), MCRYPT_RAND );
        }
        
        //Method to Encrypt Data
        //Encrypts the data passed through the data parameter in Rijndael-128 algorithm in CFB mode and returns the encrypted data in base 64 encoding prefixed by the initialization vector
        public function encrypt( $data ) {
            //Set data member variable with string passed through the data parameter
            $this->data = $data;

            //Generate Initialization Vector using the generate_iv method
            $this->generate_iv();

            //Encrypt Data to AES-128 Standards in CFB Mode
            $this->encrypted_data = mcrypt_encrypt( MCRYPT_RIJNDAEL_128, $this->key, $this->data, $this->mode, $this->iv );

            //Create Storage Encryption String from Base 64 Encrypted Data prefixed by Base 64 IV
            $this->encrypted_string = base64_encode( $this->iv ) . base64_encode( $this->encrypted_data );
            
            //Returns the Encrypted String
            return $this->encrypted_string;
        }

        //Method to Decrypt Encrypted Data
        //Decrypts Encrypted data passed through encrypted_string variable by separating the IV and Encrypted Data using the Rijndael-128 algorithm in CFB mode and returns the plain text
        public function decrypt( $encrypted_string ) {
            //Set Encryption String member variable from encrypted string parameter
            $this->encrypted_string = $encrypted_string;

            //Separate Base 64 Encoded Initialization Vector from Encrypted Data
            $b64_iv = substr( $this->encrypted_string, 0, 24 );
            $b64_data = substr( $this->encrypted_string, 24 );

            //Decode the Base 64 Initialization Vector and set Member Variable
            $this->iv = base64_decode( $b64_iv );
            
            //Decode the Base 64 Encrypted Data and set Member Variable
            $this->encrypted_data = base64_decode( $b64_data );

            //Decrypt Encrypted Data using Key and Initialization Vector with the Rijndael-128 algorithm in CFB mode and set member variable
            $this->data = mcrypt_decrypt( MCRYPT_RIJNDAEL_128, $this->key, $this->encrypted_data, $this->mode, $this->iv);
            
            //Return plain text decrypted
            return $this->data;
        }
        
        //Method to Set Key Member Variable
        public function set_key( $key ) {
            $this->key = $key;
        }
        
        //Method to Get Encrypted String
        public function get_encrypted_string() {
            //Return Encrypted String Member Variable if its not Null
            if( $this->encrypted_string != (null) )
                return $this->encrypted_string;
            //If Null, Return False
            else
                return false;
        }
        
        //Method to get plaintext data
        public function get_data() {
            //Return decrypted plain text if member variable isn't null
            if( $this->data != (null) )
                return $this->data;
            //If null, return false
            else
                return false;
        }
        
        //Method to get Initialization Vector Base 64 Encoded
        public function get_iv() {
            //Return Initialization Vector Base 64 Encoded if member variable isn't null
            if( $this->iv != (null) )
                return base64_encode($this->iv);
            //If null, return false
            else
                return false;
        }
        
        //Method to get Encrypted Data Base 64 Encoded
        public function get_encrypted_data() {
            if( $this->encrypted_data != (null) )
                return base64_encode($this->encrypted_data);
            else
                return false;
        }
    }
?> 
