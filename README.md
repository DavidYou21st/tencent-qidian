# 企点开放平台 SDK

## 使用方法

```
//1.完善服务器配置
$pc = new QDMsgCrypt($token, $encodingAesKey, $appId);
$str = $pc->VerifyURL($signature, $timestamp, $nonce, $echostr, $replyEchoStr);

//2.解析xml推送内容
$pc = new QDMsgCrypt($token, $encodingAesKey, $appId);
$errCode = $pc->decryptMsg($signature, $timeStamp, $nonce, $fromXml, $msg);

//3.获取自建应用的token
$object = new SelfBuiltService($token, $encodingAesKey, $appId);
$selfBuildToken = $object->getSelfBuildToken($appId, $sid, $secret);

//4.获取ticket票据
$object = new Common($token, $encodingAesKey, $appId);
$ticketResult = $object->getTicket($encryXml, $signature, $timestamp, $nonce);

//5.根据ticket换取应用开发商token
$object = new Common($token, $encodingAesKey, $appId);
$componentAccessTokenResult = $object->getAccessToken($appId, $appSecret, $ticketValue);

//6.获取第三方应用的企业授权code，并换取企业授权token
$object = new CompanyService($token, $encodingAesKey, $appId);
$companyAccessTokenResult = $object->getCompanyAccessToken($componentAccessToken, $appId, $code);

//7.刷新企业授权token
$object = new CompanyService($token, $encodingAesKey, $appId);
$companyRefreshTokenResult = $object->getCompanyRefreshToken($componentAccessToken, $appId, $authorizerAppid, $authorizerRefreshToken, $sid);
```

## 更多
查看 [Demo](https://github.com/TencentQidian/qidian-sdk-php/blob/master/demo/demo.php "Demo")

## 开发文档

https://api.qidian.qq.com/wiki