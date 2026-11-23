<?php
/**
 * functions.php
 *
 * @copyright  2017 opencart.cn - All Rights Reserved
 * @link       http://www.guangdawangluo.com
 * @author     Edward Yang <yangjin@opencart.cn>
 * @created    2/23/17 10:32
 * @modified   2/23/17 10:32
 */

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

// OpenCart version type check
if (!function_exists('is_pro')) {
    /**
     * @return bool
     */
    function is_pro()
    {
        return (defined('OCTYPE') && strtoupper(OCTYPE) == 'PRO');
    }
}

if (!function_exists('is_free')) {
    /**
     * @return bool
     */
    function is_free()
    {
        return (defined('OCTYPE') && strtoupper(OCTYPE) == 'FREE');
    }
}


if (!function_exists('is_free_or_pro')) {

    function is_free_or_pro()
    {
        return is_pro() || is_free();
    }
}

if (!function_exists('is_ft')) {
    /**
     * @return bool
     * @throws Exception
     */
    function is_ft()
    {
        if (defined('FT')) {
            return FT;
        }
        return config('ft');
    }
}

if (!function_exists('is_std')) {
    /**
     * @return bool
     */
    function is_std()
    {
        return !defined('OCTYPE');
    }
}

if (!function_exists('is_admin')) {
    /**
     * Check current is admin or catalog
     *
     * @return bool
     */
    function is_admin()
    {
        return defined('HTTPS_CATALOG');
    }
}

if (!function_exists('is_installer')) {
    /**
     * Check current is installer
     *
     * @return bool
     */
    function is_installer()
    {
        return defined('HTTP_OPENCART');
    }
}

if (!function_exists('is_home_page')) {
    /**
     * Check current route is common/home
     *
     * @return bool
     * @throws Exception
     */
    function is_home_page()
    {
        return current_route() == 'common/home';
    }
}

if (!function_exists('is_debug')) {
    /**
     * Check current env is debug or not
     *
     * @return string
     */
    function is_debug()
    {
        return defined('DEBUG') && DEBUG;
    }
}

if (!function_exists('admin_logo')) {
    /**
     * @return string
     * @throws Exception
     */
    function admin_logo()
    {
        $imageName = 'logo';
        if (is_ft()) {
            $imageName = 'logo_ft';
        }
        $logoUrl = "view/image/{$imageName}.png";
        return $logoUrl;
    }
}

if (!function_exists('oc_type')) {
    /**
     * @return string
     * @throws Exception
     */
    function oc_type()
    {
        if (is_ft()) {
            return 'FT';
        } elseif (is_pro()) {
            return 'PRO';
        } else {
            return 'STD';
        }
    }
}

if (!function_exists('oc_version')) {
    /**
     * @return string
     * @throws Exception
     */
    function oc_version()
    {
        $version = oc_type() . ' - ' . VERSION;
        if (defined('BUILD')) {
            $version .= ' (' . BUILD . ')';
        }
        return $version;
    }
}

if (!function_exists('dump')) {
    /**
     * @param $value
     */
    function dump($value)
    {
        if (class_exists(CliDumper::class)) {
            $dumper = in_array(PHP_SAPI, ['cli', 'phpdbg']) ? new CliDumper : new HtmlDumper;
            $dumper->dump((new VarCloner)->cloneVar($value));
        } else {
            var_dump($value);
        }
    }
}

if (!function_exists('d')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed
     * @return void
     */
    function d(...$args)
    {
        foreach ($args as $x) {
            dump($x);
        }
    }
}

if (!function_exists('dd')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed
     * @return void
     */
    function dd(...$args)
    {
        foreach ($args as $x) {
            dump($x);
        }
        exit;
    }
}

if (!function_exists('sub_string')) {
    /**
     * @param $string
     * @param $length
     * @param string $dot
     * @return string
     */
    function sub_string($string, $length, $dot = '...')
    {
        $strLength = strlen($string);
        if ($length <= 0) {
            return $string;
        } elseif ($strLength <= $length) {
            return $string;
        }
        return utf8_substr($string, 0, $length) . $dot;
    }
}

if (!function_exists('get_calling_codes')) {
    /**
     * @return mixed
     * @throws Exception
     */
    function get_calling_codes()
    {
        return model("localisation/calling_code")->getCallingCodes();
    }
}

if (!function_exists('format_date')) {
    /**
     * @param $format
     * @param null $timestamp
     * @return mixed
     */
    function format_date($format, $timestamp = null)
    {
        $timestamp = $timestamp ?: time();
        $datetime = new Utils\Datetime();
        $baseDatetime = date('Y-m-d H:i:s', $timestamp);
        $datetime = $datetime->convert($baseDatetime, '', $format);
        return $datetime;
    }
}

