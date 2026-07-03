/**
 * SwastiNexus Fields Studio Admin JS
 */
jQuery(function ($) {
    'use strict';

    var SNFS = {

        init: function () {
            this.bindFieldBuilder();
            this.bindToggle();
            this.bindDuplicateField();
            this.bindDynamicLabel();
            this.bindAutoSlug();
            this.makeSortable();
            this.initLocationRules();
        },

        // ── FIELD BUILDER ──────────────────────────────────────────────────────

        bindFieldBuilder: function () {
            $(document).on('click', '#snfs-add-field', function (e) {
                e.preventDefault();
                SNFS.addField();
            });

            $(document).on('click', '.snfs-field-remove', function (e) {
                e.preventDefault();
                if ( confirm('Remove this field?') ) {
                    $(this).closest('.snfs-field-row').remove();
                    SNFS.checkEmpty();
                    SNFS.reindex();
                }
            });
        },

        addField: function () {
            var tmpl = document.getElementById('snfs-field-row-template');
            if (!tmpl) return;

            var index = $('.snfs-field-row').length;
            var html  = tmpl.innerHTML.replace(/__INDEX__/g, index);

            $('.snfs-placeholder').remove();
            $('#snfs-fields-container').append(html);
        },

        checkEmpty: function () {
            if ($('.snfs-field-row').length === 0) {
                $('#snfs-fields-container').html(
                    '<div class="snfs-placeholder"><span class="dashicons dashicons-feedback" style="font-size:32px;color:#c3c4c7;"></span><p>No fields yet. Click <strong>Add Field</strong> to get started.</p></div>'
                );
            }
        },

        reindex: function () {
            $('.snfs-field-row').each(function (i) {
                $(this).attr('data-index', i);
                $(this).find('[name]').each(function () {
                    this.name = this.name.replace(/snfs_fields\[\d+\]/, 'snfs_fields[' + i + ']');
                });
            });
        },

        // ── TOGGLE COLLAPSE ────────────────────────────────────────────────────

        bindToggle: function () {
            $(document).on('click', '.snfs-field-toggle', function (e) {
                e.preventDefault();
                var $row  = $(this).closest('.snfs-field-row');
                var $body = $row.find('.snfs-field-body');
                var $icon = $(this).find('.dashicons');

                $body.slideToggle(150);
                $icon.toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
            });
        },

        // ── DUPLICATE ──────────────────────────────────────────────────────────

        bindDuplicateField: function () {
            $(document).on('click', '.snfs-field-duplicate', function (e) {
                e.preventDefault();
                var $original = $(this).closest('.snfs-field-row');
                var $clone    = $original.clone(false);
                var index     = $('.snfs-field-row').length;

                $clone.attr('data-index', index);
                $clone.find('[name]').each(function () {
                    this.name = this.name.replace(/snfs_fields\[\d+\]/, 'snfs_fields[' + index + ']');
                });

                // Append and re-slug the name field
                $original.after($clone);
                var $nameInput = $clone.find('.snfs-field-name');
                $nameInput.val( $nameInput.val() + '_copy' );
            });
        },

        // ── DYNAMIC LABEL → TITLE ────────────────────────────────────────────

        bindDynamicLabel: function () {
            $(document).on('input', '.snfs-field-label', function () {
                var label = $(this).val() || 'New Field';
                $(this).closest('.snfs-field-row').find('.snfs-field-title').text(label);
            });
        },

        // ── AUTO SLUG FROM LABEL ─────────────────────────────────────────────

        bindAutoSlug: function () {
            $(document).on('input', '.snfs-field-label', function () {
                var $row  = $(this).closest('.snfs-field-row');
                var $name = $row.find('.snfs-field-name');

                // Only auto-fill if name is empty (user hasn't manually typed)
                if ($name.data('manual')) return;

                var slug = $(this).val()
                    .toLowerCase()
                    .replace(/[^a-z0-9\s_]/g, '')
                    .trim()
                    .replace(/\s+/g, '_');

                $name.val(slug);
            });

            // Mark as manually edited
            $(document).on('input', '.snfs-field-name', function () {
                $(this).data('manual', $(this).val() !== '');
            });
        },

        // ── SORTABLE ─────────────────────────────────────────────────────────

        makeSortable: function () {
            if ($.fn.sortable) {
                $('#snfs-fields-container').sortable({
                    handle: '.snfs-field-handle',
                    placeholder: 'snfs-sortable-placeholder',
                    tolerance: 'pointer',
                    update: function () {
                        SNFS.reindex();
                    },
                });
            }
        },

        // ── LOCATION RULES ────────────────────────────────────────────────────

        initLocationRules: function () {
            // Restore saved values into rule dropdowns
            var rawJSON = $('#snfs-location-rules-json').val();
            if (rawJSON) {
                try {
                    var saved = JSON.parse(rawJSON);
                    $('.snfs-location-rule').each(function (i) {
                        var rule = saved[i];
                        if (!rule) return;
                        var $el = $(this);
                        $el.find('.snfs-rule-type').val(rule.type);
                        $el.find('.snfs-rule-operator').val(rule.operator);
                        SNFS.populateRuleValues($el, rule.type, rule.value);
                    });
                } catch (e) { /* ignore */ }
            }

            // Add rule
            $(document).on('click', '#snfs-add-location-rule', function (e) {
                e.preventDefault();
                SNFS.addLocationRule();
            });

            // Remove rule
            $(document).on('click', '.snfs-rule-remove', function (e) {
                e.preventDefault();
                $(this).closest('.snfs-location-rule').remove();
                SNFS.saveRulesJSON();
            });

            // Type changed → repopulate values
            $(document).on('change', '.snfs-rule-type', function () {
                var $rule = $(this).closest('.snfs-location-rule');
                SNFS.populateRuleValues($rule, $(this).val(), '');
                SNFS.saveRulesJSON();
            });

            $(document).on('change', '.snfs-rule-operator, .snfs-rule-value', function () {
                SNFS.saveRulesJSON();
            });
        },

        addLocationRule: function () {
            var tmpl = document.getElementById('snfs-location-rule-template');
            if (!tmpl) return;

            var index = $('.snfs-location-rule').length;
            var html  = tmpl.innerHTML.replace(/__RULE_INDEX__/g, index);

            $('.snfs-rules-placeholder').remove();
            $('#snfs-location-rules-container').append(html);

            var $new = $('.snfs-location-rule:last');
            SNFS.populateRuleValues($new, 'post_type', '');
            SNFS.saveRulesJSON();
        },

        populateRuleValues: function ($rule, type, selectedValue) {
            var $select = $rule.find('.snfs-rule-value');
            $select.empty();

            var options = {};
            var data    = window.sfLocationDataSet || {};

            if (type === 'post_type') {
                options = data.postTypes || {};
            } else if (type === 'taxonomy') {
                options = data.taxonomies || {};
            } else if (type === 'user_profile') {
                options = { all: 'All Users' };
            }

            $.each(options, function (key, label) {
                var $opt = $('<option>').val(key).text(label);
                if (key === selectedValue) $opt.prop('selected', true);
                $select.append($opt);
            });

            // Default to first if nothing selected
            if (!selectedValue) {
                $select.find('option:first').prop('selected', true);
            }
        },


        saveRulesJSON: function () {
            var rules = [];
            $('.snfs-location-rule').each(function () {
                rules.push({
                    type:     $(this).find('.snfs-rule-type').val(),
                    operator: $(this).find('.snfs-rule-operator').val(),
                    value:    $(this).find('.snfs-rule-value').val(),
                });
            });
            $('#snfs-location-rules-json').val(JSON.stringify(rules));
        },

        // Serialize all field rows to the hidden JSON input before form submit
        serializeFieldsToJSON: function () {
            var choiceTypes = ['checkbox', 'radio', 'select'];
            var rangeTypes  = ['range', 'number'];
            var fields = [];
            $('.snfs-field-row').each(function () {
                var $row  = $(this);
                var type  = $row.find('.snfs-field-type').val() || 'text';
                var field = {
                    label:        $row.find('.snfs-field-label').val()              || '',
                    name:         $row.find('.snfs-field-name').val()               || '',
                    type:         type,
                    required:     $row.find('.snfs-field-required').is(':checked')  ? 1 : 0,
                    'default':    $row.find('.snfs-field-default').val()            || '',
                    placeholder:  $row.find('.snfs-field-placeholder').val()        || '',
                    instructions: $row.find('.snfs-field-instructions').val()       || '',
                    choices_raw:  $row.find('.snfs-field-choices').val()            || '',
                    min:          $row.find('.snfs-field-min').val()                || '',
                    max:          $row.find('.snfs-field-max').val()                || '',
                    step:         $row.find('.snfs-field-step').val()               || '',
                };
                fields.push(field);
            });
            $('#snfs-fields-json').val(JSON.stringify(fields));
        },

    };

    SNFS.init();

    // Show/hide choices textarea when field type changes
    var choiceTypes = ['checkbox', 'radio', 'select'];
    var rangeTypes  = ['range', 'number'];
    $(document).on('change', '.snfs-field-type', function () {
        var type      = $(this).val();
        var $row      = $(this).closest('.snfs-field-row');
        var $choices  = $row.find('.snfs-choices-wrap');
        var $rangeOpts = $row.find('.snfs-range-wrap');

        if (choiceTypes.indexOf(type) !== -1) {
            $choices.slideDown(150);
        } else {
            $choices.slideUp(150);
        }

        if (rangeTypes.indexOf(type) !== -1) {
            $rangeOpts.slideDown(150);
        } else {
            $rangeOpts.slideUp(150);
        }
    });

    // Serialize fields to JSON just before WordPress submits the form
    $(document).on('click', '#publish, #save-post', function () {
        SNFS.serializeFieldsToJSON();
        SNFS.saveRulesJSON();
    });
    $('form#post').on('submit', function () {
        SNFS.serializeFieldsToJSON();
        SNFS.saveRulesJSON();
    });

});
