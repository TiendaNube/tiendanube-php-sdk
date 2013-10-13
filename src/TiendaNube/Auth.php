<?php
namespace TiendaNube;

/**
 * Provides a simple way to authenticate your app for the API of Tienda Nube/Nuvem Shop.
 * See https://github.com/TiendaNube/api-docs for details.
 */
class Auth {
    protected $client_id;
    protected $client_secret;
    protected $auth_url;
    public $requests;

    /**
     * Initialize the class to perform authentication for a specific app.
     *
     * @param string $client_id The public client id of your app
     * @param string $client_secret The private client secret of your app
     */
    public function __construct($client_id, $client_secret){
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->auth_url = "https://www.tiendanube.com/apps/authorize/token";
        $this->requests = new Requests;
    }
    
    /**
     * Return the url to login to you app in the www.nuvemshop.com.br domain.
     *
     * @return string
     */
    public function login_url_brazil(){
        return "https://www.nuvemshop.com.br/apps/{$this->client_id}/authorize";
    }
    
    /**
     * Return the url to login to you app in the www.tiendanube.com domain.
     *
     * @return string
     */
    public function login_url_spanish(){
        return "https://www.tiendanube.com/apps/{$this->client_id}/authorize";
    }
    
    /**
     * Obtain a permanent access token from an authorization code.
     *
     * @param string $code Authorization code retrieved from the redirect URI.
     */
    public function request_access_token($code){
        $params = [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'code' => $code,
            'grant_type' => 'authorization_code',
        ];
        
        $response = $this->requests->post($this->auth_url, [], $params);
        if (!$response->success){
            throw new Auth\Exception('Auth url returned with status code ' . $response->status_code);
        }
        
        $body = json_decode($response->body);
        if (isset($body->error)){
            throw new Auth\Exception("[{$body->error}] {$body->error_description}");
        }
        
        return [
            'store_id' => $body->user_id,
            'access_token' => $body->access_token,
            'scope' => $body->scope,
        ];
    }
}
