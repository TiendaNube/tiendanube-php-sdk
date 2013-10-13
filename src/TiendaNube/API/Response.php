<?php
namespace TiendaNube\API;

/**
 * Contains the headers and body of the response to an API request.
 */
class Response {
    public $body;
    public $headers;
    public $status_code;
    public $main_language;
    
    private $api;

    public function __construct($api, $response){
        $this->status_code = $response->status_code;
        $this->body = json_decode($response->body);
        $this->headers = $response->headers;
        $this->main_language = isset($response->headers['X-Main-Language']) ? $response->headers['X-Main-Language'] : null;
        
        $this->api = $api;
    }
    
    /**
     * Return the next page of a list response.
     *
     * @return TiendaNube\API\Response
     */
    public function next(){
        return $this->_parse_pagination('next');
    }
    
    /**
     * Return the previous page of a list response.
     *
     * @return TiendaNube\API\Response
     */
    public function prev(){
        return $this->_parse_pagination('prev');
    }
    
    /**
     * Return the first page of a list response.
     *
     * @return TiendaNube\API\Response
     */
    public function first(){
        return $this->_parse_pagination('first');
    }
    
    /**
     * Return the last page of a list response.
     *
     * @return TiendaNube\API\Response
     */
    public function last(){
        return $this->_parse_pagination('last');
    }
    
    
    private function _parse_pagination($key){
        if (isset($this->headers['Link'])){
            $success = preg_match('/<([^>]*)>; rel="'.$key.'"/', $this->headers['Link'], $matches);
            if ($success){
                $url = $matches[1];
                preg_match('|/v\d/\d+/(.*)|', $url, $matches);
                
                return $this->api->get($matches[1]);
            }
        }
        
        return null;
    }
    
}
