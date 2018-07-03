<?php
ini_set('display_errors', false);
error_reporting(E_ALL ^ E_NOTICE);
ignore_user_abort(true);
set_time_limit(60);
define('MINE_API_BTC', 'https://www.whattomine.com/coins/1.json');
define('MINE_API_LTC', 'https://www.whattomine.com/coins/4.json');
define('MINE_API_ETH', 'https://www.whattomine.com/coins/151.json');

class App {

    use GetRequest;

    public static function isPost($key) {
        return (isset($_POST[$key]) && !empty($_POST[$key]));
    }

    public static function isGet($key) {
        return (isset($_GET[$key]) && !empty($_GET[$key]));
    }

    public static function hasIndex(array $arr, $index) {
        return isset($arr[$index]) && !empty($arr[$index]);
    }

    public static function sanitize($value) {
        if (is_object($value) || is_resource($value) || $value === null) {
            return $value;
        }
        if (is_array($value)) {
            $tmp = [];
            foreach ($value as $k => $v) {
                $tmp[$k] = static::sanitize($v);
            }
            return $tmp;
        }
        return filter_var($value, FILTER_SANITIZE_STRING);
    }

    public static function isSameReferer() {
        if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
            return stripos($_SERVER['HTTP_REFERER'], $_SERVER['SERVER_NAME']) !== false;
        }
        return false;
    }

    public static function isPostMethod() {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    public static function isAjax() {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && (strtolower(getenv('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest'));
    }

    public static function request($url, array $options = array()) {
        $request = new static();
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $options[CURLOPT_USERAGENT] = $_SERVER['HTTP_USER_AGENT'];
        }
        return $request->trait_GetRequest_GetURL($url, $options);
    }

}

class rbc {

    const url = 'http://cbrates.rbc.ru/tsv/';
    const file = '.tsv';

    private $date = 0;

    public function __construct($date = null) {
        if ($date == null) {
            $date = time();
        }
        $this->date = $date;
    }

    public function curs($currency_code) {
        $url = self::url;
        $curs = 0;
        try {
            if (!is_numeric($currency_code)) {
                throw new Exception('Передан неверный код валюты');
            }
            $url .= $currency_code . '/';
            if ($this->date <= 0) {
                throw new Exception('Передана неверная дата');
            }
            $url .= date('Y/m/d', $this->date);
            $url .= self::file;

            $page = file_get_contents($url);
            $curs = $this->parse($page);
        } catch (Exception $e) {
            echo 'Не удалось получить курс валюты. ', $e->getMessage();
        }
        return $curs;
    }

    private function parse($file) {
        if (empty($file)) {
            throw new Exception('Возможно указан неверный код валюты, также возможно на указанную дату еще не установлен курс валюты, либо сервер "cbrates.rbc.ru" недоступен.');
        }
        $curs = explode("\t", $file);
        if (!empty($curs[1])) {
            return $curs[1];
        } else {
            throw new Exception('Сервер не выдал результатов по данной валюте на указнную дату');
        }
    }

}

trait GetRequest {

    protected function trait_GetRequest_UserAgent() {
        $ua = [
            'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36 OPR/39.0.2256.71',
            'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:49.0) Gecko/20100101 Firefox/49.0',
            'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.116 Safari/537.36',
            'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 YaBrowser/16.9.1.1131 Yowser/2.5 Safari/537.36',
            'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/534.57.2 (KHTML, like Gecko) Version/5.1.7 Safari/534.57.2',
            'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.116 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36 Edge/12.246',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36 Edge/12.246',
            'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko',
            'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.116 Safari/537.36',
            'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Maxthon/4.4.8.2000 Chrome/30.0.1599.101 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36 Edge/12.246',
        ];
        return $ua[\array_rand($ua)];
    }

    public $trait_GetRequest_Effective_URL;
    public $trait_GetRequest_Host_URL;
    public $trait_GetRequest_Scheme_URL;
    public $trait_GetRequest_Path_URL;

    public function trait_GetRequest_GetURL($url, array $options = []) {
        $ch = curl_init($url);
        $curlOptions = [
            CURLOPT_USERAGENT => $this->trait_GetRequest_UserAgent(),
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_REFERER => 'https://www.google.com/search?source=h&q=' . $this->trait_GetRequest_Host_URL . '&oq=m' . $this->trait_GetRequest_Host_URL,
            CURLOPT_HTTPHEADER => ['Expect:']
        ];
        if (is_array($options) && !empty($options)) {
            $curlOptions = array_replace($curlOptions, $options);
        }
        curl_setopt_array($ch, $curlOptions);
        unset($curlOptions, $options, $url);
        $ret = curl_exec($ch);
        $this->trait_GetRequest_Effective_URL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $chunks = parse_url($this->trait_GetRequest_Effective_URL);
        $this->trait_GetRequest_Host_URL = $chunks['host'];
        $this->trait_GetRequest_Scheme_URL = $chunks['scheme'];
        $this->trait_GetRequest_Path_URL = $chunks['path'];
        curl_close($ch);
        return $ret;
    }

    public static function diff($current, $percent) {
        return round($current * (1 - $percent / 100), 10);
    }

    public static function getMonth($coins, $percent) {
        return static::diff($coins * 30, $percent);
    }

    public static function getPeriod($period, $coins, $percent) {
        $start = static::getMonth($coins, $percent);
        $result = $start;
        while (--$period >= 0) {
            $start = static::diff($start, $percent);
            $result += $start;
        }
        return $result;
    }

}

