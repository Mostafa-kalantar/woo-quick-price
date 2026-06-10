<?php

class WQP_AdminPage
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_enqueue_scripts', [$this, 'assets']);
    }

    public function assets()
    {
        wp_enqueue_script(
                'wqp-js',
                WQP_URL . 'assets/admin.js',
                ['jquery'],
                null,
                true
        );

        wp_enqueue_style(
                'select2',
                'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css'
        );

        wp_enqueue_script(
                'select2',
                'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
                ['jquery'],
                null,
                true
        );

        wp_enqueue_style('wqp-admin', WQP_URL . 'assets/admin.css');

        wp_localize_script('wqp-js', 'wqp', [
                'nonce' => wp_create_nonce('wqp_nonce')
        ]);
    }

    public function menu()
    {
        add_menu_page(
                'آپدیت سریع قیمت',
                'آپدیت سریع قیمت',
                'manage_options',
                'wqp',
                [$this, 'page'],
                'dashicons-update',
                56
        );
    }

    public function page()
    {
        $paged = isset($_GET['paged']) ? (int)$_GET['paged'] : 1;
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $category = isset($_GET['cat']) ? sanitize_text_field($_GET['cat']) : '';

        $args = [
                'post_type' => 'product',
                'post_status' => 'publish',
                'posts_per_page' => 20,
                'paged' => $paged,
                's' => $search,
        ];

        if (!empty($category)) {
            $args['tax_query'] = [
                    [
                            'taxonomy' => 'product_cat',
                            'field' => 'slug',
                            'terms' => $category,
                    ]
            ];
        }

        $query = new WP_Query($args);

        $categories = get_terms([
                'taxonomy' => 'product_cat',
                'hide_empty' => false
        ]);

        ?>
        <div class="wrap">
            <h1>Quick Price Manager</h1>

            <form method="get">
                <input type="hidden" name="page" value="wqp">

                <input type="text" name="s" placeholder="Search product..." value="<?php echo esc_attr($search); ?>">

                <select name="cat" class="wqp-category-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat->slug; ?>" <?php selected($category, $cat->slug); ?>>
                            <?php echo $cat->name; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button>فیلتر</button>
            </form>

            <table class="widefat striped">
                <thead>
                <tr>
                    <th>محصول</th>
                    <th>قیمت اصلی</th>
                    <th>قیمت فروش</th>
                    <th>موجودی انبار</th>
                    <th>عملیات</th>
                </tr>
                </thead>

                <tbody>

                <?php while ($query->have_posts()): $query->the_post();

                    $product = wc_get_product(get_the_ID());
                    if (!$product) continue;
                    ?>

                    <!-- ===================== -->
                    <!-- PRODUCT ROW (اول محصول) -->
                    <!-- ===================== -->
                    <tr class="product-row <?php echo $product->is_type('variable') ? 'is-variable' : ''; ?>"
                        data-id="<?php echo $product->get_id(); ?>">

                        <td>
                            <a href="<?php echo esc_url(get_permalink($product->get_id())); ?>" target="_blank">
                                <?php echo esc_html($product->get_name()); ?>
                            </a>
                        </td>

                        <td>
                            <?php if ($product->is_type('variable')): ?>
                                <span style="color:#999;"></span>
                            <?php else: ?>
                                <input type="number" class="regular_price"
                                       value="<?php echo esc_attr($product->get_regular_price()); ?>">
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if ($product->is_type('variable')): ?>
                                <span style="color:#999;"></span>
                            <?php else: ?>
                                <input type="number" class="sale_price"
                                       value="<?php echo esc_attr($product->get_sale_price()); ?>">
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if ($product->is_type('variable')): ?>
                                <span style="color:#999;"></span>
                            <?php else: ?>
                                <input type="number" class="stock"
                                       value="<?php echo esc_attr($product->get_stock_quantity()); ?>">
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if ($product->is_type('simple')): ?>
                                <button class="wqp-save button">ذخیره</button>
                            <?php else: ?>
                                <span style="color:#999;"></span>
                            <?php endif; ?>
                        </td>

                    </tr>

                    <!-- ===================== -->
                    <!-- VARIATIONS (زیر محصول) -->
                    <!-- ===================== -->

                    <?php if ($product->is_type('variable')):

                        $variation_ids = [];

                        foreach ($product->get_children() as $variation_id) {
                            if (get_post_status($variation_id) !== 'publish') continue;
                            $variation_ids[] = $variation_id;
                        }

                        foreach ($variation_ids as $variation_id):

                            $variation = wc_get_product($variation_id);
                            if (!$variation) continue;

                            $variation_name = wc_get_formatted_variation(
                                    $variation,
                                    true,
                                    false,
                                    true
                            );
                            ?>

                            <tr class="variation-row" data-id="<?php echo $variation->get_id(); ?>">

                                <td style="padding-left:40px; color:#666;">
                                    ↳ <?php echo esc_html($variation_name); ?>
                                </td>

                                <td>
                                    <input type="number" class="regular_price"
                                           value="<?php echo esc_attr($variation->get_regular_price()); ?>">
                                </td>

                                <td>
                                    <input type="number" class="sale_price"
                                           value="<?php echo esc_attr($variation->get_sale_price()); ?>">
                                </td>

                                <td>
                                    <input type="number" class="stock"
                                           value="<?php echo esc_attr($variation->get_stock_quantity()); ?>">
                                </td>

                                <td>
                                    <button class="wqp-save button">ذخیره</button>
                                </td>

                            </tr>

                        <?php endforeach; endif; ?>

                <?php endwhile;
                wp_reset_postdata(); ?>

                </tbody>
            </table>

            <div class="pagination">

                <?php
                $total = $query->max_num_pages;
                $current = max(1, $paged);

                if ($total > 1): ?>

                    <div class="wqp-pagination">

                        <?php if ($current > 1): ?>

                            <a class="wqp-btn nav"
                               href="<?php echo esc_url(add_query_arg('paged', 1)); ?>">
                                « اول
                            </a>

                            <a class="wqp-btn nav"
                               href="<?php echo esc_url(add_query_arg('paged', $current - 1)); ?>">
                                ‹ قبلی
                            </a>

                        <?php endif; ?>

                        <?php

                        $start = max(1, $current - 2);
                        $end = min($total, $current + 2);

                        if ($start > 1) {
                            echo '<span class="wqp-dots">...</span>';
                        }

                        for ($i = $start; $i <= $end; $i++): ?>

                            <a class="wqp-btn page <?php echo $i == $current ? 'active' : ''; ?>"
                               href="<?php echo esc_url(add_query_arg('paged', $i)); ?>">
                                <?php echo $i; ?>
                            </a>

                        <?php endfor;

                        if ($end < $total) {
                            echo '<span class="wqp-dots">...</span>';
                        }
                        ?>

                        <?php if ($current < $total): ?>

                            <a class="wqp-btn nav"
                               href="<?php echo esc_url(add_query_arg('paged', $current + 1)); ?>">
                                بعدی ›
                            </a>

                            <a class="wqp-btn nav"
                               href="<?php echo esc_url(add_query_arg('paged', $total)); ?>">
                                آخر »
                            </a>

                        <?php endif; ?>

                        <span class="wqp-page-info">
                صفحه <?php echo $current; ?> از <?php echo $total; ?>
            </span>

                    </div>

                <?php endif; ?>

            </div>
        </div>
        <?php
    }
}




