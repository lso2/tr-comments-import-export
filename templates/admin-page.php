<?php
/**
 * Admin page template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="cei-main-title">Comments Export/Import Manager</h1>
    
    <div class="cei-donate-section-compact">
        <div class="cei-donate-compact-content">
            <a href="https://techreader.com/donate" target="_blank" class="cei-compact-donate-btn">Donate</a>
            <span class="cei-donate-icon">☕</span>
			<div class="cei-donate-text">
                <strong>Enjoying this plugin?</strong> Support development with a coffee!
            </div>
			<div><?php echo esc_html($plugin_data['Name']); ?>: <strong><?php echo esc_html($plugin_data['Version']); ?></strong> by <a href="https://techreader.com" target="_blank">TechReader</a></div>
        </div>
    </div>
    
    <!-- Comment Stats Bar -->
    <div class="cei-stats-bar">
        <a href="<?php echo esc_url(admin_url('edit-comments.php?comment_status=approved')); ?>" class="cei-stat-item approved">
            <div class="cei-stat-content">
                <span class="cei-stat-label">Approved: <?php echo esc_html(number_format($comment_counts['1'])); ?></span>
            </div>
        </a>
        <a href="<?php echo esc_url(admin_url('edit-comments.php?comment_status=moderated')); ?>" class="cei-stat-item pending">
            <div class="cei-stat-content">
                <span class="cei-stat-label">Pending: <?php echo esc_html(number_format($comment_counts['0'])); ?></span>
            </div>
        </a>
        <a href="<?php echo esc_url(admin_url('edit-comments.php?comment_status=spam')); ?>" class="cei-stat-item spam">
            <div class="cei-stat-content">
                <span class="cei-stat-label">Spam: <?php echo esc_html(number_format($comment_counts['spam'])); ?></span>
            </div>
        </a>
        <a href="<?php echo esc_url(admin_url('edit-comments.php?comment_status=trash')); ?>" class="cei-stat-item trash">
            <div class="cei-stat-content">
                <span class="cei-stat-label">Trash: <?php echo esc_html(number_format($comment_counts['trash'])); ?></span>
            </div>
        </a>
    </div>
    
    <!-- Donate Section - Original (Commented Out)
    <div class="cei-donate-section">
        <h3>💖 Support This Plugin</h3>
        <p>If this plugin has been helpful to you, consider buying me a coffee! Your support helps keep this plugin free and updated.</p>
        <a href="https://techreader.com/donate" target="_blank" class="cei-donate-btn">☕ Buy Me a Coffee</a>
        <p>Thank you for your support! 🙏</p>
    </div>
    -->
    
    <div class="cei-main-content">
        <!-- Export Card -->
        <div class="cei-section">
            <!-- Main Export Form Card -->
            <form method="post" action="">
                <?php wp_nonce_field('export_comments', 'export_nonce'); ?>
                
                <div class="cei-card">
                    <div class="cei-card-header">
                        <h2>Export Comments</h2>
                    </div>
                    <div class="cei-card-actions">
                        <?php submit_button('Export Comments', 'primary', 'export_comments', false); ?>
                    </div>
                </div>
                
                <!-- Comment Status Options Card -->
                <div class="cei-card">
                    <div class="cei-card-header">
                        <h2>Comment Status Options</h2>
                    </div>
                    <div class="cei-card-body">
                        <p class="description">Select which comment statuses to include in the export.</p>
                        <fieldset>
                            <label><input type="checkbox" name="status_approved" value="1" <?php checked($checkbox_preferences['export_status_approved'], '1'); ?>> Approved (<?php echo esc_html(number_format($comment_counts['1'])); ?>)</label><br>
                            <label><input type="checkbox" name="status_pending" value="1" <?php checked($checkbox_preferences['export_status_pending'], '1'); ?>> Pending (<?php echo esc_html(number_format($comment_counts['0'])); ?>)</label><br>
                            <label><input type="checkbox" name="status_spam" value="1" <?php checked($checkbox_preferences['export_status_spam'], '1'); ?>> Spam (<?php echo esc_html(number_format($comment_counts['spam'])); ?>)</label><br>
                            <label><input type="checkbox" name="status_trash" value="1" <?php checked($checkbox_preferences['export_status_trash'], '1'); ?>> Trash (<?php echo esc_html(number_format($comment_counts['trash'])); ?>)</label>
                        </fieldset>
                        <p class="export-select-links"><a href="#" class="select-all-export">Select All</a> | <a href="#" class="deselect-all-export">Deselect All</a></p>
                    </div>
                </div>
                
                <!-- Export Filename Options Card -->
                <div class="cei-card">
                    <div class="cei-card-header">
                        <h2>Export Filename Options</h2>
                    </div>
                    <div class="cei-card-body">
                        <p class="description">Choose how you want to name your exported file.</p>
                        <fieldset>
                            <label><input type="checkbox" name="export_include_site_name" value="1" <?php checked($checkbox_preferences['export_include_site_name'], '1'); ?>> Include Site Name</label><br>
                            <label><input type="checkbox" name="export_include_site_url" value="1" <?php checked($checkbox_preferences['export_include_site_url'], '1'); ?>> Include Site URL</label><br>
                            <label><input type="checkbox" name="export_include_date" value="1" <?php checked($checkbox_preferences['export_include_date'], '1'); ?>> Include Date</label><br>
                            <label><input type="checkbox" name="export_include_time" value="1" <?php checked($checkbox_preferences['export_include_time'], '1'); ?>> Include Time</label><br>
                            <label><input type="checkbox" name="export_include_comment_status" value="1" <?php checked($checkbox_preferences['export_include_comment_status'], '1'); ?>> Include Comment Status</label>
                        </fieldset>
                        <p class="filename-select-links"><a href="#" class="select-all-filename">Select All</a> | <a href="#" class="deselect-all-filename">Deselect All</a></p>
                        <div class="cei-info-block">
                            <p><strong>Comment Status in Filename:</strong> When enabled (default), includes status codes in the filename (appr_pend for approved+pending comments). The CSV file always contains human-readable status names (approved, pending, spam, trash) regardless of this setting.</p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Import Card -->
        <div class="cei-section">
            <!-- Main Import Form Card -->
            <form method="post" action="" enctype="multipart/form-data">
                <?php wp_nonce_field('import_comments', 'import_nonce'); ?>
                
                <div class="cei-card">
                    <div class="cei-card-header">
                        <h2>Import Comments</h2>
                    </div>
                    <div class="cei-card-actions">
                        <?php submit_button('Import Comments', 'primary', 'import_comments', false); ?>
                    </div>
                </div>
                
                <!-- CSV File Card -->
                <div class="cei-card">
                    <div class="cei-card-header">
                        <h2>CSV File</h2>
                    </div>
                    <div class="cei-card-body">
                        <p class="description">Select a CSV file exported from this tool.</p>
                        <input type="file" name="import_file" accept=".csv" required>
                        <div id="selected-filename" class="cei-filename-display" style="display: none;">
                            <strong>Selected file:</strong> <span id="filename-text"></span>
                        </div>
                    </div>
                </div>
                
                <!-- Import Status Options Card -->
                <div class="cei-card">
                    <div class="cei-card-header">
                        <h2>Import Status Options</h2>
                    </div>
                    <div class="cei-card-body">
                        <p class="description">Select which statuses to import and where they should go.</p>
                        <table class="cei-mapping-table">
                            <thead>
                                <tr>
                                    <th>Import</th>
                                    <th>From Status</th>
                                    <th>To Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="checkbox" name="import_approved" value="1" <?php checked($checkbox_preferences['import_approved'], '1'); ?>></td>
                                    <td><strong>Approved</strong></td>
                                    <td>
                                        <select name="status_mapping_approved">
                                            <option value="1" selected>Approved</option>
                                            <option value="0">Pending</option>
                                            <option value="spam">Spam</option>
                                            <option value="trash">Trash</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" name="import_pending" value="1" <?php checked($checkbox_preferences['import_pending'], '1'); ?>></td>
                                    <td><strong>Pending</strong></td>
                                    <td>
                                        <select name="status_mapping_pending">
                                            <option value="1">Approved</option>
                                            <option value="0" selected>Pending</option>
                                            <option value="spam">Spam</option>
                                            <option value="trash">Trash</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" name="import_spam" value="1" <?php checked($checkbox_preferences['import_spam'], '1'); ?>></td>
                                    <td><strong>Spam</strong></td>
                                    <td>
                                        <select name="status_mapping_spam">
                                            <option value="1">Approved</option>
                                            <option value="0">Pending</option>
                                            <option value="spam" selected>Spam</option>
                                            <option value="trash">Trash</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" name="import_trash" value="1" <?php checked($checkbox_preferences['import_trash'], '1'); ?>></td>
                                    <td><strong>Trash</strong></td>
                                    <td>
                                        <select name="status_mapping_trash">
                                            <option value="1">Approved</option>
                                            <option value="0">Pending</option>
                                            <option value="spam">Spam</option>
                                            <option value="trash" selected>Trash</option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <p class="cei-select-links import-select-links"><a href="#" class="select-all-import">Select All</a> | <a href="#" class="deselect-all-import">Deselect All</a></p>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <?php /*
    <!-- Statistics -->
    <div class="cei-stats-section">
        <h2>Comment Statistics</h2>
        <?php $this->display_comment_stats(); ?>
    </div>
    */ ?>
    
    <!-- About -->
    <div class="cei-about-section">
        <div class="cei-about-content">
            <p><strong>About This Plugin:</strong></p>
            <p>This tool helps you backup and transfer WordPress comments between sites. Export your comments to CSV format with flexible filtering options, then import them to another WordPress installation while preserving or changing their approval status. Useful for site migrations, spam filtering workflows, or creating comment backups.</p>
            <p><strong>Export:</strong> Choose which comment statuses to include and download as CSV. All comment data including author info, content, and metadata is preserved.</p>
            <p><strong>Import:</strong> Upload CSV files and map original statuses to new ones. Comments with existing IDs are automatically skipped to prevent duplicates.</p>
        </div>
    </div>

    <!-- Plugin Info Section -->
    <div class="cei-plugin-info">
        <div class="cei-plugin-info-content">
            <div class="cei-plugin-details">
                <?php echo esc_html($plugin_data['Name']); ?>: <strong><?php echo esc_html($plugin_data['Version']); ?></strong> by <a href="https://techreader.com" target="_blank">TechReader</a> &nbsp;|&nbsp; <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-github" viewBox="0 0 16 16">
  <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27s1.36.09 2 .27c1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.01 8.01 0 0 0 16 8c0-4.42-3.58-8-8-8"/>
</svg>
 <a href="<?php echo esc_url($plugin_data['GithubURI']); ?>" target="_blank">Github</a>
            </div>
            <div class="cei-plugin-support">
                <a href="https://techreader.com/donate" target="_blank" class="cei-mini-donate-btn">☕ Buy Me a Coffee</a>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    <div id="cei-notifications"></div>
</div>
