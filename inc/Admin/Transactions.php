<?php
/**
 *  Get admin page with successful transactions.
 */
namespace Inc\Admin;

class Transactions {

    public $title; 
    public $description;

    public function __construct() {

        // Variables for titles before the table in Admin section
        $this->title = 'Cashleo Cashflows';
        $this->description = 'These are the latest transactions. Please note that data from the server is refreshed every 5 minutes';

    }

    public function register() {

        add_action( 'admin_menu', array ( $this, 'main_admin_menu') );
        add_action( 'admin_enqueue_scripts', array( $this, 'woocashleo_admin_enqueue' ) );       

    }

    public function main_admin_menu() {

        add_menu_page( 'Cashleo Transactions', 'Cashleo', 'administrator', 'woocashleo', array( $this, 'transactions_tables' ), 'dashicons-tickets', 6 );

        add_submenu_page( 'woocashleo', 'Cashleo Transactions', 'Transactions', 'manage_options', 'woocashleo', array( $this, 'transactions_tables' ) );

    }

    /**
    * Styling admin tables and pages
    */
    public function woocashleo_admin_enqueue() {
                
        global $pagenow; 
        
        if ( ( 'admin.php' === $pagenow ) && ( 'woocashleo' === $_GET['page'] ) ) {
            
            wp_enqueue_style( 'woocashleo-admin-tables-dbs', plugin_dir_url( dirname( __FILE__, 2 ) ) . 'elements/css/dataTables.bootstrap.min.css' );
            
            wp_enqueue_style( 'woocashleo-admin-tables', plugin_dir_url( dirname( __FILE__, 2 ) ) . 'elements/css/tables.css' );
            wp_enqueue_style( 'woocashleo-admin-tables-new', plugin_dir_url( dirname( __FILE__, 2 ) ) . 'elements/css/table-new.css' );
            
            wp_enqueue_script( 'pay-table', plugin_dir_url( dirname( __FILE__, 2 ) ) . 'elements/js/jquery.dataTables.min.js' , array( 'jquery'), true  );
            wp_enqueue_script( 'pay-table-bs', plugin_dir_url( dirname( __FILE__, 2 ) ) . 'elements/js/dataTables.bootstrap.min.js' , array( 'jquery' ), true  );

            wp_register_script( 'pay-script', plugin_dir_url( dirname( __FILE__, 2 ) ) . 'elements/js/woocashleo.plugin.js', array( 'pay-table' ), true );
            wp_localize_script( 'pay-script', 'php_vars', array ( 'js_array' => get_transient( 'filtered_transactions_results' ) ) );
            wp_enqueue_script( 'pay-script' );

        }
    
    }

    public function transactions_tables(){

        $array_data = array();
        
        if ( true === ( $transactions_results = get_transient( 'transactions_results' ) ) ) {
            return;
        } else {

            $json = json_decode( $transactions_results, true );

            $i=1;
            foreach ( $json['data'] as $key => $value) {

                $str2 = substr($value['description'], 19);

                $single_array = array();
                
                if ( $value['payment_provider']['name'] == 'MTN Mobile Money' ) {
                    $provider = 'MTN';
                } elseif ( $value['payment_provider']['name'] == 'Airtel Money' ) {
                    $provider = 'Airtel'; 
                } else {
                    $provider = $value['payment_provider']['name'];
                }

                $transaction_date = date('Y-m-d h:i:s', strtotime($value['transaction_date']));
                $completed_on = date('Y-m-d h:i:s', strtotime($value['completed_on']));
                $order_link_var = '<a href=\'' . get_bloginfo('url') . '/wp-admin/post.php?post=' . $str2 . '&action=edit\'>' .  $value['description'] . '</a>';

                array_push( 
                    $single_array, 
                    $i,
                    $value['transaction_id'], 
                    $value['msisdn'], 
                    $value['currency'] . ' ' . number_format($value['amount']),
                    $provider,
                    $order_link_var, $value['status'], 
                    $transaction_date, $completed_on 
                );
                    
                array_push( $array_data, $single_array );
                        
                $i++;
            }

            set_transient( 'filtered_transactions_results', $array_data, 1 * HOUR_IN_SECONDS );
        }

        ?>
            <div class="wrap">
                <h3><?php echo $this->title; ?></h3>
                <p><?php echo $this->description; ?></p>
                <hr>
                <table id="example" class="display"></table>
            </div><!--div.wrap-->

        <?php
    }
}