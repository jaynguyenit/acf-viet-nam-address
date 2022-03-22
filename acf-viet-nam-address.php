<?php
/*
Plugin Name: Advanced Custom Fields: Viet Nam Address
Plugin URI: https://levantoan.com/
Description: Thêm lựa chọn tỉnh/thành phố; quận/huyện; xã/phường/thị trấn vào ACF(Addvanced Custom Field)
Version: 1.0.0
Author: Le Van Toan
Author URI: https://levantoan.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


// check if class already exists
if( !class_exists('acf_plugin_viet_nam_address') ) :

class acf_plugin_viet_nam_address {

	function __construct() {
		
		// vars
		$this->settings = array(
			'version'	=> '1.0.0',
			'url'		=> plugin_dir_url( __FILE__ ),
			'path'		=> plugin_dir_path( __FILE__ )
		);

		load_plugin_textdomain( 'acf-viet-nam-address', false, plugin_basename( dirname( __FILE__ ) ) . '/lang' );
		
		// include field
		add_action('acf/include_field_types', 	array($this, 'include_field_types')); // v5
		add_action('acf/register_fields', 		array($this, 'include_field_types')); // v4

        add_action( 'wp_ajax_acf_load_diagioihanhchinh', array($this, 'acf_load_diagioihanhchinh_func') );
		
	}
	
	function include_field_types( $version = false ) {
		
		// support empty $version
		if( !$version ) $version = 4;
		
		// include
		include_once('fields/acf-viet-nam-address-v' . $version . '.php');
		
	}

	function get_all_cities(){
        include 'cities/tinh_thanhpho.php';
        return $tinh_thanhpho;
    }

    function get_all_district(){
        include 'cities/quan_huyen.php';
        return $quan_huyen;
    }

    function get_all_village(){
        include 'cities/xa_phuong_thitran.php';
        return $xa_phuong_thitran;
    }

    function get_list_district($matp = ''){
        if(!$matp) return false;
        $quan_huyen = $this->get_all_district();
        $matp = $matp;
        $result = $this->search_in_array($quan_huyen,'matp',$matp);
        return $result;
    }

    function get_list_village($maqh = ''){
        if(!$maqh) return false;
        $xa_phuong_thitran = $this->get_all_village();
        $id_xa = sprintf("%05d", intval($maqh));
        $result = $this->search_in_array($xa_phuong_thitran,'maqh',$id_xa);
        return $result;
    }


    function search_in_array($array, $key, $value){
        $results = array();

        if (is_array($array)) {
            if (isset($array[$key]) && $array[$key] && $array[$key] == $value) {
                $results[] = $array;
            }elseif(isset($array[$key]) && is_serialized($array[$key]) && in_array($value,maybe_unserialize($array[$key]))){
                $results[] = $array;
            }
            foreach ($array as $subarray) {
                $results = array_merge($results, $this->search_in_array($subarray, $key, $value));
            }
        }

        return $results;
    }

    function acf_load_diagioihanhchinh_func() {
        if ( !wp_verify_nonce( $_REQUEST['nonce'], "acf_vn_nonce")) {
            wp_send_json_error('hack');
        }
        $matp = wp_unslash($_POST['matp']);
        $maqh = intval($_POST['maqh']);
        if($matp){
            $result = $this->get_list_district($matp);
            wp_send_json_success($result);
        }
        if($maqh){
            $result = $this->get_list_village($maqh);
            wp_send_json_success($result);
        }
        wp_send_json_error();
        die();
    }

    function get_name_city($id = ''){
        $tinh_thanhpho = $this->get_all_cities();
        $id_tinh = sprintf("%02d", intval($id));
        $tinh_thanhpho = (isset($tinh_thanhpho[$id_tinh]))?$tinh_thanhpho[$id_tinh]:'';
        return $tinh_thanhpho;
    }

    function get_name_district($id = ''){
        $quan_huyen = $this->get_all_district();
        $id_quan = sprintf("%03d", intval($id));
        if(is_array($quan_huyen) && !empty($quan_huyen)){
            $nameQuan = $this->search_in_array($quan_huyen,'maqh',$id_quan);
            $nameQuan = isset($nameQuan[0]['name'])?$nameQuan[0]['name']:'';
            return $nameQuan;
        }
        return false;
    }

    function get_name_village($id = ''){
        $xa_phuong_thitran = $this->get_all_village();
        $id_xa = sprintf("%05d", intval($id));
        if(is_array($xa_phuong_thitran) && !empty($xa_phuong_thitran)){
            $name = $this->search_in_array($xa_phuong_thitran,'xaid',$id_xa);
            $name = isset($name[0]['name'])?$name[0]['name']:'';
            return $name;
        }
        return false;
    }
}

// initialize
new acf_plugin_viet_nam_address();

// class_exists check
endif;
?>