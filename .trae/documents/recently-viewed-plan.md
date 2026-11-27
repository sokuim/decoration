# 最近浏览（最近見たアイテム）功能实现计划

## Context

当前项目没有按用户记录的浏览历史，只有商品全局浏览计数 `oc_product.viewed`。需要新增一个前台「最近見たアイテム」模块，并在后台可配置（标题、显示数量、尺寸、状态），由管理员在「布局管理」中自行放置。

关键要求：
- 未登录访客也能记录浏览（存储于 cookie，30 天），登录后自动并入账号历史并清空访客记录。
- 每个用户/访客最多保留最近 30 个商品，模块显示数量可配置（默认 6）。
- 卡片使用标准商品卡片（图片 + 名称 + 价格 + 购物车/收藏/对比），与「最新商品」模块一致。
- 仅默认主题（desktop）需要模块模板。

已确认：
- MySQL 5.7.42（支持 `ON DUPLICATE KEY`、JSON 函数）。
- `oc_customer_history` 已被客户备注占用，新表使用 `oc_customer_product_viewed`。
- 不预置布局位置，由用户在后台布局管理中拖放。

## 实现步骤

### 1. 数据表

```sql
CREATE TABLE IF NOT EXISTS `oc_customer_product_viewed` (
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `date_viewed` datetime NOT NULL,
  PRIMARY KEY (`customer_id`,`product_id`),
  KEY `idx_customer_date` (`customer_id`,`date_viewed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

### 2. 全局 cookie 帮助函数

文件：`system/helper/functions.php`（全局加载）
- `recently_viewed_get_ids()`：读取原始 `$_COOKIE['recently_viewed']`（避免 `Request` 的 htmlspecialchars），值用 `.` 分隔（cookie 值不能含逗号），过滤非数字、去重、截取前 30，最新在前。
- `recently_viewed_add_id($product_id)`：插入到最前、去重、截断 30，`setcookie('recently_viewed', implode('.', $ids), time()+2592000, '/')`。
- `recently_viewed_clear()`：用相同 path 过期清除 cookie。
- `recently_viewed_merge($customer_id)`：读取 cookie ids → 调用模型合并 → 清 cookie（无数据时不发头）。

### 3. 历史模型

新建 `catalog/model/account/product_viewed.php`：
- `addView($customer_id, $product_id)`：`INSERT ... ON DUPLICATE KEY UPDATE date_viewed = NOW()`，随后把该用户记录裁剪到 30 条（派生表规避 MySQL 1093）。
- `merge($customer_id, array $ids)`：过滤仍上架商品后按“最新在前”批量写入，时间用 `DATE_SUB(NOW(), INTERVAL n SECOND)` 错开，重复商品更新为较新时间，末尾裁剪 30 条。
- `getViewedIds($customer_id, $limit)`：按 `date_viewed DESC` 返回 product_id。
- `getProductsByIds(array $ids)`：join `product_description`（当前语言）、`product_to_store`，条件 `status=1 AND date_available<=NOW()`，按传入 ids 顺序返回商品行（供模块装配）。

### 4. 埋点记录浏览

`catalog/controller/product/product.php`：在 `if ($product_info)` 内、`updateViewed()` 附近（约 L689）记录一次：
```php
$this->load->model('account/product_viewed');
if ($this->customer->isLogged()) {
    $this->model_account_product_viewed->addView($this->customer->getId(), $product_id);
} else {
    recently_viewed_add_id($product_id);
}
```
404 商品不记录；已登录不写 cookie。

### 5. 登录后合并

在以下成功登录位置调用 `recently_viewed_merge($this->customer->getId())`（需先 `load->model('account/product_viewed')`）：
- `catalog/controller/account/login.php`：POST 成功块内、wishlist 合并之后、`addLogin` 之前。
- `catalog/controller/account/register.php`：`$this->customer->login(...)` 之后、重定向前。
- `catalog/controller/account/social.php`：`completeLogin()` 完成登录后（如使用社交登录）。
- 可选（低成本）：`affiliate/login.php`、`account/login` 的 token 自动登录分支。

