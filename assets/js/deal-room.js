jQuery(document).ready(function($) {
    // 1. Modal Logic
    var expectedCaptchaResult = 0;

    window.openDealRoomModal = function() {
        $('#rpa-captcha-answer').val('');
        
        // Smart Captcha Logic: 
        // Handles both new HTML (with spans) and old cached HTML (hardcoded text)
        var $label = $('.rpa-captcha-group label');
        var $num1Span = $('#rpa-captcha-num1');
        var $num2Span = $('#rpa-captcha-num2');

        if ($num1Span.length > 0 && $num2Span.length > 0) {
            // New HTML is loaded: Generate fresh numbers
            var num1 = Math.floor(Math.random() * 10) + 1;
            var num2 = Math.floor(Math.random() * 10) + 1;
            expectedCaptchaResult = num1 + num2;
            $num1Span.text(num1);
            $num2Span.text(num2);
        } else {
            // Old cached HTML is loaded: Extract the numbers from the text
            var text = $label.text();
            var match = text.match(/(\d+)\s*\+\s*(\d+)/);
            if (match) {
                expectedCaptchaResult = parseInt(match[1], 10) + parseInt(match[2], 10);
            }
        }

        $('#rpaDealRoomModal').fadeIn(300);
        resizeCanvas();
    };

    window.closeDealRoomModal = function() {
        $('#rpaDealRoomModal').fadeOut(300);
    };

    // Close on outside click
    $(window).on('click', function(e) {
        if ($(e.target).is('#rpaDealRoomModal')) {
            closeDealRoomModal();
        }
    });

    // 2. Signature Pad Logic
    var canvas = document.getElementById('rpaSignatureCanvas');
    var signaturePad;

    if (canvas) {
        signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)', // Set white background for proper PDF rendering
            penColor: 'rgb(0, 0, 0)'
        });

        function resizeCanvas() {
            var ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePad.clear();
        }

        window.clearSignature = function() {
            signaturePad.clear();
        };

        window.addEventListener("resize", resizeCanvas);
    }

    // 3. Form Submission
    $('#rpaDealRoomForm').on('submit', function(e) {
        e.preventDefault();

        // 1. Validate signature
        if (signaturePad && signaturePad.isEmpty()) {
            alert('Please provide a signature first.');
            return;
        }

        // 2. Validate math captcha locally
        var userAnswer = parseInt($('#rpa-captcha-answer').val(), 10);
        if (isNaN(userAnswer) || userAnswer !== expectedCaptchaResult) {
            alert('Incorrect Math Captcha answer. Please try again.');
            $('#rpa-captcha-answer').val('').focus();
            
            // Optionally regenerate the captcha only if the new HTML is present
            if ($('#rpa-captcha-num1').length > 0) {
                var num1 = Math.floor(Math.random() * 10) + 1;
                var num2 = Math.floor(Math.random() * 10) + 1;
                expectedCaptchaResult = num1 + num2;
                $('#rpa-captcha-num1').text(num1);
                $('#rpa-captcha-num2').text(num2);
            }
            
            return;
        }

        var signatureData = signaturePad.toDataURL();
        $('#rpaSignatureData').val(signatureData);

        // Use FormData instead of serialize() to avoid URL-encoding the large base64 signature
        var formData = new FormData(this);

        var $submitBtn = $(this).find('.rpa-submit-btn');
        var $msg = $(this).find('.rpa-form-msg');
        var $fieldsContainer = $(this).find('.rpa-form-fields-container');

        $submitBtn.prop('disabled', true).text('Submitting...');
        $msg.text('').removeClass('error success');

        $.ajax({
            url: rpaDealRoom.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    // Set cookie in JS just to be safe
                    var projectId = $('input[name="project_id"]').val();
                    if (projectId && res.data.token) {
                        var expiryDate = new Date();
                        expiryDate.setTime(expiryDate.getTime() + (365 * 24 * 60 * 60 * 1000));
                        document.cookie = "rpa_deal_access_" + projectId + "=" + res.data.token + "; expires=" + expiryDate.toUTCString() + "; path=/";
                    }

                    $fieldsContainer.hide();
                    $('.rpa-ca-scrollable-text, .rpa-ca-text, .rpa-ca-title').hide();

                    var successHtml = '<div style="text-align: center; padding: 40px 30px;">' +
                        '<div style="width: 70px; height: 70px; border-radius: 50%; background: linear-gradient(135deg, #c09e6c, #d4b88a); margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(192, 158, 108, 0.4);">' +
                            '<svg viewBox="0 0 24 24" width="36" height="36" fill="#fff"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>' +
                        '</div>' +
                        '<h3 style="color: #1a2f55; font-size: 22px; font-weight: 700; margin: 0 0 8px;">Success!</h3>' +
                        '<p style="color: #1a2f55; opacity: 0.7; font-size: 15px; margin: 0 0 20px; line-height: 1.5;">' + res.data.message + '</p>' +
                        '<div style="display: inline-block; padding: 8px 20px; background: rgba(26, 47, 85, 0.08); border-radius: 20px; color: #1a2f55; font-size: 13px; font-weight: 600;">Redirecting...</div>' +
                    '</div>';
                    $msg.html(successHtml);

                    // Reload the page after 2.5 seconds to show the deal room content
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);

                } else {
                    var msg = res.data.message || 'An error occurred.';
                    alert(msg);
                    $msg.css('color', 'red').text(msg);
                    $submitBtn.prop('disabled', false).text('Submit');
                }
            },
            error: function(xhr) {
                var errMsg = 'Server error. Please try again.';
                try {
                    var json = JSON.parse(xhr.responseText);
                    if (json && json.data && json.data.message) {
                        errMsg = json.data.message;
                    }
                } catch(ex) {}
                $msg.css('color', 'red').text(errMsg);
                $submitBtn.prop('disabled', false).text('Submit');
            }
        });
    });

    // 4. Frontend Document Manager Logic
    var $docManager = $('.rpa-frontend-doc-manager');
    if ($docManager.length) {
        var projectId = $docManager.data('project-id');
        var docs = $docManager.data('docs') || [];
        if (typeof docs === 'string') {
            try { docs = JSON.parse(docs); } catch(e) { docs = []; }
        }

        var currentPath = [];
        var selectedItems = [];
        var viewMode = 'grid'; // 'grid' or 'list'

        function formatBytes(bytes, decimals = 2) {
            if (!+bytes) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
        }

        function getFileIcon(item) {
            if (item.type === 'folder') {
                return '<div class="rpa-doc-icon folder"><svg viewBox="0 0 24 24" fill="#F4D160" width="32" height="32"><path d="M10 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"/></svg></div>';
            } else {
                var ext = item.name.split('.').pop().toLowerCase();
                if (['jpg', 'jpeg', 'png', 'gif', 'webp'].indexOf(ext) !== -1) {
                    var imgUrl = item.url || item.thumbnail;
                    if (imgUrl) {
                        return '<div class="rpa-doc-preview"><img src="' + imgUrl + '" alt="' + item.name + '"></div>';
                    }
                } else if (ext === 'pdf') {
                    // PDF icon matching the second screenshot (white outline on red background)
                    return '<div class="rpa-doc-icon pdf"><svg viewBox="0 0 24 24" fill="#dc2626" width="32" height="32"><rect width="24" height="24" rx="4" fill="#dc2626"/><path d="M11.5 13.5c-.5-1.5-1.5-2.5-1.5-2.5s-1-1.5-1-2c0-.5.5-1 1-1s1 .5 1 1c0 .5-.5 1.5-.5 1.5s1 2 1.5 2.5c1.5-.5 3.5-.5 4.5.5.5.5.5 1 0 1.5-.5.5-1.5 0-2.5-1-1 1-2.5 1.5-3.5 1.5-.5 0-1.5-.5-1.5-1 0-.5 1-1 2.5-1zm0 0c.5 1 1 2 1.5 3 .5 1 1.5 1.5 2 1.5s.5-.5 0-1c-.5-.5-1.5-1-2.5-1.5-.5-1-1-2-1-3z" fill="none" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></div>';
                }
                return '<div class="rpa-doc-icon file"><svg viewBox="0 0 24 24" fill="#9ca3af" width="32" height="32"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg></div>';
            }
        }

        function renderDocs() {
            var $grid = $('#rpa-frontend-grid');
            $grid.empty();
            
            if (viewMode === 'list') {
                $grid.addClass('list-view');
            } else {
                $grid.removeClass('list-view');
            }
            
            var currentItems = docs;
            for (var i = 0; i < currentPath.length; i++) {
                var pathIdx = currentPath[i];
                if (currentItems[pathIdx]) {
                    currentItems = currentItems[pathIdx].children || [];
                }
            }

            // Sorting
            var sortVal = $('#rpa-frontend-sort').val();
            currentItems.sort(function(a, b) {
                if (sortVal === 'name') {
                    return a.name.localeCompare(b.name);
                }
                return 0; // default/date if not implemented
            });

            if (currentItems.length === 0) {
                $grid.html('<p style="padding: 20px; color: #666;">No items found.</p>');
            } else {
                if (viewMode === 'list') {
                    var $headerRow = $('<div class="rpa-doc-list-header"></div>');
                    $headerRow.append('<div class="rpa-doc-list-col-check"></div>');
                    $headerRow.append('<div class="rpa-doc-list-col-name" style="padding-left: 36px;">Name</div>');
                    $headerRow.append('<div class="rpa-doc-list-col-count">#</div>');
                    $headerRow.append('<div class="rpa-doc-list-col-size">Size</div>');
                    $headerRow.append('<div class="rpa-doc-list-col-date">Uploaded</div>');
                    $headerRow.append('<div class="rpa-doc-list-col-creator">Creator</div>');
                    $grid.append($headerRow);
                }

                $.each(currentItems, function(index, item) {
                    var isSelected = selectedItems.indexOf(item.id) !== -1;
                    var $item = $('<div class="rpa-doc-item ' + (isSelected ? 'selected' : '') + '"></div>');
                    $item.data('index', index);
                    $item.data('item', item);

                    var $checkbox = $('<input type="checkbox" class="rpa-doc-checkbox" value="' + item.id + '">');
                    $checkbox.prop('checked', isSelected);

                    var sizeText = '';
                    if (item.type === 'folder') {
                        sizeText = '';
                    } else {
                        // size can be string with "MB" already, or bytes
                        if (item.size) {
                            var parsedSize = parseFloat(item.size);
                            if (isNaN(parsedSize)) {
                                sizeText = item.size;
                            } else {
                                // If it's a number, assume bytes
                                // if it's stored as number but it's meant to be KB/MB, we just format it as bytes for now.
                                // Some plugins save file size in bytes.
                                // If the original item.size is a string with "MB" or "KB", isNaN will be true above.
                                // If we reach here, it's just numbers. 
                                // But if item.size was already formatted string, isNaN(parseFloat(item.size)) is actually false!
                                // For example parseFloat("19 MB") is 19.
                                // Let's check if the string contains any text.
                                 if (typeof item.size === 'string' && item.size.match(/[a-zA-Z]/)) {
                                        sizeText = item.size;
                                    } else {
                                        // Some size might be KB already. If the number is small, it's probably bytes. Let's just format it.
                                        sizeText = formatBytes(parsedSize, 0);
                                    }
                            }
                        } else {
                            sizeText = 'Unknown size';
                        }
                    }

                    if (viewMode === 'grid') {
                        var $topArea = $('<div class="rpa-doc-item-top"></div>');
                        $topArea.append($checkbox);
                        
                        var iconHtml = getFileIcon(item);
                        // Make icon bigger for grid view
                        iconHtml = iconHtml.replace('width="32"', 'width="64"').replace('height="32"', 'height="64"');
                        $topArea.append(iconHtml);

                        var $bottomArea = $('<div class="rpa-doc-item-bottom"></div>');
                        $bottomArea.append('<div class="rpa-doc-title" title="' + item.name + '">' + item.name + '</div>');
                        
                        var gridMetaText = item.type === 'folder' ? ((item.children ? item.children.length : 0) + ' items') : sizeText;
                        $bottomArea.append('<div class="rpa-doc-meta">' + gridMetaText + '</div>');

                        $item.append($topArea);
                        $item.append($bottomArea);
                    } else {
                        $item.append($('<div class="rpa-doc-list-col-check"></div>').append($checkbox));
                        
                        var $nameCol = $('<div class="rpa-doc-list-col-name"></div>');
                        
                        var listIconHtml = getFileIcon(item);
                        listIconHtml = listIconHtml.replace('width="32"', 'width="24"').replace('height="32"', 'height="24"');
                        $nameCol.append(listIconHtml);
                        
                        $nameCol.append('<span class="rpa-doc-list-title">' + item.name + '</span>');
                        $item.append($nameCol);

                        var countText = item.type === 'folder' ? '(' + (item.children ? item.children.length : 0) + ')' : '';
                        $item.append('<div class="rpa-doc-list-col-count">' + countText + '</div>');
                        $item.append('<div class="rpa-doc-list-col-size">' + sizeText + '</div>');
                        
                        // Added Uploaded and Creator for List View if they exist, otherwise empty
                        var uploadedDate = item.date || 'N/A';
                        var creatorName = item.creator || 'N/A';
                        $item.append('<div class="rpa-doc-list-col-date">' + uploadedDate + '</div>');
                        $item.append('<div class="rpa-doc-list-col-creator">' + creatorName + '</div>');
                    }

                    $grid.append($item);
                });
            }

            renderBreadcrumbs();
            updateDownloadBtn();
            updateSelectAllState();
        }

        function renderBreadcrumbs() {
            var $bc = $('#rpa-frontend-breadcrumbs');
            $bc.empty();
            
            var $home = $('<span style="cursor:pointer; color: #374151;">Home</span>');
            $home.on('click', function() {
                currentPath = [];
                selectedItems = [];
                renderDocs();
            });
            $bc.append($home);

            var currentItems = docs;
            for (var i = 0; i < currentPath.length; i++) {
                var pathIdx = currentPath[i];
                var folder = currentItems[pathIdx];
                $bc.append(' / ');
                var $link = $('<span style="cursor:pointer; color: #374151;">' + folder.name + '</span>');
                
                (function(depth) {
                    $link.on('click', function() {
                        currentPath = currentPath.slice(0, depth + 1);
                        selectedItems = [];
                        renderDocs();
                    });
                })(i);
                
                $bc.append($link);
                currentItems = folder.children || [];
            }
            
            if (currentPath.length === 0) {
                $bc.hide();
            } else {
                $bc.show();
            }
        }

        function updateSelectAllState() {
            var totalCheckable = $('#rpa-frontend-grid .rpa-doc-checkbox').length;
            var checkedCount = $('#rpa-frontend-grid .rpa-doc-checkbox:checked').length;
            var $selectAll = $('#rpa-frontend-select-all');
            
            if (totalCheckable === 0) {
                $selectAll.prop('checked', false);
                $selectAll.prop('indeterminate', false);
            } else if (checkedCount === totalCheckable) {
                $selectAll.prop('checked', true);
                $selectAll.prop('indeterminate', false);
            } else if (checkedCount > 0) {
                $selectAll.prop('checked', false);
                $selectAll.prop('indeterminate', true);
            } else {
                $selectAll.prop('checked', false);
                $selectAll.prop('indeterminate', false);
            }
        }

        $('.rpa-view-btn').on('click', function() {
            $('.rpa-view-btn').removeClass('active');
            $(this).addClass('active');
            if ($(this).hasClass('rpa-view-list')) {
                viewMode = 'list';
            } else {
                viewMode = 'grid';
            }
            renderDocs();
        });

        $('#rpa-frontend-sort').on('change', function() {
            renderDocs();
        });

        $('#rpa-frontend-select-all').on('change', function() {
            var isChecked = $(this).prop('checked');
            $('#rpa-frontend-grid .rpa-doc-checkbox').each(function() {
                var val = $(this).val();
                var idx = selectedItems.indexOf(val);
                if (isChecked && idx === -1) {
                    selectedItems.push(val);
                } else if (!isChecked && idx !== -1) {
                    selectedItems.splice(idx, 1);
                }
            });
            renderDocs();
        });

        function updateDownloadBtn() {
            var count = selectedItems.length;
            $('#rpa-frontend-selected-count').text(count);
            $('#rpa-frontend-download').prop('disabled', count === 0);
        }

        $('#rpa-frontend-grid').on('click', '.rpa-doc-item', function(e) {
            if ($(e.target).closest('input[type="checkbox"]').length) {
                return; // Handled by checkbox change
            }

            var item = $(this).data('item');
            var index = $(this).data('index');

            if (item.type === 'folder') {
                currentPath.push(index);
                selectedItems = []; // Clear selection when entering folder
                $('#rpa-frontend-sort').val('name'); // Reset sort
                renderDocs();
            } else {
                var $cb = $(this).find('.rpa-doc-checkbox');
                $cb.prop('checked', !$cb.prop('checked')).trigger('change');
            }
        });

        $('#rpa-frontend-grid').on('change', '.rpa-doc-checkbox', function(e) {
            e.stopPropagation();
            var val = $(this).val();
            if ($(this).is(':checked')) {
                if (selectedItems.indexOf(val) === -1) selectedItems.push(val);
                $(this).closest('.rpa-doc-item').addClass('selected');
            } else {
                var idx = selectedItems.indexOf(val);
                if (idx !== -1) selectedItems.splice(idx, 1);
                $(this).closest('.rpa-doc-item').removeClass('selected');
            }
            updateDownloadBtn();
            updateSelectAllState();
        });

        $('#rpa-frontend-download').on('click', function() {
            if (selectedItems.length === 0) return;

            var $btn = $(this);
            var originalText = $btn.text();
            $btn.prop('disabled', true).text('Preparing ZIP...');

            $.ajax({
                url: rpaDealRoom.ajax_url,
                type: 'POST',
                data: {
                    action: 'rpa_download_deal_docs',
                    project_id: projectId,
                    security: rpaDealRoom.nonce,
                    file_ids: selectedItems
                },
                success: function(res) {
                    if (res.success && res.data.zip_url) {
                        var a = document.createElement('a');
                        a.href = res.data.zip_url;
                        a.target = '_blank'; // Fallback for cross-origin or unsupported download attr
                        // Extract filename from URL
                        var filename = res.data.zip_url.substring(res.data.zip_url.lastIndexOf('/') + 1);
                        a.download = filename || 'download';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        
                        $btn.text('Downloaded!');
                        setTimeout(function() {
                            $btn.prop('disabled', false).text(originalText);
                            selectedItems = [];
                            renderDocs();
                        }, 2000);
                    } else {
                        alert(res.data.message || 'Error generating ZIP.');
                        $btn.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    alert('Server error.');
                    $btn.prop('disabled', false).text(originalText);
                }
            });
        });

        renderDocs();
    }
});