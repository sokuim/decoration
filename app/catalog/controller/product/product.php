<?php
class ControllerProductProduct extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('product/product');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		// 通过 SEO URL 访问（无 path）时，用商品分类回填 path，保证面包屑分类层级完整
		if (!isset($this->request->get['path']) && isset($this->request->get['product_id'])) {
			$product_categories = $this->model_catalog_product->getCategories($this->request->get['product_id']);

			if ($product_categories) {
				$category_id = (int)$product_categories[0]['category_id'];
				$path_ids = array();

				while ($category_id) {
					$category_info = $this->model_catalog_category->getCategory($category_id);

					if (!$category_info) {
						break;
					}

					array_unshift($path_ids, $category_id);
					$category_id = (int)$category_info['parent_id'];
				}

				if ($path_ids) {
					$this->request->get['path'] = implode('_', $path_ids);
				}
			}
		}

		if (isset($this->request->get['path'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_category_all'),
				'href' => $this->url->link('product/category_list')
			);

			$path = '';

			$parts = explode('_', (string)$this->request->get['path']);

			$category_id = (int)array_pop($parts);

			foreach ($parts as $path_id) {
				if (!$path) {
					$path = $path_id;
				} else {
					$path .= '_' . $path_id;
				}

				$category_info = $this->model_catalog_category->getCategory($path_id);

				if ($category_info) {
					$data['breadcrumbs'][] = array(
						'text' => $category_info['name'],
						'href' => $this->url->link('product/category', 'path=' . $path)
					);
				}
			}

			// Set the last category breadcrumb
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$url = '';

				if (isset($this->request->get['sort'])) {
					$url .= '&sort=' . $this->request->get['sort'];
				}

				if (isset($this->request->get['order'])) {
					$url .= '&order=' . $this->request->get['order'];
				}

				if (isset($this->request->get['page'])) {
					$url .= '&page=' . $this->request->get['page'];
				}

				if (isset($this->request->get['limit'])) {
					$url .= '&limit=' . $this->request->get['limit'];
				}

				$data['breadcrumbs'][] = array(
					'text' => $category_info['name'],
					'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url)
				);
			}
		}

		$this->load->model('catalog/manufacturer');

		if (isset($this->request->get['manufacturer_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_brand'),
				'href' => $this->url->link('product/manufacturer')
			);

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($this->request->get['manufacturer_id']);

			if ($manufacturer_info) {
				$data['breadcrumbs'][] = array(
					'text' => $manufacturer_info['name'],
					'href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url)
				);
			}
		}

		if (isset($this->request->get['search']) || isset($this->request->get['tag'])) {
			$url = '';

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_search'),
				'href' => $this->url->link('product/search', $url)
			);
		}

		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info) {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $product_info['name'],
				'href' => $this->url->link('product/product', $url . '&product_id=' . $this->request->get['product_id'])
			);

			$category_name = (isset($category_info) && $category_info) ? html_entity_decode($category_info['name'], ENT_QUOTES, 'UTF-8') : '';

			$site_name = $this->config->get('config_name');
			$product_name = html_entity_decode($product_info['name'], ENT_QUOTES, 'UTF-8');

			$seo_replace = array(
				'name'      => $product_name,
				'category'  => $category_name,
				'brand'     => $product_info['manufacturer'] ? html_entity_decode($product_info['manufacturer'], ENT_QUOTES, 'UTF-8') : '',
				'model'     => $product_info['model'],
				'site_name' => $site_name
			);

			// 标题：后台未填写或仅重复商品名时，使用后台配置的标题模板
			$meta_title = trim(html_entity_decode($product_info['meta_title'], ENT_QUOTES, 'UTF-8'));

			if ($meta_title === '' || $meta_title === $product_name) {
				$meta_title = seo_fill(seo_config('config_seo_product_title'), $seo_replace);

				if ($meta_title === '') {
					$meta_title = $product_name . '｜' . $site_name;
				}
			} elseif (mb_strpos($meta_title, $site_name) === false) {
				$meta_title .= '｜' . $site_name;
			}

			// 描述：后台未填写时，从商品详情自动摘要 + 后台配置的补充文案
			$meta_description = trim(html_entity_decode($product_info['meta_description'], ENT_QUOTES, 'UTF-8'));

			if ($meta_description === '') {
				$plain_description = trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8'))));

				if ($plain_description !== '') {
					$meta_description = rtrim(utf8_substr($plain_description, 0, 110), '。.!！') . '。';
				} else {
					$meta_description = $product_name . 'を' . $site_name . '（くらしマート）公式オンラインショップでご購入いただけます。';
				}

				$meta_description .= seo_fill(seo_config('config_seo_product_description'), $seo_replace);
			}

			// 关键词：后台未填写时，由商品名、分类、品牌、标签与后台配置的关键词组合
			$meta_keywords = trim(html_entity_decode($product_info['meta_keyword'], ENT_QUOTES, 'UTF-8'));

			if ($meta_keywords === '') {
				$keyword_terms = array($product_name, $category_name, $product_info['manufacturer']);

				foreach (explode(',', (string)$product_info['tag']) as $tag) {
					$keyword_terms[] = trim($tag);
				}

				$keyword_terms[] = seo_fill(seo_config('config_seo_product_keywords'), $seo_replace);
				$keyword_terms[] = seo_fill(seo_config('config_seo_common_keywords'), $seo_replace);

				$meta_keywords = implode(',', array_unique(array_filter(array_map('trim', explode(',', implode(',', $keyword_terms))))));
			}

			$product_url = $this->url->link('product/product', 'product_id=' . $this->request->get['product_id']);

			$this->document->setTitle($meta_title);
			$this->document->setDescription($meta_description);
			$this->document->setKeywords($meta_keywords);
			$this->document->setType('product');
			$this->document->setImage($product_info['image']);
			$this->document->setPrice($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')));
			$this->document->addLink($product_url, 'canonical');
			$this->document->addScript('catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js');
			$this->document->addStyle('catalog/view/javascript/jquery/magnific/magnific-popup.css');
			$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
			$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
			$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
			$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');
			$this->document->addScript('catalog/view/javascript/jquery/elevatezoom/jquery.elevateZoom.min.js');
			$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
			$this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.min.js');
			$this->document->addStyle('catalog/view/javascript/jquery/fancybox/jquery.fancybox.css');
			$this->document->addScript('catalog/view/javascript/jquery/fancybox/jquery.fancybox.pack.js');

			$data['heading_title'] = $product_info['name'];

			$data['text_minimum'] = sprintf($this->language->get('text_minimum'), $product_info['minimum']);
			$data['text_login'] = sprintf($this->language->get('text_review_login'), $this->url->link('account/login'), $this->url->link('account/register'));

			$this->load->model('catalog/review');

			$data['tab_review'] = sprintf($this->language->get('tab_review'), $product_info['reviews']);

			$data['product_id'] = (int)$this->request->get['product_id'];
			$data['manufacturer'] = $product_info['manufacturer'];
			$data['manufacturers'] = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id']);
			$data['model'] = $product_info['model'];
			$data['reward'] = $product_info['reward'];
			$data['points'] = $product_info['points'];
			$description = $this->addImageDimensions(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8'));

			// 移动端描述图补充 alt / loading / decoding
			if (is_mobile_device()) {
				$description = $this->addMobileImageAttributes($description, $product_info['name']);
			}

			$data['description'] = $description;

			// 系统设置：发货与送达 / 返品・交換（未设置时前台不显示）
			$shipping_info = trim((string)$this->config->get('config_shipping_info'));
			$data['shipping_info'] = $shipping_info !== '' ? nl2br(htmlspecialchars($shipping_info, ENT_QUOTES, 'UTF-8')) : '';

			$return_info = trim((string)$this->config->get('config_return_info'));
			$data['return_info'] = $return_info !== '' ? nl2br(htmlspecialchars($return_info, ENT_QUOTES, 'UTF-8')) : '';

			if ($product_info['quantity'] <= 0) {
				$data['stock'] = $product_info['stock_status'];
			} elseif ($this->config->get('config_stock_display')) {
				$data['stock'] = $product_info['quantity'];
			} else {
				$data['stock'] = $this->language->get('text_instock');
			}

			$preview_width = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_preview_width') ?: 400;
			$preview_height = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_preview_height') ?: 400;

			$thumb_width = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width') ?: 100;
			$thumb_height = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height') ?: 100;

			$data['preview_width'] = $preview_width;
			$data['preview_height'] = $preview_height;

			$this->load->model('tool/image');
			$data['images'] = array();

			if ($product_info['image']) {
				$data['images'][] = array(
					'thumb' => $this->model_tool_image->resize($product_info['image'], $thumb_width, $thumb_height),
					'thumb_width' => $thumb_width,
					'thumb_height' => $thumb_height,
					'preview' => $this->model_tool_image->resize($product_info['image'], $preview_width, $preview_height),
					'popup' => $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height')),
				);
			}

			$results = $this->model_catalog_product->getProductImages($this->request->get['product_id']);

			foreach ($results as $result) {
				$data['images'][] = array(
					'thumb' => $this->model_tool_image->resize($result['image'], $thumb_width, $thumb_height),
					'thumb_width' => $thumb_width,
					'thumb_height' => $thumb_height,
					'preview' => $this->model_tool_image->resize($result['image'], $preview_width, $preview_height),
					'popup' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height')),
				);
			}

			// 结构化数据：Product
			$structured_price = (float)((float)$product_info['special'] > 0 ? $product_info['special'] : $product_info['price']);
			$structured_price = $this->tax->calculate($structured_price, $product_info['tax_class_id'], $this->config->get('config_tax'));
			$structured_price = ($this->config->get('config_currency') == 'JPY') ? (string)round($structured_price) : number_format($structured_price, 2, '.', '');

			$structured_images = array();

			foreach ($data['images'] as $image) {
				$structured_images[] = $image['popup'];
			}

			$structured_description = trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8'))));

			if ($structured_description === '') {
				$structured_description = $meta_description;
			} else {
				$structured_description = utf8_substr($structured_description, 0, 300);
			}

			$product_structured_data = array(
				'@context'    => 'https://schema.org',
				'@type'       => 'Product',
				'name'        => $product_name,
				'url'         => html_entity_decode($product_url, ENT_QUOTES, 'UTF-8'),
				'description' => $structured_description,
				'sku'         => $product_info['model'],
				'mpn'         => $product_info['model'],
				'brand'       => array(
					'@type' => 'Brand',
					'name'  => $product_info['manufacturer'] ? html_entity_decode($product_info['manufacturer'], ENT_QUOTES, 'UTF-8') : $site_name
				),
				'offers'      => array(
					'@type'           => 'Offer',
					'url'             => html_entity_decode($product_url, ENT_QUOTES, 'UTF-8'),
					'priceCurrency'   => $this->config->get('config_currency'),
					'price'           => $structured_price,
					'priceValidUntil' => date('Y-m-d', strtotime('+1 year')),
					'availability'    => $product_info['quantity'] > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
					'itemCondition'   => 'https://schema.org/NewCondition',
					'seller'          => array('@type' => 'Organization', 'name' => $site_name)
				)
			);

			if ($structured_images) {
				$product_structured_data['image'] = $structured_images;
			}

			if ($this->config->get('config_review_status') && (int)$product_info['reviews'] > 0 && (float)$product_info['rating'] > 0) {
				$product_structured_data['aggregateRating'] = array(
					'@type'       => 'AggregateRating',
					'ratingValue' => (string)round((float)$product_info['rating'], 1),
					'reviewCount' => (int)$product_info['reviews']
				);
			}

			$this->document->addStructuredData($product_structured_data);

			// 结构化数据：BreadcrumbList
			$breadcrumb_structured_data = array(
				'@context'        => 'https://schema.org',
				'@type'           => 'BreadcrumbList',
				'itemListElement' => array()
			);

			$position = 1;

			foreach ($data['breadcrumbs'] as $breadcrumb) {
				$breadcrumb_structured_data['itemListElement'][] = array(
					'@type'    => 'ListItem',
					'position' => $position++,
					'name'     => html_entity_decode($breadcrumb['text'], ENT_QUOTES, 'UTF-8'),
					'item'     => html_entity_decode($breadcrumb['href'], ENT_QUOTES, 'UTF-8')
				);
			}

			$this->document->addStructuredData($breadcrumb_structured_data);

			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$data['price'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$data['price'] = false;
			}

			if ((float)$product_info['special']) {
				$data['special'] = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$data['special'] = false;
			}

			// 数量 × 单价合计（供前端 JS 计算）
			$data['price_value'] = (float)$this->tax->calculate((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax'));
			$data['currency_value'] = $this->currency->getValue($this->session->data['currency']);
			$data['currency_symbol_left'] = $this->currency->getSymbolLeft($this->session->data['currency']);
			$data['currency_symbol_right'] = $this->currency->getSymbolRight($this->session->data['currency']);
			$data['currency_decimal_place'] = $this->currency->getDecimalPlace($this->session->data['currency']);
			$data['thousand_point'] = $this->language->get('thousand_point');
			$data['decimal_point'] = $this->language->get('decimal_point');

			if ($this->config->get('config_tax')) {
				$data['tax'] = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
			} else {
				$data['tax'] = false;
			}

			$discounts = $this->model_catalog_product->getProductDiscounts($this->request->get['product_id']);

			$data['discounts'] = array();

			foreach ($discounts as $discount) {
				$data['discounts'][] = array(
					'quantity' => $discount['quantity'],
					'price'    => $this->currency->format($this->tax->calculate($discount['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'])
				);
			}

			$data['options'] = array();

			foreach ($this->model_catalog_product->getProductOptions($this->request->get['product_id']) as $option) {
				$product_option_value_data = array();

				foreach ($option['product_option_value'] as $option_value) {
					if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
						if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
							$price = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);
						} else {
							$price = false;
						}

						$product_option_value_data[] = array(
							'product_option_value_id' => $option_value['product_option_value_id'],
							'option_value_id'         => $option_value['option_value_id'],
							'name'                    => $option_value['name'],
							'image'                   => $this->model_tool_image->resize($option_value['image'], 50, 50, false),
							'price'                   => $price,
							'price_prefix'            => $option_value['price_prefix']
						);
					}
				}

				$data['options'][] = array(
					'product_option_id'    => $option['product_option_id'],
					'product_option_value' => $product_option_value_data,
					'option_id'            => $option['option_id'],
					'name'                 => $option['name'],
					'type'                 => $option['type'],
					'value'                => $option['value'],
					'required'             => $option['required']
				);
			}

			if ($product_info['minimum']) {
				$data['minimum'] = $product_info['minimum'];
			} else {
				$data['minimum'] = 1;
			}

			// 可购买数量上限：减库存且有库存时按库存数量限制
			$data['maximum'] = 0;

			if ($product_info['subtract'] && $product_info['quantity'] > 0) {
				$data['maximum'] = (int)$product_info['quantity'];
			}

			// 商品颜色（未设置时前台不显示）
			$data['color'] = trim((string)$product_info['color']);

			// 材料（未设置时前台不显示）
			$data['material'] = trim((string)$product_info['material']);

			// 附件（未设置时前台不显示）
			$data['accessory'] = trim((string)$product_info['accessory']);

			// 表圈（未设置时前台不显示）
			$data['bezel'] = trim((string)$product_info['bezel']);

			// 表带（未设置时前台不显示）
			$data['band'] = trim((string)$product_info['band']);

			// 防水（未设置时前台不显示）
			$data['waterproof'] = trim((string)$product_info['waterproof']);

			// 功能（未设置时前台不显示）
			$data['feature'] = trim((string)$product_info['feature']);

			// 五金（未设置时前台不显示）
			$data['hardware'] = trim((string)$product_info['hardware']);

			// 动力（未设置时前台不显示）
			$data['movement'] = trim((string)$product_info['movement']);

			// 规格描述（纯文本，保留换行；未设置时前台不显示）
			$specification = trim((string)$product_info['specification']);
			$data['specification'] = $specification !== '' ? nl2br(htmlspecialchars($specification, ENT_QUOTES, 'UTF-8')) : '';

			// 尺寸：仅显示后台「尺寸」字段（不再使用长宽高）
			$data['size'] = trim((string)$product_info['size']);

			// 重量：文本字段，未设置（或数值为 0）时不显示
			$weight = trim((string)$product_info['weight']);
			$data['weight_value'] = ($weight !== '' && (!is_numeric($weight) || (float)$weight > 0)) ? $weight : '';

			$data['review_status'] = $this->config->get('config_review_status');

			if ($this->config->get('config_review_guest') || $this->customer->isLogged()) {
				$data['review_guest'] = true;
			} else {
				$data['review_guest'] = false;
			}

			if ($this->customer->isLogged()) {
				$data['customer_name'] = get_name($this->customer->getFirstName(), $this->customer->getLastName());
			} else {
				$data['customer_name'] = '';
			}

			$data['reviews'] = sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']);
			$data['rating'] = (int)$product_info['rating'];

			// Captcha
			if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
				$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'));
			} else {
				$data['captcha'] = '';
			}

			$data['share'] = $this->url->link('product/product', 'product_id=' . (int)$this->request->get['product_id']);

			$data['attribute_groups'] = $this->model_catalog_product->getProductAttributes($this->request->get['product_id']);

			$data['products'] = array();

			$results = $this->model_catalog_product->getProductRelated($this->request->get['product_id']);

			foreach ($results as $result) {
				$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}

				$data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => $rating,
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				);
			}

			$data['tags'] = array();

			if ($product_info['tag']) {
				$tags = explode(',', $product_info['tag']);

				foreach ($tags as $tag) {
					$data['tags'][] = array(
						'tag'  => trim($tag),
						'href' => $this->url->link('product/search', 'tag=' . trim($tag))
					);
				}
			}

			$data['recurrings'] = $this->model_catalog_product->getProfiles($this->request->get['product_id']);

			$this->model_catalog_product->updateViewed($this->request->get['product_id']);

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('product/product', $data));
		} else {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('product/product', $url . '&product_id=' . $product_id)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}

	public function review() {
		$this->load->language('product/product');

		$this->load->model('catalog/review');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['reviews'] = array();

		$review_total = $this->model_catalog_review->getTotalReviewsByProductId($this->request->get['product_id']);

		$results = $this->model_catalog_review->getReviewsByProductId($this->request->get['product_id'], ($page - 1) * 5, 5);

		foreach ($results as $result) {
			$data['reviews'][] = array(
				'author'     => $result['author'],
				'text'       => nl2br($result['text']),
				'rating'     => (int)$result['rating'],
				'date_added' => date($this->language->get('datetime_format'), strtotime($result['date_added']))
			);
		}

		$pagination = new Pagination();
		$pagination->total = $review_total;
		$pagination->page = $page;
		$pagination->limit = 5;
		$pagination->url = $this->url->link('product/product/review', 'product_id=' . $this->request->get['product_id'] . '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($review_total) ? (($page - 1) * 5) + 1 : 0, ((($page - 1) * 5) > ($review_total - 5)) ? $review_total : ((($page - 1) * 5) + 5), $review_total, ceil($review_total / 5));

		$this->response->setOutput($this->load->view('product/review', $data));
	}

	public function write() {
		$this->load->language('product/product');

		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if ((utf8_strlen($this->request->post['name']) < 2) || (utf8_strlen($this->request->post['name']) > 25)) {
				$json['error'] = $this->language->get('error_name');
			}

			if ((utf8_strlen($this->request->post['text']) < 5) || (utf8_strlen($this->request->post['text']) > 1000)) {
				$json['error'] = $this->language->get('error_text');
			}

			if (empty($this->request->post['rating']) || $this->request->post['rating'] < 0 || $this->request->post['rating'] > 5) {
				$json['error'] = $this->language->get('error_rating');
			}

			// Captcha
			if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
				$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

				if ($captcha) {
					$json['error'] = $captcha;
				}
			}

			if (!isset($json['error'])) {
				$this->load->model('catalog/review');

				$this->model_catalog_review->addReview($this->request->get['product_id'], $this->request->post);

				$json['success'] = $this->language->get('text_success');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getRecurringDescription() {
		$this->load->language('product/product');
		$this->load->model('catalog/product');

		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		if (isset($this->request->post['recurring_id'])) {
			$recurring_id = $this->request->post['recurring_id'];
		} else {
			$recurring_id = 0;
		}

		if (isset($this->request->post['quantity'])) {
			$quantity = $this->request->post['quantity'];
		} else {
			$quantity = 1;
		}

		$product_info = $this->model_catalog_product->getProduct($product_id);

		$recurring_info = $this->model_catalog_product->getProfile($product_id, $recurring_id);

		$json = array();

		if ($product_info && $recurring_info) {
			if (!$json) {
				$frequencies = array(
					'day'        => $this->language->get('text_day'),
					'week'       => $this->language->get('text_week'),
					'semi_month' => $this->language->get('text_semi_month'),
					'month'      => $this->language->get('text_month'),
					'year'       => $this->language->get('text_year'),
				);

				if ($recurring_info['trial_status'] == 1) {
					$price = $this->currency->format($this->tax->calculate($recurring_info['trial_price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$trial_text = sprintf($this->language->get('text_trial_description'), $price, $recurring_info['trial_cycle'], $frequencies[$recurring_info['trial_frequency']], $recurring_info['trial_duration']) . ' ';
				} else {
					$trial_text = '';
				}

				$price = $this->currency->format($this->tax->calculate($recurring_info['price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);

				if ($recurring_info['duration']) {
					$text = $trial_text . sprintf($this->language->get('text_payment_description'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
				} else {
					$text = $trial_text . sprintf($this->language->get('text_payment_cancel'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
				}

				$json['success'] = $text;
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	// 为移动端商品描述中的 <img> 补充 alt、loading="lazy"、decoding="async"
	private function addMobileImageAttributes($html, $alt_text) {
		if (strpos($html, '<img') === false) {
			return $html;
		}

		$alt = htmlspecialchars((string)$alt_text, ENT_QUOTES, 'UTF-8');

		return preg_replace_callback('/<img\b[^>]*>/i', function ($matches) use ($alt) {
			$tag = $matches[0];
			$attributes = '';

			if (!preg_match('/\balt\s*=/i', $tag)) {
				$attributes .= ' alt="' . $alt . '"';
			} elseif (preg_match('/\balt\s*=\s*(["\'])\s*\1/i', $tag)) {
				$tag = preg_replace_callback('/\balt\s*=\s*(["\'])\s*\1/i', function () use ($alt) {
					return 'alt="' . $alt . '"';
				}, $tag, 1);
			}

			if (!preg_match('/\bloading\s*=/i', $tag)) {
				$attributes .= ' loading="lazy"';
			}

			if (!preg_match('/\bdecoding\s*=/i', $tag)) {
				$attributes .= ' decoding="async"';
			}

			if ($attributes !== '') {
				$tag = preg_replace('/<img\b/i', '<img' . $attributes, $tag, 1);
			}

			return $tag;
		}, $html);
	}

	// 为商品描述中缺少 width/height 的 <img> 补充本地图片的真实尺寸
	private function addImageDimensions($html) {
		if (strpos($html, '<img') === false) {
			return $html;
		}

		return preg_replace_callback('/<img\b[^>]*>/i', function ($matches) {
			$tag = $matches[0];

			if (preg_match('/\bwidth\s*=/i', $tag) && preg_match('/\bheight\s*=/i', $tag)) {
				return $tag;
			}

			if (!preg_match('/\bsrc\s*=\s*["\']([^"\']+)["\']/i', $tag, $src)) {
				return $tag;
			}

			$path = parse_url(html_entity_decode($src[1], ENT_QUOTES, 'UTF-8'), PHP_URL_PATH);

			if (!$path) {
				return $tag;
			}

			$file = DIR_IMAGE . ltrim(preg_replace('#^/image/#', '', $path), '/');

			if (!is_file($file)) {
				return $tag;
			}

			$size = @getimagesize($file);

			if (!$size) {
				return $tag;
			}

			$attributes = '';

			if (!preg_match('/\bwidth\s*=/i', $tag)) {
				$attributes .= ' width="' . (int)$size[0] . '"';
			}

			if (!preg_match('/\bheight\s*=/i', $tag)) {
				$attributes .= ' height="' . (int)$size[1] . '"';
			}

			return preg_replace('/<img\b/i', '<img' . $attributes, $tag, 1);
		}, $html);
	}
}
