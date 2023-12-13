<?php

require __DIR__ . '/const/WpBlogConst.php';
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
        $city = $arr[WpBlogConst::IP_ADDRESS_CITY] ?? '';
        $country = $arr[WpBlogConst::IP_ADDRESS_COUNTRY] ?? '';
        $region = $arr[WpBlogConst::IP_ADDRESS_REGION] ?? '';

        $ipAddressFormat = get_option('wpblog_post_ip_address_format', WPBLOG_POST_DEFAULT_IP_ADDRESS_FORMAT);
        if (empty($city) && empty($country) && empty($region)) return $ipAddressFormat;

        $result = $ipAddressFormat;

        $result = str_replace(WpBlogConst::IP_ADDRESS_CITY, $city, $result);
        $result = str_replace(WpBlogConst::IP_ADDRESS_COUNTRY, $country, $result);
        $result = str_replace(WpBlogConst::IP_ADDRESS_REGION, $region, $result);

        return $result;
    }

    private array $ips = [];
    public function getIpCheckerByIp($ip)
    {
        if (isset($this->ips[$ip])) return $this->ips[$ip];
        if (empty($ip)) return '';
        $ipChecker = get_option('wpblog_post_ip_checker', WPBLOG_POST_DEFAULT_IP_CHECKER);
        $dataArr = [
            WpBlogConst::IP_ADDRESS_CITY => '',
            WpBlogConst::IP_ADDRESS_COUNTRY => '',
            WpBlogConst::IP_ADDRESS_REGION => ''
        ];
        switch ($ipChecker) {
            case WPBLOG_POST_DEFAULT_IP_CHECKER:
            default:
                $reader = new Reader(__DIR__ . '/ipipfree.ipdb');
                try {
                    if ($reader->find($ip)) {
                        $dataArr[WpBlogConst::IP_ADDRESS_CITY] = $reader->find($ip)[2];
                        $dataArr[WpBlogConst::IP_ADDRESS_REGION] = $reader->find($ip)[1];
                        $dataArr[WpBlogConst::IP_ADDRESS_COUNTRY] = $reader->find($ip)[0];
                    }
                    return $this->format_ip_address_city($dataArr);
                } catch (\Throwable $th) {
                    return '';
                }
            case 'ipapi':
                // api url
                $url = 'http://ip-api.com/json/'. $ip .'?lang=zh-CN';
                $response = wp_remote_get($url);
                // if err != nil return empty
                if (is_wp_error($response)) return '';
                
                $body = wp_remote_retrieve_body($response);
                $arr = json_decode($body, true) ?? [];
                $dataArr[WpBlogConst::IP_ADDRESS_CITY] = $arr['city'] ?? '';
                $dataArr[WpBlogConst::IP_ADDRESS_REGION] = $arr['regionName'] ?? '';
                $dataArr[WpBlogConst::IP_ADDRESS_COUNTRY] = $arr['country'] ?? '';
                return $this->format_ip_address_city($dataArr);
        }
    }
}