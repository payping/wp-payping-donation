<?php
 if ( ! defined( 'ABSPATH' ) ) exit; 

class payping_donation { 
    public function __construct() {
        add_action( 'admin_enqueue_scripts', [$this, 'callback_for_setting_up_scripts'] );
		add_action( 'admin_init', [$this, 'register_Donate_PayPing_plugin_settings'] );
        add_action('admin_menu', [$this, 'Donate_Payping_Menu']);
        add_action( 'wp_footer', [$this, 'Donate_PayPing_Script'] );
        add_action( 'wp_ajax_Donate_PayPing_ajax_request', [$this, 'Donate_PayPing_ajax_request']);
        add_action( 'wp_ajax_nopriv_Donate_PayPing_ajax_request', [$this, 'Donate_PayPing_ajax_request']);
        add_shortcode('PayPingDonate', [$this, 'Donate_PayPing_Script_shortcode']);
	}
    public function callback_for_setting_up_scripts(){
        wp_register_style( 'index-donate', DPPDU . 'assets/css/index.css' );
        wp_enqueue_style( 'index-donate' );
        wp_enqueue_script( 'donate-ajax', DPPDU . 'assets/js/donate-ajax.js', array('jquery'), null, true );
        wp_localize_script(
            'donate-ajax',
            'donate_ajax_obj',
            array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) )
        );
    }
    public function Donate_Payping_Menu(){
        add_menu_page( __( 'افزونه حمایت مالی پی‌پینگ', 'textdomain' ), __( 'حمایت مالی پی‌پینگ', 'textdomain' ), 'read', 'PayPing-Donate', [$this, 'Donate_Payping_function'], 'dashicons-chart-bar', 7 );
    }
    
    public function register_Donate_PayPing_plugin_settings(){
        register_setting( 'Donate_PayPing-plugin-settings-group', 'Script' );
        register_setting( 'Donate_PayPing-plugin-settings-group', 'Pages' );
        register_setting( 'Donate_PayPing-plugin-settings-group', 'Pages_Ids' );
    }
    public function Donate_Payping_function(){ ?>
		<div id="form" class="wrap">
		    <h1 class="wp-heading-inline">تنظیمات افزونه حمایت مالی پی‌پینگ</h1>
		    <hr class="wp-header-end">
            <form id="donate_payping" method="post" action="options.php">
				<?php wp_nonce_field( 'pp_donate_nonce', 'donate_payping_nonce' ); ?>
                <?php settings_fields( 'Donate_PayPing-plugin-settings-group' ); ?>
                <?php do_settings_sections( 'Donate_PayPing-plugin-settings-group' ); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('توکن پی‌پینگ', 'text_domain') ?></th>
                        <td><input id="TokenCode" placeholder="توکن پی‌پینگ" type="text" name="TokenCode" value="<?php echo esc_html(get_option('Payping_TokenCode')); ?>"/></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('نام کاربری پی‌پینگ', 'text_domain') ?></th>
                        <td><input id="userName" placeholder="نام کاربری پی‌پینگ" type="text" name="UserName"  />
							<div id="loader" class="ajax-loader">
  								<div class="spinner"></div>
							</div>
						</td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('توضیحات', 'text_domain') ?></th>
                        <td>
                            <textarea id="description" placeholder="توضیحی برای حمایت از خودتان بنویسید" id="" cols="30" rows="10"></textarea>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"></th>
                        <td>
                            <p id="title_price"><?php esc_html_e('مبلغ پیشنهادی', 'text_domain') ?></p><span class="suggested_subtitle">مبلغ باید بیشتر از ۱۰۰۰ تومان و کمتر از ۵۰ میلیون تومان باشد</span>
                            <input name="switch" value="" type="checkbox" id="switch">
                            <label id="price_switch" for="switch" onclick="switch_checkbox_donate()"></label>
                            <script type="text/javascript">
                                function switch_checkbox_donate(){
                                    // Get the checkbox
                                    var checkBox = document.getElementById( "switch" );
                                    // Get the output text
                                    var text = document.getElementById( "other_prices" );

                                    // If the checkbox is checked, display the output text
                                    if( checkBox.checked == false ){
                                        text.style.display = "table-row";
                                    }else{
										var donateScript = document.getElementById('donateScript');
										var scriptContent = donateScript.value;
										var tempElement = document.createElement('div');
										tempElement.innerHTML = scriptContent;
										var scriptTag = tempElement.querySelector('script');
										scriptTag.setAttribute('pp-amounts','');
										donateScript.value = tempElement.innerHTML;
										var inputs = document.querySelectorAll('.amountInput');
										for (var i = 0; i < inputs.length; i++) {
    										inputs[i].value = '';
										}
										var elements = document.querySelectorAll('.select-amount');
										for (var i = 0; i < elements.length; i++) {
    										elements[i].remove();
										}
                                        text.style.display = "none";
                                    }
                                }
                            </script>
                        </td>
                    </tr>

                    <tr id="other_prices" valign="top" style="display:none;">
                        <th scope="row"><?php esc_html_e('مبلغ پیشنهادی', 'text_domain') ?></th>
                        <td>
                            <div class="PriceInput">
								<input id="amount0" type="number" placeholder="مبلغ پیشنهادی خود را وارد کنید" class="amountInput" />
								<span class="errorTxt"></span>
							</div>
                            <div id="prices"></div>
                            <button id="add_price" type="button">مبلغ جدید</button>
                        </td>
                    </tr>

                    <tr valign="top" style="display:none">
                        <th scope="row"><?php esc_html_e('کد اسکریپت', 'text_domain') ?></th>
                        <td><textarea id="donateScript" name="Script" rows="10" cols="50"><?php echo esc_html(get_option('Script')); ?></textarea>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                       <th scope="row"><?php esc_html_e('نمایش در برگه', 'text_domain') ?></th>
                        <td>
                           <?php  
                            $pages = get_pages();
                            $count_page = 1;
                            /* get pages options */
                            if( !empty( get_option( 'Pages' ) ) ){
                                $select_pages = get_option( 'Pages' );
                            }else{
                                $select_pages = array();
                            }
                            if( in_array( "all" , $select_pages ) ){
                                echo '<div class="checkbox-all"><input name="Pages[]" checked type="checkbox" id="all_pages" class="input-value" value="all" onclick="AllPages_checkbox_donate()"/><span class="show-title" >' . esc_attr( __( 'همه' ) ) . '</span></div><br/><hr/>';
                            }else{
                                echo '<div class="checkbox-all"><input name="Pages[]" type="checkbox" id="all_pages" class="input-value" value="all" onclick="AllPages_checkbox_donate()"/><span class="show-title" >' . esc_attr( __( 'همه' ) ) . '</span></div><br/><hr/>';
                            }
                            echo '
                            <script type="text/javascript">
                                function AllPages_checkbox_donate(){
                                    // Get the checkbox
                                    var AllPages = document.getElementById( "all_pages" );
                                    var DivDisable = document.getElementById( "OtherPages" );
                                    if( AllPages.checked == true ){
                                        DivDisable.classList.remove("enable");
                                        DivDisable.classList.add("disabled");
                                    }else{
                                        DivDisable.classList.remove("disabled");
                                        DivDisable.classList.add("enable");
                                    }
                                }
                            </script>';
                            /* wordpress pages */
                            $wordpress_pages = array( 
                                'is_home' => __('برگه نخست', 'text_domain'),
                                'is_single' => __('برگه نوشته', 'text_domain'),
                                'is_archive' => __('برگه بایگانی', 'text_domain')
                            );
                            echo '<section id="OtherPages" class="enable"><div class="title_checkbox">'. esc_html__( 'برگه‌های وردپرس', 'text_domain' ) .'</div><hr/>';
                            foreach( $wordpress_pages as $key => $wp_page ){
                                if( in_array( $key , $select_pages ) ){
                                    $option = '<div class="checkbox-pages"><input name="Pages[]" checked type="checkbox" class="input-value" value="' . wp_kses_post($key) . '"/>';
                                }else{
                                    $option = '<div class="checkbox-pages"><input name="Pages[]" type="checkbox" class="input-value" value="' . wp_kses_post($key) . '"/>';
                                }
                                $option .= '<span class="show-title" >' . $wp_page . '</span></div>';
                                if( $count_page%3 == 0 ){
                                    echo $option;
                                }else{
                                    echo $option;
                                }
                                $count_page++;   
                            }
                            /* check install and active woocommerce */
                            if( class_exists( 'WooCommerce' ) ):
                            $woocommerce_pages = array( 
                                'is_shop' => __('فروشگاه', 'text_domain'),
                                'is_product' => __('برگه محصول', 'text_domain'),
                                'is_cart' => __('سبد خرید', 'text_domain'),
                                'is_checkout' => __('تسویه حساب', 'text_domain'),
                                'is_account_page' => __('حساب کاربری', 'text_domain')
                            );
                            echo '<div class="title_checkbox">'. esc_html__( 'افزونه ووکامرس', 'text_domain' ) .'</div><hr/>';
                            foreach( $woocommerce_pages as $key => $woo_page ){
                                if( in_array( $key , $select_pages ) ){
                                    $option = '<div class="checkbox-pages"><input name="Pages[]" checked type="checkbox" class="input-value" value="' . wp_kses_post($key) . '"/>';
                                }else{
                                    $option = '<div class="checkbox-pages"><input name="Pages[]" type="checkbox" class="input-value" value="' . wp_kses_post($key) . '"/>';
                                }
                                $option .= '<span class="show-title" >' . $woo_page . '</span></div>';
                                if( $count_page%3 == 0 ){
                                    echo $option;
                                }else{
                                    echo $option;
                                }
                                $count_page++;   
                            }
                            endif;      
                            /* other pages */
                            if( !empty( get_option( 'Pages_Ids' ) ) ){
                                $Pages_Ids = get_option( 'Pages_Ids' );
                            }else{
                                $Pages_Ids = __('در هر خط یک آیدی یا نامک وارد کنید', 'text_domain');
                            }
                            echo '<div class="title_checkbox">'. esc_html__( 'آیدی یا نامک برگه را وارد کنید', 'text_domain' ) .'</div><br/><span class="note-danger">'. esc_html__( 'در هر خط یک آیدی یا نامک را وارد کنید', 'text_domain' ) .'</span><hr/>';
                            echo '<textarea name="Pages_Ids">' . esc_html($Pages_Ids) . '</textarea></section>';
                            ?>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                       <th scope="row"><?php esc_html_e('شورتکد اسکریپت', 'text_domain') ?></th>
                        <td>
                           <span class="note-danger" ><?php esc_html_e('با استفاده از شورتکد زیر اسکریپت حمایت مالی را در هر بخشی که میخواهید نمایش دهید.', 'text_domain') ?></span><br/>
                            <input type="text" value="[PayPingDonate]" id="pp_shortcode_donate" class="pp-shortcode-donate"/>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
		</div>
		<div class="pp-donate">
			<div class="pp-donate-box" style="visibility: visible; opacity: 1;">
				<div class="pp-donate-box-header">
					<svg
						width="16"
						height="16"
						class="close-btn pp-operate-btn"
						viewBox="0 0 24 24"
						xmlns="http://www.w3.org/2000/svg"
						fill-rule="evenodd"
						clip-rule="evenodd"
					>
						<path
							d="M12 11.293l10.293-10.293.707.707-10.293 10.293 10.293 10.293-.707.707-10.293-10.293-10.293 10.293-.707-.707 10.293-10.293-10.293-10.293.707-.707 10.293 10.293z"
						/>
					</svg>
					<p class="pp-donate-text">
						<svg
							xmlns="http://www.w3.org/2000/svg"
							width="20"
							height="20"
							viewBox="0 0 24 24"
							fill="#DE3B3D"
						>
							<path
								d="M12 4.435c-1.989-5.399-12-4.597-12 3.568 0 4.068 3.06 9.481 12 14.997 8.94-5.516 12-10.929 12-14.997 0-8.118-10-8.999-12-3.568z"
							/>
						</svg>
						حمایت مالی
					</p>
				</div>
				<div class="pp-donate-body" id="first-page">
					<div id="pp-donate-logo" class="pp-donate-logo pp-mb-25"></div>
					<p class="pp-mb-25" id="ppDescription"></p>
					<p class="pp-text-align-center" id="enterText">
						مبلغ دلخواه خود را وارد کنید
					</p>
					<div class="pp-amount pp-select-amount">
						<p><br /></p>
					</div>
					<div class="pp-relative">
						<input
							id="ppDonateAmount"
							autocomplete="off"
							class="pp-donate-input"
							placeholder="مبلغ دلخواه خود را به تومان وارد کنید"
						/>
					</div>
					<div class="pp-donate-btn pp-mt-25" id="nextPageBtn">
						مرحله بعد
					</div>
				</div>
				<div class="pp-donate-box-footer">
					<div class="pp-flex-row pp-justify-center">
						<p>
							پرداخت امن
							<a href="https://www.payping.ir" target="_blank">
								<svg
									xmlns="http://www.w3.org/2000/svg"
									width="60px"
									viewBox="0 0 274.52 110.25"
								>
									<defs></defs>
									<title>درگاه پرداخت اینترنتی پی‌پینگ</title>
									<g id="Layer_2" data-name="Layer 2">
										<g id="Layer_1-2" data-name="Layer 1">
											<polygon
												fill="#172b4d"
												points="251.22 110.25 274.5 101.68 274.5 97.86 251.21 106.92 251.22 110.25"
											></polygon>
											<polygon
												fill="#172b4d"
												points="84.03 7.43 81.9 0 57.39 9.13 57.42 17.28 84.03 7.43"
											></polygon>
											<path
												fill="#172b4d"
												d="M169.47,49.19a5.72,5.72,0,0,1-1.18,4,5.08,5.08,0,0,1-3.86,1.28h-7.79a7,7,0,0,1-4.32-1.25,4.66,4.66,0,0,1-1.7-4V40.3H140v8.89a5.51,5.51,0,0,1-1.22,4,5.27,5.27,0,0,1-3.91,1.28h-7.76a7,7,0,0,1-4.31-1.25,4.64,4.64,0,0,1-1.71-4V40.3H110.42v8.89a5.52,5.52,0,0,1-1.23,4,5.24,5.24,0,0,1-3.91,1.28h-2.07a49.12,49.12,0,0,1-5.07-.23,10.82,10.82,0,0,1-3.72-1.14,10.66,10.66,0,0,1-1-.47,35,35,0,0,1-5.89-4.37c-2.31-2.05-5.28-4.89-8.82-8.43L67.63,28.58l-.31-.31.42-.16,20-7L85,10.71,57.33,20.8V33.12L72.35,48a10.2,10.2,0,0,1,1.54,2.06,4.34,4.34,0,0,1,.47,2.1,2.42,2.42,0,0,1-.54,1.5,2.68,2.68,0,0,1-2.2.73h-29l-3.83-.09H38.5v-4.6l-.06-9.42H27.88l.06,12.33V65.76L40.12,66h33a15.93,15.93,0,0,0,7.08-1.53,8.53,8.53,0,0,0,4.4-5.25l.15-.43.31.33a29.12,29.12,0,0,0,4.63,4,16.47,16.47,0,0,0,4.16,2,15.91,15.91,0,0,0,3.43.68c.29,0,.57,0,.87.06l.94,0c1.39,0,2.94.06,4.13.06h2.07a14.1,14.1,0,0,0,5.34-1,11.09,11.09,0,0,0,4.67-4.26l.23-.34.23.34a10,10,0,0,0,4.85,4.25,17.28,17.28,0,0,0,6,1h8.2a14.12,14.12,0,0,0,5.33-1,11.11,11.11,0,0,0,4.68-4.26l.23-.34.23.34a10,10,0,0,0,4.85,4.25,17.16,17.16,0,0,0,6,1h9.2c4.78,0,8.46-1.33,10.95-3.93s3.77-6.61,3.77-11.86V40.3H169.47Z"
											></path>
											<rect
												fill="#172b4d"
												x="110.4"
												y="21.17"
												width="10.65"
												height="10.13"
											></rect>
											<path
												fill="#172b4d"
												d="M99.46,66.22h3.75c-1.5,0-2.89,0-4.14,0A1.6,1.6,0,0,0,99.46,66.22Z"
											></path>
											<path
												fill="#172b4d"
												d="M244.94,66h14.68c4.77,0,8.47-1.33,11-3.93s3.76-6.6,3.76-11.86V40.3H263.69v8.89a5.69,5.69,0,0,1-1.18,4,5,5,0,0,1-3.86,1.28h-8.33l-20.71,0L226.33,66h6.47a3.74,3.74,0,0,1,3.27,1.32,4.76,4.76,0,0,1,.88,2.84,5.6,5.6,0,0,1-.56,2.64,4.06,4.06,0,0,1-2,1.76,13.11,13.11,0,0,1-3.65.95,42.81,42.81,0,0,1-5.73.3h-6.79a16.31,16.31,0,0,1-5.53-.81,8.44,8.44,0,0,1-3.55-2.33A8.77,8.77,0,0,1,207.3,69a18.58,18.58,0,0,1-.54-4.71V49H196.19V64.81c0,7,1.82,12.5,5.41,16.49s8.65,6,15.14,6h9.79a27.43,27.43,0,0,0,9.7-1.52,19.43,19.43,0,0,0,6.3-3.79A14,14,0,0,0,246,77.06a14.2,14.2,0,0,0,1.08-5C247.18,67.75,244.94,66,244.94,66Z"
											></path>
											<rect
												fill="#172b4d"
												x="118.64"
												y="73.86"
												width="10.65"
												height="10.13"
											></rect>
											<rect
												fill="#172b4d"
												x="130.72"
												y="73.86"
												width="10.65"
												height="10.13"
											></rect>
											<rect
												fill="#172b4d"
												x="156.61"
												y="85.11"
												width="10.65"
												height="10.13"
											></rect>
											<rect
												fill="#172b4d"
												x="150.57"
												y="73.86"
												width="10.65"
												height="10.13"
											></rect>
											<rect
												fill="#172b4d"
												x="162.65"
												y="73.86"
												width="10.65"
												height="10.13"
											></rect>
											<rect
												fill="#172b4d"
												x="257.83"
												y="85.11"
												width="10.65"
												height="10.13"
											></rect>
											<rect
												fill="#172b4d"
												x="251.79"
												y="73.86"
												width="10.65"
												height="10.13"
											></rect>
											<rect
												fill="#172b4d"
												x="263.87"
												y="73.86"
												width="10.65"
												height="10.13"
											></rect>
											<rect
												fill="#0080ff"
												y="48.49"
												width="17.45"
												height="17.45"
											></rect>
										</g>
									</g>
								</svg>
							</a>
						</p>
					</div>
				</div>
			</div>
        </div> 
<?php
}