if (!function_exists('render_csv')) {
    /**
     * Convert a value to studly caps case.
     *
     * @param  string $value
     * @return string
     */
    function render_csv($value)
    {
        $filename = date('Ymd') . '.csv';
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $value;
        exit;
    }
}


if (!function_exists('registry')) {
    /**
     * Get Registry Instance
     *
     * @param null $type
     * @return Registry|Config|Session|Document|Url|Loader|\Cart\Currency|Language|Request|Log|\Cart\Customer
     * @throws Exception
     */
    function registry($type = null)
    {
        if ($type) {
            return Registry::getSingleton()->get($type);
        }
        return Registry::getSingleton();
    }
}

if (!function_exists('config')) {
    /**
     * Get config values through keys
     *
     * @param string $key
     * @param null $default
     * @return mixed|null
     */
    function config($key = '', $default = null)
    {
        try {
            if (empty($key)) {
                return $default;
            }
            $segments = explode('.', $key);
            $value = registry('config')->get($segments[0]);
            if (is_null($value)) {
                return $default;
            }
            if (count($segments) == 1) {
                return is_null($value) ? $default : $value;
            }
            array_shift($segments);
            return array_get($value, implode('.', $segments), $default);
        } catch (\Exception $e) {
            return $default;
        }
    }
}

if (!function_exists('model')) {
    /**
     * Get model object through route
     *
     * @param string $route
     * @return mixed
     * @throws Exception
     */
    function model($route = '')
    {
        $registry = registry();
        $registry->get('load')->model($route);
        $route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);

        $modelName = 'model_' . str_replace('/', '_', $route);
        if ($registry->has($modelName)) {
            return $registry->get($modelName);
        }
        return null;
    }
}

if (!function_exists('table')) {
    /**
     * Get model object through route
     *
     * @param string $name
     * @return \Illuminate\Database\Query\Builder
     */
    function table($name = '')
    {
        try {
            $connection = registry('conn');
            if (empty($name)) {
                return $connection->query();
            }
            return $connection->table($name);
        } catch (\Exception $e) {
            return null;
        }
    }
}

if (!function_exists('raw')) {
    /**
     * Get model object through route
     *
     * @param $sql $name
     * @return \Illuminate\Database\Query\Builder
     */
    function raw($sql = '')
    {
        try {
            $connection = registry('conn');
            $query = $connection->query();
            return $query->raw($sql);
        } catch (\Exception $e) {
            return null;
        }
    }
}

if (!function_exists('session')) {
    /**
     * Get Session Instance
     *
     * @return Session
     * @throws Exception
     */
    function session()
    {
        return registry('session');
    }
}

if (!function_exists('document')) {
    /**
     * Get Document instance
     *
     * @return Document
     * @throws Exception
     */
    function document()
    {
        return registry('document');
    }
}

if (!function_exists('request')) {
    /**
     * Get Request instance
     *
     * @return Request
     * @throws Exception
     */
    function request()
    {
        return registry('request');
    }
}

if (!function_exists('url')) {
    /**
     * Get Url instance
     *
     * @return Url
     * @throws Exception
     */
    function url()
    {
        return registry('url');
    }
}

if (!function_exists('currency')) {
    /**
     * Get Currency instance
     *
     * @return mixed|\Cart\Currency
     * @throws Exception
     */
    function currency()
    {
        return registry('currency');
    }
}

if (!function_exists('customer')) {
    /**
     * Get customer instance
     *
     * @return mixed|Registry
     * @throws Exception
     */
    function customer()
    {
        return registry('customer');
    }
}

if (!function_exists('t')) {
    /**
     * Get Translation text
     *
     * @param $key
     * @return mixed
     */
    function t($key)
    {
        try {
            return registry('language')->get($key);
        } catch (\Exception $e) {
            return '';
        }
    }
}

if (!function_exists('debug_bar')) {
    /**
     * Get DebugBar Renderer
     *
     * @return \DebugBar\JavascriptRenderer
     * @throws Exception
     */
    function debug_bar()
    {
        return registry()->get('debug_bar');
    }
}

if (!function_exists('current_route')) {
    /**
     * Get current route.
     *
     * @return mixed
     * @throws Exception
     */
    function current_route()
    {
        return array_get(request()->get, 'route', 'common/home');
    }
}

if (!function_exists('current_language_id')) {
    /**
     * Get current language code
     *
     * @return string
     */
    function current_language_id()
    {
        return config('config_language_id');
    }
}

