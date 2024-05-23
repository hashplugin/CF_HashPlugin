<?php
/*
Plugin Name: CF Fetcher API
Plugin URI: https://www.hashplugin.com/
Description: A plugin to receive and display contact data on the dashboard, including data from an external API.
Version: 1.1
Author: HashPlugin
Author URI: https://www.hashplugin.com/
License: GPL2
*/


// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Register the menu item and admin page.
add_action('admin_menu', 'wp_contact_data_menu');

function wp_contact_data_menu() {
    add_menu_page(
        'Contact API Data Table',       // Page title
        'Contact API Data Table',       // Menu title
        'manage_options',     // Capability
        'contact-data',       // Menu slug
        'wp_contact_data_page', // Function to display the page
        'dashicons-admin-users', // Icon
        3                     // Position
    );
}

function wp_contact_data_page() {
    // Check if user is allowed access
    if (!current_user_can('manage_options')) {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'contact_data';

    // Handle search form submission
    $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
    $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
    $query = "SELECT * FROM $table_name";
    if ($start_date && $end_date) {
        $query .= $wpdb->prepare(" WHERE DATE(date) BETWEEN %s AND %s", $start_date, $end_date);
    }

    // Fetch data from the database
    $results = $wpdb->get_results($query);


    // Handle text file download
    if (isset($_POST['download_txt'])) {
        if (!headers_sent()) {
            // Clean output buffer
            ob_clean();

            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="contact_data.txt"');
            $output = fopen('php://output', 'w');
            fwrite($output, "ID\tName\tEmail\tPhone\tMessage\tDate\n");
            foreach ($results as $row) {
                fwrite($output, "{$row->id}\t{$row->name}\t{$row->email}\t{$row->phone}\t{$row->message}\t{$row->date}\n");
            }
            fclose($output);
            exit;
        }
    }

    // Display the contact data table
    echo '<div class="wrap">';
    echo '<h1>Encore Website Contact Leads</h1>';
    echo '<form method="POST" action="">';
    echo '<input type="date" name="start_date" value="' . esc_attr($start_date) . '">';
    echo '<input type="date" name="end_date" value="' . esc_attr($end_date) . '">';
    echo '<input type="submit" value="Search">';
   
    echo '<input type="submit" name="download_txt" value="Download TXT">';
    echo '</form>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Message</th><th>Date</th><th>Actions</th></tr></thead>';
    echo '<tbody>';

    foreach ($results as $row) {
        echo '<tr>';
        echo '<td>' . esc_html($row->id) . '</td>';
        echo '<td>' . esc_html($row->name) . '</td>';
        echo '<td>' . esc_html($row->email) . '</td>';
        echo '<td>' . esc_html($row->phone) . '</td>';
        echo '<td>' . esc_html($row->message) . '</td>';
        echo '<td>' . esc_html(isset($row->date) ? $row->date : '') . '</td>';
        echo '<td>';
        echo '<a href="?page=contact-data&action=edit&id=' . $row->id . '">Edit</a> | ';
        echo '<a href="?page=contact-data&action=delete&id=' . $row->id . '" onclick="return confirm(\'Are you sure?\')">Delete</a>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
    echo '</div>';

    // Handle edit action
    if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $contact = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
        if ($contact) {
            echo '<div class="wrap">';
            echo '<h2>Edit Contact Data</h2>';
            echo '<form method="POST" action="">';
            echo '<input type="hidden" name="id" value="' . esc_attr($contact->id) . '">';
            echo '<p><label for="name">Name</label><input type="text" name="name" value="' . esc_attr($contact->name) . '" required></p>';
            echo '<p><label for="email">Email</label><input type="email" name="email" value="' . esc_attr($contact->email) . '" required></p>';
            echo '<p><label for="phone">Phone</label><input type="text" name="phone" value="' . esc_attr($contact->phone) . '" required></p>';
            echo '<p><label for="message">Message</label><textarea name="message" required>' . esc_textarea($contact->message) . '</textarea></p>';
            echo '<p><input type="submit" name="update_contact" value="Update"></p>';
            echo '</form>';
            echo '</div>';
        }
    }

    // Handle update contact
    if (isset($_POST['update_contact']) && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $message = sanitize_textarea_field($_POST['message']);

        $wpdb->update(
            $table_name,
            [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'message' => $message
            ],
            ['id' => $id],
            ['%s', '%s', '%s', '%s'],
            ['%d']
        );
        if (!headers_sent()) {
            wp_redirect(admin_url('admin.php?page=contact-data'));
            exit;
        }
    }

    // Handle delete action
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $wpdb->delete($table_name, ['id' => $id], ['%d']);
        if (!headers_sent()) {
            wp_redirect(admin_url('admin.php?page=contact-data'));
            exit;
        }
    }
}

