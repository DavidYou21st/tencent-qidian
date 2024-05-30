<?php

namespace TencentQidian\App\QdStaff;

use TencentQidian\App\Qdhttp\QdHttpUtils;

/**
 * 通用-员工
 * 处理企点员工模块的接口请求
 */
class Staff
{
    private $access_token;
    private $staffId_by_account_batch_url = 'https://api.qidian.qq.com/cgi-bin/v1/org/basic/StaffIdByAccountBatch?access_token=%s';

    private $staffId_by_department_id_url = 'https://api.qidian.qq.com/cgi-bin/v1/org/OrgService/SubNodesRecursive?access_token=%s';

    /**
     * 构造函数
     * @param string $access_token 访问令牌
     */
    public function __construct($access_token)
    {
        $this->access_token = $access_token;
    }

    /**
     * 根据账号名获取企点openId
     * @see https://api.qidian.qq.com/wiki/doc/open/e1lvod8ftkgs44hupuu3
     * @param array $accounts 账号名列表 例如：["kefu_a","kefu_b"]
     * @return array
     */
    public function getStaffIdByAccountBatch($accounts)
    {
        $url = sprintf($this->staffId_by_account_batch_url, $this->access_token);
        $params = json_encode(array(
            'accounts' => $accounts,
        ));
        $response = QdHttpUtils::httpPost($url, $params);
        $response = json_decode($response, true);
        return array(
            'code' => $response['errcode'] ?? 0,
            'message' => $response['message'] ?? 'success',
            'data' => $response['data']['info'] ?? []
        );
    }

    /**
     * 根据账号名获取企点openId
     * @see https://api.qidian.qq.com/wiki/doc/open/enq3asm58qd30e5thznk
     * @param int $page_size 分页大小 ，用于工号分页，不可大于500
     * @param int $page 当前页，从0开始
     * @param int $id 部门ID,不填写或者填0则返回全部部门里面的员工信息
     * @return array
     */
    public function getStaffIdByDepartmentId($page_size=10, $page=1, $id=0)
    {
        $url = sprintf($this->staffId_by_department_id_url, $this->access_token);
        $params = json_encode(array(
            'page_size' => $page_size,
            'page' => $page,
            'id' => $id,
        ));
        $response = QdHttpUtils::httpPost($url, $params);
        $response = json_decode($response, true);
        return array(
            'code' => $response['errcode'] ?? 0,
            'message' => $response['errmsg'] ?? 'ok',
            'data' => $response['data'] ?? []
        );
    }

}