if (!function_exists('create_dir')) {
    /**
     * @param $filePath
     * @return bool
     */
    function create_dir($filePath)
    {
        $directory = dirname($filePath);
        if (is_dir($directory)) {
            return true;
        }
        if (create_dir($directory)) {
            mkdir($directory, 0777);
            return true;
        }
    }
}

if (!function_exists('current_language_code')) {
    /**
     * Get current language code
     *
     * @return string
     * @throws Exception
     */
    function current_language_code()
    {
        return strtolower(array_get(session()->data, 'language'));
    }
}

if (!function_exists('is_zh_cn')) {
    /**
     * Check if the language is zh_cn
     *
     * @throws Exception
     */
    function is_zh_cn()
    {
        return current_language_code() == 'zh-cn';
    }
}

if (!function_exists('image_resize')) {
    /**
     * @param string $image
     * @param int $width
     * @param int $height
     * @return mixed
     * @throws Exception
     */
    function image_resize($image = 'placeholder.png', $width = 100, $height = 100)
    {
        if (starts_with($image, 'https://') || starts_with($image, 'http://')) {
            return $image;
        }
        registry('load')->model('tool/image');
        return registry()->get('model_tool_image')->resize($image, $width, $height);
    }
}

if (!function_exists('image_exists')) {
    /**
     * @param $image
     * @return bool
     */
    function image_exists($image)
    {
        return is_file(DIR_IMAGE . $image);
    }
}

if (!function_exists('image_original_url')) {
    /**
     * @param $image
     * @return string
     * @throws Exception
     */
    function image_original_url($image)
    {
        $extension_image = 'extension/image/' . $image;
        if (is_file(DIR_OCROOT . $extension_image)) {
            return url()->getBaseUrl() . $extension_image;
        }
        return url()->imageLink($image);
    }
}

if (!function_exists('original_url')) {
    /**
     * @param $uri
     * @return string
     * @throws Exception
     */
    function resource_url($uri)
    {
        $resource = 'extension/' . $uri;
        if (is_file(DIR_OCROOT . $resource)) {
            return url()->getBaseUrl() . $resource;
        }
        return url()->getBaseUrl() . $uri;
    }
}

if (!function_exists('template_exists')) {
    /**
     * Check if twig file exists
     *
     * @param $route
     * @return bool
     * @throws Exception
     */
    function template_exists($route)
    {
        if (is_mobile_device() && is_file(DIR_TEMPLATE . "mobile/template/{$route}.twig")) {
            return true;
        }

        $theme = config('config_theme') == 'default' ? config('theme_default_directory') : config('config_theme');
        return is_file(DIR_TEMPLATE . "{$theme}/template/{$route}.twig") ||
            is_file(DIR_TEMPLATE . "default/template/{$route}.twig");
    }
}

if (!function_exists('base_url')) {
    /**
     * Get base url.
     *
     * @return bool|string
     */
    function base_url()
    {
        if (defined('HTTPS_CATALOG')) {
            return HTTPS_CATALOG;
        }
        return HTTPS_SERVER;
    }
}

if (!function_exists('address_format')) {
    /**
     * Format address.
     *
     * @param array $address
     * @param string $format
     * @param string $prefix
     * @return mixed
     */
    function address_format($address = array(), $format = '', $prefix = '')
    {
        // 日本地址固定显示：不显示国家/公司，邮编 xxx-xxxx
        $format = '{firstname} {lastname} ({telephone})' . "\n" . '{postcode}' . "\n" . '{zone}{city}{county}{address_1}';

        if ($prefix) {
            $prefix .= '_';
        }

        $find = array(
            '{firstname}',
            '{lastname}',
            ' ({telephone})',
            '{company}',
            '{address_1}',
            '{address_2}',
            '{county}',
            '{city}',
            '{postcode}',
            '{zone}',
            '{zone_code}',
            '{country}',
        );

        $calling_code = '';
        if (is_ft()) {
            $calling_code = array_get($address, $prefix . 'calling_code', '');
        }

        if ($calling_code) {
            $calling_code = '+' . $calling_code;
        }

        // 三段电话拼接显示，兼容旧数据中的完整 telephone 字段
        $telephone = get_telephone(
            array_get($address, $prefix . 'telephone_1', ''),
            array_get($address, $prefix . 'telephone_2', ''),
            array_get($address, $prefix . 'telephone_3', '')
        );
        if ($telephone === '') {
            $telephone = (string)array_get($address, $prefix . 'telephone', '');
        }

        // 邮编显示为 〒xxx-xxxx
        $postcode = format_postcode((string)array_get($address, $prefix . 'postcode', ''));

        $replace = array(
            'firstname'  => (string)array_get($address, $prefix . 'firstname', ''),
            'lastname'   => (string)array_get($address, $prefix . 'lastname', ''),
            'telephone'  => '(' . $calling_code . $telephone . ')',
            'company'    => (string)array_get($address, $prefix . 'company', ''),
            'address_1'  => (string)array_get($address, $prefix . 'address_1', ''),
            'address_2'  => (string)array_get($address, $prefix . 'address_2', ''),
            'county'     => (string)array_get($address, $prefix . 'county', ''),
            'city'       => (string)array_get($address, $prefix . 'city', ''),
            'postcode'   => $postcode,
            'zone'       => (string)array_get($address, $prefix . 'zone', ''),
            'zone_code'  => (string)array_get($address, $prefix . 'zone_code', ''),
            'country'    => (string)array_get($address, $prefix . 'country', '')
        );

        $strAddress = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));
        return $strAddress;
    }
}

