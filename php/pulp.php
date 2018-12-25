<?php

require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;
use Qiniu\Http\Client;

$accessKey = '';
$secretKey = '';
$auth = new Auth($accessKey, $secretKey);

$reqBody = array();
$reqBody['uri'] = "xxxx";

list($ret, $err) = pulp($reqBody, $auth);
if ($err !== null) {
    var_dump($err);
} else {
    var_dump($ret);
}

function pulp($reqBody, $auth){
    $url = 'http://ai.qiniuapi.com/v1/image/censor';
    $req = array();
    $req['data'] = $reqBody;
    $body = json_encode($req);
    return plupost($url, $body, $auth);
}

function plupost($url, $body, $auth)
{
    $headers = authorizationV2($url, 'POST', $body, 'application/json', $auth);
    $headers['Content-Type']='application/json';
    // var_dump($body);exit;
    $ret = Client::post($url, $body, $headers);
    if (!$ret->ok()) {
        print($ret->statusCode);
        return array(null, new Error($url, $ret));
    }
    $r = ($ret->body === null) ? array() : $ret->json();
    return array($r, null);
}

function authorizationV2($url, $method, $body = null, $contentType = null, $auth)
{
    $urlItems = parse_url($url);
    $host = $urlItems['host'];

    if (isset($urlItems['port'])) {
        $port = $urlItems['port'];
    } else {
        $port = '';
    }

    $path = $urlItems['path'];
    if (isset($urlItems['query'])) {
        $query = $urlItems['query'];
    } else {
        $query = '';
    }

    //write request uri
    $toSignStr = $method . ' ' . $path;
    if (!empty($query)) {
        $toSignStr .= '?' . $query;
    }

    //write host and port
    $toSignStr .= "\nHost: " . $host;
    if (!empty($port)) {
        $toSignStr .= ":" . $port;
    }

    if (!empty($contentType)) {
        $toSignStr .= "\nContent-Type: " . $contentType;
    }

    $toSignStr .= "\n\n";

    if (!empty($body)) {
        $toSignStr .= $body;
    }

    $sign = $auth->sign($toSignStr);
    $auth = 'Qiniu ' . $sign;
    // echo $auth;exit;
    return array('Authorization' => $auth);
}












// $reqBodyold = <<<EOT
// {
//     "data": {
//         "uri": "http://liufangxing.qiniuts.com/test/uptesto.jpeg"
//     }
// }
// EOT;