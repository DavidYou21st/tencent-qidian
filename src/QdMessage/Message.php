<?php

namespace TencentQidian\App\QdMessage;

use TencentQidian\App\Qdhttp\QdHttpUtils;

/**
 * 消息方向
 */
const MSG_DIRECTION_B2C = "B2C";//内部联系人对外部联系人
const MSG_DIRECTION_C2B = "C2B";//外部联系人对内部联系人
const MSG_DIRECTION_B2B = "B2B";//内部联系人对内部联系人

/**
 * 企点消息
 * 处理企点消息模块的接口请求
 */
class Message
{
    private $access_token;
    private $transfer_save_url = 'https://api.qidian.qq.com/cgi-bin/v1/message/transfer/save?access_token=%s';

    /**
     * 构造函数
     * @param string $access_token 访问令牌
     */
    public function __construct($access_token)
    {
        $this->access_token = $access_token;
    }

    /**
     * 企微消息转存企点
     * @see https://api.qidian.qq.com/wiki/doc/open/eoayulpm66nuwd1e8rui
     * @params array $msg 企业微信消息
     * @return array
     */
    public function transferSave($msg)
    {
        $url = sprintf($this->transfer_save_url, $this->access_token);
        $params = [
            "fromuser" => $msg["from_openid"],     //发送方的企点openid
            "touser" => $msg["to_openid"],       //接收方的企点openid
            "msgid" => $msg["msg_id"],          //消息id，需要在号码对之间全局唯一，只支持数字、大小写英文字母、下划线和中划线，长度小于64位
            "msgtype" => $msg["msg_type"],        //消息类型
            "sessiontype" => $msg["session_type"] ?? 1,                       //个人/群
            "msgseq" => $msg["msg_seq"],         //消息序号，用于排序、去重
            "msgtime" => $msg["msg_time"],        //消息时间戳
            "msgdirection" => $msg["msg_direction"],   //消息方向
            "content" => $msg['msg_content'],//消息内容体（非图片/文件消息）
            "media" => $msg["media"] ?? '',           //文件/图片消息内容体（小于5M）
        ];
        if (isset($msg["session_type_v2"])) {
            $params["sessionTypeV2"] = $msg["session_type_v2"];//通路类型
        }
        $params = json_encode($params);
        $response = QdHttpUtils::httpPost($url, $params);
        $response = json_decode($response, true);
        return array(
            'code' => $response['errcode'] ?? 0,
            'message' => $response['errmsg'] ?? 'success',
            'data' => $response['data'] ?? []
        );
    }
}
