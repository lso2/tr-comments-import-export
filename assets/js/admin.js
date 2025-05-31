/**
 * TR Comments Export/Import Manager
 */

jQuery(document).ready(function($) {
    
    // Global notification positioning system
    let notificationQueue = [];
    const NOTIFICATION_HEIGHT = 65;
    const NOTIFICATION_START_TOP = 50;
    
    function getNextNotificationPosition() {
        return NOTIFICATION_START_TOP + (notificationQueue.length * NOTIFICATION_HEIGHT);
    }
    
    function addToQueue(element) {
        notificationQueue.push(element);
        return notificationQueue.length - 1;
    }
    
    function removeFromQueue(element) {
        const index = notificationQueue.indexOf(element);
        if (index > -1) {
            notificationQueue.splice(index, 1);
            repositionAllNotifications();
        }
    }
    
    function repositionAllNotifications() {
        notificationQueue.forEach(function(element, index) {
            if (element && $(element).length) {
                const newTop = NOTIFICATION_START_TOP + (index * NOTIFICATION_HEIGHT);
                $(element).css('top', newTop + 'px');
            }
        });
    }
    
    // Export section: Select/Deselect all checkboxes
    $('.select-all-export').click(function(e) {
        e.preventDefault();
        $('input[name^=\"status_\"]').prop('checked', true);
        showNotificationBubble('All export status options selected', 'success', true);
    });
    
    $('.deselect-all-export').click(function(e) {
        e.preventDefault();
        $('input[name^=\"status_\"]').prop('checked', false);
        showNotificationBubble('All export status options deselected', 'deselected', true);
    });
    
    // Import section: Select/Deselect all checkboxes
    $('.select-all-import').click(function(e) {
        e.preventDefault();
        $('input[name^=\"import_\"]').prop('checked', true);
        showNotificationBubble('All import options selected', 'success', true);
    });
    
    $('.deselect-all-import').click(function(e) {
        e.preventDefault();
        $('input[name^=\"import_\"]').prop('checked', false);
        showNotificationBubble('All import options deselected', 'deselected', true);
    });
    
    // Filename section: Select/Deselect all checkboxes
    $('.select-all-filename').click(function(e) {
        e.preventDefault();
        $('input[name^=\"export_include_\"]').prop('checked', true);
        showNotificationBubble('All filename options selected', 'success', true);
    });
    
    $('.deselect-all-filename').click(function(e) {
        e.preventDefault();
        $('input[name^=\"export_include_\"]').prop('checked', false);
        showNotificationBubble('All filename options deselected', 'deselected', true);
    });
    
    // Handle file upload selection
    $('input[name=\"import_file\"]').change(function() {
        const fileInput = $(this)[0];
        if (fileInput.files.length > 0) {
            const fileName = fileInput.files[0].name;
            showNotificationBubble(`File selected:\\n${fileName}`, 'info');
            $('#filename-text').text(fileName);
            $('#selected-filename').show();
        } else {
            $('#selected-filename').hide();
        }
    });
    
    // Save export status preferences when changed
    $('input[name^=\"status_\"]').change(function() {
        const checkbox = $(this);
        const checkboxName = checkbox.attr('name');
        const isChecked = checkbox.is(':checked');
        
        const labels = {
            'status_approved': 'Export Approved Comments',
            'status_pending': 'Export Pending Comments', 
            'status_spam': 'Export Spam Comments',
            'status_trash': 'Export Trash Comments'
        };
        
        const label = labels[checkboxName] || checkboxName;
        const message = isChecked ? 
            `${label} selected` : 
            `${label} deselected`;
        
        const notificationType = isChecked ? 'success' : 'deselected';
        showNotificationBubble(message, notificationType, true);
        saveExportStatusPreferences(true);
    });
    
    // Save import preferences when changed
    $('input[name^=\"import_\"]:not([name=\"import_file\"])').change(function() {
        const checkbox = $(this);
        const checkboxName = checkbox.attr('name');
        const isChecked = checkbox.is(':checked');
        
        const labels = {
            'import_approved': 'Import Approved Comments',
            'import_pending': 'Import Pending Comments',
            'import_spam': 'Import Spam Comments',
            'import_trash': 'Import Trash Comments'
        };
        
        const label = labels[checkboxName] || checkboxName;
        const message = isChecked ? 
            `${label} selected` : 
            `${label} deselected`;
        
        const notificationType = isChecked ? 'success' : 'deselected';
        showNotificationBubble(message, notificationType, true);
        saveImportPreferences(true);
    });
    
    // Save filename preferences when changed
    $('input[name^=\"export_include_\"]').change(function() {
        const checkbox = $(this);
        const checkboxName = checkbox.attr('name');
        const isChecked = checkbox.is(':checked');
        
        const labels = {
            'export_include_site_name': 'Include Site Name',
            'export_include_site_url': 'Include Site URL',
            'export_include_date': 'Include Date',
            'export_include_time': 'Include Time',
            'export_include_comment_status': 'Include Comment Status'
        };
        
        const label = labels[checkboxName] || checkboxName;
        const message = isChecked ? 
            `${label} selected` : 
            `${label} deselected`;
        
        const notificationType = isChecked ? 'success' : 'deselected';
        showNotificationBubble(message, notificationType, true);
        saveFilenamePreferences(true);
    });
    
    function saveExportStatusPreferences(isIndividualChange = false) {
        const data = {
            action: 'save_export_status_preferences',
            nonce: trCeiAjax.export_status_nonce,
            export_status_approved: $('input[name=\"status_approved\"]').is(':checked') ? '1' : '0',
            export_status_pending: $('input[name=\"status_pending\"]').is(':checked') ? '1' : '0',
            export_status_spam: $('input[name=\"status_spam\"]').is(':checked') ? '1' : '0',
            export_status_trash: $('input[name=\"status_trash\"]').is(':checked') ? '1' : '0'
        };
        
        $.post(trCeiAjax.ajaxurl, data)
            .done(function(response) {
                if (!isIndividualChange) {
                    showNotificationBubble('Export status preferences saved!', 'success');
                }
            })
            .fail(function(xhr, status, error) {
                showNotificationBubble('Failed to save export status preferences', 'error');
            });
    }
    
    function saveImportPreferences(isIndividualChange = false) {
        const data = {
            action: 'save_import_preferences',
            nonce: trCeiAjax.import_nonce,
            import_approved: $('input[name=\"import_approved\"]').is(':checked') ? '1' : '0',
            import_pending: $('input[name=\"import_pending\"]').is(':checked') ? '1' : '0',
            import_spam: $('input[name=\"import_spam\"]').is(':checked') ? '1' : '0',
            import_trash: $('input[name=\"import_trash\"]').is(':checked') ? '1' : '0'
        };
        
        $.post(trCeiAjax.ajaxurl, data)
            .done(function(response) {
                if (!isIndividualChange) {
                    showNotificationBubble('Import preferences saved!', 'success');
                }
            })
            .fail(function(xhr, status, error) {
                showNotificationBubble('Failed to save import preferences', 'error');
            });
    }
    
    function saveFilenamePreferences(isIndividualChange = false) {
        const data = {
            action: 'save_filename_preferences',
            nonce: trCeiAjax.filename_nonce,
            export_include_site_name: $('input[name=\"export_include_site_name\"]').is(':checked') ? '1' : '0',
            export_include_site_url: $('input[name=\"export_include_site_url\"]').is(':checked') ? '1' : '0',
            export_include_date: $('input[name=\"export_include_date\"]').is(':checked') ? '1' : '0',
            export_include_time: $('input[name=\"export_include_time\"]').is(':checked') ? '1' : '0',
            export_include_comment_status: $('input[name=\"export_include_comment_status\"]').is(':checked') ? '1' : '0'
        };
        
        $.post(trCeiAjax.ajaxurl, data)
            .done(function(response) {
                if (!isIndividualChange) {
                    showNotificationBubble('Filename preferences saved!', 'success');
                }
            })
            .fail(function(xhr, status, error) {
                showNotificationBubble('Failed to save filename preferences', 'error');
            });
    }
    
    function showNotificationBubble(message, type = 'success', centered = false, duration = 3000) {
        const formattedMessage = message.replace(/\\n/g, '<br>');
        const topOffset = getNextNotificationPosition();
        const centeredClass = centered ? 'centered' : '';
        
        const bubble = $(`
            <div class=\"cei-notification-bubble ${type} ${centeredClass}\" style=\"top: ${topOffset}px;\">
                ${formattedMessage}
            </div>
        `);
        
        $('body').append(bubble);
        addToQueue(bubble[0]);
        
        setTimeout(function() {
            bubble.addClass('show');
        }, 10);
        
        setTimeout(function() {
            bubble.removeClass('show');
            setTimeout(function() {
                removeFromQueue(bubble[0]);
                bubble.remove();
            }, 300);
        }, duration);
    }
    
    checkForNotifications();
    
    function checkForNotifications() {
        const urlParams = new URLSearchParams(window.location.search);
        
        if (urlParams.has('export_error')) {
            const errorType = urlParams.get('export_error');
            let message = '';
            
            switch(errorType) {
                case 'no_comments':
                    message = 'No comments found matching the selected criteria. Please check your selections and try again.';
                    break;
                default:
                    message = 'An error occurred during export. Please try again.';
            }
            
            showNotification(message, 'error');
        }
        
        if (urlParams.has('import_error')) {
            const errorType = urlParams.get('import_error');
            let message = '';
            
            switch(errorType) {
                case 'file_error':
                    message = 'Error uploading file. Please check your file and try again.';
                    break;
                case 'file_read_error':
                    message = 'Could not read the uploaded file. Please ensure it is a valid CSV file.';
                    break;
                case 'invalid_format':
                    message = 'Invalid CSV format. Required headers are missing from the file.';
                    break;
                case 'no_status_selected':
                    message = 'Please select at least one comment status to import.';
                    break;
                default:
                    message = 'An error occurred during import. Please try again.';
            }
            
            showNotification(message, 'error');
        }
        
        if (urlParams.has('import_success')) {
            const imported = urlParams.get('imported') || 0;
            const skipped = urlParams.get('skipped') || 0;
            const duplicatesSkipped = urlParams.get('duplicates_skipped') || 0;
            const statusSkipped = urlParams.get('status_skipped') || 0;
            const breakdown = decodeURIComponent(urlParams.get('breakdown') || '');
            
            let message = `Import completed successfully!<br>`;
            
            if (imported > 0) {
                message += `<strong>${imported}</strong> comments imported`;
                if (breakdown) {
                    message += `: ${breakdown}`;
                }
                message += `<br>`;
            }
            
            if (skipped > 0) {
                const skipDetails = [];
                if (duplicatesSkipped > 0) {
                    skipDetails.push(`${duplicatesSkipped} duplicates`);
                }
                if (statusSkipped > 0) {
                    skipDetails.push(`${statusSkipped} unselected statuses`);
                }
                
                message += `<strong>${skipped}</strong> comments skipped`;
                if (skipDetails.length > 0) {
                    message += ` (${skipDetails.join(', ')})`;
                }
            }
            
            showNotification(message, 'success', 8000);
        }
        
        if (urlParams.has('import_info')) {
            const infoType = urlParams.get('import_info');
            const skipped = urlParams.get('skipped') || 0;
            const duplicatesSkipped = urlParams.get('duplicates_skipped') || 0;
            const statusSkipped = urlParams.get('status_skipped') || 0;
            
            if (infoType === 'nothing_imported') {
                let message = 'Import completed - no comments were imported.<br>';
                
                if (skipped > 0) {
                    const skipDetails = [];
                    if (duplicatesSkipped > 0) {
                        skipDetails.push(`${duplicatesSkipped} duplicates`);
                    }
                    if (statusSkipped > 0) {
                        skipDetails.push(`${statusSkipped} unselected statuses`);
                    }
                    
                    message += `<strong>${skipped}</strong> comments were skipped`;
                    if (skipDetails.length > 0) {
                        message += ` (${skipDetails.join(', ')})`;
                    }
                    message += '.';
                } else {
                    message += '<br>The file may not contain comments matching your selected statuses for import.';
                }
                
                showNotification(message, 'info', 6000);
            }
        }
        
        if (urlParams.has('export_error') || urlParams.has('import_error') || urlParams.has('import_success') || urlParams.has('import_info')) {
            const cleanUrl = window.location.href.split('?')[0] + '?page=comments-export-import';
            window.history.replaceState({}, document.title, cleanUrl);
        }
    }
    
    function showNotification(message, type = 'info', duration = 5000) {
        const formattedMessage = message.replace(/\\n/g, '<br>');
        const topOffset = getNextNotificationPosition();
        
        const notification = $(`
            <div class=\"cei-notification ${type}\" style=\"top: ${topOffset}px;\">
                <button class=\"cei-notification-close\" aria-label=\"Close\">&times;</button>
                <div class=\"cei-notification-content\">${formattedMessage}</div>
            </div>
        `);
        
        $('body').append(notification);
        addToQueue(notification[0]);
        
        notification.find('.cei-notification-close').click(function() {
            closeNotification(notification);
        });
        
        setTimeout(function() {
            closeNotification(notification);
        }, duration);
    }
    
    function closeNotification(notification) {
        notification.addClass('removing');
        setTimeout(function() {
            removeFromQueue(notification[0]);
            notification.remove();
        }, 300);
    }
    
    $('form').on('submit', function(e) {
        const form = $(this);
        
        if (form.find('input[name=\"export_comments\"]').length) {
            const checkedStatuses = form.find('input[name^=\"status_\"]:checked').length;
            if (checkedStatuses === 0) {
                e.preventDefault();
                showNotification('Please select at least one comment status to export.', 'error');
                return false;
            }
        }
        
        if (form.find('input[name=\"import_comments\"]').length) {
            const fileInput = form.find('input[name=\"import_file\"]');
            const checkedMappings = form.find('input[name^=\"import_\"]:checked').length;
            
            if (!fileInput.val()) {
                e.preventDefault();
                showNotification('Please select a CSV file to import.', 'error');
                return false;
            }
            
            if (checkedMappings === 0) {
                e.preventDefault();
                showNotification('Please select at least one status mapping for import.', 'error');
                return false;
            }
            
            const fileName = fileInput.val().toLowerCase();
            if (!fileName.endsWith('.csv')) {
                e.preventDefault();
                showNotification('Please select a valid CSV file.', 'error');
                return false;
            }
        }
    });
    
    $('form').on('submit', function() {
        const submitButton = $(this).find('input[type=\"submit\"]');
        const originalValue = submitButton.val();
        
        submitButton.val('Processing...').prop('disabled', true);
        
        setTimeout(function() {
            submitButton.val(originalValue).prop('disabled', false);
        }, 10000);
    });
});
