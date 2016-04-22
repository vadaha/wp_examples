<?

function full_pay_link_sandbox($atts)
{
    $url_to_contract   =   0;
    extract(shortcode_atts(array(
        'urla' => '0',
    ), $atts));

    $contract[0] = "2278109";
    $contract[1] = "2278109";
    $contract[2] = "2278529";
    $contract[3] = "2278531";
    if ($url_to_contract > 3) $url_to_contract = 3;
    if ($url_to_contract < 0) $url_to_contract = 0;

    $wp_session = WP_Session::get_instance();
    $user_id = Usersession::get_user_id($wp_session['login']);
    if ($user_id) {

        $url = "https://sandbox.bluesnap.com/jsp/buynow.jsp?contractId=" . $contract[$url_to_contract] . '&custom1=' . $user_id;
    } else {
        $url = 'http://stgfit.com/member-login/?contract=' . $contract[$url_to_contract];
    }

    return '
<p><a class="wpb_button_a" href="' . $url . '"><span class="wpb_button wpb_btn-danger wpb_regularsize">ORDER NOW</span></a></p>	 	';

}

add_shortcode('full_pay_link', 'full_pay_link_sandbox');


?>