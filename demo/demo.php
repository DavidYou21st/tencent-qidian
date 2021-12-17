<?php
namespace TencentQidian\App;
include '../vendor/autoload.php';

use TencentQidian\App\Qdbizmsgcrypt\QDMsgCrypt;
use TencentQidian\App\Qdauthorize\Common\Common;
use TencentQidian\App\Qdauthorize\Company\CompanyService;
use TencentQidian\App\Qdauthorize\Person\PersonService;
use TencentQidian\App\Qdauthorize\Selfbuilt\SelfBuiltService;
use \DOMDocument;
ini_set("display_errors","On");

//1.完善服务器配置 —— 详细步骤参见https://api.qidian.qq.com/wiki/doc/open/emxmpkwd5soewhkky37i
// appId/token/encodingAesKey/appSecret的获取方式可参考 https://api.qidian.qq.com/wiki/doc/open/e0ootbmv1d7et30ri43l
$token = 'token1';
$appId = '202187955';
$appSecret = '******';
$encodingAesKey = 's2I6wx2vDpAxHNmhTkYaDK3J4kCMg7t0pjkl4gbTTHD';

$pc = new QDMsgCrypt($token, $encodingAesKey, $appId);
/** 
 * 
 * signature/echostr/timestamp/nonce 这四个参数是带在“服务器地址”上的params。
 * 假设开发者填写的服务器地址URL为https://api.qidian.qq.com/index.php,
 * 企点侧会以GET请求的方式访问该地址，访问内容类似如下格式
 * https://api.qidian.qq.com/index.php?signature=f208bdcde5cd6b1c83e911446b9a318e7d59242c&timestamp=1637478915&nonce=kavlyvgg&echostr=cnopzpro
 */
$signature = 'd9d5bf6e7fe62ee9b0d726c4dd4225bb850f41d1';
$echostr = 'alzzqyig';
$timestamp = '1626678323';
$nonce = 'ziteiswy';
$replyEchoStr = '';
$str = $pc->VerifyURL($signature, $timestamp, $nonce, $echostr, $replyEchoStr);
if ($str == $echostr) {
	echo $replyEchoStr; // 检测保存服务器配置操作，需要直接返回$replyEchoStr
} else {
	var_dump("验证失败，错误码：$str\n");
}

//2.解析xml推送内容 —— 详细步骤参见https://api.qidian.qq.com/wiki/doc/open/epko939s7aq8br19gz0i
/**
 * 假设企业开发者正确开启了推送服务，并注册了服务器地址`http://api.qidian.qq.com/index.php`
 * 企点服务器向注册的服务器地址发送post请求，请求url类似如下格式
 * `http://api.qidian.qq.com/index.php?timestamp=1637478915&nonce=kavlyvgg&encrypt_type=aes&msg_signature=f208bdcde5cd6b1c83e911446b9a318e7d59242c`
 * 请求body体内容如下格式：
    <xml>
	  <AppId><![CDATA[%s]]></AppId>
	  <Encrypt><![CDATA[%s]]></Encrypt>
    </xml>
 */
$signature = '3d856737466a00951f83695af237df8239165d46';
$timeStamp = '1626678323';
$nonce = 'ziteiswy';
$fromXml = '<xml><Encrypt><![CDATA[Dr7kfyA4rzU7oVWUvdsNffSWaXayat+cFDIK223YMyKMuXDzBbF163IOZ24QaxGUhp4OPZ9GZ6cFRQaIADkkubL8tILTqmHOML63WWzWmXIU6yQwjtC2FLqUszlmFCQrJ1lBYhHMZF14u+NKRyI4lbNzXWB9Mzd3176Z1kDLI4piwfsjX0kVQHi4hTo0Io9aNlTmr6/x09de6d2RUZAhoqmj1vUq2tq2LEwz5TQq49JHV5g2WC/YUNk50EEYYcAWUvl4glHCDjw1cyLsby9IKeJNW8mODBHM1ZyDrJ9u/MYkE7rm4MCrAFqjXDv516zNiDlv+JmaZarLOe9dvRiC+qsug7AWXBRcgx5/e2WnQmhl0HLAToOqc/mjIv19OVm0wpS/e6U+A5M9PA0Ocre4gO3tpaHUp+DSwgy5oELNVU0vRoGKypsExrBxsZ9bAk4+Kfs+LsjFmGhdodfuZL5eoNg3UiV1jJO0QseFU1pWuDKGQLG5TNgakQdcBCBk8JIlhL6tkG7NKtL5iB3eS6GMISYQsCeELKmyMxFLlXsfLaBU9oR2dp1b+px0CBpruz4xb8gdlDPNdGB5DUqQHQkNeegc8YJbOpdaCDHBg2NTcUM=]]></Encrypt><AppId><![CDATA[202187955]]></AppId></xml>';
$msg = '';
$errCode = $pc->decryptMsg($signature, $timeStamp, $nonce, $fromXml, $msg);
if ($errCode == 0) {
	var_dump("解密后: " . $msg . "\n");
} else {
	print($errCode . "\n");
}