public function Donate_PayPing_Script(){
    if( !empty( get_option( 'Pages_Ids' ) ) ){
        $Pages_Ids = get_option( 'Pages_Ids' );
        $Pages_Ids_array = array_map( 'trim', explode("\n", $Pages_Ids ) );
    }else{
        $Pages_Ids_array = array();
    }
    
    if( !empty( get_option( 'Pages' ) ) ){
        $select_pages = get_option( 'Pages' );
    }else{
        $select_pages = array();
    }
    $wordpress_pages = array( 
        'is_home',
        'is_single',
        'is_archive',
        'is_shop',
        'is_product',
        'is_cart',
        'is_checkout',
        'is_account_page'
    );
    
    $Pages = array_intersect( $wordpress_pages, $select_pages );
	$ppScript = get_option( 'Script' );
	$sanitized_ppScript = sanitize_option( 'pp_option', $ppScript );
    if( in_array( "all" , $select_pages ) ){
        echo $sanitized_ppScript;
    }elseif( ! empty( $Pages ) || ! empty( $Pages_Ids_array ) ){
        foreach( $Pages as $Page ){
            if( $Page() ){
                echo $sanitized_ppScript;
                exit;
            }else{
                foreach( $Pages_Ids_array as $page ){
                    if( is_page( $page ) ){
                        echo $sanitized_ppScript;
                    }
                }
            }
        }
    } 
}
    public function Donate_PayPing_Script_shortcode(){
        echo esc_html(get_option( 'Script' )); 
    }
    public function Donate_PayPing_ajax_request(){
     if( isset( $_REQUEST )){
		 $nonce = sanitize_text_field($_REQUEST['donate_payping_nonce']);
         if ( ! wp_verify_nonce( $nonce, 'pp_donate_nonce' ) ) {
             wp_send_json_error( 'Invalid nonce' );
             exit;
         }
         $TokenCode_Ajax = sanitize_text_field( $_REQUEST['TokenCode'] );
         if( $TokenCode_Ajax == '' || $TokenCode_Ajax == null || $TokenCode_Ajax == false || $TokenCode_Ajax == 'NONE' ){
             $TokenCode_Ajax = 'None';
         }
         
         $getUserInfo_args = array(
             'body' => wp_json_encode(array()),
             'timeout' => '45',
             'redirection' => '5',
             'httpsversion' => '1.0',
             'blocking' => true,
             'headers' => array(
             'Authorization' => 'Bearer ' . $TokenCode_Ajax,
             'Content-Type' => 'application/json',
             'Accept' => 'application/json' ),
             'cookies' => array()
			 
         );
 
         $getUserInfo_url = 'https://oauth.payping.ir/connect/userinfo';
         $getUserInfo_response = wp_remote_post( $getUserInfo_url, $getUserInfo_args );
         if( is_wp_error( $getUserInfo_response ) ){
             echo sprintf('خطا در ارتباط به پی‌پینگ : شرح خطا %s%', $getUserInfo_response->get_error_message());
         }else{
             $code = wp_remote_retrieve_response_code( $getUserInfo_response );
         if( $code === 200 ){
             if ( isset( $getUserInfo_response["body"] ) and $getUserInfo_response["body"] != '' ) {
                  $userInfo = $getUserInfo_response["body"];
				  echo wp_kses_post($userInfo);
                 exit;
             }else{
				 echo sprintf('کد خطا : %s%', $getUserInfo_response->get_error_message());
             }
         }elseif( $code === 400){
			  echo sprintf('%s% کد خطا: %s%', wp_remote_retrieve_body( $response ), $getUserInfo_response->get_error_message() );
         }else{
			  echo sprintf('%s% کد خطا: %s%', wp_remote_retrieve_body( $response ), $getUserInfo_response->get_error_message() );
         }
         }
     }
        die();
    }
}
new payping_donation();