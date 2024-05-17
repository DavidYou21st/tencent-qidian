<?php

namespace TencentQidian\App\Qdauthorize\Person;

use TencentQidian\App\Qdhttp\QdHttpUtils;

class PersonService
{

    private $company_access_token_url = 'https://api.qidian.qq.com/cgi-bin/component/oauth_personal_app_token?component_access_token=%s';

    private $company_refresh_token_url = 'https://api.qidian.qq.com/cgi-bin/component/api_personal_authorizer_token?component_access_token=%s';
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
     * 应用授权code换取应用授权token、应用刷新token
     * @param $component_access_token string 应用开发商token
     * @param $component_appid string 应用开发商的appid
     * @param $authorization_code string 应用授权code
     * @return array
     */
    public function getPersonAccessToken($component_access_token, $component_appid, $authorization_code)
    {
        $this->company_access_token_url = sprintf($this->company_access_token_url, $component_access_token);
        $apiParams       = array(
            'component_appid'    => $component_appid,
            'authorization_code' => $authorization_code,
        );
        $apiParams       = json_encode($apiParams);
        $qdhttp          = new QdHttpUtils();
        $response        = $qdhttp->httpPost($this->company_access_token_url, $apiParams);
        $response        = json_decode($response, true);
        return array(
            'code'    => $response['errcode'] ? $response['errcode'] : 0,
            'message' => $response['errmsg'] ? $response['errmsg'] : '请求成功',
            'data'    => $response['authorization_info']
        );
    }

    /**
     * 通过RefreshToken获取最新的企业应用Token
     * @param $component_access_token string 被授权的个人openid
     * @param $user_id string 应用开发商的appid
     * @param $authorizer_uin string 被授权个q对应的主号
     * @param $authorizer_refresh_token string 应用授权刷新token（长期有效）
     * @param $sid string 应用的id
     * @return array
     */
    public function getPersonRefreshToken($component_access_token, $user_id, $authorizer_uin, $authorizer_refresh_token, $sid)
    {
        $this->company_refresh_token_url = sprintf($this->company_refresh_token_url, $component_access_token);
        $apiParams       = array(
            'user_id'                   => $user_id,
            'authorizer_uin'            => $authorizer_uin,
            'authorizer_refresh_token'  => $authorizer_refresh_token,
            'sid'                       => $sid,
        );
        $apiParams       = json_encode($apiParams);
        $qdhttp          = new QdHttpUtils();
        $response        = $qdhttp->httpPost($this->company_refresh_token_url, $apiParams);
        $response        = json_decode($response, true);
        return array(
            'code'    => $response['errcode'] ? $response['errcode'] : 0,
            'message' => $response['errmsg'] ? $response['errmsg'] : '请求成功',
            'data'    => $response
        );
    }
}
