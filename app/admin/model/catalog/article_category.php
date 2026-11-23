<?php
class ModelCatalogArticleCategory extends Model {
	public function addCategory($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "article_category SET name = '" . $this->db->escape($data['name']) . "', description = '" . $this->db->escape($data['description']) . "', meta_title = '" . $this->db->escape($data['meta_title']) . "', meta_description = '" . $this->db->escape($data['meta_description']) . "', meta_keyword = '" . $this->db->escape($data['meta_keyword']) . "', image = '" . $this->db->escape(isset($data['image']) ? (string)$data['image'] : '') . "', status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_added = NOW(), date_modified = NOW()");

		$article_category_id = $this->db->getLastId();

		// SEO URL
		if (isset($data['article_category_seo_url'])) {
			foreach ($data['article_category_seo_url'] as $store_id => $keyword) {
				if (!empty($keyword)) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$this->config->get('config_language_id') . "', query = 'article_category_id=" . (int)$article_category_id . "', keyword = '" . $this->db->escape($keyword) . "'");
				}
			}
		}

		return $article_category_id;
	}

	public function editCategory($article_category_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "article_category SET name = '" . $this->db->escape($data['name']) . "', description = '" . $this->db->escape($data['description']) . "', meta_title = '" . $this->db->escape($data['meta_title']) . "', meta_description = '" . $this->db->escape($data['meta_description']) . "', meta_keyword = '" . $this->db->escape($data['meta_keyword']) . "', image = '" . $this->db->escape(isset($data['image']) ? (string)$data['image'] : '') . "', status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_modified = NOW() WHERE article_category_id = '" . (int)$article_category_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'article_category_id=" . (int)$article_category_id . "'");

		if (isset($data['article_category_seo_url'])) {
			foreach ($data['article_category_seo_url'] as $store_id => $keyword) {
				if (!empty($keyword)) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$this->config->get('config_language_id') . "', query = 'article_category_id=" . (int)$article_category_id . "', keyword = '" . $this->db->escape($keyword) . "'");
				}
			}
		}
	}

	public function deleteCategory($article_category_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "article_category WHERE article_category_id = '" . (int)$article_category_id . "'");
		$this->db->query("UPDATE " . DB_PREFIX . "article SET article_category_id = '0' WHERE article_category_id = '" . (int)$article_category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'article_category_id=" . (int)$article_category_id . "'");
	}

	public function getCategory($article_category_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "article_category WHERE article_category_id = '" . (int)$article_category_id . "'");

		return $query->row;
	}

	public function getCategories($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "article_category";

		if (!empty($data['filter_name'])) {
			$sql .= " WHERE name LIKE '%" . $this->db->escape((string)$data['filter_name']) . "%'";
		}

		$sort_data = array(
			'name',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalCategories() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "article_category");

		return $query->row['total'];
	}

	public function getArticleCategorySeoUrls($article_category_id) {
		$article_category_seo_url_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'article_category_id=" . (int)$article_category_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

		foreach ($query->rows as $result) {
			$article_category_seo_url_data[$result['store_id']] = $result['keyword'];
		}

		return $article_category_seo_url_data;
	}
}
