<?php
namespace Utils;

use \GuzzleHttp\Cookie\CookieJar;
use \GuzzleHttp\Client;

class Kd100Utils
{

    const BASE_URL = 'https://m.kuaidi100.com';

    protected $client;
    protected $jar;
    protected $timeout= 30;
    protected $proxy  = "";
    protected $header = [
        'User-Agent'     => 'Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_1 like Mac OS X) AppleWebKit/603.1.30 (KHTML, like Gecko) Version/10.0 Mobile/14E304 Safari/602.1',
        'Referer'        => 'https://m.kuaidi100.com/',
        'Sec-Fetch-Dest' => 'empty',
        'Sec-Fetch-Mode' => 'cors',
        'Sec-Fetch-Site' => 'same-origin',
        'Content-Type'   => 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8'
    ];


    public function __construct($configs = null)
    {
        if (!empty($configs['timeout']) && is_numeric($configs['timeout']) && $configs['timeout'] > 0) {
            $this->timeout = $configs['timeout'];
        }

        if (!empty($configs['proxy'])) {
            $parse = parse_url($configs['proxy']);
            if ($parse['scheme'] && $parse['host'] && $parse['port']) {
                $this->proxy = "{$parse['scheme']}://{$parse['host']}:{$parse['port']}";
            }
        }
    }

    public function find($no, $phone = '')
    {
        if (empty($no)) {
            return false;
        }

        $this->initCookie($no);
        $companyCode = $this->getCompanyCode($no);

        if (!$companyCode) {
            return false;
        }

        //顺丰快递需要验证手机尾号
        if ($companyCode == 'shunfeng') {
            $phone = substr($phone, -4);
            if (empty($phone) || strlen($phone) != 4) {
                throw new \Exception("顺丰快递需填写收件人手机号后4位");
            }
        } else {
            $phone = '';
        }

        return $this->getProgress($companyCode, $no, $phone);
    }

    /**
     * 初始化请求,获取COOKIE
     * @param $no
     */
    protected function initCookie($no)
    {
        $this->jar    = new CookieJar(true);
        $this->client = new Client([
            'base_uri' => self::BASE_URL,
            'headers' => $this->header,
            'cookies' => $this->jar,
            'proxy'   => $this->proxy,
            'timeout' => $this->timeout
        ]);

        $this->client->request('GET', '/result.jsp', [
            'query' => [
                'nu' => $no,
            ],
        ]);
    }

    /**
     * 获取快递公司编码
     * @param $no
     */
    public function getCompanyCode($no)
    {
        $response = $this->client->request('GET', '/apicenter/kdquerytools.do', [
            'query' => [
                'method' => 'autoComNum',
                'text'   => $no
            ]
        ]);

        $re = $response->getBody()->getContents();
        $re = @json_decode($re, true);

        if (!empty($re['message'])) {
            throw new \Exception("Error:" . $re['message']);
        }

        if (!$re['auto'][0]['comCode']) {
            return false;
        }

        return $re['auto'][0]['comCode'];
    }

    /**
     * 获取快递信息
     * @param $code
     * @param $no
     * @param string $phone
     */
    public function getProgress($code, $no, $phone = '')
    {
        $params = [
            'postid' => $no,
            'id' => 1,
            'valicode' => '',
            'temp' => '0.1131618778' . rand(1111111, 9999999),
            'type' => $code,
            'phone' => $phone,
            'token' => '',
            'platform' => 'MWWW'
        ];

        $response = $this->client->request('POST', '/query', [
            'form_params' => $params,
        ]);

        $re = $response->getBody()->getContents();
        $re = @json_decode($re, true);

        if (!empty($re['message']) && $re['message'] != 'ok') {
            throw new \Exception("Error:" . $re['message']);
        }

        $result = [];

        foreach ($re['data'] as $row) {
            $result[] =  [
                'time' => $row['time'],
                'content' => $row['context']
            ];
        }

        return $result;
    }
}