//3.获取自建应用的token —— 详细步骤参见https://api.qidian.qq.com/wiki/doc/open/e6c4dpmm53tq68e27h80
$sid = '******';
$selfBuiltAppSecret = '******'; // 注意此处的secret是自建应用的secret，非开发者secret
$object = new SelfBuiltService($token, $encodingAesKey, $appId);
$selfBuildToken = $object->getSelfBuildToken($appId, $sid, $selfBuiltAppSecret);
var_dump($selfBuildToken); // $access_token = $selfBuildToken['data']['access_token']
print("\n");

//4.获取ticket票据 —— 详细步骤参见 应用授权流程 第一步:企点推送第三方应用开发商ticket https://api.qidian.qq.com/wiki/doc/open/enudsepks7pq90r54frh
$encryXml =  '<xml><AppId><![CDATA[202187955]]></AppId><Encrypt><![CDATA[VSq7MZqlKPgkUhcPKj6bKnAlTMSBSjIe/YEP09I84qhC4NScb6Z7/dmEFv9kUUFV3nWdIVPDO1HK36TOIceFAk9XR1iwrAjKVHFw//Y33REHmU3StpRlVxeji6/Dk2yXIhV3SetBAvwjaBgiPVJubRqlZHpmR9lsCmD1M6d/Ul69EHm13f1Su1OeY/vDy63mYIpAKv9yiAnkr/2NRx+iMnjbT7Q12N4cxDw5yfingA3wrg8xCxDqJhlxb5BtUjsKuQh2rXfbpkHwAOPCMD262B6s21lcKacUc4eJb0Adj6rLgu26C1wPe2+Yf4lZixTgiPYcBOezLf+FtXlSowvtmg==]]></Encrypt></xml>';
$signature = '4e182dd2652bca811f86c2f04a11d65d80ae4b67';
$timestamp =  '1630374604';
$nonce = '37381544744';
$object = new Common($token, $encodingAesKey, $appId);
$ticketResult = $object->getTicket($encryXml, $signature, $timestamp, $nonce);
var_dump($ticketResult); // $ticket = $ticketResult['ticket']
print("\n");

//5.根据ticket换取应用开发商token —— 详细步骤参见 应用授权流程 第二步：获取应用开发商token https://api.qidian.qq.com/wiki/doc/open/enudsepks7pq90r54frh
$ticketValue = $ticketResult['ticket'];
$componentAccessTokenResult = $object->getAccessToken($appId, $appSecret, $ticketValue);
var_dump($componentAccessTokenResult); // $componentAccessToken = $componentAccessTokenResult['data']['component_access_token'];
print("\n");

//6.获取第三方应用的企业授权code，并换取企业授权token —— 详细步骤参见 第三步:获取应用授权code & 第四步:使用应用授权code换取应用授权token https://api.qidian.qq.com/wiki/doc/open/
$componentAccessToken = $componentAccessTokenResult['data']['component_access_token'];
$code = "******"; // 企业开启应用时会往指令回调地址推送code
$object = new CompanyService($token, $encodingAesKey, $appId);
$companyAccessTokenResult = $object->getCompanyAccessToken($componentAccessToken, $appId, $code);
var_dump($companyAccessTokenResult); // $companyAccessToken = $companyAccessTokenResult['authorization_info']['authorizer_access_token']
print("\n");

//7.刷新企业授权token —— 详细步骤参见 第五步:应用授权刷新token的使用方法 https://api.qidian.qq.com/wiki/doc/open/
$componentAccessToken = $componentAccessTokenResult['data']['component_access_token'];
$authorizerAppid = $companyAccessTokenResult['authorization_info']['authorizer_appid'];
$authorizerRefreshToken = $companyAccessTokenResult['authorization_info']['authorizer_refresh_token'];
$sid = $companyAccessTokenResult['authorization_info']['applicationId'];
$object = new CompanyService($token, $encodingAesKey, $appId);
$companyRefreshTokenResult = $object->getCompanyRefreshToken($componentAccessToken, $appId, $authorizerAppid, $authorizerRefreshToken, $sid);
var_dump($companyRefreshTokenResult);
print("\n");