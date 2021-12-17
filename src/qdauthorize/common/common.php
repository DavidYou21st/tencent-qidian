<?php

namespace TencentQidian\App\Qdauthorize\Common;

use \DOMDocument;
use TencentQidian\App\Qdbizmsgcrypt\QDMsgCrypt;
use TencentQidian\App\Qdhttp\QdHttpUtils;

class Common
{

    private $token;
    private $encodingAesKey;
    private $appId;
    private $url = 'https://api.qidian.qq.com/cgi-bin/component/api_component_token';

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
     * 解密Ticket  EncryXml, Signature, Timestamp, Nonce
     * @param  $encryXml 加密Ticket
     * @param  $Signature 签名串
     * @param  $timestamp 时间戳
     * @param  $nonce 随机串
     * @return array
     */
    public function getTicket($encryXml, $signature, $timestamp, $nonce)
    {
        $pc = new QDMsgCrypt($this->token, $this->encodingAesKey, $this->appId);
        $xml_tree = new DOMDocument();
        $xml_tree->loadXML($encryXml);
        $array_e = $xml_tree->getElementsByTagName('Encrypt');
        $encrypt = $array_e->item(0)->nodeValue;
        $format = "<xml><Encrypt><![CDATA[%s]]></Encrypt><AppId><![CDATA[" . $this->appId . "]]></AppId></xml>";
        $from_xml = sprintf($format, $encrypt);
        $errCode = $pc->decryptMsg($signature, $timestamp, $nonce, $from_xml, $msg);
        if ($errCode == 0) {
            $xmlstring  = simplexml_load_string($msg);
            $ticket_value = json_decode(json_encode($xmlstring), true)['ComponentVerifyTicket'];
        }
        return array('code' => $errCode, 'ticket' => $ticket_value);
    }

    /**
     * 获取开发商Token
     * @param  $component_appid 应用开发商appid
     * @param  $component_appsecret 应用开发商appsecret
     * @param  $component_verify_ticket 企点API后台推送的ticket，此ticket会定时推送，具体请见上文的推送说明
     * @return array
     */
    public function getAccessToken($component_appid, $component_appsecret, $component_verify_ticket)
    {

        $params = array(
            "component_appid" => $component_appid,
            "component_appsecret" => $component_appsecret,
            "component_verify_ticket" => $component_verify_ticket
        );
        $params   = json_encode($params);
        $qdhttp = new QdHttpUtils();
        $response = $qdhttp->httpPost($this->url, $params);
        $response = json_decode($response, true);
        return array(
            'code'    => $response['errcode'] ? $response['errcode'] : 0,
            'message' => $response['errmsg'] ? $response['errmsg'] : '请求成功',
            'data'    => array(
                'component_access_token' => $response['component_access_token'],
                'expires_in'             => $response['expires_in'],
            )
        );
    }
}
