<?php

namespace TencentQidian\App\QdCustomer;

use TencentQidian\App\Qdhttp\QdHttpUtils;

/**
 * 客户管理
 * 处理企点客户模块的接口请求
 */
class Customer
{
    private $access_token;
    private $customer_transfer_url = 'https://api.qidian.qq.com/cgi-bin/v1/message/transfer/convert?access_token=%s';
    private $customer_list_url = 'https://api.qidian.qq.com/cgi-bin/cust/cust_info/getCustList?next_custid=%s&access_token=%s';
    private $customer_base_info_url = 'https://api.qidian.qq.com/cgi-bin/cust/cust_info/getSingCustBaseInfo?access_token=%s';
    private $customer_ext_info1_url = 'https://api.qidian.qq.com/cgi-bin/cust/cust_info/getSingCustExteInfo1?cust_id=%s&access_token=%s';
    private $customer_ext_info2_url = 'https://api.qidian.qq.com/cgi-bin/cust/cust_info/getSingCustExteInfo2?access_token=%s';
    private $add_customer_url = 'https://api.qidian.qq.com/cgi-bin/cust/cust_info/addcustomer?access_token=%s';

    private $get_noncust_info_by_qq_url = 'https://api.qidian.qq.com/cgi-bin/cust/cust_info/getNonCustInfo?openid=%s&type=%s&access_token=%s';
    private $get_noncust_info_by_wxaccount_url = 'https://api.qidian.qq.com/cgi-bin/cust/cust_info/getNonCustInfo?openid=%s&type=%s&appid=%s&access_token=%s';
    private $get_noncust_info_by_webim_url = 'https://api.qidian.qq.com/cgi-bin/cust/cust_info/getNonCustInfo?visitorId=%s&type=%s&access_token=%s';

    /**
     * 构造函数
     * @param string $access_token 访问令牌
     */
    public function __construct($access_token)
    {
        $this->access_token = $access_token;
    }


    /**
     * 企微openid换取企点openid
     * @see https://api.qidian.qq.com/wiki/doc/open/ewboe48lrpgo2qsntrh5
     * @params string $c_qw_openid 外部联系人（c侧）的企微openid
     * @params string $b_qd_openid 内部联系人（b侧）的企点openid
     * @params string $session_type 通路类型
     * @return array
     */
    public function transferOpenid($c_qw_openid, $b_qd_openid, $session_type)
    {
        $url = sprintf($this->customer_transfer_url, $this->access_token);
        $apiParams = json_encode(array(
            'b_qd_openid' => $b_qd_openid,
            'c_qw_openid' => $c_qw_openid,
            'sessionType' => $session_type,
        ));
        $response = QdHttpUtils::httpPost($url, $apiParams);
        $response = json_decode($response, true);
        return array(
            'code' => $response['errcode'] ?? 0,
            'message' => $response['errmsg'] ?? 'success',
            'data' => $response['data'] ?? []
        );
    }

    /**
     * 拉取客户列表
     * @see https://api.qidian.qq.com/wiki/doc/open/evblxstplyo4dpgm4ri6
     * @params string $next_custid 此次拉取的第一个custid，上次拉取的next_custid
     * @return array
     */
    public function getCustList($next_custid)
    {
        $url = sprintf($this->customer_list_url, $next_custid, $this->access_token);
        $response = QdHttpUtils::httpGet($url);
        $response = json_decode($response, true);
        return array(
            'total' => $response['total'] ?? 0, //客户总数
            'count' => $response['count'] ?? 0, //本次拉取的客户个数
            'data' => $response['data'] ?? [],
            'next_custid' => $response['next_custid'] ?? '', //拉取列表的最后一个cust_id
        );
    }

    /**
     * 添加客户
     * @see https://api.qidian.qq.com/wiki/doc/open/ealns35zml4p1cpg4hl9
     * @params int $source 导入来源:0 表单页提交;1其他
     * @params int $account_type 账户类型 1-QQ,2-微信公众号,4-cookie(visitid),9-手机号,11-微信小程序,50-app/imsdk
     * @params string $account 对应的账户名，这里填的账户不要在下面参数中填写，会返回错误。微信公众号为"appidlopenid"或“appidlopenidlunionid(unionid为选填)
     * 微信小程序为“appidlopenid”或”appidlopenidlunionid”(unionid为选填),app/imsdk为"appid#aid”
     * @return array
     */
    public function addCustomer($source, $account_type, $account)
    {
        $url = sprintf($this->add_customer_url, $this->access_token);
        $apiParams = json_encode(array(
            'source' => $source,
            'account_type' => $account_type,
            'account' => $account,
        ));
        $response = QdHttpUtils::httpPost($url, $apiParams);
        $response = json_decode($response, true);
        return array(
            'cust_id' => $response['cust_id'] ?? '',
            'errcode' => $response['errcode'] ?? 0
        );
    }

    /**
     * 拉取客户基本信息
     * @see https://api.qidian.qq.com/wiki/doc/open/ebmlne6s3lpzplguht26
     * @params string $cust_id 用户唯一凭证
     * @params array $data 获取哪些信息 例如： [ "identity","contact","socialAccount","controlInfo","udfInfo"]
     * identity 身份信息
     * contact 联系方式
     * socialAccount 社交账号信息
     * controlInfo 管理信息
     * udfInfo 自定义字段数据
     * @return array
     */
    public function getCustBaseInfo($cust_id, $data = ["identity", "contact", "socialAccount", "controlInfo", "udfInfo"])
    {
        $url = sprintf($this->customer_base_info_url, $this->access_token);
        $apiParams = json_encode(array(
            'cust_id' => $cust_id,
            'data' => $data,
        ));
        $response = QdHttpUtils::httpPost($url, $apiParams);
        $response = json_decode($response, true);
        return array(
            'cust_id' => $response['cust_id'] ?? '',
            'data' => $response['data'] ?? []
        );
    }