if (App::isAjax() && App::isGet('calc_data')) {

    $usd = (float) (new rbc())->curs(840);

    foreach (App::sanitize($_GET['calc_data']) as $item) {
        $calcData[$item['name']] = $item['value'];
    }

    if (!App::hasIndex($calcData, 'crypt')) {
        echo json_encode([
            'status' => false,
            'data' => 'Вы не выбрари криптовалюту!',
        ]);
        die;
    }
    $crypt = $calcData['crypt'];

    if (!App::hasIndex($calcData, 'hash')) {
        echo json_encode([
            'status' => false,
            'data' => 'Вы не указали хешрейт!',
        ]);
        die;
    }
    $hash = $calcData['hash'];

    if (!App::hasIndex($calcData, 'electricity')) {
        echo json_encode([
            'status' => false,
            'data' => 'Вы не указали расход эелектричества!',
        ]);
        die;
    }
    $electricity = (float) $calcData['electricity'];

    if (!App::hasIndex($calcData, 'electricity-price')) {
        echo json_encode([
            'status' => false,
            'data' => 'Вы не стоимость электричества!',
        ]);
        die;
    }
    $electricityPrice = round($calcData['electricity-price'] / $usd, 2);

    if (!App::hasIndex($calcData, 'period')) {
        echo json_encode([
            'status' => false,
            'data' => 'Вы не указали период расчета!',
        ]);
        die;
    }
    $period = (int) $calcData['period'];

    if (!App::hasIndex($calcData, 'expected-price')) {
        echo json_encode([
            'status' => false,
            'data' => 'Вы не указали желаемую стоимость $!',
        ]);
        die;
    }
    $expectedPrice = (float) $calcData['expected-price'];

    if (!App::hasIndex($calcData, 'percent')) {
        echo json_encode([
            'status' => false,
            'data' => 'Вы не указали процент сложности!',
        ]);
        die;
    }
    $percent = (float) $calcData['percent'];


    $API = null;
    switch ($crypt) {
        case 'btc':
            $API = MINE_API_BTC . '?' . http_build_query([
                        'hr' => round($hash * 1000, 1),
                        'p' => 0,
                        'fee' => '0.0',
                        'cost' => 0,
                        'hcost' => '0.0',
                        'commit' => 'Calculate',
            ]);
            break;
        case 'ltc':
            $API = MINE_API_LTC . '?' . http_build_query([
                        'hr' => $hash,
                        'p' => 0,
                        'fee' => '0.0',
                        'cost' => 0,
                        'hcost' => '0.0',
                        'commit' => 'Calculate',
            ]);
            break;
        case 'eth':
            $API = MINE_API_ETH . '?' . http_build_query([
                        'hr' => $hash,
                        'p' => 0,
                        'fee' => '0.0',
                        'cost' => 0,
                        'hcost' => '0.0',
                        'commit' => 'Calculate',
            ]);
            break;
        default:
            echo json_encode([
                'status' => false,
                'data' => 'Вы не выбрари криптовалюту!',
            ]);
            die;
    }

    $response = App::request($API);

    if ($response && ($json = json_decode($response, true)) !== null) {
        $dailyMining = (float) $json['estimated_rewards'];
        $monthMining = round(App::getMonth($dailyMining, $percent), 10);
        $periodMining = round(App::getPeriod($period, $dailyMining, $percent), 10);

        $totalDays = $period * 30;

        $result = [];
        $result['dailyMining'] = $dailyMining;
        $result['monthMining'] = $monthMining;
        $result['periodMining'] = $periodMining;

        $powerDailyPrice = round($electricity * $electricityPrice * 24, 2);
        $powerDailyPriceRU = round($powerDailyPrice * $usd, 2);
        $powerMonthPrice = round($powerDailyPrice * 30, 2);
        $powerMonthPriceRU = round($powerMonthPrice * $usd, 2);
        $powerPeriodPrice = round($powerMonthPrice * $period, 2);
        $powerPeriodPriceRU = round($powerPeriodPrice * $usd, 2);

        $result['powerDailyPrice'] = $powerDailyPrice;
        $result['powerDailyPriceRU'] = $powerDailyPriceRU;
        $result['powerMonthPrice'] = $powerMonthPrice;
        $result['powerMonthPriceRU'] = $powerMonthPriceRU;
        $result['powerPeriodPrice'] = $powerPeriodPrice;
        $result['powerPeriodPriceRU'] = $powerPeriodPriceRU;

        $result['usd'] = $usd;
        $result['dailyProfit'] = (float) substr($json['profit'], 1);

        $expectedTotalProfit = round($expectedPrice * $periodMining, 2);
        $expectedDailyProfit = round($expectedTotalProfit / $totalDays, 2);

        $result['total'] = [
            'period' => $period,
            'totalCoins' => $periodMining,
            'expectedTotalProfit' => $expectedTotalProfit,
            'expectedDailyProfit' => $expectedDailyProfit,
            'powerDailyPriceRU' => $powerDailyPriceRU,
            'powerPeriodPrice' => $powerPeriodPrice,
            'powerPeriodPriceRU' => $powerPeriodPriceRU,
            'usd' => $usd,
        ];

        $result['data'] = $json;
        $result['status'] = true;

        echo json_encode($result);
    } else {
        echo json_encode([
            'status' => false,
            'data' => 'Невозможно преобразовать данные.',
        ]);
    }
    die;
}
