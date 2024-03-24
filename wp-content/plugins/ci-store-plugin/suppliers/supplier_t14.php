<?php

include_once WP_PLUGIN_DIR . '/ci-store-plugin/utils/Supplier.php';
require_once WP_PLUGIN_DIR . '/ci-store-plugin/vendor/autoload.php';

// use League\OAuth2\Client\Provider\AbstractProvider;
// use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
// use League\OAuth2\Client\Provider\GenericProvider;

// // Replace with your OAuth 2.0 provider's configuration
// $provider = new GenericProvider([
//     'clientId'                => 'df98c919f33c6144f06bcfc287b984f809e33322',
//     'clientSecret'            => '021320311e77c7f7e661d697227f80ae45b548a9',
//     'redirectUri'             => 'http://apitest.turn14.com',
//     // 'urlAuthorize'            => 'https://authorization-server.com/authorize',
//     'urlAccessToken'          => 'https://apitest.turn14.com/v1/token',
//     // 'urlResourceOwnerDetails' => 'https://api.example.com/me',
// ]);

// // Step 1: Authorization Request
// $authorizationUrl = $provider->getAuthorizationUrl();

// // Step 2: Redirect user to authorization URL
// header('Location: ' . $authorizationUrl);
// exit;

// // Step 3: Handle authorization code (callback endpoint)
// $code = $_GET['code'];
// $accessToken = $provider->getAccessToken('authorization_code', [
//     'code' => $code
// ]);

// // Step 4: Access protected resources
// try {
//     // Use the access token to access protected resources
//     $resourceOwner = $provider->getResourceOwner($accessToken);
//     $userDetails = $resourceOwner->toArray();
//     var_dump($userDetails);
// } catch (IdentityProviderException $e) {
//     // Handle errors
//     echo $e->getMessage();
// }

class Supplier_t14 extends Supplier
{

    private string $clientId = 'df98c919f33c6144f06bcfc287b984f809e33322';
    private string $clientSecret = '021320311e77c7f7e661d697227f80ae45b548a9';

    private string $api_domain = 'apitest.turn14.com';
    private string $api_version = 'v1';
    // private string $api_domain = 'api.turn14.com';
    // $domain = 'api.turn14.com';

    public function __construct()
    {
        parent::__construct([
            'key' => 't14',
            'name' => 'Turn14',
            'supplierClass' => 'WooDropship\\Suppliers\\Turn14',
            'import_version' => '0.1',
        ]);
    }

    public function getAccessToken()
    {
        $current_token = get_option($this->access_token_flag);
        if (isset($current_token['created']) && isset($current_token['expires']) && time() > ($current_token['expires'] * 0.9)) {
            return $current_token['access_token'];
        }
        $response = wp_safe_remote_request('https://' . $this->api_domain . '/' . $this->api_version . '/token', ['method' => 'POST', 'body' => ['client_id' => $this->clientId, 'client_secret' => $this->clientSecret, 'grant_type' => 'client_credentials']]);
        $result = ['access_token' => ''];

        if (is_wp_error($response)) {
            return ['error' => $response->get_error_message()];
        } else {
            $response_body = wp_remote_retrieve_body($response);
            $json_data = json_decode($response_body, true);

            if ($json_data === null) {
                return ['error' => 'Error decoding JSON response'];
            } else {
                $result['access_token'] = $json_data['access_token'];
                $result['expires_in'] = $json_data['expires_in'];
                $result['created'] = time();
                $result['expires'] = strtotime('+' . $result['expires_in'] . ' seconds');
                update_option($this->access_token_flag, $result);
            }
        }
        return $result['access_token'];
    }

    public function flushAccessToken()
    {
        delete_option($this->access_token_flag);
    }

    public function get_api($path, $params = [], $retry = 0)
    {
        $access_token = $this->getAccessToken();
        $query_string = http_build_query($params);
        $remote_url = implode('/', ['https://' . $this->api_domain, $this->api_version, trim($path, '/')]) . '?' . $query_string;
        $response = wp_safe_remote_request($remote_url, ['headers' => [
            'Authorization' => "Bearer " . $access_token,
            'Content-Type' => 'application/json',
        ]]);
        if (is_wp_error($response)) {
            return ['error' => 'Request failed'];
        }
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);
        if (isset($data['error'])) {
            if ($data['error'] === 'invalid_token') {
                if ($retry === 0) {
                    $this->flushAccessToken();
                    return $this->get_api($path, $params = [], $retry + 1);
                }
            }
        }
        if (isset($data['message'])) {
            $data['error'] = $data['message'];
        }
        return ['retry'=>$retry, 'remote_url' => $remote_url, 'data' => $data];
    }

    public function get_product($product_id)
    {
        // $params = [];
        // $params['include'] = implode(',', [
        //     'features', //
        //     'tags',
        //     'attributekeys',
        //     'attributevalues',
        //     'items',
        //     'items.images',
        //     'items.inventory',
        //     'items.attributevalues',
        //     'items.taxonomyterms',
        //     'taxonomyterms',
        //     'items:filter(status_id|NLA|ne)',
        // ]);
        // $product = $this->get_api('products/' . $product_id, $params);
        // if(isset($product['status_code']) && $product['status_code']===404){
        //     return null; // product doesn't exist
        // }
        // $product['data']['attributekeys']['data'] = get_western_attributes_from_product($product);
        return []; //$product;
    }
}
