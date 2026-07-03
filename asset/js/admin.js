/**
 * Swastikaa Fieldkit Admin JS
 */
jQuery(function ($) {
    'use strict';

    var SWFK = {

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
            $(document).on('click', '#swfk-add-field', function (e) {
                e.preventDefault();
                SWFK.addField();
            });

            $(document).on('click', '.swfk-field-remove', function (e) {
                e.preventDefault();
                if ( confirm('Remove this field?') ) {
                    $(this).closest('.swfk-field-row').remove();
                    SWFK.checkEmpty();
                    SWFK.reindex();
                }
            });
        },

        addField: function () {
            var tmpl = document.getElementById('swfk-field-row-template');
            if (!tmpl) return;

            var index = $('.swfk-field-row').length;
            var html  = tmpl.innerHTML.replace(/__INDEX__/g, index);

            $('.swfk-placeholder').remove();
            $('#swfk-fields-container').append(html);
        },

        checkEmpty: function () {
            if ($('.swfk-field-row').length === 0) {
                $('#swfk-fields-container').html(
                    '<div class="swfk-placeholder"><span class="dashicons dashicons-feedback" style="font-size:32px;color:#c3c4c7;"></span><p>No fields yet. Click <strong>Add Field</strong> to get started.</p></div>'
                );
            }
        },

        reindex: function () {
            $('.swfk-field-row').each(function (i) {
                $(this).attr('data-index', i);
                $(this).find('[name]').each(function () {
                    this.name = this.name.replace(/swfk_fields\[\d+\]/, 'swfk_fields[' + i + ']');
                });
            });
        },

        // ── TOGGLE COLLAPSE ────────────────────────────────────────────────────

        bindToggle: function () {
            $(document).on('click', '.swfk-field-toggle', function (e) {
                e.preventDefault();
                var $row  = $(this).closest('.swfk-field-row');
                var $body = $row.find('.swfk-field-body');
                var $icon = $(this).find('.dashicons');

                $body.slideToggle(150);
                $icon.toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
            });
        },

        // ── DUPLICATE ──────────────────────────────────────────────────────────

        bindDuplicateField: function () {
            $(document).on('click', '.swfk-field-duplicate', function (e) {
                e.preventDefault();
                var $original = $(this).closest('.swfk-field-row');
                var $clone    = $original.clone(false);
                var index     = $('.swfk-field-row').length;

                $clone.attr('data-index', index);
                $clone.find('[name]').each(function () {
                    this.name = this.name.replace(/swfk_fields\[\d+\]/, 'swfk_fields[' + index + ']');
                });

                // Append and re-slug the name field
                $original.after($clone);
                var $nameInput = $clone.find('.swfk-field-name');
                $nameInput.val( $nameInput.val() + '_copy' );
            });
        },

        // ── DYNAMIC LABEL → TITLE ────────────────────────────────────────────

        bindDynamicLabel: function () {
            $(document).on('input', '.swfk-field-label', function () {
                var label = $(this).val() || 'New Field';
                $(this).closest('.swfk-field-row').find('.swfk-field-title').text(label);
            });
        },

        // ── AUTO SLUG FROM LABEL ─────────────────────────────────────────────

        bindAutoSlug: function () {
            $(document).on('input', '.swfk-field-label', function () {
                var $row  = $(this).closest('.swfk-field-row');
                var $name = $row.find('.swfk-field-name');

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
            $(document).on('input', '.swfk-field-name', function () {
                $(this).data('manual', $(this).val() !== '');
            });
        },

        // ── SORTABLE ─────────────────────────────────────────────────────────

        makeSortable: function () {
            if ($.fn.sortable) {
                $('#swfk-fields-container').sortable({
                    handle: '.swfk-field-handle',
                    placeholder: 'swfk-sortable-placeholder',
                    tolerance: 'pointer',
                    update: function () {
                        SWFK.reindex();
                    },
                });
            }
        },

        // ── LOCATION RULES ────────────────────────────────────────────────────

        initLocationRules: function () {
            // Restore saved values into rule dropdowns
            var rawJSON = $('#swfk-location-rules-json').val();
            if (rawJSON) {
                try {
                    var saved = JSON.parse(rawJSON);
                    $('.swfk-location-rule').each(function (i) {
                        var rule = saved[i];
                        if (!rule) return;
                        var $el = $(this);
                        $el.find('.swfk-rule-type').val(rule.type);
                        $el.find('.swfk-rule-operator').val(rule.operator);
                        SWFK.populateRuleValues($el, rule.type, rule.value);
                    });
                } catch (e) { /* ignore */ }
            }

            // Add rule
            $(document).on('click', '#swfk-add-location-rule', function (e) {
                e.preventDefault();
                SWFK.addLocationRule();
            });

            // Remove rule
            $(document).on('click', '.swfk-rule-remove', function (e) {
                e.preventDefault();
                $(this).closest('.swfk-location-rule').remove();
                SWFK.saveRulesJSON();
            });

            // Type changed → repopulate values
            $(document).on('change', '.swfk-rule-type', function () {
                var $rule = $(this).closest('.swfk-location-rule');
                SWFK.populateRuleValues($rule, $(this).val(), '');
                SWFK.saveRulesJSON();
            });

            $(document).on('change', '.swfk-rule-operator, .swfk-rule-value', function () {
                SWFK.saveRulesJSON();
            });
        },

        addLocationRule: function () {
            var tmpl = document.getElementById('swfk-location-rule-template');
            if (!tmpl) return;

            var index = $('.swfk-location-rule').length;
            var html  = tmpl.innerHTML.replace(/__RULE_INDEX__/g, index);

            $('.swfk-rules-placeholder').remove();
            $('#swfk-location-rules-container').append(html);

            var $new = $('.swfk-location-rule:last');
            SWFK.populateRuleValues($new, 'post_type', '');
            SWFK.saveRulesJSON();
        },

        populateRuleValues: function ($rule, type, selectedValue) {
            var $select = $rule.find('.swfk-rule-value');
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
            $('.swfk-location-rule').each(function () {
                rules.push({
                    type:     $(this).find('.swfk-rule-type').val(),
                    operator: $(this).find('.swfk-rule-operator').val(),
                    value:    $(this).find('.swfk-rule-value').val(),
                });
            });
            $('#swfk-location-rules-json').val(JSON.stringify(rules));
        },

        // Serialize all field rows to the hidden JSON input before form submit
        serializeFieldsToJSON: function () {
            var choiceTypes = ['checkbox', 'radio', 'select'];
            var rangeTypes  = ['range', 'number'];
            var fields = [];
            $('.swfk-field-row').each(function () {
                var $row  = $(this);
                var type  = $row.find('.swfk-field-type').val() || 'text';
                var field = {
                    label:        $row.find('.swfk-field-label').val()              || '',
                    name:         $row.find('.swfk-field-name').val()               || '',
                    type:         type,
                    required:     $row.find('.swfk-field-required').is(':checked')  ? 1 : 0,
                    'default':    $row.find('.swfk-field-default').val()            || '',
                    placeholder:  $row.find('.swfk-field-placeholder').val()        || '',
                    instructions: $row.find('.swfk-field-instructions').val()       || '',
                    choices_raw:  $row.find('.swfk-field-choices').val()            || '',
                    min:          $row.find('.swfk-field-min').val()                || '',
                    max:          $row.find('.swfk-field-max').val()                || '',
                    step:         $row.find('.swfk-field-step').val()               || '',
                };
                fields.push(field);
            });
            $('#swfk-fields-json').val(JSON.stringify(fields));
        },

    };

    SWFK.init();

    // Show/hide choices textarea when field type changes
    var choiceTypes = ['checkbox', 'radio', 'select'];
    var rangeTypes  = ['range', 'number'];
    $(document).on('change', '.swfk-field-type', function () {
        var type      = $(this).val();
        var $row      = $(this).closest('.swfk-field-row');
        var $choices  = $row.find('.swfk-choices-wrap');
        var $rangeOpts = $row.find('.swfk-range-wrap');

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
        SWFK.serializeFieldsToJSON();
        SWFK.saveRulesJSON();
    });
    $('form#post').on('submit', function () {
        SWFK.serializeFieldsToJSON();
        SWFK.saveRulesJSON();
    });

});
