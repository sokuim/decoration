<?php
class ControllerStartupStartup extends Controller {
	public function index() {
		// Store
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "store` WHERE REPLACE(`url`, 'www.', '') = '" . $this->db->escape(($this->request->server['HTTPS'] ? 'https://' : 'http://') . str_replace('www.', '', $_SERVER['HTTP_HOST']) . rtrim(dirname($_SERVER['PHP_SELF']), '/.\\') . '/') . "'");

		if (isset($this->request->get['store_id'])) {
			$this->config->set('config_store_id', (int)$this->request->get['store_id']);
		} else if ($query->num_rows) {
			$this->config->set('config_store_id', $query->row['store_id']);
		} else {
			$this->config->set('config_store_id', 0);
		}

		if (!$query->num_rows) {
			$this->config->set('config_url', HTTP_SERVER);
		}

		// Settings
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE store_id = '0' OR store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY store_id ASC");

		foreach ($query->rows as $result) {
			if (!$result['serialized']) {
				$this->config->set($result['key'], $result['value']);
			} else {
				$this->config->set($result['key'], json_decode($result['value'], true));
			}
		}

		// Theme
		$this->config->set('template_cache', $this->config->get('developer_theme'));

		// Url
		$this->registry->set('url', new Url($this->config->get('config_url')));

		// Language
		$code = '';

		$this->load->model('localisation/language');

		$languages = $this->model_localisation_language->getLanguages();

		$language_codes = array_column($languages, 'language_id', 'code');

		// 硬编码前台语言为 ja-jp（去掉多语言，不可切换）
		$code = 'ja-jp';

		if (!array_key_exists($code, $language_codes)) {
			$code = $this->config->get('config_language');
		}

		if (!isset($this->session->data['language']) || $this->session->data['language'] != $code) {
			$this->session->data['language'] = $code;
		}

		if (!isset($this->request->cookie['language']) || $this->request->cookie['language'] != $code) {
			setcookie('language', $code, time() + 60 * 60 * 24 * 30, '/', $this->request->server['HTTP_HOST']);
		}

		// Overwrite the default language object
		$language = new Language($code);
		$language->load($code);

		$this->registry->set('language', $language);

		// Set the config language_id
		$this->config->set('config_language_id', $language_codes[$code]);

		// Customer
		$customer = new Cart\Customer($this->registry);
		$this->registry->set('customer', $customer);

		// Customer Group
		if (isset($this->session->data['customer']) && isset($this->session->data['customer']['customer_group_id'])) {
			// For API calls
			$this->config->set('config_customer_group_id', $this->session->data['customer']['customer_group_id']);
		} elseif ($this->customer->isLogged()) {
			// Logged in customers
			$this->config->set('config_customer_group_id', $this->customer->getGroupId());
		} elseif (isset($this->session->data['guest']) && isset($this->session->data['guest']['customer_group_id'])) {
			$this->config->set('config_customer_group_id', $this->session->data['guest']['customer_group_id']);
		}

		// Currency
		$code = '';

		$this->load->model('localisation/currency');

		$currencies = $this->model_localisation_currency->getCurrencies();

		// 硬编码前台货币为 JPY（去掉多货币，不可切换）
		$code = 'JPY';

		if (!isset($this->session->data['currency']) || $this->session->data['currency'] != $code) {
			$this->session->data['currency'] = $code;
		}

		if (!isset($this->request->cookie['currency']) || $this->request->cookie['currency'] != $code) {
			setcookie('currency', $code, time() + 60 * 60 * 24 * 30, '/', $this->request->server['HTTP_HOST']);
		}

		$this->registry->set('currency', new Cart\Currency($this->registry));

		// Tax
		$this->registry->set('tax', new Cart\Tax($this->registry));

		if (!empty($this->session->data['shipping_address']) && is_array($this->session->data['shipping_address'])) {
			$this->tax->setShippingAddress(array_get($this->session->data['shipping_address'], 'country_id', $this->config->get('config_country_id')), array_get($this->session->data['shipping_address'], 'zone_id', $this->config->get('config_zone_id')));
		} elseif ($this->config->get('config_tax_default') == 'shipping') {
			$this->tax->setShippingAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));
		}

		// 取消 payment_address 后，付款税率改用配送地址
		$this->tax->setPaymentAddress(array_get($this->session->data, 'shipping_address.country_id', $this->config->get('config_country_id')), array_get($this->session->data, 'shipping_address.zone_id', $this->config->get('config_zone_id')));

		$this->tax->setStoreAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));

		// Weight
		$this->registry->set('weight', new Cart\Weight($this->registry));

		// Length
		$this->registry->set('length', new Cart\Length($this->registry));

		// Cart
		$this->registry->set('cart', new Cart\Cart($this->registry));

		// Encryption
		$this->registry->set('encryption', new Encryption());
	}
}
