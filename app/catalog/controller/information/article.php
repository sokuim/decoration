<?php
class ControllerInformationArticle extends Controller {
	public function index() {
		$this->load->language('information/article');

		$this->load->model('catalog/article');

		$site_name = $this->config->get('config_name');

		if (isset($this->request->get['article_category_id'])) {
			$article_category_id = (int)$this->request->get['article_category_id'];
		} else {
			$article_category_id = 0;
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$limit = 10;

		$seo_replace = array('site_name' => $site_name);

		$heading = '';
		$meta_title = '';
		$meta_description = '';
		$meta_keywords = '';

		$category_info = array();

		if ($article_category_id) {
			$category_info = $this->model_catalog_article->getArticleCategory($article_category_id);

			if (!$category_info || !$category_info['status']) {
				$this->document->setTitle($this->language->get('text_error'));

				$data['breadcrumbs'] = array();

				$data['breadcrumbs'][] = array(
					'text' => $this->language->get('text_home'),
					'href' => $this->url->link('common/home')
				);

				$data['breadcrumbs'][] = array(
					'text' => $this->language->get('text_error'),
					'href' => $this->url->link('information/article')
				);

				$data['heading_title'] = $this->language->get('text_error');
				$data['text_error'] = $this->language->get('text_error');
				$data['continue'] = $this->url->link('common/home');

				$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

				$data['column_left'] = $this->load->controller('common/column_left');
				$data['column_right'] = $this->load->controller('common/column_right');
				$data['content_top'] = $this->load->controller('common/content_top');
				$data['content_bottom'] = $this->load->controller('common/content_bottom');
				$data['footer'] = $this->load->controller('common/footer');
				$data['header'] = $this->load->controller('common/header');

				$this->response->setOutput($this->load->view('error/not_found', $data));

				return;
			}

			$category_name = html_entity_decode($category_info['name'], ENT_QUOTES, 'UTF-8');
			$category_replace = array(
				'name'      => $category_name,
				'site_name' => $site_name
			);

			$heading = seo_fill($this->config->get('config_seo_article_category_heading'), $category_replace);
			if ($heading === '') {
				$heading = $category_name;
			}

			$meta_title = trim(html_entity_decode($category_info['meta_title'], ENT_QUOTES, 'UTF-8'));
			if ($meta_title === '' || $meta_title === $category_name) {
				$meta_title = seo_fill($this->config->get('config_seo_article_category_title'), $category_replace);

				if ($meta_title === '') {
					$meta_title = $category_name . '｜' . $site_name;
				}
			} elseif (mb_strpos($meta_title, $site_name) === false) {
				$meta_title .= '｜' . $site_name;
			}

			$meta_description = trim(html_entity_decode($category_info['meta_description'], ENT_QUOTES, 'UTF-8'));
			if ($meta_description === '') {
				$plain_category_description = trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8'))));

				if ($plain_category_description !== '') {
					$meta_description = rtrim(utf8_substr($plain_category_description, 0, 120), '。.!！') . '。';
				}

				$meta_description .= seo_fill($this->config->get('config_seo_article_category_description'), $category_replace);
			}

			$meta_keywords = trim(html_entity_decode($category_info['meta_keyword'], ENT_QUOTES, 'UTF-8'));
			if ($meta_keywords === '') {
				$keyword_terms = array(
					$category_name,
					seo_fill($this->config->get('config_seo_article_category_keywords'), $category_replace),
					seo_fill($this->config->get('config_seo_common_keywords'), $category_replace)
				);

				$meta_keywords = implode(',', array_unique(array_filter(array_map('trim', explode(',', implode(',', $keyword_terms))))));
			}

			// 分类简介与图片
			$data['category_description'] = html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8');
			$data['category_image'] = '';

			if (!empty($category_info['image']) && is_file(DIR_IMAGE . $category_info['image'])) {
				$this->load->model('tool/image');

				$data['category_image'] = $this->model_tool_image->resize($category_info['image'], 1200, 480);
			}
		} else {
			$heading = seo_fill($this->config->get('config_seo_article_list_heading'), $seo_replace);
			$meta_title = seo_fill($this->config->get('config_seo_article_list_title'), $seo_replace);
			$meta_description = seo_fill($this->config->get('config_seo_article_list_description'), $seo_replace);
			$meta_keywords = seo_fill($this->config->get('config_seo_article_list_keywords'), $seo_replace);

			$data['category_description'] = '';
			$data['category_image'] = '';
		}

		$data['article_category_id'] = $article_category_id;

		if ($heading === '') {
			$heading = $this->language->get('heading_title');
		}

		if ($meta_title === '' || $meta_title === $heading) {
			$meta_title = $heading . '｜' . $site_name;
		} elseif (mb_strpos($meta_title, $site_name) === false) {
			$meta_title .= '｜' . $site_name;
		}

		if ($meta_description === '') {
			$meta_description = $heading . '。' . $site_name . '（くらしマート）公式オンラインショップ。';
		}

		if ($meta_keywords === '') {
			$meta_keywords = $heading;
		}

		$this->document->setTitle($meta_title);
		$this->document->setDescription($meta_description);
		$this->document->setKeywords($meta_keywords);

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		if ($article_category_id && $category_info) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('information/article')
			);

			$data['breadcrumbs'][] = array(
				'text' => $heading,
				'href' => $this->url->link('information/article', 'article_category_id=' . $article_category_id)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $heading,
				'href' => $this->url->link('information/article')
			);
		}

