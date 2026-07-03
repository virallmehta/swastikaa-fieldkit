/**
 * SwastiNexus Fields Studio Admin JS
 * Handles: Image, File, Gallery media pickers + Repeater + Group sync
 */
(function($) {
    'use strict';

    // ── Image field ──────────────────────────────────────────────────────────

    $(document).on('click', '.snfs-image-upload-btn', function(e) {
        e.preventDefault();
        var btn       = $(this);
        var fieldId   = btn.data('field');
        var container = $('#' + btn.data('preview'));
        var frame = wp.media({
            title:    snfsAdmin.i18n.selectImage,
            button:   { text: snfsAdmin.i18n.useImage },
            multiple: false,
            library:  { type: 'image' }
        });
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $('#' + fieldId).val(attachment.id);
            container.find('.snfs-image-preview').html(
                '<img src="' + attachment.sizes.thumbnail.url + '" style="max-width:200px;max-height:200px;display:block;" />'
            );
            btn.text(snfsAdmin.i18n.changeImage);
            if (!container.find('.snfs-image-remove-btn').length) {
                btn.after('<button type="button" class="button snfs-image-remove-btn" data-field="' + fieldId + '" data-preview="' + btn.data('preview') + '" style="margin-left:4px;">' + snfsAdmin.i18n.remove + '</button>');
            }
        });
        frame.open();
    });

    $(document).on('click', '.snfs-image-remove-btn', function(e) {
        e.preventDefault();
        var btn       = $(this);
        var fieldId   = btn.data('field');
        var container = $('#' + btn.data('preview'));
        $('#' + fieldId).val('');
        container.find('.snfs-image-preview').empty();
        container.find('.snfs-image-upload-btn').text(snfsAdmin.i18n.selectImage);
        btn.remove();
    });

    // ── File field ───────────────────────────────────────────────────────────

    $(document).on('click', '.snfs-file-upload-btn', function(e) {
        e.preventDefault();
        var btn       = $(this);
        var fieldId   = btn.data('field');
        var container = $('#' + btn.data('preview'));
        var frame = wp.media({
            title:    snfsAdmin.i18n.selectFile,
            button:   { text: snfsAdmin.i18n.useFile },
            multiple: false
        });
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $('#' + fieldId).val(attachment.id);
            container.find('.snfs-file-info').html(
                '<a href="' + attachment.url + '" target="_blank">' + attachment.filename + '</a>'
            );
            btn.text(snfsAdmin.i18n.changeFile);
            if (!container.find('.snfs-file-remove-btn').length) {
                btn.after('<button type="button" class="button snfs-file-remove-btn" data-field="' + fieldId + '" data-preview="' + btn.data('preview') + '" style="margin-left:4px;">' + snfsAdmin.i18n.remove + '</button>');
            }
        });
        frame.open();
    });

    $(document).on('click', '.snfs-file-remove-btn', function(e) {
        e.preventDefault();
        var btn       = $(this);
        var fieldId   = btn.data('field');
        var container = $('#' + btn.data('preview'));
        $('#' + fieldId).val('');
        container.find('.snfs-file-info').empty();
        container.find('.snfs-file-upload-btn').text(snfsAdmin.i18n.selectFile);
        btn.remove();
    });

    // ── Gallery field ────────────────────────────────────────────────────────

    $(document).on('click', '.snfs-gallery-add-btn', function(e) {
        e.preventDefault();
        var btn       = $(this);
        var fieldId   = btn.data('field');
        var container = $('#' + btn.data('container'));
        var frame = wp.media({
            title:    snfsAdmin.i18n.addImages,
            button:   { text: snfsAdmin.i18n.addImages },
            multiple: true,
            library:  { type: 'image' }
        });
        frame.on('select', function() {
            var selection  = frame.state().get('selection');
            var existingIds = $('#' + fieldId).val()
                ? $('#' + fieldId).val().split(',').map(Number).filter(Boolean)
                : [];
            selection.each(function(attachment) {
                var a = attachment.toJSON();
                if (existingIds.indexOf(a.id) === -1) {
                    existingIds.push(a.id);
                    var thumbUrl = a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url;
                    container.find('.snfs-gallery-images').append(
                        '<div class="snfs-gallery-item" data-id="' + a.id + '" style="position:relative;">' +
                        '<img src="' + thumbUrl + '" style="width:80px;height:80px;object-fit:cover;display:block;" />' +
                        '<button type="button" class="snfs-gallery-remove" data-id="' + a.id + '" style="position:absolute;top:2px;right:2px;padding:0 4px;cursor:pointer;">&times;</button>' +
                        '</div>'
                    );
                }
            });
            $('#' + fieldId).val(existingIds.join(','));
            container.find('.snfs-repeater-empty').hide();
        });
        frame.open();
    });

    $(document).on('click', '.snfs-gallery-remove', function(e) {
        e.preventDefault();
        var removeId  = parseInt($(this).data('id'));
        var container = $(this).closest('.snfs-gallery-field');
        var fieldId   = container.find('input[type=hidden]').attr('id');
        $(this).closest('.snfs-gallery-item').remove();
        var ids = $('#' + fieldId).val()
            ? $('#' + fieldId).val().split(',').map(Number).filter(function(id) { return id !== removeId; })
            : [];
        $('#' + fieldId).val(ids.join(','));
    });

    // ── Repeater field ───────────────────────────────────────────────────────

    function sfRepeaterSync(container) {
        var rows  = [];
        container.find('.snfs-repeater-row').each(function() {
            var row = {};
            $(this).find('.snfs-repeater-subfield').each(function() {
                row[$(this).data('key')] = $(this).val();
            });
            rows.push(row);
        });
        container.find('.snfs-repeater-input').val(JSON.stringify(rows));
    }

    $(document).on('click', '.snfs-repeater-add', function(e) {
        e.preventDefault();
        var container  = $(this).closest('.snfs-repeater-field');
        var rowsWrap   = container.find('.snfs-repeater-rows');
        var index      = rowsWrap.find('.snfs-repeater-row').length;
        var fieldId    = container.data('field');
        // Build a blank row by cloning the hidden template if present, else from existing row
        var firstRow   = rowsWrap.find('.snfs-repeater-row').first();
        if (firstRow.length) {
            var newRow = firstRow.clone();
            newRow.find('.snfs-repeater-subfield').val('');
            newRow.find('.snfs-repeater-row-handle').text('↕ Row ' + (index + 1));
            rowsWrap.append(newRow);
        } else {
            // No existing rows — render a minimal placeholder row
            rowsWrap.find('.snfs-repeater-empty').hide();
            rowsWrap.append(
                '<div class="snfs-repeater-row" style="border:1px solid #ddd;padding:12px;margin-bottom:8px;position:relative;">' +
                '<div class="snfs-repeater-row-handle" style="cursor:move;color:#999;margin-bottom:8px;">↕ Row ' + (index + 1) + '</div>' +
                '<button type="button" class="snfs-repeater-remove button-link" style="position:absolute;top:8px;right:8px;color:#b32d2e;">&times; Remove</button>' +
                '<div class="snfs-repeater-row-fields"><p style="color:#999;">Configure sub-fields to see inputs here.</p></div>' +
                '</div>'
            );
        }
        sfRepeaterSync(container);
    });

    $(document).on('click', '.snfs-repeater-remove', function(e) {
        e.preventDefault();
        var container = $(this).closest('.snfs-repeater-field');
        $(this).closest('.snfs-repeater-row').remove();
        sfRepeaterSync(container);
        if (container.find('.snfs-repeater-row').length === 0) {
            container.find('.snfs-repeater-empty').show();
        }
    });

    $(document).on('change input', '.snfs-repeater-subfield', function() {
        sfRepeaterSync($(this).closest('.snfs-repeater-field'));
    });

    // ── Group field ──────────────────────────────────────────────────────────

    function sfGroupSync(groupInput) {
        var container = groupInput.closest('.snfs-group-field');
        var data      = {};
        container.find('.snfs-group-subfield').each(function() {
            data[$(this).data('key')] = $(this).val();
        });
        groupInput.val(JSON.stringify(data));
    }

    $(document).on('change input', '.snfs-group-subfield', function() {
        sfGroupSync($(this).closest('.snfs-group-field').find('.snfs-group-input'));
    });

})(jQuery);
