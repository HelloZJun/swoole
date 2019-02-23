<?php
// 指明给谁推送，为空表示向所有在线用户推送
$to_uid = "";
// 推送的url地址，使用自己的服务器地址
$push_api_url = "192.168.61.130";
$post_data = 'test';
$ch = curl_init ();
curl_setopt ( $ch, CURLOPT_URL, $push_api_url );
curl_setopt ( $ch, CURLOPT_POST, 1 );
curl_setopt ( $ch, CURLOPT_HEADER, 0 );
curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_data );
curl_setopt ($ch, CURLOPT_HTTPHEADER, array("Expect:"));
$return = curl_exec ( $ch );
curl_close ( $ch );
var_export($return);