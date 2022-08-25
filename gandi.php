#!/usr/bin/php -d open_basedir=/usr/syno/bin/ddns
<?php
// Gandi DNS API documentation can be found here: https://api.gandi.net/docs/livedns/

if ($argc !== 5) {
    echo 'badparam';
    exit();
}

$rrset_name = (string)$argv[1];
$apikey = (string)$argv[2];
$fqdn = (string)$argv[3];
$ipv4 = (string)$argv[4];

// check the hostname contains '.'
if (strpos($fqdn, '.') === false) {
    echo 'badparam';
    exit();
}

// only for IPv4 format
if (!filter_var($ipv4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
    echo 'badparam';
    exit();
}

$url = 'https://api.gandi.net/v5/livedns/domains/' . $fqdn . '/records/' . $rrset_name . '/A';
$headers = array('Authorization:Apikey ' . $apikey, 'Content-Type:application/json');
$data = '{"rrset_values": ["' . $ipv4 . '"]}';

$req = curl_init();
curl_setopt($req, CURLOPT_URL, $url);
curl_setopt($req, CURLOPT_HTTPHEADER, $headers);
curl_setopt($req, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($req, CURLOPT_POSTFIELDS, $data);
curl_setopt($req, CURLOPT_RETURNTRANSFER, 1);
$res = curl_exec($req);

switch (curl_getinfo($req, CURLINFO_HTTP_CODE)) {
    case 0:
        echo 'badresolv';
        break;
    case 201:
        echo 'good';
        break;
    case 401:
    case 403:
        echo 'badauth';
        break;
    case 404:
        echo 'nohost';
        break;
    default:
        echo 'badagent';
}

curl_close($req);
