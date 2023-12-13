<?php

// ip checker service
class IpCheckerService
{
    private static $instance;

    private function __construct() {}

    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // format ip address
    public function format_ip_address_city($arr) {
        $city = $arr['city'] ?? '';
        $country = $arr['country'] ?? '';
        $region = $arr['region'] ?? '';

        $ipAddressFormat = get_option('wpblog_post_ip_address_format', WPBLOG_POST_DEFAULT_IP_ADDRESS_FORMAT);
        if (empty($city) && empty($country) && empty($region)) return $ipAddressFormat;

        $result = $ipAddressFormat;

        $result = str_replace('city', $city, $result);
        $result = str_replace('country', $country, $result);
        $result = str_replace('region', $region, $result);

        return $result;
    }

    private array $ips = [];
    public function getIpCheckerByIp($ip)
    {
        if (isset($this->ips[$ip])) return $this->ips[$ip];
        if (empty($ip)) return '';
        $ipChecker = get_option('wpblog_post_ip_checker', WPBLOG_POST_DEFAULT_IP_CHECKER);
        $dataArr = [
            'city' => '',
            'country' => '',
            'region' => ''
        ];
        switch ($ipChecker) {
            case WPBLOG_POST_DEFAULT_IP_CHECKER:
            default:
                // 国   省   市
                $reader = new Reader(__DIR__ . '/ipipfree.ipdb');
                try {
                    if ($reader->find($ip)) {
                        $dataArr['city'] = $reader->find($ip)[2];
                        $dataArr['region'] = $reader->find($ip)[1];
                        $dataArr['country'] = $reader->find($ip)[0];
                    }
                    return $this->format_ip_address_city($dataArr);
                } catch (\Throwable $th) {
                    return '';
                }
            case 'ipapi':
                $url = 'http://ip-api.com/json/'. $ip .'/?lang=zh-CN';
                $response = wp_remote_get($url);
                if (is_wp_error($response)) return '';

                $body = wp_remote_retrieve_body($response);
                $arr = json_decode($body, true) ?? [];
                return $this->format_ip_address_city($arr);
        }
    }
}