// Fetch data from the API
function wp_fetch_contact_data_from_api() {
    $response = wp_remote_get('https://api.example.com/contacts'); // Replace with your API URL
    if (is_wp_error($response)) {
        return;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!empty($data)) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'contact_data';

        foreach ($data as $contact) {
            $wpdb->insert(
                $table_name,
                [
                    'name' => sanitize_text_field($contact['name']),
                    'email' => sanitize_email($contact['email']),
                    'phone' => sanitize_text_field($contact['phone']),
                    'message' => sanitize_textarea_field($contact['message']),
                    'date' => current_time('mysql')
                ]
            );
        }
    }
}


// Activate plugin and create database table
register_activation_hook(__FILE__, 'wp_contact_data_install');

function wp_contact_data_install() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'contact_data';

    $charset_collate = $wpdb->get_charset_collate();

    // Drop the table if it exists
    $wpdb->query("DROP TABLE IF EXISTS $table_name");

    // Create the table
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        email text NOT NULL,
        phone text NOT NULL,
        message text NOT NULL,
        date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Register form shortcode
add_shortcode('contact_form', 'wp_contact_form');

function wp_contact_form() {
    ob_start();
    ?>
    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
        <input type="hidden" name="action" value="submit_contact_form">
        <p>
            <label for="name">Name</label>
            <input type="text" name="name" required>
        </p>
        <p>
            <label for="email">Email</label>
            <input type="email" name="email" required>
        </p>
        <p>
            <label for="phone">Phone</label>
            <input type="text" name="phone" required>
        </p>
        <p>
            <label for="message">Message</label>
            <textarea name="message" required></textarea>
        </p>
        <p>
            <input type="submit" value="Submit">
        </p>
    </form>
    <?php
    return ob_get_clean();
}

// Handle form submission
add_action('admin_post_nopriv_submit_contact_form', 'wp_handle_contact_form');
add_action('admin_post_submit_contact_form', 'wp_handle_contact_form');

function wp_handle_contact_form() {
    if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['phone']) && isset($_POST['message'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'contact_data';

        $wpdb->insert(
            $table_name,
            [
                'name' => sanitize_text_field($_POST['name']),
                'email' => sanitize_email($_POST['email']),
                'phone' => sanitize_text_field($_POST['phone']),
                'message' => sanitize_textarea_field($_POST['message']),
                'date' => current_time('mysql')
            ]
        );
    }
    if (!headers_sent()) {
        wp_redirect(home_url());
        exit;
    }
}

// Register REST API route with authentication
add_action('rest_api_init', function () {
    register_rest_route('wp-contact-data/v1', '/submit', [
        'methods' => 'POST',
        'callback' => 'wp_handle_rest_contact_form',
        'permission_callback' => function () {
            return is_user_logged_in();
        },
    ]);
});

function wp_handle_rest_contact_form(WP_REST_Request $request) {
    // Verify if the user has correct permissions
    if (!current_user_can('edit_posts')) {
        return new WP_REST_Response('Unauthorized', 403);
    }

    $params = $request->get_json_params();

    if (isset($params['name']) && isset($params['email']) && isset($params['phone']) && isset($params['message'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'contact_data';

        $wpdb->insert(
            $table_name,
            [
                'name' => sanitize_text_field($params['name']),
                'email' => sanitize_email($params['email']),
                'phone' => sanitize_text_field($params['phone']),
                'message' => sanitize_textarea_field($params['message']),
                'date' => current_time('mysql')
            ]
        );

        return new WP_REST_Response('Contact data saved successfully.', 200);
    }

    return new WP_REST_Response('Invalid data.', 400);
}
?>
