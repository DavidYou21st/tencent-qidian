<?php

namespace TencentQidian\App\Qdauthorize\Selfbuilt;

use TencentQidian\App\Qdhttp\QdHttpUtils;

class SelfBuiltService
{
    private $url = 'https://api.qidian.qq.com/cgi-bin/token/getSelfBuildToken?sid=%s&appid=%s&secret=%s';

    /**
     * 构造函数
     * @param $token string 公众平台上，开发者设置的token
     * @param $encodingAesKey string 公众平台上，开发者设置的EncodingAESKey
     * @param $appId string 公众平台的appId
     */
    public function __construct($token, $encodingAesKey, $appId)
    {
        $this->token = $token;
        $this->encodingAesKey = $encodingAesKey;
        $this->appId = $appId;
    }


    /**
     * 获取自建应用Token
     * @param  $component_appid 应用开发商appid
     * @param  $component_appsecret 应用开发商appsecret
     * @param  $component_verify_ticket 企点API后台推送的ticket，此ticket会定时推送，具体请见上文的推送说明
     * @return array
     */
    public function getSelfBuildToken($appid, $sid, $secret)
    {
        $this->url = sprintf($this->url, $sid, $appid, $secret);
        $response = QdHttpUtils::httpGet($this->url);
        $response = json_decode($response, true);
        return array(
            'code' => $response['errcode'] ?? 0,
            'message' => $response['errmsg'] ?? '请求成功',
            'data' => array(
                'access_token' => $response['access_token'] ?? '',
                'expires_in' => $response['expires_in'] ?? 0,
            )
        );
    }
}