		$data['heading_title'] = $heading;

		// 文章分类
		$data['categories'] = array();

		$this->load->model('tool/image');

		$results = $this->model_catalog_article->getArticleCategories();

		foreach ($results as $result) {
			$description = trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))));

			if (utf8_strlen($description) > 80) {
				$description = utf8_substr($description, 0, 80) . '…';
			}

			$thumb = '';

			if (!empty($result['image']) && is_file(DIR_IMAGE . $result['image'])) {
				$thumb = $this->model_tool_image->resize($result['image'], 600, 340);
			}

			$data['categories'][] = array(
				'article_category_id' => $result['article_category_id'],
				'name'                => $result['name'],
				'description'         => $description,
				'thumb'               => $thumb,
				'href'                => $this->url->link('information/article', 'article_category_id=' . $result['article_category_id'])
			);
		}

		// 文章列表
		$data['articles'] = array();

		$filter_data = array(
			'filter_article_category_id' => $article_category_id,
			'start'                      => ($page - 1) * $limit,
			'limit'                      => $limit
		);

		$article_total = $this->model_catalog_article->getTotalArticles($filter_data);

		$results = $this->model_catalog_article->getArticles($filter_data);

		foreach ($results as $result) {
			$description = strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'));
			$description = trim(preg_replace('/\s+/u', ' ', $description));

			$data['articles'][] = array(
				'article_id'  => $result['article_id'],
				'title'       => $result['title'],
				'description' => utf8_substr($description, 0, 90) . '…',
				'date_added'  => date('Y.m.d', strtotime($result['date_added'])),
				'href'        => $this->url->link('information/article/info', 'article_id=' . $result['article_id'])
			);
		}

		$url = '';

		if ($article_category_id) {
			$url .= '&article_category_id=' . $article_category_id;
		}

		$pagination = new Pagination();
		$pagination->total = $article_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('information/article', $url . '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($article_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($article_total - $limit)) ? $article_total : ((($page - 1) * $limit) + $limit), $article_total, ceil($article_total / $limit));

		$this->document->addLink($this->url->link('information/article', ($article_category_id ? 'article_category_id=' . $article_category_id : '') . ($page > 1 ? ($article_category_id ? '&' : '') . 'page=' . $page : '')), 'canonical');

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('information/article_list', $data));
	}

	public function info() {
		$this->load->language('information/article');

		$this->load->model('catalog/article');

		if (isset($this->request->get['article_id'])) {
			$article_id = (int)$this->request->get['article_id'];
		} else {
			$article_id = 0;
		}

		$article_info = $this->model_catalog_article->getArticle($article_id);

		if ($article_info) {
			$site_name = $this->config->get('config_name');
			$article_title = html_entity_decode($article_info['title'], ENT_QUOTES, 'UTF-8');

			$seo_replace = array(
				'title'     => $article_title,
				'site_name' => $site_name
			);

			$meta_title = trim(html_entity_decode($article_info['meta_title'], ENT_QUOTES, 'UTF-8'));

			if ($meta_title === '' || $meta_title === $article_title) {
				$meta_title = seo_fill(seo_config('config_seo_information_title'), $seo_replace);

				if ($meta_title === '') {
					$meta_title = $article_title . '｜' . $site_name;
				}
			} elseif (mb_strpos($meta_title, $site_name) === false) {
				$meta_title .= '｜' . $site_name;
			}

			$meta_description = trim(html_entity_decode($article_info['meta_description'], ENT_QUOTES, 'UTF-8'));

			if ($meta_description === '') {
				$plain_description = trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode($article_info['description'], ENT_QUOTES, 'UTF-8'))));

				if ($plain_description !== '') {
					$meta_description = rtrim(utf8_substr($plain_description, 0, 120), '。.!！') . '。';
				}

				$meta_description .= seo_fill(seo_config('config_seo_information_description'), $seo_replace);
			}

			$meta_keywords = trim(html_entity_decode($article_info['meta_keyword'], ENT_QUOTES, 'UTF-8'));

			if ($meta_keywords === '') {
				$keyword_terms = array(
					$article_title,
					seo_fill(seo_config('config_seo_information_keywords'), $seo_replace),
					seo_fill(seo_config('config_seo_common_keywords'), $seo_replace)
				);

				$meta_keywords = implode(',', array_unique(array_filter(array_map('trim', explode(',', implode(',', $keyword_terms))))));
			}

			$article_url = $this->url->link('information/article/info', 'article_id=' . $article_id);

			$this->document->setTitle($meta_title);
			$this->document->setDescription($meta_description);
			$this->document->setKeywords($meta_keywords);
			$this->document->addLink($article_url, 'canonical');

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('information/article')
			);

			// 结构化数据：Article
			$this->document->addStructuredData(array(
				'@context'      => 'https://schema.org',
				'@type'         => 'Article',
				'headline'      => $article_title,
				'description'   => $meta_description,
				'url'           => html_entity_decode($article_url, ENT_QUOTES, 'UTF-8'),
				'inLanguage'    => 'ja-JP',
				'datePublished' => date('c', strtotime($article_info['date_added'])),
				'dateModified'  => date('c', strtotime($article_info['date_modified'])),
				'isPartOf'      => array(
					'@type' => 'WebSite',
					'name'  => $site_name,
					'url'   => html_entity_decode($this->config->get('config_url'), ENT_QUOTES, 'UTF-8')
				)
			));

			// 结构化数据：BreadcrumbList
			$breadcrumb_items = array();
			$position = 1;

			foreach ($data['breadcrumbs'] as $breadcrumb) {
				$breadcrumb_items[] = array(
					'@type'    => 'ListItem',
					'position' => $position++,
					'name'     => html_entity_decode($breadcrumb['text'], ENT_QUOTES, 'UTF-8'),
					'item'     => html_entity_decode($breadcrumb['href'], ENT_QUOTES, 'UTF-8')
				);
			}

			$breadcrumb_items[] = array(
				'@type'    => 'ListItem',
				'position' => $position++,
				'name'     => $article_title,
				'item'     => html_entity_decode($article_url, ENT_QUOTES, 'UTF-8')
			);

			$this->document->addStructuredData(array(
				'@context'        => 'https://schema.org',
				'@type'           => 'BreadcrumbList',
				'itemListElement' => $breadcrumb_items
			));

			$data['breadcrumbs'][] = array(
				'text' => $article_info['title'],
				'href' => $article_url
			);

			$data['heading_title'] = $article_info['title'];
			$data['description'] = html_entity_decode($article_info['description'], ENT_QUOTES, 'UTF-8');
			$data['date_added'] = date('Y.m.d', strtotime($article_info['date_added']));
			$data['continue'] = $this->url->link('information/article');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('information/article_detail', $data));
		} else {
			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('information/article/info', 'article_id=' . $article_id)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

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
}