if (!function_exists('format_postcode')) {
    /**
     * 邮编显示为 〒xxx-xxxx
     */
    function format_postcode($postcode)
    {
        $postcode = (string)$postcode;
        if (preg_match('/^(\d{3})(\d{4})$/', $postcode, $m)) {
            return '〒' . $m[1] . '-' . $m[2];
        }
        return $postcode ? '〒' . $postcode : '';
    }
}

if (!function_exists('address_format_address')) {
    /**
     * 仅格式化地址信息（不含邮编、姓名、电话），用于地址列表分列展示。
     */
    function address_format_address($address = array(), $prefix = '')
    {
        if ($prefix) {
            $prefix .= '_';
        }

        $parts = array(
            (string)array_get($address, $prefix . 'zone', ''),
            (string)array_get($address, $prefix . 'city', ''),
            (string)array_get($address, $prefix . 'county', ''),
            (string)array_get($address, $prefix . 'address_1', '')
        );

        return trim(implode('', array_filter($parts)));
    }
}

if (!function_exists('old')) {
    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     * @throws Exception
     */
    function old($key, $default = null)
    {
        return request()->post ? array_get(request()->post, $key, $default) : $default;
    }
}

if (!function_exists('parse_filters')) {
    /**
     * @param $filter_params
     * @return array
     */
    function parse_filters($filter_params)
    {
        $filters = explode('|', $filter_params);
        return array_filter($filters, function ($filter) {
            return (int)($filter) > 0;
        });
    }
}

if (!function_exists('parse_attributes')) {
    /**
     * @param $filter_params
     * @return array
     */
    function parse_attributes($filter_params)
    {
        $filters = parse_filters($filter_params);
        $attributes = array();
        foreach ($filters as $filter) {
            $item = explode(':', $filter);
            if (count($item) == 2) {
                $attributes[$item[0]][] = $item[1];
            }
        }
        return $attributes;
    }
}

if (!function_exists('template')) {
    /**
     * @param $route
     * @return string
     * @throws Exception
     */
    function template($route)
    {
        // 移动端（手机/平板）优先使用 mobile 模板目录
        if (is_mobile_device() && is_file(DIR_TEMPLATE . "mobile/template/{$route}.twig")) {
            return "mobile/template/{$route}.twig";
        }

        if (config('config_theme') == 'default') {
            if (is_file(DIR_TEMPLATE . config('theme_default_directory') . "/template/{$route}.twig")) {
                return config('theme_default_directory') . "/template/{$route}.twig";
            }
        }

        $template = 'extension/catalog/view/theme/' . config('config_theme') . "/template/{$route}.twig";
        if (is_file(DIR_OCROOT . $template)) {
            return config('config_theme') . "/template/{$route}.twig";
        }

        $template = "extension/catalog/view/theme/default/template/{$route}.twig";
        if (is_file(DIR_OCROOT . $template)) {
            return "default/template/{$route}.twig";
        }

        if (is_file(DIR_TEMPLATE . config('config_theme') . "/template/{$route}.twig")) {
            return config('config_theme') . "/template/{$route}.twig";
        }
        return "default/template/{$route}.twig";
    }
}

if (!function_exists('get_calling_codes')) {
    /**
     * @return mixed
     * @throws Exception
     */
    function get_calling_codes()
    {
        return model("localisation/calling_code")->getCallingCodes();
    }
}

if (!function_exists('is_single_store')) {
    /**
     * Check if there is only one store (default store)
     * @return mixed
     * @throws Exception
     */
    function is_single_store()
    {
        return !(int)model("setting/store")->getTotalStores();
    }
}