不改 `Cart\Customer::login()` 核心库，避免影响所有登录入口的副作用。

### 6. 模块：`recently_viewed`（后台 + 前台）

后台（后台语言 zh-cn）：
- `admin/controller/extension/module/recently_viewed.php`：克隆 `latest` 结构，字段 `name`、`heading`（标题，默认 `最近見たアイテム`）、`limit`（1–30，默认 6）、`width`、`height`、`status`。
- `admin/view/template/extension/module/recently_viewed.twig`：同上字段表单。
- `admin/language/zh-cn/extension/module/recently_viewed.php`：`heading_title=最近浏览`、`entry_heading=标题` 等。

前台：
- `catalog/language/ja-jp/extension/module/recently_viewed.php`：`heading_title = '最近見たアイテム'`。
- `catalog/controller/extension/module/recently_viewed.php`：
  - 取 ids：登录用模型 `getViewedIds`，游客用 `recently_viewed_get_ids()`。
  - 商品页（`route=product/product`）排除当前 `product_id`；多取 1 条再按 `limit` 截断。
  - 用 `getProductsByIds` + `model_catalog_product->handleSingleProduct($row, width, height)` 生成商品数据。
  - `heading` 取模块设置（`html_entity_decode`），为空则用语言默认；无数据返回 `''`。
- `catalog/view/theme/default/template/extension/module/recently_viewed.twig`：
  - `section.product-section` + `.section-header`（有标题时）+ `ul.product-grid`，循环 `include(helper.template('_partial/product/product_card'))`。
  - 复用现有商品表格样式，无需新增 CSS。

### 7. 注册数据（不写布局）

```sql
INSERT INTO oc_extension (type, code)
SELECT 'module','recently_viewed' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM oc_extension WHERE type='module' AND code='recently_viewed');

INSERT INTO oc_module (name, code, setting) VALUES
('最近浏览','recently_viewed',
 '{"name":"最近浏览","heading":"最近見たアイテム","limit":"6","width":"300","height":"300","status":"1"}');

-- 管理员组权限（access / modify），用 PHP 追加更稳妥：
-- oc_user_group.permission JSON 的 access/modify 数组加入 'extension/module/recently_viewed'
```
完成后清一次 `system/storage/cache`（模板缓存）。

## 需要新增/修改的文件

新增：
- `catalog/model/account/product_viewed.php`
- `catalog/controller/extension/module/recently_viewed.php`
- `catalog/view/theme/default/template/extension/module/recently_viewed.twig`
- `catalog/language/ja-jp/extension/module/recently_viewed.php`
- `admin/controller/extension/module/recently_viewed.php`
- `admin/view/template/extension/module/recently_viewed.twig`
- `admin/language/zh-cn/extension/module/recently_viewed.php`

修改：
- `system/helper/functions.php`（cookie 帮助函数）
- `catalog/controller/product/product.php`（记录浏览）
- `catalog/controller/account/login.php`、`account/register.php`（登录合并）
- （可选）`catalog/controller/account/social.php`、`affiliate/login.php`

## 验证清单

1. 游客依次浏览商品 A、B、C：cookie `recently_viewed=C.B.A`，30 天有效期；浏览第 31 个后仍为 30 项。
2. 未登录时在「布局管理」把「最近浏览」模块加到任意位置，前台显示标题（默认「最近見たアイテム」）与标准商品卡片。
3. 商品详情页模块不显示当前正在浏览的商品。
4. 游客浏览 A/B/C 后登录：数据库 `oc_customer_product_viewed` 出现 3 条且顺序正确，cookie 被清空；再次登录不重复。
5. 已登录继续浏览 D：D 排在最前，重复浏览会更新时间而非新增。
6. 注册自动登录、社交登录同样触发合并。
7. 后台「扩展 → 模块」可见「最近浏览」，可编辑标题/数量/状态；用户组权限正常。
8. 下架/删除的商品不显示，也不占用 30 条额度。
9. 退出登录后以游客浏览正常（新的 cookie），重新登录再次合并。
