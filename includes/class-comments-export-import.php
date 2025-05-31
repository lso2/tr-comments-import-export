<?php
/**
 * Comments Export/Import Manager Class
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CommentsExportImport {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'handle_actions'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_save_export_status_preferences', array($this, 'save_export_status_preferences'));
        add_action('wp_ajax_save_import_preferences', array($this, 'save_import_preferences'));
        add_action('wp_ajax_save_filename_preferences', array($this, 'save_filename_preferences'));
        add_filter('plugin_action_links_' . plugin_basename(TR_CEI_PLUGIN_DIR . 'tr-comments-import-export.php'), array($this, 'add_settings_link'));
    }
    
    public function add_admin_menu() {
        add_comments_page(
            'Comments Export/Import',
            'Export/Import',
            'manage_options',
            'comments-export-import',
            array($this, 'admin_page')
        );
    }
    
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('edit-comments.php?page=comments-export-import') . '">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    public function enqueue_scripts($hook) {
        if ($hook !== 'comments_page_comments-export-import') {
            return;
        }
        wp_enqueue_script('jquery');
        wp_enqueue_script(
            'tr-comments-export-import-js',
            TR_CEI_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            TR_CEI_VERSION,
            true
        );
        
        // Pass data to JavaScript
        wp_localize_script('tr-comments-export-import-js', 'trCeiAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'export_status_nonce' => wp_create_nonce('save_export_status_preferences'),
            'import_nonce' => wp_create_nonce('save_import_preferences'),
            'filename_nonce' => wp_create_nonce('save_filename_preferences')
        ));
        wp_enqueue_style(
            'tr-comments-export-import-css',
            TR_CEI_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            TR_CEI_VERSION
        );
    }
    
    public function handle_actions() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Only handle actions on our admin page
        if (!isset($_GET['page']) || $_GET['page'] !== 'comments-export-import') {
            return;
        }
        
        // Check for export action
        if (isset($_POST['export_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['export_nonce'])), 'export_comments')) {
            // Check if any status checkboxes are selected
            if (isset($_POST['status_approved']) || isset($_POST['status_pending']) || 
                isset($_POST['status_spam']) || isset($_POST['status_trash'])) {
                $this->export_comments();
            }
        }
        
        // Check for import action
        if (isset($_POST['import_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['import_nonce'])), 'import_comments')) {
            // Check if file is uploaded and mapping is selected
            if (isset($_FILES['import_file']) && isset($_FILES['import_file']['error']) && $_FILES['import_file']['error'] === UPLOAD_ERR_OK &&
                (isset($_POST['import_approved']) || isset($_POST['import_pending']) || 
                 isset($_POST['import_spam']) || isset($_POST['import_trash']))) {
                $this->import_comments();
            }
        }
    }
    
    public function export_comments() {
        // Check nonce first
        if (!isset($_POST['export_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['export_nonce'])), 'export_comments')) {
            wp_die('Security check failed');
        }
        
        $statuses = array();
        $status_codes = array(); // For filename
        
        if (isset($_POST['status_approved'])) {
            $statuses[] = '1';
            $status_codes[] = 'appr';
        }
        if (isset($_POST['status_pending'])) {
            $statuses[] = '0';
            $status_codes[] = 'pend';
        }
        if (isset($_POST['status_spam'])) {
            $statuses[] = 'spam';
            $status_codes[] = 'spam';
        }
        if (isset($_POST['status_trash'])) {
            $statuses[] = 'trash';
            $status_codes[] = 'trash';
        }
        
        if (empty($statuses)) {
            wp_die('Please select at least one comment status to export.');
        }
        
        global $wpdb;
        
        // Prepare query parameters first
        $query_params = array('', 'comment');
        
        // Build WHERE clause for statuses
        $status_where = '';
        if (count($statuses) > 0) {
            $status_placeholders = implode(',', array_fill(0, count($statuses), '%s'));
            $status_where = " AND comment_approved IN ($status_placeholders)";
            $query_params = array_merge($query_params, $statuses);
        }
        
        // Execute query with caching based on status count
        $cache_key = 'tr_cei_export_' . md5(serialize($query_params));
        $comments = wp_cache_get($cache_key, 'tr_comments_export');
        
        if ($comments === false) {
            // Use static prepared queries based on status count
            if (count($statuses) === 1) {
                $comments = $wpdb->get_results($wpdb->prepare( // db call ok; no-cache ok
                    "SELECT comment_ID, comment_post_ID, comment_author, comment_author_email, comment_author_url, comment_author_IP, comment_date, comment_date_gmt, comment_content, comment_karma, comment_approved, comment_agent, comment_type, comment_parent, user_id FROM {$wpdb->comments} WHERE (comment_type = %s OR comment_type = %s) AND comment_approved = %s ORDER BY comment_date DESC",
                    '', 'comment', $statuses[0]
                ), ARRAY_A);
            } elseif (count($statuses) === 2) {
                $comments = $wpdb->get_results($wpdb->prepare( // db call ok; no-cache ok
                    "SELECT comment_ID, comment_post_ID, comment_author, comment_author_email, comment_author_url, comment_author_IP, comment_date, comment_date_gmt, comment_content, comment_karma, comment_approved, comment_agent, comment_type, comment_parent, user_id FROM {$wpdb->comments} WHERE (comment_type = %s OR comment_type = %s) AND comment_approved IN (%s, %s) ORDER BY comment_date DESC",
                    '', 'comment', $statuses[0], $statuses[1]
                ), ARRAY_A);
            } elseif (count($statuses) === 3) {
                $comments = $wpdb->get_results($wpdb->prepare( // db call ok; no-cache ok
                    "SELECT comment_ID, comment_post_ID, comment_author, comment_author_email, comment_author_url, comment_author_IP, comment_date, comment_date_gmt, comment_content, comment_karma, comment_approved, comment_agent, comment_type, comment_parent, user_id FROM {$wpdb->comments} WHERE (comment_type = %s OR comment_type = %s) AND comment_approved IN (%s, %s, %s) ORDER BY comment_date DESC",
                    '', 'comment', $statuses[0], $statuses[1], $statuses[2]
                ), ARRAY_A);
            } else {
                $comments = $wpdb->get_results($wpdb->prepare( // db call ok; no-cache ok
                    "SELECT comment_ID, comment_post_ID, comment_author, comment_author_email, comment_author_url, comment_author_IP, comment_date, comment_date_gmt, comment_content, comment_karma, comment_approved, comment_agent, comment_type, comment_parent, user_id FROM {$wpdb->comments} WHERE (comment_type = %s OR comment_type = %s) AND comment_approved IN (%s, %s, %s, %s) ORDER BY comment_date DESC",
                    '', 'comment', $statuses[0], $statuses[1], $statuses[2], $statuses[3]
                ), ARRAY_A);
            }
            wp_cache_set($cache_key, $comments, 'tr_comments_export', 300); // Cache for 5 minutes
        }
        
        if (empty($comments)) {
            // Instead of wp_die, redirect back with error message
            $redirect_url = add_query_arg(array(
                'page' => 'comments-export-import',
                'export_error' => 'no_comments'
            ), admin_url('edit-comments.php'));
            wp_redirect($redirect_url);
            exit;
        }
        
        // Build filename components based on export options
        $filename_parts = array();
        
        // Site name (optional - default unchecked)
        if (isset($_POST['export_include_site_name'])) {
            $site_name = strtolower(sanitize_file_name(get_bloginfo('name')));
            if (!empty($site_name)) {
                $filename_parts[] = $site_name;
            }
        }
        
        // Site URL (default checked)
        if (isset($_POST['export_include_site_url'])) {
            $site_url = wp_parse_url(get_site_url(), PHP_URL_HOST);
            if (!empty($site_url)) {
                $filename_parts[] = sanitize_file_name($site_url);
            }
        }
        
        // Always include 'comments' identifier
        $filename_parts[] = 'comments';
        
        // Comment Status (default checked)
        if (isset($_POST['export_include_comment_status'])) {
            $type_string = implode('_', $status_codes);
            $filename_parts[] = $type_string;
        }
        
        // Date (default checked)
        if (isset($_POST['export_include_date'])) {
            $filename_parts[] = gmdate('Y-m-d');
        }
        
        // Time (default checked)
        if (isset($_POST['export_include_time'])) {
            $filename_parts[] = gmdate('H-i-s');
        }
        
        // Fallback if no parts
        if (empty($filename_parts)) {
            $filename_parts = array('wordpress', 'comments', $type_string, gmdate('Y-m-d_H-i-s'));
        }
        
        $filename = implode('_', $filename_parts) . '.csv';


        
        // Initialize WordPress filesystem
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once(ABSPATH . '/wp-admin/includes/file.php');
            WP_Filesystem();
        }
        
        // Build CSV content in memory
        $csv_content = '';
        
        // CSV headers
        $headers = array(
            'comment_ID', 'comment_post_ID', 'comment_author', 'comment_author_email',
            'comment_author_url', 'comment_author_IP', 'comment_date', 'comment_date_gmt',
            'comment_content', 'comment_karma', 'comment_approved', 'comment_agent',
            'comment_type', 'comment_parent', 'user_id'
        );
        
        // Add headers to CSV content  
        $escaped_headers = array();
        foreach ($headers as $header) {
            $escaped_header = str_replace('"', '""', $header);
            if (strpos($escaped_header, ',') !== false || strpos($escaped_header, '"') !== false || strpos($escaped_header, "\n") !== false) {
                $escaped_header = '"' . $escaped_header . '"';
            }
            $escaped_headers[] = $escaped_header;
        }
        $csv_content .= implode(',', $escaped_headers) . "\n";
        
        // Convert status values to human readable format (always)
        foreach ($comments as $comment) {
            // Always convert comment_approved to human readable
            switch ($comment['comment_approved']) {
                case '1':
                    $comment['comment_approved'] = 'approved';
                    break;
                case '0':
                    $comment['comment_approved'] = 'pending';
                    break;
                case 'spam':
                    $comment['comment_approved'] = 'spam';
                    break;
                case 'trash':
                    $comment['comment_approved'] = 'trash';
                    break;
            }
            
            // Properly escape CSV values
            $escaped_values = array();
            foreach ($comment as $value) {
                // Escape quotes and wrap in quotes if necessary
                $escaped_value = str_replace('"', '""', $value);
                if (strpos($escaped_value, ',') !== false || strpos($escaped_value, '"') !== false || strpos($escaped_value, "\n") !== false) {
                    $escaped_value = '"' . $escaped_value . '"';
                }
                $escaped_values[] = $escaped_value;
            }
            $csv_content .= implode(',', $escaped_values) . "\n";
        }
        
        // Send headers and output - no escaping needed for file download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        
        // Direct output for file download - escaping would corrupt the CSV
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $csv_content;
        exit;
    }
    
    public function import_comments() {
        // Verify nonce first - already done in handle_actions but double-check
        if (!isset($_POST['import_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['import_nonce'])), 'import_comments')) {
            wp_die('Security check failed');
        }
        
        if (!isset($_FILES['import_file']) || !isset($_FILES['import_file']['error']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            $redirect_url = add_query_arg(array(
                'page' => 'comments-export-import',
                'import_error' => 'file_error'
            ), admin_url('edit-comments.php'));
            wp_redirect($redirect_url);
            exit;
        }
        
        // Validate file
        if (!isset($_FILES['import_file']['tmp_name']) || empty($_FILES['import_file']['tmp_name'])) {
            $redirect_url = add_query_arg(array(
                'page' => 'comments-export-import',
                'import_error' => 'file_error'
            ), admin_url('edit-comments.php'));
            wp_redirect($redirect_url);
            exit;
        }
        
        $file = sanitize_text_field($_FILES['import_file']['tmp_name']);
        
        // Initialize WordPress filesystem
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once(ABSPATH . '/wp-admin/includes/file.php');
            WP_Filesystem();
        }
        
        // Read file content using WP_Filesystem
        $file_content = $wp_filesystem->get_contents($file);
        if ($file_content === false) {
            $redirect_url = add_query_arg(array(
                'page' => 'comments-export-import',
                'import_error' => 'file_read_error'
            ), admin_url('edit-comments.php'));
            wp_redirect($redirect_url);
            exit;
        }
        
        // Parse CSV content
        $lines = str_getcsv($file_content, "\n");
        if (empty($lines)) {
            $redirect_url = add_query_arg(array(
                'page' => 'comments-export-import',
                'import_error' => 'invalid_format'
            ), admin_url('edit-comments.php'));
            wp_redirect($redirect_url);
            exit;
        }
        
        $headers = str_getcsv(array_shift($lines));
        
        if (!$headers || !in_array('comment_content', $headers)) {
            $redirect_url = add_query_arg(array(
                'page' => 'comments-export-import',
                'import_error' => 'invalid_format'
            ), admin_url('edit-comments.php'));
            wp_redirect($redirect_url);
            exit;
        }
        
        // Get import settings with proper sanitization
        $import_statuses = array();
        if (isset($_POST['import_approved'])) {
            $import_statuses['1'] = isset($_POST['status_mapping_approved']) ? sanitize_text_field(wp_unslash($_POST['status_mapping_approved'])) : '1';
        }
        if (isset($_POST['import_pending'])) {
            $import_statuses['0'] = isset($_POST['status_mapping_pending']) ? sanitize_text_field(wp_unslash($_POST['status_mapping_pending'])) : '0';
        }
        if (isset($_POST['import_spam'])) {
            $import_statuses['spam'] = isset($_POST['status_mapping_spam']) ? sanitize_text_field(wp_unslash($_POST['status_mapping_spam'])) : 'spam';
        }
        if (isset($_POST['import_trash'])) {
            $import_statuses['trash'] = isset($_POST['status_mapping_trash']) ? sanitize_text_field(wp_unslash($_POST['status_mapping_trash'])) : 'trash';
        }
        
        // Also handle human readable statuses from our exports
        if (isset($_POST['import_approved'])) {
            $import_statuses['approved'] = isset($_POST['status_mapping_approved']) ? sanitize_text_field(wp_unslash($_POST['status_mapping_approved'])) : '1';
        }
        if (isset($_POST['import_pending'])) {
            $import_statuses['pending'] = isset($_POST['status_mapping_pending']) ? sanitize_text_field(wp_unslash($_POST['status_mapping_pending'])) : '0';
        }
        // spam and trash are already the same
        
        if (empty($import_statuses)) {
            $redirect_url = add_query_arg(array(
                'page' => 'comments-export-import',
                'import_error' => 'no_status_selected'
            ), admin_url('edit-comments.php'));
            wp_redirect($redirect_url);
            exit;
        }
        
        global $wpdb;
        $imported = 0;
        $skipped = 0;
        $duplicates_skipped = 0;
        $status_counts = array();
        
        foreach ($lines as $line) {
            $data = str_getcsv($line);
            if (count($data) !== count($headers)) {
                $skipped++;
                continue;
            }
            
            $comment_data = array_combine($headers, array_map('sanitize_text_field', $data));
            
            // Check if this comment status should be imported
            $original_status = isset($comment_data['comment_approved']) ? $comment_data['comment_approved'] : '0';
            
            // Normalize status for checking (convert human readable to numeric if needed)
            $normalized_status = $original_status;
            if ($original_status === 'approved') {
                $normalized_status = '1';
            } elseif ($original_status === 'pending') {
                $normalized_status = '0';
            }
            
            // Check both original and normalized status
            if (!isset($import_statuses[$original_status]) && !isset($import_statuses[$normalized_status])) {
                $skipped++;
                continue;
            }
            
            // Get the correct mapping
            $new_status = isset($import_statuses[$original_status]) ? 
                         $import_statuses[$original_status] : 
                         $import_statuses[$normalized_status];
            
            // Enhanced duplicate checking
            $is_duplicate = false;
            
            // Method 1: Check by comment_ID if provided
            if (isset($comment_data['comment_ID']) && !empty($comment_data['comment_ID']) && is_numeric($comment_data['comment_ID'])) {
                $cache_key = 'tr_cei_duplicate_id_' . intval($comment_data['comment_ID']);
                $existing = wp_cache_get($cache_key, 'tr_comments_import');
                
                if ($existing === false) {
                    $existing = $wpdb->get_var($wpdb->prepare( // db call ok; no-cache ok
                        "SELECT comment_ID FROM {$wpdb->comments} WHERE comment_ID = %d",
                        intval($comment_data['comment_ID'])
                    ));
                    wp_cache_set($cache_key, $existing ? $existing : 'not_found', 'tr_comments_import', 300);
                }
                
                if ($existing !== 'not_found') {
                    $is_duplicate = true;
                }
            }
            
            // Method 2: Check by content + author + post combination (comprehensive duplicate detection)
            if (!$is_duplicate) {
                $check_values = array();
                
                // Build query parts for duplicate checking
                if (isset($comment_data['comment_content']) && !empty(trim($comment_data['comment_content']))) {
                    $check_values[] = trim($comment_data['comment_content']);
                }
                
                if (isset($comment_data['comment_author']) && !empty(trim($comment_data['comment_author']))) {
                    $check_values[] = trim($comment_data['comment_author']);
                }
                
                if (isset($comment_data['comment_author_email']) && !empty(trim($comment_data['comment_author_email']))) {
                    $check_values[] = trim($comment_data['comment_author_email']);
                }
                
                if (isset($comment_data['comment_post_ID']) && !empty($comment_data['comment_post_ID']) && is_numeric($comment_data['comment_post_ID'])) {
                    $check_values[] = intval($comment_data['comment_post_ID']);
                }
                
                if (isset($comment_data['comment_date']) && !empty($comment_data['comment_date'])) {
                    $check_values[] = $comment_data['comment_date'];
                }
                
                // Only perform duplicate check if we have at least content or author+email
                if (count($check_values) >= 2) {
                    $cache_key = 'tr_cei_duplicate_' . md5(serialize($check_values));
                    $existing_duplicate = wp_cache_get($cache_key, 'tr_comments_import');
                    
                    if ($existing_duplicate === false) {
                        // Use a prepared query based on field count
                        if (count($check_values) === 2) {
                            $existing_duplicate = $wpdb->get_var($wpdb->prepare( // db call ok; no-cache ok
                                "SELECT comment_ID FROM {$wpdb->comments} WHERE comment_content = %s AND comment_author = %s LIMIT 1",
                                $check_values[0], $check_values[1]
                            ));
                        } elseif (count($check_values) === 3) {
                            $existing_duplicate = $wpdb->get_var($wpdb->prepare( // db call ok; no-cache ok
                                "SELECT comment_ID FROM {$wpdb->comments} WHERE comment_content = %s AND comment_author = %s AND comment_author_email = %s LIMIT 1",
                                $check_values[0], $check_values[1], $check_values[2]
                            ));
                        } elseif (count($check_values) === 4) {
                            $existing_duplicate = $wpdb->get_var($wpdb->prepare( // db call ok; no-cache ok
                                "SELECT comment_ID FROM {$wpdb->comments} WHERE comment_content = %s AND comment_author = %s AND comment_author_email = %s AND comment_post_ID = %d LIMIT 1",
                                $check_values[0], $check_values[1], $check_values[2], $check_values[3]
                            ));
                        } else {
                            $existing_duplicate = $wpdb->get_var($wpdb->prepare( // db call ok; no-cache ok
                                "SELECT comment_ID FROM {$wpdb->comments} WHERE comment_content = %s AND comment_author = %s AND comment_author_email = %s AND comment_post_ID = %d AND comment_date = %s LIMIT 1",
                                $check_values[0], $check_values[1], $check_values[2], $check_values[3], $check_values[4]
                            ));
                        }
                        
                        wp_cache_set($cache_key, $existing_duplicate ? $existing_duplicate : 'not_found', 'tr_comments_import', 300);
                    }
                    
                    if ($existing_duplicate !== 'not_found') {
                        $is_duplicate = true;
                    }
                }
            }
            
            // Skip if duplicate found
            if ($is_duplicate) {
                $duplicates_skipped++;
                continue;
            }
            
            // Prepare comment data for insertion
            $insert_data = array();
            
            $allowed_fields = array(
                'comment_post_ID', 'comment_author', 'comment_author_email',
                'comment_author_url', 'comment_author_IP', 'comment_date',
                'comment_date_gmt', 'comment_content', 'comment_karma',
                'comment_approved', 'comment_agent', 'comment_type',
                'comment_parent', 'user_id'
            );
            
            foreach ($allowed_fields as $field) {
                if (isset($comment_data[$field])) {
                    $insert_data[$field] = $comment_data[$field];
                }
            }
            
            // Set the new status based on mapping
            $insert_data['comment_approved'] = $new_status;
            
            // Set defaults for required fields
            if (empty($insert_data['comment_date'])) {
                $insert_data['comment_date'] = current_time('mysql');
            }
            if (empty($insert_data['comment_date_gmt'])) {
                $insert_data['comment_date_gmt'] = current_time('mysql', 1);
            }
            
            // Insert comment using wpdb->insert which automatically prepares the statement
            $result = $wpdb->insert($wpdb->comments, $insert_data); // db call ok; no-cache ok
            
            // Clear comment count cache after successful insert
            if ($result) {
                wp_cache_delete('tr_cei_comment_counts', 'tr_comments_stats');
            }
            
            if ($result) {
                $imported++;
                // Track status counts
                if (!isset($status_counts[$new_status])) {
                    $status_counts[$new_status] = 0;
                }
                $status_counts[$new_status]++;
                
                // Clear relevant caches
                wp_cache_delete('tr_cei_export_' . md5(serialize(array('', 'comment', $new_status))), 'tr_comments_export');
            } else {
                $skipped++;
            }
        }
        
        // Build status breakdown message
        $status_labels = array(
            '1' => 'Approved',
            '0' => 'Pending',
            'spam' => 'Spam',
            'trash' => 'Trash'
        );
        
        $status_breakdown = array();
        foreach ($status_counts as $status => $count) {
            $label = isset($status_labels[$status]) ? $status_labels[$status] : $status;
            $status_breakdown[] = $count . ' ' . $label;
        }
        
        // Calculate total skipped and breakdown
        $total_skipped = $skipped + $duplicates_skipped;
        $skip_breakdown = array();
        if ($duplicates_skipped > 0) {
            $skip_breakdown[] = $duplicates_skipped . ' duplicates';
        }
        if ($skipped > 0) {
            $skip_breakdown[] = $skipped . ' unselected statuses';
        }
        
        // Determine redirect type and message
        if ($imported > 0) {
            // Success - something was imported
            $redirect_url = add_query_arg(array(
                'page' => 'comments-export-import',
                'import_success' => '1',
                'imported' => $imported,
                'skipped' => $total_skipped,
                'duplicates_skipped' => $duplicates_skipped,
                'status_skipped' => $skipped,
                'breakdown' => urlencode(implode(', ', $status_breakdown))
            ), admin_url('edit-comments.php'));
        } else {
            // Nothing imported - show info message (not error)
            $redirect_url = add_query_arg(array(
                'page' => 'comments-export-import',
                'import_info' => 'nothing_imported',
                'skipped' => $total_skipped,
                'duplicates_skipped' => $duplicates_skipped,
                'status_skipped' => $skipped
            ), admin_url('edit-comments.php'));
        }
        
        wp_redirect($redirect_url);
        exit;
    }
    
    public function get_comment_counts() {
        $cache_key = 'tr_cei_comment_counts';
        $counts = wp_cache_get($cache_key, 'tr_comments_stats');
        
        if ($counts === false) {
            global $wpdb;
            
            $stats = $wpdb->get_results($wpdb->prepare( // db call ok; no-cache ok
                "SELECT comment_approved, COUNT(*) as count FROM {$wpdb->comments} WHERE comment_type = %s OR comment_type = %s GROUP BY comment_approved",
                '', 'comment'
            ));
            
            $counts = array(
                '1' => 0,      // Approved
                '0' => 0,      // Pending
                'spam' => 0,   // Spam
                'trash' => 0   // Trash
            );
            
            foreach ($stats as $stat) {
                if (isset($counts[$stat->comment_approved])) {
                    $counts[$stat->comment_approved] = $stat->count;
                }
            }
            
            wp_cache_set($cache_key, $counts, 'tr_comments_stats', 300); // Cache for 5 minutes
        }
        
        return $counts;
    }
    
    public function save_export_status_preferences() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'save_export_status_preferences')) {
            wp_die('Nonce verification failed');
        }
        
        $saved_prefs = get_option('tr_cei_checkbox_preferences', array());
        
        $saved_prefs['export_status_approved'] = isset($_POST['export_status_approved']) ? sanitize_text_field(wp_unslash($_POST['export_status_approved'])) : '0';
        $saved_prefs['export_status_pending'] = isset($_POST['export_status_pending']) ? sanitize_text_field(wp_unslash($_POST['export_status_pending'])) : '0';
        $saved_prefs['export_status_spam'] = isset($_POST['export_status_spam']) ? sanitize_text_field(wp_unslash($_POST['export_status_spam'])) : '0';
        $saved_prefs['export_status_trash'] = isset($_POST['export_status_trash']) ? sanitize_text_field(wp_unslash($_POST['export_status_trash'])) : '0';
        
        update_option('tr_cei_checkbox_preferences', $saved_prefs);
        wp_cache_delete('tr_cei_checkbox_preferences', 'options');
        
        wp_send_json_success(array(
            'message' => 'Export status preferences saved'
        ));
    }
    
    public function save_import_preferences() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'save_import_preferences')) {
            wp_die('Nonce verification failed');
        }
        
        $saved_prefs = get_option('tr_cei_checkbox_preferences', array());
        
        $saved_prefs['import_approved'] = isset($_POST['import_approved']) ? sanitize_text_field(wp_unslash($_POST['import_approved'])) : '0';
        $saved_prefs['import_pending'] = isset($_POST['import_pending']) ? sanitize_text_field(wp_unslash($_POST['import_pending'])) : '0';
        $saved_prefs['import_spam'] = isset($_POST['import_spam']) ? sanitize_text_field(wp_unslash($_POST['import_spam'])) : '0';
        $saved_prefs['import_trash'] = isset($_POST['import_trash']) ? sanitize_text_field(wp_unslash($_POST['import_trash'])) : '0';
        
        update_option('tr_cei_checkbox_preferences', $saved_prefs);
        wp_cache_delete('tr_cei_checkbox_preferences', 'options');
        
        wp_send_json_success(array(
            'message' => 'Import preferences saved'
        ));
    }
    
    public function save_filename_preferences() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'save_filename_preferences')) {
            wp_die('Nonce verification failed');
        }
        
        $saved_prefs = get_option('tr_cei_checkbox_preferences', array());
        
        $saved_prefs['export_include_site_name'] = isset($_POST['export_include_site_name']) ? sanitize_text_field(wp_unslash($_POST['export_include_site_name'])) : '0';
        $saved_prefs['export_include_site_url'] = isset($_POST['export_include_site_url']) ? sanitize_text_field(wp_unslash($_POST['export_include_site_url'])) : '0';
        $saved_prefs['export_include_date'] = isset($_POST['export_include_date']) ? sanitize_text_field(wp_unslash($_POST['export_include_date'])) : '0';
        $saved_prefs['export_include_time'] = isset($_POST['export_include_time']) ? sanitize_text_field(wp_unslash($_POST['export_include_time'])) : '0';
        $saved_prefs['export_include_comment_status'] = isset($_POST['export_include_comment_status']) ? sanitize_text_field(wp_unslash($_POST['export_include_comment_status'])) : '0';
        
        update_option('tr_cei_checkbox_preferences', $saved_prefs);
        wp_cache_delete('tr_cei_checkbox_preferences', 'options');
        
        wp_send_json_success(array(
            'message' => 'Filename preferences saved'
        ));
    }
    
    public function get_checkbox_preferences() {
        $defaults = array(
            'export_status_approved' => '1',
            'export_status_pending' => '1',
            'export_status_spam' => '0',
            'export_status_trash' => '0',
            'import_approved' => '1',
            'import_pending' => '1',
            'import_spam' => '0',
            'import_trash' => '0',
            'export_include_site_name' => '0',        // unchecked
            'export_include_site_url' => '1',         // checked
            'export_include_date' => '1',             // checked
            'export_include_time' => '1',             // checked
            'export_include_comment_status' => '1'    // checked
        );
        
        $saved_prefs = get_option('tr_cei_checkbox_preferences', array());
        $final_prefs = wp_parse_args($saved_prefs, $defaults);
        
        return $final_prefs;
    }
    
    public function admin_page() {
        $plugin_data = get_plugin_data(TR_CEI_PLUGIN_DIR . 'tr-comments-import-export.php');
        $comment_counts = $this->get_comment_counts();
        $checkbox_preferences = $this->get_checkbox_preferences();
        
        // Include the template
        include TR_CEI_PLUGIN_DIR . 'templates/admin-page.php';
    }
    
    private function display_comment_stats() {
        $stats = $this->get_comment_counts();
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Status</th><th>Count</th></tr></thead>';
        echo '<tbody>';
        
        $status_labels = array(
            '1' => 'Approved',
            '0' => 'Pending',
            'spam' => 'Spam',
            'trash' => 'Trash'
        );
        
        foreach ($stats as $status => $count) {
            $label = isset($status_labels[$status]) ? 
                     $status_labels[$status] : 
                     'Other (' . $status . ')';
            
            echo '<tr>';
            echo '<td>' . esc_html($label) . '</td>';
            echo '<td>' . number_format($count) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }
}
