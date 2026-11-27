<?php

class ModelAccountProductViewed extends Model
{
    const MAX_ITEMS = 30;

    /**
     * 记录已登录客户浏览的商品
     *
     * @param int $customer_id
     * @param int $product_id
     */
    public function addView($customer_id, $product_id)
    {
        $customer_id = (int)$customer_id;
        $product_id = (int)$product_id;

        if (!$customer_id || !$product_id) {
            return;
        }

        $this->db->query("INSERT INTO `" . DB_PREFIX . "customer_product_viewed` SET customer_id = '" . $customer_id . "', product_id = '" . $product_id . "', date_viewed = NOW() ON DUPLICATE KEY UPDATE date_viewed = NOW()");

        $this->prune($customer_id);
    }

    /**
     * 合并访客浏览历史（ids 最新在前）
     *
     * @param int $customer_id
     * @param array $ids
     */
    public function merge($customer_id, array $ids)
    {
        $customer_id = (int)$customer_id;

        if (!$customer_id || !$ids) {
            return;
        }

        $ids = $this->filterAvailableIds($ids);

        if (!$ids) {
            return;
        }

        $values = array();

        foreach ($ids as $index => $product_id) {
            $values[] = "('" . $customer_id . "', '" . (int)$product_id . "', DATE_SUB(NOW(), INTERVAL " . (int)$index . " SECOND))";
        }

        $this->db->query("INSERT INTO `" . DB_PREFIX . "customer_product_viewed` (`customer_id`, `product_id`, `date_viewed`) VALUES " . implode(',', $values) . " ON DUPLICATE KEY UPDATE `date_viewed` = GREATEST(`date_viewed`, VALUES(`date_viewed`))");

        $this->prune($customer_id);
    }

    /**
     * 获取客户最近浏览的商品 ID（最新在前）
     *
     * @param int $customer_id
     * @param int $limit
     * @return array
     */
    public function getViewedIds($customer_id, $limit = 30)
    {
        $customer_id = (int)$customer_id;
        $limit = (int)$limit;

        if (!$customer_id) {
            return array();
        }

        if ($limit <= 0 || $limit > self::MAX_ITEMS) {
            $limit = self::MAX_ITEMS;
        }

        $query = $this->db->query("SELECT product_id FROM `" . DB_PREFIX . "customer_product_viewed` WHERE customer_id = '" . $customer_id . "' ORDER BY date_viewed DESC, product_id DESC LIMIT " . $limit);

        return array_map('intval', array_column($query->rows, 'product_id'));
    }

    /**
     * 按传入顺序返回仍可购买的商品数据
     *
     * @param array $ids
     * @return array
     */
    public function getProductsByIds(array $ids)
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        if (!$ids) {
            return array();
        }

        $sql = "SELECT p.product_id, p.image, p.price, p.tax_class_id, p.minimum, pd.name, pd.description, pd.intro, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.product_id IN (" . implode(',', $ids) . ") AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

        $query = $this->db->query($sql);

        $rows = array();

        foreach ($query->rows as $row) {
            $row['price'] = $row['discount'] ? $row['discount'] : $row['price'];
            $rows[$row['product_id']] = $row;
        }

        $products = array();

        foreach ($ids as $product_id) {
            if (isset($rows[$product_id])) {
                $products[] = $rows[$product_id];
            }
        }

        return $products;
    }

    /**
     * 过滤出仍然上架可购买的商品 ID，保留原顺序
     *
     * @param array $ids
     * @return array
     */
    protected function filterAvailableIds(array $ids)
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        if (!$ids) {
            return array();
        }

        $query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.product_id IN (" . implode(',', $ids) . ") AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

        $available = array_map('intval', array_column($query->rows, 'product_id'));

        $result = array();

        foreach ($ids as $product_id) {
            if (in_array($product_id, $available, true)) {
                $result[] = $product_id;
            }
        }

        return $result;
    }

    /**
     * 每个客户仅保留最近 30 条
     *
     * @param int $customer_id
     */
    protected function prune($customer_id)
    {
        $customer_id = (int)$customer_id;

        $this->db->query("DELETE FROM `" . DB_PREFIX . "customer_product_viewed` WHERE customer_id = '" . $customer_id . "' AND product_id NOT IN (SELECT product_id FROM (SELECT product_id FROM `" . DB_PREFIX . "customer_product_viewed` WHERE customer_id = '" . $customer_id . "' ORDER BY date_viewed DESC, product_id DESC LIMIT " . self::MAX_ITEMS . ") AS keep)");
    }
}
