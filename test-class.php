<?php

/**
 * Description of Box JWT messing around
 *
 * @author n_young_at_uncg_dot_edu
 */

use \Firebase\JWT\JWT;

class App_Model_Boxapihelperjwt {


    public function getAccessToken()
    {

        $proxyCreds = array(
            'url'       => '',
            'port'      => '',
        );

        $vendorConfig = array(
            'vendorArray' => array(
                'privateKeyFileName' => '/path/to/box_private.key',
                'sub'                => '123456', // enterprise ID from the Box console
                'box_sub_type'       => 'enterprise', // enterprise or user
                'aud'                => 'https://api.box.com/oauth2/token',
                'jti'                => 'changeThisToSomethingUniqueForThisApp', // unique string for this app, your choice
                'kid'                => '', // string from the console. This is the value listed for the Public Key ID that you uploaded. IE: "Public Key 1 ID: jwkfdsqwn"
                'grant_type'         => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'iss'                => '', // client ID from the console. IE: 0voeib751pmwoe57ubgrmteckyqqa5qb
                'clientSecret'       => '', // client secret from the console
            ),
            'apiEndPointsArray' => array(
                'jwtExchangeUri'    => 'https://api.box.com/oauth2/token',
            ),
        );

        // in theory you should not have to change anything underneath this line

        $key = file_get_contents($vendorConfig['vendorArray']['privateKeyFileName']);

        $time = time();
        $exp = $time + 60; // expiration time for this token

        $payloadArray = array(
            'iss' => $vendorConfig['vendorArray']['iss'],
            'sub' => $vendorConfig['vendorArray']['sub'],
            'box_sub_type' => $vendorConfig['vendorArray']['box_sub_type'],
            'aud' => $vendorConfig['vendorArray']['aud'],
            'exp' => $exp,
            'jti' => $vendorConfig['vendorArray']['jti'],
        );

        $encodedJwt = JWT::encode($payloadArray, $key, 'RS256', $vendorConfig['vendorArray']['kid']);

        $parametersArray = array(
            'grant_type'    => $vendorConfig['vendorArray']['grant_type'],
            'client_id'     => $vendorConfig['vendorArray']['iss'],
            'client_secret' => $vendorConfig['vendorArray']['clientSecret'],
            'assertion'     => $encodedJwt,
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
            'Cache-Control: no-cache',
            'typ: JWT',
            'alg: RS256',
        ));

        curl_setopt($ch, CURLOPT_URL, $vendorConfig['apiEndPointsArray']['jwtExchangeUri']);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parametersArray));

        if (!empty($proxyCreds['url'])) {
            curl_setopt($ch, CURLOPT_PROXY, $proxyCreds['url'] . ':' . $proxyCreds['port']);
        }

        $result = curl_exec($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        //$header = substr($result, 0, $header_size);
        $body = json_decode(substr($result, $header_size));

        echo '<pre>';
        print_r($body);
        die(__CLASS__ . ':' . __FUNCTION__ . ':' . __LINE__);

        return $body;

    }


}
