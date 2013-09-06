<?php
namespace TiendaNube\API;

class Exception extends \Exception {
    public $response;
    
    public function __construct($response){
        $body = $response->body;
        if (isset($body->message)){
            $message = isset($body->description) ? $body->description : $body->message;
        } else {
            $message = '';
            foreach ((array) $body as $field => $errors){
                foreach ($errors as $error){
                    $message .= "\n[$field] $error";
                }
            }
        }
        
        parent::__construct('Returned with status code ' . $response->status_code . ': ' . $message);
        $this->response = $response;
    }
}
