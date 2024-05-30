<?php

namespace TencentQidian\App\Qdauthorize\Common;

use \DOMDocument;
use TencentQidian\App\Qdbizmsgcrypt\QDMsgCrypt;
use TencentQidian\App\Qdhttp\QdHttpUtils;

class Web
{

    private $token;
    private $encodingAesKey;
    private $appId;
    private $url = 'https://api.qidian.qq.com/cgi-bin/token?grant_type=authorization_code&appid=%s&secret=%s&code=%s&state=%s&version=2';

    /**
     * 构造函数
     * @param $appId string 第三方开发者的appid
     */
    public function __construct($appId)
    {
        $this->appId = $appId;
    }

    /**
     * 通过code换取用户id及access_token
     * @see https://api.qidian.qq.com/wiki/doc/open/essbrpkw14m1y9cvdqip
     * @param string $state state参数，企业可以填写a-zA-Z0-9的参数值，长度不可超过128个字节,后面会返回
     * @param  string $secret 第三方开发者AppSecret
     * @param  string $code 填写第一步获取的code参数
     * @return array
     */
    public function getAccessToken($state, $secret, $code)
    {
        $this->url = sprintf($this->url, $this->appId, $secret, $code, $state);
        $qdhttp = new QdHttpUtils();
        $response = $qdhttp->httpGet($this->url);
        $response = json_decode($response, true);
        var_dump($response);exit();
        return array(
            'expires_in' => $response['expires_in'] ?? 0,
            'access_token' => $response['access_token'] ?? '',
            'open_id' => $response['open_id'] ?? '',
            'state' => $response['state'] ?? '',
            'authorizer_appid' => $response['authorizer_appid'] ?? '',
            'auth_appid' => $response['auth_appid'] ?? '',
        );
    }
}
