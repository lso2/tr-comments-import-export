=== Comments Export/Import Manager ===
Contributors: techreader
Tags: comments, export, import, csv, migration
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.7.7
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Export and import WordPress comments with filtering and status mapping. Perfect for site migrations and comment backups.

== Description ==

The Comments Export/Import Manager is a powerful tool for WordPress administrators who need to backup, transfer, or manage comments across WordPress installations. Whether you're migrating to a new site, implementing spam filtering workflows, or creating comment backups, this plugin provides the flexibility and control you need.

**Key Features:**

* **Flexible Export Options** - Choose which comment statuses to export (Approved, Pending, Spam, Trash)
* **Smart Import Mapping** - Map imported comment statuses to new statuses during import
* **CSV Format** - Industry-standard CSV format for maximum compatibility
* **Duplicate Prevention** - Automatically skips comments with existing IDs to prevent duplicates
* **Complete Data Preservation** - Exports all comment metadata including author info, timestamps, and content
* **Status Statistics** - View current comment counts by status before export/import
* **User-Friendly Interface** - Clean, intuitive admin interface with helpful descriptions

**Perfect For:**

* Site migrations and transfers
* Spam filtering workflows (export comments, filter through external services, re-import)
* Creating comment backups before major changes
* Moving comments between staging and production sites
* Bulk comment status changes via external processing

**How It Works:**

1. **Export**: Select comment statuses to include and download as CSV
2. **Process**: Optionally process the CSV file with external tools or services
3. **Import**: Upload the CSV and map original statuses to new ones
4. **Verify**: Check import results and comment statistics