if (!function_exists('get_name')) {
    /**
     * @param $firstName
     * @param $lastName
     * @return string
     * @throws Exception
     */
    function get_name($firstName, $lastName)
    {
        if (is_ft()) {
            return implode(' ', [$lastName, $firstName]);
        }
        return $firstName;
    }
}

if (!function_exists('is_katakana')) {
    /**
     * フリガナ校验：仅接受カタカナ，半角自动转全角，平假名/汉字返回 false
     *
     * @param string $value
     * @return string|bool 全角カタカナ字符串；非法返回 false
     */
    function is_katakana($value)
    {
        $value = (string)$value;
        $value = trim(mb_convert_kana($value, 'KV', 'UTF-8'));
        return preg_match('/^[ァ-ヶー]+$/u', $value) ? $value : false;
    }
}


if (!function_exists('is_valid_phone_segment')) {
    /**
     * 电话段校验：全角数字转半角 + 纯数字 + 长度范围
     *
     * @param string $value
     * @param int $min
     * @param int $max
     * @return string|bool 半角数字串；非法返回 false
     */
    function is_valid_phone_segment($value, $min, $max)
    {
        $value = trim(mb_convert_kana((string)$value, 'n', 'UTF-8'));
        return preg_match('/^\d{' . $min . ',' . $max . '}$/', $value) ? $value : false;
    }
}

if (!function_exists('get_telephone')) {
    /**
     * 三段电话拼接显示：0X-XXXX-XXXX
     */
    function get_telephone($t1, $t2, $t3)
    {
        $t1 = (string)$t1; $t2 = (string)$t2; $t3 = (string)$t3;
        if ($t1 === '' && $t2 === '' && $t3 === '') {
            return '';
        }
        return $t1 . '-' . $t2 . '-' . $t3;
    }
}

if (!function_exists('check_weak_password')) {
    /**
     * Check the password if weak, password role: Uppercase + Lowercase + Number
     *
     * @param string $password
     *
     * @return bool
     */
    function check_weak_password($password = '')
    {
        if (!$password) {
            return false;
        }

        $password_preg = '/^(?=.*[0-9].*)(?=.*[A-Z].*)(?=.*[a-z].*).{4,40}$/';

        return preg_match($password_preg, $password);
    }
}

if (!function_exists('seo_config_defaults')) {
    /**
     * SEO 自动填充配置的默认值（后台可修改）
     *
     * @return array
     */
    function seo_config_defaults()
    {
        return array(
            'config_seo_common_keywords'        => '通販,オンラインショップ,日用雑貨,SokuMart,くらしマート',
            'config_seo_product_title'          => '{name}の通販・価格｜{site_name}',
            'config_seo_product_description'    => '3,980円以上送料無料、最短翌日発送。{site_name}（くらしマート）公式オンラインショップ。',
            'config_seo_product_keywords'       => '',
            'config_seo_category_title'         => '{name}の人気商品・通販一覧｜{site_name}',
            'config_seo_category_description'   => '3,980円以上送料無料、最短翌日発送。{site_name}（くらしマート）公式オンラインショップ。',
            'config_seo_category_keywords'      => '',
            'config_seo_information_title'      => '{title}｜{site_name}',
            'config_seo_information_description' => '',
            'config_seo_information_keywords'   => '',
            'config_seo_manufacturer_title'      => '{name}の商品一覧・通販｜{site_name}',
            'config_seo_manufacturer_description' => '{name}の商品を{site_name}（くらしマート）公式オンラインショップで。3,980円以上送料無料、最短翌日発送。',
            'config_seo_manufacturer_keywords'   => '',
        );
    }
}

if (!function_exists('seo_config')) {
    /**
     * 读取 SEO 自动填充配置，未设置时返回默认值
     *
     * @param string $key
     * @return string
     */
    function seo_config($key)
    {
        $defaults = seo_config_defaults();
        $default = isset($defaults[$key]) ? $defaults[$key] : '';
        $value = config($key);

        return is_null($value) ? $default : (string)$value;
    }
}

if (!function_exists('seo_fill')) {
    /**
     * 替换 SEO 模板占位符，并清理重复标点
     *
     * @param string $template
     * @param array $replace
     * @return string
     */
    function seo_fill($template, $replace = array())
    {
        $template = (string)$template;

        foreach ($replace as $key => $value) {
            $template = str_replace('{' . $key . '}', (string)$value, $template);
        }

        $template = preg_replace('/[。、，,]{2,}/u', '。', $template);

        return trim($template);
    }
}
