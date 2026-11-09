<?php
define('ABSPATH', __DIR__);
function add_action(...$a) {} function add_shortcode(...$a) {}
function absint($v) {return abs((int)$v);}
function wp_is_post_revision($v) {return false;} function wp_is_post_autosave($v) {return false;}
function get_post_meta($id,$key,$single=true) {return $GLOBALS['meta'][$id][$key] ?? '';}
function update_post_meta($id,$key,$v) {$GLOBALS['meta'][$id][$key]=$v;}
function metadata_exists($t,$id,$key) {return isset($GLOBALS['meta'][$id][$key]);}
function current_time($v) {return '2026-11-09';}
function wp_verify_nonce($n,$a) {return $n==='valid';} function current_user_can(...$a) {return true;}
function sanitize_text_field($s) {return $s;} function wp_unslash($s) {return $s;}
function is_wp_error($v) {return false;} function wp_get_post_terms(...$a) {return [];}
function wc_get_products($a) {$GLOBALS['queries']++; return array_values($GLOBALS['products']);}
function wc_get_product($id) {return $GLOBALS['products'][$id] ?? false;}
class Product {
    private $id,$name;
    function __construct($id,$name){$this->id=$id;$this->name=$name;}
    function get_id(){return $this->id;} function get_name(){return $this->name;}
    function get_status(){return 'publish';} function is_visible(){return true;}
    function is_in_stock(){return true;} function is_type($t){return $t==='simple';}
    function get_short_description(){return '';}
}
require dirname(__DIR__).'/webinew-related-products.php';
function check($v,$message){if(!$v)throw new Exception($message);echo "PASS $message\n";}
$GLOBALS['products']=[1=>new Product(1,'گان جراحی استریل'),2=>new Product(2,'شان جراحی یکبار مصرف'),3=>new Product(3,'بوت طبی پا'),4=>new Product(4,'فشارسنج دیجیتال')];
$GLOBALS['queries']=0;
$post=(object)['post_title'=>'بهترین تولید کننده گان و شان جراحی','post_content'=>'گان جراحی و شان استریل برای اتاق عمل','post_excerpt'=>'','post_status'=>'publish'];
WRP_Plugin::save(10,$post);
check(count(get_post_meta(10,'_bzap_ids'))===2,'Relevant gown/drape only; no unrelated boot');
check($GLOBALS['queries']===1,'One catalog scan on first save');
WRP_Plugin::save(10,$post);
check($GLOBALS['queries']===1,'Unchanged content skips catalog scan');
$_POST=['wrp_nonce'=>'valid','wrp_mode'=>'manual','wrp_ids'=>'۳، 1,3,999'];
WRP_Plugin::save(10,$post);
check(get_post_meta(10,'_bzap_ids')===[3,1],'Persian IDs parsed, duplicates and nonexistent IDs removed; order retained');
$post->post_content='فشارسنج دیجیتال';
WRP_Plugin::save(10,$post);
check(get_post_meta(10,'_bzap_ids')===[3,1] && $GLOBALS['queries']===1,'Manual selection survives content update without scan');
$_POST['wrp_rescan']=1;
WRP_Plugin::save(10,$post);
check(get_post_meta(10,'_bzap_ids')===[3,1] && $GLOBALS['queries']===2,'Forced suggestions refresh preserves manual IDs');
$_POST=['wrp_nonce'=>'invalid','wrp_mode'=>'auto','wrp_ids'=>'2'];
WRP_Plugin::save(10,$post);
check(get_post_meta(10,'_bzap_mode')==='manual','Invalid nonce cannot change editorial controls');
check(WRP_Matcher::tokens('شان جراحي كتان')===WRP_Matcher::tokens('شان جراحی کتان'),'Arabic/Persian normalization');
check(WRP_Matcher::rank('موضوع بیربط','متن متفاوت',[1=>WRP_Matcher::tokens('گان استریل')])===[],'No arbitrary fallback');
