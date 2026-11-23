<?php
class ControllerStartupStartup extends Controller {
	public function index() {
		// Settings
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '0'");
		
		foreach ($query->rows as $setting) {
			if (!$setting['serialized']) {
				$this->config->set($setting['key'], $setting['value']);
			} else {
				$this->config->set($setting['key'], json_decode($setting['value'], true));
			}
		}

		// Theme
		$this->config->set('template_cache', $this->config->get('developer_theme'));
				
		// 数据语言固定为日语（商品/分类等 description 只存日语）
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE code = 'ja-jp'");
		
		if ($query->num_rows) {
			$this->config->set('config_language_id', $query->row['language_id']);
		}
		
		// 后台界面语言固定为中文
		$language = new Language('zh-cn');
		$language->load('zh-cn');
		$this->registry->set('language', $language);
		
		// Customer
		$this->registry->set('customer', new Cart\Customer($this->registry));

		// Currency
		$this->registry->set('currency', new Cart\Currency($this->registry));
	
		// Tax
		$this->registry->set('tax', new Cart\Tax($this->registry));
		
		if ($this->config->get('config_tax_default') == 'shipping') {
			$this->tax->setShippingAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));
		}

		if ($this->config->get('config_tax_default') == 'payment') {
			$this->tax->setPaymentAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));
		}

		$this->tax->setStoreAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));

		// Weight
		$this->registry->set('weight', new Cart\Weight($this->registry));
		
		// Length
		$this->registry->set('length', new Cart\Length($this->registry));
		
		// Cart
		$this->registry->set('cart', new Cart\Cart($this->registry));
		
		// Encryption
		$this->registry->set('encryption', new Encryption($this->config->get('config_encryption')));
	}
}