#!/usr/bin/php -d open_basedir=/usr/syno/bin/ddns
<?php
// Gandi DNS API documentation can be found here: https://api.gandi.net/docs/livedns/

function query_url($request_method, $url, $headers, $data = null, $verbose = true)
{
    $req = curl_init($url);
    curl_setopt($req, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($req, CURLOPT_CUSTOMREQUEST, $request_method);
    if ($data != null) curl_setopt($req, CURLOPT_POSTFIELDS, $data);
    curl_setopt($req, CURLOPT_RETURNTRANSFER, 1);
    $res = curl_exec($req);
    $res_code = curl_getinfo($req, CURLINFO_HTTP_CODE);

    if ($verbose) {
        switch ($res_code) {
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
    }

    curl_close($req);
    return [$res_code, $res];
}

function update_record($url, $headers, $rrset_value, $rrset_ttl = null, $verbose = true)
{
    if ($rrset_ttl)
        $data = '{"rrset_values": ["' . $rrset_value . '"], "rrset_ttl": ' . $rrset_ttl . '}';
    else
        $data = '{"rrset_values": ["' . $rrset_value . '"]}';
    query_url('PUT', $url, $headers, $data, $verbose);
}

function get_record_ttl($url, $headers)
{
    $res = query_url('GET', $url, $headers, null, false);
    if ($res[0] == 200)
        return json_decode($res[1])->rrset_ttl;
    return null;
}

function get_ipv6()
{
    $req = curl_init("https://api6.ipify.org"); // obtain IPv6 of the default geteway
    curl_setopt($req, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($req, CURLOPT_RETURNTRANSFER, 1);
    $ipv6 = curl_exec($req);
    curl_close($req);
    return $ipv6;
}

if ($argc !== 5) {
    echo 'badparam';
    exit();
}

$rrset_name = (string)$argv[1];
$pat = (string)$argv[2];
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
$headers = array('Authorization:Bearer ' . $pat, 'Content-Type:application/json');
$ttl = get_record_ttl($url, $headers);
update_record($url, $headers, $ipv4, $ttl);

$ipv6 = get_ipv6();
// only for IPv6 format
if (filter_var($ipv6, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
    $url = 'https://api.gandi.net/v5/livedns/domains/' . $fqdn . '/records/' . $rrset_name . '/AAAA';
    $ttl = get_record_ttl($url, $headers);
    update_record($url, $headers, $ipv6, $ttl, false); // IPv6 update is optional, ignore feedback
}
