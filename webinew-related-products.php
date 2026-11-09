<?php
/**
 * Plugin Name: Webinew Related Products
 * Description: Save-time Persian product recommendations with editorial overrides and an accessible sidebar slider.
 * Version: 1.1.0
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * Author: احسان قسیمی | شرکت وبینیو
 * Author URI: https://webinew.com/
 * Plugin URI: https://github.com/EhsanGhasimi/webinew-related-products
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * License: GPL-2.0-or-later
 * Text Domain: webinew-related-products
 */
defined('ABSPATH') || exit;
require_once __DIR__ . '/matcher.php';

final class WRP_Plugin {
    public static function boot() {
        add_action('add_meta_boxes_post', [__CLASS__, 'boxes']);
        add_action('save_post_post', [__CLASS__, 'save'], 30, 2);
        add_shortcode('webinew_related_products', [__CLASS__, 'render']);
        // Compatibility with installations before the public Webinew release.
        add_shortcode('behzist_related_products', [__CLASS__, 'render']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'assets']);
        add_action('admin_notices', function () {
            if (!function_exists('wc_get_products') && current_user_can('activate_plugins')) {
                echo '<div class="notice notice-warning"><p>افزونه محصولات مرتبط به ووکامرس فعال نیاز دارد.</p></div>';
            }
        });
    }
    public static function assets() {
        // Registered styles must be enqueued before wp_head, including Elementor templates.
        if (is_singular('post')) {
            wp_enqueue_style('wrp', plugins_url('assets/slider.css', __FILE__), [], '1.1.0');
            wp_enqueue_script('wrp', plugins_url('assets/slider.js', __FILE__), [], '1.1.0', true);
        }
    }
    public static function boxes() {
        add_meta_box('wrp-products', 'محصولات مرتبط مقاله — وبینیو', [__CLASS__, 'box'], 'post', 'normal', 'default');
    }
    public static function box($post) {
        wp_nonce_field('wrp_save_' . $post->ID, 'wrp_nonce');
        $mode = get_post_meta($post->ID, '_bzap_mode', true);
        $ids = (array) get_post_meta($post->ID, '_bzap_ids', true);
        $suggested = (array) get_post_meta($post->ID, '_bzap_suggestions', true);
        echo '<div dir="rtl"><p><strong>توسعه‌دهنده: احسان قسیمی | شرکت <a href="https://webinew.com/" target="_blank" rel="noopener noreferrer">وبینیو</a></strong><br>طراحی سایت، توسعه وردپرس و سئو — راهکارهای وبینیو برای کسب‌وکارهای آنلاین.</p><p>پیشنهاد خودکار: حداکثر ۷ محصول. اگر ارتباط کافی پیدا نشود تعداد کمتر خواهد بود. برای افزودن، حذف یا جابه‌جایی، حالت دستی را انتخاب کنید و شناسه‌ها را به ترتیب وارد کنید.</p>';
        echo '<p><label>روش انتخاب <select name="wrp_mode"><option value="auto" ' . selected($mode, 'auto', false) . '>خودکار</option><option value="manual" ' . selected($mode, 'manual', false) . '>دستی (حفظ انتخاب من)</option></select></label></p>';
        echo '<p><label for="wrp_ids">شناسه محصولات، جداشده با ویرگول (حداکثر ۲۰ مورد)</label><br><input class="widefat" id="wrp_ids" name="wrp_ids" dir="ltr" value="' . esc_attr(implode(', ', array_filter($ids))) . '"></p>';
        echo '<p><label><input type="checkbox" name="wrp_rescan" value="1"> محاسبه مجدد پیشنهادها هنگام ذخیره (در حالت دستی، انتخاب شما حفظ می‌شود)</label></p>';
        echo '<p>پیشنهادهای آخر: <code dir="ltr">' . esc_html(implode(', ', array_filter($suggested))) . '</code></p><ul>';
        foreach (array_filter($ids) as $id) {
            $product = function_exists('wc_get_product') ? wc_get_product($id) : false;
            if ($product) {
                echo '<li>' . esc_html($id . ' — ' . $product->get_name()) . ' <a href="' . esc_url(get_edit_post_link($id)) . '">ویرایش محصول</a></li>';
            }
        }
        echo '</ul><p>نمایش در ابزارک شورت‌کد یا ابزارک Shortcode المنتور در سایدبار: <code>[webinew_related_products]</code></p><p>پس از ذخیره، ویرایشگر را دوباره باز کنید تا فهرست جدید را ببینید. محصولات مخفی، حذف‌شده و ناموجود در اسلایدر نمایش داده نمی‌شوند.</p></div>';
    }
    public static function parse_ids($raw) {
        $raw = strtr($raw, array_combine(preg_split('//u', '۰۱۲۳۴۵۶۷۸۹٠١٢٣٤٥٦٧٨٩', -1, PREG_SPLIT_NO_EMPTY), str_split('01234567890123456789')));
        return array_slice(array_values(array_unique(array_filter(array_map('absint', preg_split('/[\s,،;]+/u', $raw))))), 0, 20);
    }
    public static function eligible($p) {
        return $p && $p->get_status() === 'publish' && $p->is_visible() && $p->is_in_stock() && !$p->is_type('variation');
    }
    public static function save($id, $post) {
        if (!function_exists('wc_get_products') || wp_is_post_revision($id) || wp_is_post_autosave($id) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || in_array($post->post_status, ['auto-draft', 'trash'], true)) return;
        // Background imports/REST saves may generate recommendations, but may not edit editorial controls.
        $form = isset($_POST['wrp_nonce']) && is_string($_POST['wrp_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wrp_nonce'])), 'wrp_save_' . $id) && current_user_can('edit_post', $id);
        $mode = get_post_meta($id, '_bzap_mode', true) === 'manual' ? 'manual' : 'auto';
        if ($form) {
            $mode = isset($_POST['wrp_mode']) && $_POST['wrp_mode'] === 'manual' ? 'manual' : 'auto';
            update_post_meta($id, '_bzap_mode', $mode);
            if ($mode === 'manual' && isset($_POST['wrp_ids']) && is_string($_POST['wrp_ids'])) {
                $ids = self::parse_ids(wp_unslash($_POST['wrp_ids']));
                $ids = array_values(array_filter($ids, function ($pid) {
                    $p = wc_get_product($pid);
                    return $p && !$p->is_type('variation') && $p->get_status() === 'publish';
                }));
                update_post_meta($id, '_bzap_ids', $ids);
            }
        }
        $hash = hash('sha256', 'v1|' . $post->post_title . '|' . $post->post_content . '|' . $post->post_excerpt);
        $force = $form && !empty($_POST['wrp_rescan']);
        $changed = get_post_meta($id, '_bzap_hash', true) !== $hash;
        if (($mode === 'auto' && $changed) || $force || !metadata_exists('post', $id, '_bzap_suggestions')) {
            $ids = self::recommend($post);
            update_post_meta($id, '_bzap_suggestions', $ids);
            update_post_meta($id, '_bzap_hash', $hash);
            update_post_meta($id, '_bzap_scanned_at', current_time('mysql'));
        }
        if ($mode === 'auto') update_post_meta($id, '_bzap_ids', (array) get_post_meta($id, '_bzap_suggestions', true));
    }
    public static function recommend($post) {
        $docs = [];
        $page = 1;
        do {
            $products = wc_get_products(['status' => 'publish', 'type' => ['simple', 'variable', 'grouped', 'external'], 'limit' => 100, 'page' => $page++, 'orderby' => 'ID', 'order' => 'ASC']);
            foreach ($products as $p) {
                if (!self::eligible($p)) continue;
                $terms = wp_get_post_terms($p->get_id(), ['product_cat', 'product_tag'], ['fields' => 'names']);
                $docs[$p->get_id()] = WRP_Matcher::tokens($p->get_name() . ' ' . (is_wp_error($terms) ? '' : implode(' ', $terms)) . ' ' . $p->get_short_description());
            }
        } while (count($products) === 100);
        return WRP_Matcher::rank($post->post_title, $post->post_content . ' ' . $post->post_excerpt, $docs, 7);
    }
    public static function render($atts = []) {
        if (!function_exists('wc_get_product') || !is_singular('post') || post_password_required(get_queried_object_id())) return '';
        $atts = shortcode_atts(['title' => 'محصولات مرتبط با این مقاله'], $atts, 'webinew_related_products');
        $ids = array_slice(array_filter((array) get_post_meta(get_queried_object_id(), '_bzap_ids', true)), 0, 20);
        $cards = [];
        foreach ($ids as $id) {
            $p = wc_get_product(absint($id));
            if (!self::eligible($p)) continue;
            ob_start(); ?>
            <article class="wrp-card">
                <a class="wrp-product" href="<?php echo esc_url($p->get_permalink()); ?>"><?php echo wp_kses_post($p->get_image('woocommerce_thumbnail', ['loading' => 'lazy'])); ?><h3><?php echo esc_html($p->get_name()); ?></h3></a>
                <div class="wrp-price"><?php echo $p->get_price_html() !== '' ? wp_kses_post($p->get_price_html()) : 'برای قیمت تماس بگیرید'; ?></div>
                <?php if ($p->is_type('simple') && $p->is_purchasable()) : ?>
                <a class="wrp-buy" rel="nofollow" href="<?php echo esc_url($p->add_to_cart_url()); ?>" aria-label="<?php echo esc_attr('افزودن به سبد خرید: ' . $p->get_name()); ?>">افزودن به سبد خرید</a>
                <?php else : ?><a class="wrp-buy" href="<?php echo esc_url($p->get_permalink()); ?>">مشاهده و خرید</a><?php endif; ?>
            </article>
            <?php $cards[] = ob_get_clean();
        }
        if (!$cards) return '';
        $label = wp_unique_id('wrp-title-');
        $html = '<section class="wrp" dir="rtl" aria-labelledby="' . esc_attr($label) . '"><h2 id="' . esc_attr($label) . '">' . esc_html($atts['title']) . '</h2><div class="wrp-track">' . implode('', $cards) . '</div>';
        if (count($cards) > 1) {
            $html .= '<div class="wrp-controls" hidden><button type="button" data-step="-1" aria-label="محصول قبلی">قبلی</button><button type="button" data-autoplay>توقف</button><span class="wrp-status" aria-live="off"></span><button type="button" data-step="1" aria-label="محصول بعدی">بعدی</button></div>';
        }
        return $html . '</section>';
    }
}
WRP_Plugin::boot();
