<?php
class ControllerCatalogArticleCategory extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('catalog/article_category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/article_category');

		$this->getList();
	}

	public function add() {
		$this->load->language('catalog/article_category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/article_category');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_article_category->addCategory($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('catalog/article_category', 'user_token=' . $this->session->data['user_token']));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('catalog/article_category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/article_category');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_article_category->editCategory($this->request->get['article_category_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('catalog/article_category', 'user_token=' . $this->session->data['user_token']));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('catalog/article_category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/article_category');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $article_category_id) {
				$this->model_catalog_article_category->deleteCategory($article_category_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('catalog/article_category', 'user_token=' . $this->session->data['user_token']));
		}

		$this->getList();
	}

	protected function getList() {
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/article_category', 'user_token=' . $this->session->data['user_token'])
		);

		$data['add'] = $this->url->link('catalog/article_category/add', 'user_token=' . $this->session->data['user_token']);
		$data['delete'] = $this->url->link('catalog/article_category/delete', 'user_token=' . $this->session->data['user_token']);

		$data['categories'] = array();

		$results = $this->model_catalog_article_category->getCategories();

		foreach ($results as $result) {
			$data['categories'][] = array(
				'article_category_id' => $result['article_category_id'],
				'name'                => $result['name'],
				'sort_order'          => $result['sort_order'],
				'status'              => $result['status'],
				'edit'                => $this->url->link('catalog/article_category/edit', 'user_token=' . $this->session->data['user_token'] . '&article_category_id=' . $result['article_category_id'])
			);
		}

		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$data['success'] = isset($this->session->data['success']) ? $this->session->data['success'] : '';

		if (isset($this->session->data['success'])) {
			unset($this->session->data['success']);
		}

		$data['selected'] = isset($this->request->post['selected']) ? (array)$this->request->post['selected'] : array();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/article_category_list', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['article_category_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$data['error_name'] = isset($this->error['name']) ? $this->error['name'] : '';
		$data['error_keyword'] = isset($this->error['keyword']) ? $this->error['keyword'] : array();

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/article_category', 'user_token=' . $this->session->data['user_token'])
		);

		if (!isset($this->request->get['article_category_id'])) {
			$data['action'] = $this->url->link('catalog/article_category/add', 'user_token=' . $this->session->data['user_token']);
		} else {
			$data['action'] = $this->url->link('catalog/article_category/edit', 'user_token=' . $this->session->data['user_token'] . '&article_category_id=' . $this->request->get['article_category_id']);
		}

		$data['cancel'] = $this->url->link('catalog/article_category', 'user_token=' . $this->session->data['user_token']);

		$data['user_token'] = $this->session->data['user_token'];

		$category_info = array();

		if (isset($this->request->get['article_category_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$category_info = $this->model_catalog_article_category->getCategory($this->request->get['article_category_id']);
		}

		foreach (array('name', 'description', 'meta_title', 'meta_description', 'meta_keyword') as $field) {
			if (isset($this->request->post[$field])) {
				$data[$field] = $this->request->post[$field];
			} elseif (!empty($category_info)) {
				$data[$field] = $category_info[$field];
			} else {
				$data[$field] = '';
			}
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($category_info)) {
			$data['status'] = $category_info['status'];
		} else {
			$data['status'] = 1;
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($category_info)) {
			$data['sort_order'] = $category_info['sort_order'];
		} else {
			$data['sort_order'] = 0;
		}

		$this->load->model('setting/store');

		$data['stores'] = array();

		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);

		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}

		if (isset($this->request->post['article_category_seo_url'])) {
			$data['article_category_seo_url'] = $this->request->post['article_category_seo_url'];
		} elseif (isset($this->request->get['article_category_id'])) {
			$data['article_category_seo_url'] = $this->model_catalog_article_category->getArticleCategorySeoUrls($this->request->get['article_category_id']);
		} else {
			$data['article_category_seo_url'] = array();
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/article_category_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/article_category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 1) || (utf8_strlen($this->request->post['name']) > 255)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if (isset($this->request->post['article_category_seo_url'])) {
			$this->load->model('design/seo_url');

			foreach ($this->request->post['article_category_seo_url'] as $store_id => $keyword) {
				if (!empty($keyword)) {
					$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);

					foreach ($seo_urls as $seo_url) {
						if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['article_category_id']) || ($seo_url['query'] != 'article_category_id=' . $this->request->get['article_category_id']))) {
							$this->error['keyword'][$store_id] = $this->language->get('error_keyword');
						}
					}
				}
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/article_category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