The plugin is lightweight, secure, and follows WordPress coding standards. All operations are performed with proper nonce verification and capability checks.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/tr-comments-import-export` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to Comments → Export/Import to access the plugin features

== Frequently Asked Questions ==

= What comment data is exported? =

The plugin exports all comment data including: comment ID, post ID, author name, email, URL, IP address, dates, content, karma, approval status, user agent, comment type, parent comment, and user ID.

= Can I export only specific comment statuses? =

Yes! You can choose to export any combination of Approved, Pending, Spam, and Trash comments using the checkboxes in the export section.

= What happens to comment statuses during import? =

You have full control over status mapping. For each original status (Approved, Pending, Spam, Trash), you can choose what status it should become when imported.

= Will importing create duplicate comments? =

No. The plugin automatically checks for existing comment IDs and skips any comments that already exist in your database.

= Can I use this with large numbers of comments? =

Yes, the plugin is designed to handle large comment datasets efficiently. However, very large imports may take some time to process depending on your server configuration.

= Is the CSV format compatible with other tools? =

Yes, the plugin uses standard CSV format that can be opened and edited with Excel, Google Sheets, or any CSV-compatible application.

== Changelog ==

= 1.7.7 =
* Security improvements with static prepared statements and enhanced caching
* Complete WordPress coding standards compliance for database operations
* Final resolution of all plugin check database warnings

= 1.7.6 =
* Complete database security overhaul with prepared statements
* Redesigned duplicate checking system for better security
* Replaced dynamic query building with static prepared statements

= 1.7.5 =
* Enhanced nonce verification and database query security
* Improved prepared statement usage throughout
* Complete WordPress plugin check compliance

= 1.7.4 =
* SECURITY FIX: Additional database query security improvements
* SECURITY FIX: Enhanced form processing with better nonce verification
* SECURITY FIX: Fixed CSV output security with proper phpcs ignore comments
* UI IMPROVEMENT: Separate card layout for each section (Export, Status Options, Filename Options)
* UI IMPROVEMENT: Removed icons and reverted to standard WordPress button styling
* UI IMPROVEMENT: Clean card-based interface with improved visual hierarchy
* CODE STANDARDS: Fixed text domain for proper internationalization
* MAINTENANCE: Comprehensive plugin check compliance improvements

= 1.7.3 =
* SECURITY FIX: All output is now properly escaped using WordPress escaping functions
* SECURITY FIX: Enhanced nonce verification and input sanitization throughout
* SECURITY FIX: Improved $_POST and $_FILES validation with proper checks
* WORDPRESS COMPATIBILITY: Replaced parse_url() with wp_parse_url() for better compatibility
* WORDPRESS COMPATIBILITY: Replaced date() with gmdate() to avoid timezone issues
* WORDPRESS COMPATIBILITY: Replaced direct file operations with WP_Filesystem methods
* DATABASE SECURITY: All database queries now use proper prepared statements
* CODE STANDARDS: Fixed text domain to match plugin requirements
* UI IMPROVEMENT: Added modern card-based layout for export and import sections
* UI IMPROVEMENT: Enhanced visual design with gradients and improved spacing
* UI IMPROVEMENT: Better responsive design for mobile and tablet devices
* UI IMPROVEMENT: Fixed HTML entity display issues in header and footer links
* MAINTENANCE: Updated WordPress compatibility to 6.8
* MAINTENANCE: Enhanced error handling and input validation

= 1.6.0 =
* MAJOR IMPROVEMENT: Enhanced duplicate detection system - now prevents ALL types of duplicate comments
* NEW: Comprehensive duplicate checking using content, author, email, post ID, and date combinations
* NEW: Detailed import notifications showing exactly what was skipped (duplicates vs unselected statuses)
* IMPROVED: Import process now distinguishes between different types of skipped comments
* IMPROVED: Better error handling and validation for comment data during import
* IMPROVED: More informative success/info messages with breakdown of skip reasons
* FIXED: Comments without IDs or with invalid data are now properly handled
* TECHNICAL: Refactored import logic for better performance and reliability
* TECHNICAL: Added sanitization and validation for all comment fields during duplicate checking

= 1.5.4 =
* NEW: Added Select All/Deselect All links for Export Filename Options section
* IMPROVED: Site name in export filenames now uses lowercase for consistency
* IMPROVED: Notification bubbles now stack vertically instead of overlapping
* IMPROVED: Better formatting for multiline notifications with line break support
* IMPROVED: File upload now shows proper notification with full filename (no cropping)
* IMPROVED: Select All/Deselect All notifications are more specific ("All export status options selected" etc.)
* IMPROVED: Longer notification display time (3 seconds) and better bubble positioning
* IMPROVED: Enhanced bubble styling with better padding and line height
* FIXED: Removed duplicate notifications when using Select All/Deselect All buttons
* FIXED: Import file selection now shows correct notification instead of "import_file deselected"
* TECHNICAL: Better bubble stacking algorithm with automatic repositioning when bubbles disappear

= 1.5.1 =
* CRITICAL FIX: Fixed checkbox persistence bug where all checkboxes would appear checked after page reload
* FIXED: Export filename generation now properly respects unchecked options
* IMPROVED: Checkbox states now save and restore correctly when unchecked

= 1.5.0 =
* FIXED: Export filename options now use consistent "Include" logic with proper defaults (Site URL, Date, Time, Comment Status checked by default)
* FIXED: Checkbox persistence bug - selections now properly save and restore when unchecked
* FIXED: CSV export always contains human-readable status names (approved, pending, spam, trash) regardless of filename options
* IMPROVED: Enhanced plugin info section layout with better coffee button placement and styling
* IMPROVED: More intuitive export filename customization with all positive "Include" options
* TECHNICAL: Refactored filename generation logic for better maintainability and consistency
* TECHNICAL: Fixed AJAX preference saving to handle all export filename options correctly

= 1.4.6 =
* NEW: Persistent checkbox selections - plugin now remembers your export/import preferences across page loads
* NEW: Export filename customization options - choose to include/exclude site name, site URL, date, and time
* NEW: Export comment status format option - choose between human-readable (approved, pending) or WordPress internal codes (1, 0)
* IMPROVED: No more annoying checkbox resets - your selections are automatically saved and restored
* IMPROVED: More flexible export naming with granular control over filename components
* ENHANCED: Better user experience with preferences that persist between sessions
* TECHNICAL: Added AJAX-powered preference saving system for seamless checkbox state management

= 1.4.5 =
* Enhanced export filename format: now includes site domain (sitename_domain.com_comments_appr_2025-05-31.csv)
* Removed brackets from export filenames for cleaner appearance
* Improved import notifications: shows detailed breakdown of what was actually imported
* Added grey info notification when nothing is imported (not an error - just no matching criteria)
* Fixed status mapping table alignment and made columns more compact
* Better distinction between import errors vs. no imports (expected behavior)

= 1.4.4 =
* FIXED: Export and import buttons now work correctly - removed dependency on button name checking
* Improved form handling by checking nonce and form data instead of button names  
* Fixed export functionality - now properly triggers CSV download
* Fixed import functionality - now properly processes uploaded files
* Enhanced debugging and error handling for better troubleshooting

= 1.4.3 =
* Moved export/import buttons back to top position (using original HTML structure)
* Added CSS styling to properly position buttons instead of changing HTML
* Added debugging logs to troubleshoot form submission issues
* Maintained button alignment at top of each section for better UX
* Fixed CSS positioning for submit buttons

= 1.4.2 =
* Fixed export and import buttons functionality
* Moved submit buttons to proper position at end of forms
* Corrected form submission behavior to prevent page reload without action
* Export and import now work correctly with proper form structure
* Buttons maintain form selections and execute proper actions

= 1.4.1 =
* User interface improvements and tweaks
* Removed version numbers from individual files for cleaner maintenance

= 1.3.3 =
* Separated HTML template from PHP class for better code organization
* Moved statistics section to bottom of page as requested
* Created dedicated templates directory for cleaner architecture
* Improved separation of concerns between logic and presentation
* Enhanced code maintainability and readability

= 1.3.2 =
* Enhanced export filename format: [sitename]_comments_appr_pend_2025-05-31_09-51-05.csv
* Export now uses human-readable status values (approved, pending, spam, trash) instead of 0/1
* Import function now handles both numeric (0/1) and text (approved/pending) status formats
* Improved filename generation with site name and selected status types
* Better backward compatibility with existing CSV files

= 1.3.1 =
* Restructured plugin architecture with separate class, CSS, and JS files
* Added popup notification system for export/import feedback
* Display comment counts next to each status in export section (e.g., "Approved (482)")
* Enhanced import notifications with detailed status breakdown
* Replaced error pages with user-friendly popup notifications
* Improved code organization and maintainability

= 1.3.0 =
* Enhanced user interface with better styling and layout
* Moved all CSS to organized style blocks
* Fixed export/import button functionality
* Improved status mapping table layout
* Added comprehensive plugin information section

= 1.2.0 =
* Added smart status mapping for imports
* Improved import validation and error handling
* Enhanced user interface with better organization
* Added plugin info and donation sections

= 1.1.0 =
* Added Settings link to plugins page
* Moved menu to Comments submenu
* Updated admin page styling
* Fixed hook references

= 1.0.0 =
* Initial release
* Basic export/import functionality
* Comment status filtering
* CSV format support
* Duplicate prevention

== Upgrade Notice ==

= 1.7.7 =
Security and performance improvements.

= 1.6.0 =
Major improvement: Enhanced duplicate detection prevents ALL types of duplicate comments during import, with detailed reporting.

= 1.5.1 =
Critical bugfix: Fixes checkbox persistence issue.

= 1.5.0 =
Critical fixes for checkbox persistence and export filename options.

= 1.4.6 =
Major usability improvement! Plugin now remembers your checkbox selections and offers flexible export filename options.

= 1.4.2 =
Critical fix: Export and import buttons now work correctly.

== Support ==

For support, feature requests, or bug reports, please visit [https://github.com/lso2/tr-comments-import-export/issues](https://github.com/lso2/tr-comments-import-export/issues).

If this plugin has been helpful, consider supporting development at [https://techreader.com/donate](https://techreader.com/donate).
