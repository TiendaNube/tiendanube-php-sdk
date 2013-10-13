<?php
namespace TiendaNube;

/**
 * Provides access to the endpoints of the API of Tienda Nube/Nuvem Shop.
 * See https://github.com/TiendaNube/api-docs for details.
 */
class API {
    protected $version = 'v1';

    protected $url;
    protected $access_token;
    protected $user_agent;
    public $requests;

    /**
     * Initialize the class to perform requests to a specific store.
     *
     * @param int $store_id The id of the store
     * @param string $access_token The access token obtained from the Auth class
     * @param string $user_agent The user agent to use to identify your app
     */
    public function __construct($store_id, $access_token, $user_agent){
        $this->access_token = $access_token;
        $this->user_agent = $user_agent;
        $this->requests = new Requests;
        
        $this->url = "https://api.tiendanube.com/{$this->version}/$store_id/";
    }
    
    /**
     * Make a GET request to the specified path.
     *
     * @param string $path The path to the desired resource
     * @param array $params Optional parameters to send in the query string
     * @return TiendaNube/API/Response
     */
    public function get($path, $params = null){
        $url_params = '';
        if (is_array($params)){
            $url_params = '?' . http_build_query($params);
        }
        
        return $this->_call('GET', $path . $url_params);
    }
    
    /**
     * Make a POST request to the specified path.
     *
     * @param string $path The path to the desired resource
     * @param array $params Parameters to send in the POST data
     * @return TiendaNube/API/Response
     */
    public function post($path, $params = []){
        $json = json_encode($params);
        
        return $this->_call('POST', $path, $json);
    }
    
    /**
     * Make a PUT request to the specified path.
     *
     * @param string $path The path to the desired resource
     * @param array $params Parameters to send in the PUT data
     * @return TiendaNube/API/Response
     */
    public function put($path, $params = []){
        $json = json_encode($params);
        
        return $this->_call('PUT', $path, $json);
    }
    
    /**
     * Make a DELETE request to the specified path.
     *
     * @param string $path The path to the desired resource
     * @return TiendaNube/API/Response
     */
    public function delete($path){
        return $this->_call('DELETE', $path);
    }
    
    protected function _call($method, $path, $data = null){
        $headers = [
            'Authentication' => "bearer {$this->access_token}",
            'Content-Type' => 'application/json',
        ];
        
        $options = [
            'timeout' => 10,
            'useragent' => $this->user_agent,
        ];
    
        $response = $this->requests->request($this->url . $path, $headers, $data, $method, $options);
        $response = new API\Response($this, $response);
        if ($response->status_code == 404){
            throw new API\NotFoundException($response);
        } elseif (!in_array($response->status_code, [200, 201])){
            throw new API\Exception($response);
        }
        
        return $response;
    }
}
