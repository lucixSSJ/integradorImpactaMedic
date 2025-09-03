<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Orders Manage Add Customer Controller
 *
 * @author  WC Lovers
 * @package wcfmu/controllers/orders
 * @version 5.2.0
 */

class WCFMu_Orders_Manage_Customer_Add_Controller
{


    public function __construct()
    {
        global $WCFM, $WCFMu;

        $this->processing();

    }//end __construct()


    public function processing()
    {
        global $WCFM, $WCFMu,$wpdb, $wcfm_customer_form_data;

        $wcfm_customer_form_data = [];
        parse_str($_POST['wcfm_order_add_customer_form'], $wcfm_customer_form_data);

        $wcfm_customer_messages = get_wcfm_customers_manage_messages();
        $has_error              = false;

        if (isset($wcfm_customer_form_data['wcbc_user_email']) && ! empty($wcfm_customer_form_data['wcbc_user_email'])) {
            $customer_id = 0;
            $is_update   = false;

            // WCFM form custom validation filter
            $custom_validation_results = apply_filters('wcfm_form_custom_validation', $wcfm_customer_form_data, 'customer_manage');
            if (isset($custom_validation_results['has_error']) && ! empty($custom_validation_results['has_error'])) {
                $custom_validation_error = __('There has some error in submitted data.', 'wc-frontend-manager');
                if (isset($custom_validation_results['message']) && ! empty($custom_validation_results['message'])) {
                    $custom_validation_error = $custom_validation_results['message'];
                }

                echo '{"status": false, "message": "'.$custom_validation_error.'"}';
                die;
            }

            if (! is_email($wcfm_customer_form_data['wcbc_user_email'])) {
                echo '{"status": false, "message": "'.__('Please provide a valid email address.', 'woocommerce').'"}';
                die;
            }

            $user_email = sanitize_email($wcfm_customer_form_data['wcbc_user_email']);

            if (!isset($wcfm_customer_form_data['wcbc_user_name']) || empty($wcfm_customer_form_data['wcbc_user_name'])) {
                echo '{"status": false, "message": "' . esc_html( $wcfm_customer_messages['no_username'] ) . '"}';
                die;
            }
            if (!validate_username($wcfm_customer_form_data['wcbc_user_name'])) {
                echo '{"status": false, "message": "'.__('Please enter a valid account username.', 'woocommerce').'"}';
                die;
            }
            if(username_exists($wcfm_customer_form_data['wcbc_user_name'])) {
                echo '{"status": false, "message": "' . $wcfm_customer_messages['username_exists'] . '"}';
                die;
            }

            if (email_exists($wcfm_customer_form_data['wcbc_user_email']) == false) {
            } else {
                $has_error = true;
                echo '{"status": false, "message": "'.$wcfm_customer_messages['email_exists'].'"}';
            }

            $wcfm_customer_form_data['user_name'] = $wcfm_customer_form_data['wcbc_user_name'];
            $wcfm_customer_form_data['user_email'] = $wcfm_customer_form_data['wcbc_user_email'];
            $wcfm_customer_form_data['first_name'] = $wcfm_customer_form_data['wcbc_first_name'];
            $wcfm_customer_form_data['last_name'] = $wcfm_customer_form_data['wcbc_last_name'];

            $password = wp_generate_password($length = 12, $include_standard_special_chars = false);
            if (! $has_error) {
                $user_data = [
                    'user_login'   => $wcfm_customer_form_data['user_name'],
                    'user_email'   => $wcfm_customer_form_data['wcbc_user_email'],
                    'display_name' => $wcfm_customer_form_data['user_name'],
                    'nickname'     => $wcfm_customer_form_data['user_name'],
                    'first_name'   => $wcfm_customer_form_data['wcbc_first_name'],
                    'last_name'    => $wcfm_customer_form_data['wcbc_last_name'],
                    'user_pass'    => $password,
                    'role'         => 'customer',
                    'ID'           => $customer_id,
                ];

                $customer_id = wp_insert_user($user_data);

                if (! $customer_id) {
                    $has_error = true;
                } else {
                    if (apply_filters('wcfm_allow_customer_billing_details', true)) {
                        $wcfm_customer_billing_fields = [
                            'billing_first_name' => 'wcbc_first_name',
                            'billing_last_name'  => 'wcbc_last_name',
                            'billing_phone'      => 'wcbc_phone',
                            'billing_email'      => 'wcbc_user_email',
                            'billing_address_1'  => 'baddr_1',
                            'billing_address_2'  => 'baddr_2',
                            'billing_country'    => 'bcountry',
                            'billing_city'       => 'bcity',
                            'billing_state'      => 'bstate',
                            'billing_postcode'   => 'bzip',
                        ];
                        foreach ($wcfm_customer_billing_fields as $wcfm_customer_default_key => $wcfm_customer_default_field) {
                            if (isset($wcfm_customer_form_data[$wcfm_customer_default_field]) && ! empty($wcfm_customer_form_data[$wcfm_customer_default_field])) {
                                update_user_meta($customer_id, $wcfm_customer_default_key, $wcfm_customer_form_data[$wcfm_customer_default_field]);
                            }
                        }
                    }

                    if (!defined('DOING_WCFM_EMAIL'))
                    define('DOING_WCFM_EMAIL', true);

                    // Sending Mail to new user
                    $mail_to = $wcfm_customer_form_data['user_email'];

                    // Switch language context…
                    if (apply_filters('wcfm_allow_wpml_email_translation', true)) {
                        do_action('wpml_switch_language_for_email', $mail_to);
                    }

                    $new_account_mail_subject = "{site_name}: Nueva Cuenta Creada";
                    $new_account_mail_body = '<br/>' . __('Dear', 'wc-frontend-manager') . ' {first_name}' . ',<br/><br/>' .
                    __('Your account has been created as {user_role}. Follow the bellow details to log into the system', 'wc-frontend-manager') .
                    '<br/><br/>' .
                    __('Site', 'wc-frontend-manager') . ': {site_url}' .
                    '<br/>' .
                    __('Login', 'wc-frontend-manager') . ': {username}' .
                    '<br/>' .
                    __('Password', 'wc-frontend-manager') . ': {password}' .
                    '<br /><br/>Gracias' .
                    '<br/><br/>';

                    $subject = str_replace('{site_name}', get_bloginfo('name'), $new_account_mail_subject);
                    $subject = apply_filters('wcfm_email_subject_wrapper', $subject);
                    $message = str_replace('{site_url}', get_bloginfo('url'), $new_account_mail_body);
                    $message = str_replace('{first_name}', $wcfm_customer_form_data['first_name'], $message);
                    $message = str_replace('{username}', $wcfm_customer_form_data['user_name'], $message);
                    $message = str_replace('{password}', $password, $message);
                    $message = str_replace('{user_role}', 'Cliente', $message);
                    $message = apply_filters('wcfm_email_content_wrapper', $message, __('New Account', 'wc-frontend-manager'));

                    wp_mail($mail_to, $subject, $message);

                    do_action('wcfm_customers_manage', $customer_id, $wcfm_customer_form_data);
                }//end if

                if (! $has_error) {
                    echo '{"status": true, "message": "'.$wcfm_customer_messages['customer_saved'].'", "customer_id": "'.$customer_id.'", "username": "'.$wcfm_customer_form_data['user_name'].' ('.$user_email.')'.'"}';
                } else {
                    echo '{"status": false, "message": "'.$wcfm_customer_messages['customer_failed'].'"}';
                }
            }//end if
        } else {
            echo '{"status": false, "message": "'.$wcfm_customer_messages['no_email'].'"}';
        }//end if

        die;

    }//end processing()


}//end class