    /**
     * 拉取客户扩展信息1
     * @see https://api.qidian.qq.com/wiki/doc/open/e5kcmore8kgo6ktyh217
     * @params string $cust_id 用户唯一凭证
     * @params string $relationshipType 关系链种类， official account（公众号）或者group（群）
     * @params string $relationshipId 关系链id，如果relationship type是official account这里应该是公众号id即accountid，如果是group，这里应该是groupid
     * @return array
     */
    public function getSingCustExteInfo1($cust_id)
    {
        $url = sprintf($this->customer_ext_info1_url, $cust_id, $this->access_token);
        $response = QdHttpUtils::httpGet($url);
        $response = json_decode($response, true);
        return array(
            'cust_id' => $response['cust_id'] ?? '',
            'data' => $response['data'] ?? []
        );
    }

    /**
     * 拉取客户扩展信息2
     * @see https://api.qidian.qq.com/wiki/doc/open/e5kcmore8kgo6ktyh217
     * @params string $cust_id 用户唯一凭证
     * @params string $relationshipType 关系链种类， official account（公众号）或者group（群）
     * @params string $relationshipId 关系链id，如果relationship type是official account这里应该是公众号id即accountid，如果是group，这里应该是groupid
     * @return array
     */
    public function getSingCustExteInfo2($cust_id, $relationshipType, $relationshipId)
    {
        $url = sprintf($this->customer_ext_info2_url, $this->access_token);
        $apiParams = json_encode(array(
            'cust_id' => $cust_id,
            'relationshipType' => $relationshipType,
            'relationshipId' => $relationshipId,
        ));
        $response = QdHttpUtils::httpPost($url, $apiParams);
        $response = json_decode($response, true);
        return array(
            'cust_id' => $response['cust_id'] ?? '',
            'data' => $response['data'] ?? []
        );
    }

    /**
     * 根据第三方用户ID获取cust_id
     * @see https://api.qidian.qq.com/wiki/doc/open/easxe3h4m29kiq653v2q
     * @params string $third_crm_id 第三方用户ID
     * @return array
     */
    public function getSingCustInfoByThirdcrmid($third_crm_id)
    {
        $url = sprintf('https://api.qidian.qq.com/cgi-bin/v1/cust/cust_info/getcuinbythirdcrmid?access_token=%s', $this->access_token);
        $apiParams = json_encode(array(
            'third_crm_id' => $third_crm_id,
        ));
        $response = QdHttpUtils::httpPost($url, $apiParams);
        $response = json_decode($response, true);
        return array(
            'r' => $response['r'] ?? 1,
            'source_cust_id' => $response['source_cust_id'] ?? '',
            'cust_id' => $response['cust_id'] ?? ''
        );
    }

    /**
     * 根据社交账号获取cust_id
     * @see https://api.qidian.qq.com/wiki/doc/open/eal6s336lsl1czg5h569
     * @params string $type 通路
     * 0:QQ
     * 1:微信公众号(appid和openid使用|分隔)
     * 3:webim
     * 9:微信小程序(appid和openid使用|分隔)
     * 22:电话
     * 50:app(appid和aid使用#分隔)
     * 51:企业微信external_userid
     * 53:微信客服
     * @params string $cid 社交账号
     * @return array
     */
    public function getCuinBySocialAccount($type, $cid)
    {
        $url = sprintf('https://api.qidian.qq.com/cgi-bin/cust/cust_info/getCuinBySocialAccount?access_token=%s', $this->access_token);
        $apiParams = json_encode(array(
            'type' => $type,
            'cid' => $cid,
        ));
        $response = QdHttpUtils::httpPost($url, $apiParams);
        return json_decode($response, true);
    }

    /**
     * 拉取不在客户库的客户资料
     * 1.拉取 qq/qq公众号通路 c客户的资料
     * @see https://api.qidian.qq.com/wiki/doc/open/elws87atco9gq9w5i181
     * @params string $type 1-qq会话，2-qq公众号，6-主号会话
     * @params string $openid c客户在不同通路下的标识id
     * @return array
     */
    public function getNonCustInfoByQQ($type, $openid)
    {
        $url = sprintf($this->get_noncust_info_by_qq_url, $openid, $type, $this->access_token);
        $response = QdHttpUtils::httpGet($url);
        return json_decode($response, true);
    }

    /**
     * 拉取不在客户库的客户资料
     * 2.拉取微信公众号通路c客户资料
     * @see https://api.qidian.qq.com/wiki/doc/open/elws87atco9gq9w5i181
     * @params string $type 1-qq会话，2-qq公众号，6-主号会话
     * @params string $openid c客户在不同通路下的标识id
     * @params string $appid 微信公众号id
     * @return array
     */
    public function getNonCustInfoByWxAccount($type, $openid, $appid)
    {
        $url = sprintf($this->get_noncust_info_by_wxaccount_url, $openid, $type, $appid, $this->access_token);
        $response = QdHttpUtils::httpGet($url);
        return json_decode($response, true);
    }

    /**
     * 拉取不在客户库的客户资料
     * 3.拉取webim 通路c客户资料
     * @see https://api.qidian.qq.com/wiki/doc/open/elws87atco9gq9w5i181
     * @params string $visitorId c客户在不同通路下的标识id
     * @params string $type 4
     * @return array
     */
    public function getNonCustInfoByWebim($visitorId, $type=4)
    {
        $url = sprintf($this->get_noncust_info_by_webim_url, $visitorId, $type, $this->access_token);
        $response = QdHttpUtils::httpGet($url);
        return json_decode($response, true);
    }
}
