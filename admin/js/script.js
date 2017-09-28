(function($) {
    'use strict';
    getTags();
    $('form.tag-generator-panel').each(function() {
        updateTagGenerateForm($(this));
    });

    $('form.tag-generator-panel').submit(function(event) {
        return false;
    });

    $('form.tag-generator-panel .control-box :input').change(function(event) {
        var form = $(this).closest('form.tag-generator-panel');
        normalizeTagGenerator($(this));
        updateTagGenerateForm(form);
    });

    $('input.insert-tag').click(function(event) {
        var form = $(this).closest('form.tag-generator-panel');
        var tag = form.find('input.tag').val();
        insertTagGenerator(tag);
        tb_remove(); // close thickbox
        getTags();
        return false;
    });
    
    $('#madeit-forms-form').change(function() {
        getTags();
    });
    
    function getTags() {
        var formData = $('#madeit-forms-form').val();
        var tags = [];
        if(formData !== undefined) {
            $.each(formData.split("["), function(i, v) {
                v = v.trim();
                if(v.length > 0) {
                    var posName = v.indexOf('name="');
                    if(posName > 0) {
                        v = v.substring(posName + 6);
                        v = v.substring(0, v.indexOf('"'));
                        tags.push(v);
                    }
                }
            });
            if(tags.length > 0) {
                $('.name-tags').html("[" + tags.join('], [') + "]")
            } else {
                $('.name-tags').html("");
            }
        }
    }

    function updateTagGenerateForm($form) {
        var id = $form.attr('data-id');
        var name = '';
        var name_fields = $form.find('input[name="name"]');

        if (name_fields.length) {
            name = name_fields.val();

            if ('' === name) {
                name = id + '-' + Math.floor(Math.random() * 1000);
                name_fields.val(name);
            }
        }

        $form.find('input.tag').each(function() {
            var tag_type = $(this).attr('name');

            if ($form.find(':input[name="tagtype"]').length) {
                tag_type = $form.find(':input[name="tagtype"]').val();
            }

            var components = composeTagGenerator(tag_type, $form);
            $(this).val(components);
        });

        $form.find('span.mail-tag').text('[' + name + ']');

        $form.find('input.mail-tag').each(function() {
            $(this).val('[' + name + ']');
        });

    }

    function composeTagGenerator(tagType, $form) {
        if($form.find('input[name="name"]').length > 0) {
            var name = 'name="' + $form.find('input[name="name"]').val() + '"';
        }
        var scope = $form.find('.scope.' + tagType);

        if (! scope.length) {
            scope = $form;
        }

        var options = [];



        if ($form.find(':input[name="required"]').is(':checked')) {
            options.push('required="yes"');
        }

        scope.find('input.option').not(':checkbox,:radio').each(function(i) {
            var val = $(this).val();

            if (! val) {
                return;
            }

            if ($(this).hasClass('filetype')) {
                val = val.split(/[,|\s]+/).join('|');
            }

            if ($(this).hasClass('color')) {
                val = '#' + val;
            }

            /*if ('class' == $(this).attr('name')) {
                $.each(val.split(' '), function(i, n) {
                    options.push('class:' + n)
                });
            } else {*/
                options.push($(this).attr('name') + '="' + val + '"');
            /*}*/
        });

        var placeholder = false;
        scope.find('input:checkbox.option').each(function(i) {
            if ($(this).is(':checked')) {
                if($(this).attr('name') == "placeholder") {
                    placeholder = true;
                } else {
                    options.push($(this).attr('name') + '="yes"');
                }
            }
        });

        scope.find('input:radio.option').each(function(i) {
            if ($(this).is(':checked') && ! $(this).hasClass('default')) {
                options.push($(this).attr('name') + '="' + $(this).val() + '"');
            }
        });

        if ('radio' == tagType) {
            options.push('default:1');
        }

        options = (options.length > 0) ? options.join(' ') : '';

        var value = '';

        if (scope.find(':input[name="values"]').val()) {
            if(scope.find(':input[name="values"]').val().split("\n").length <= 1) {
                if(placeholder) {
                        value += 'placeholder';
                    } else {
                        value += 'value';
                    }
                value += '="' + scope.find(':input[name="values"]').val().replace(/["]/g, '&quot;') + '"';
            } else {
                if(placeholder) {
                    value += 'placeholder="';
                } else {
                    value += 'value="';
                }
                $.each(scope.find(':input[name="values"]').val().split("\n"), function(i, n) {
                    if(i > 0) {
                        value += "|";
                    }
                    value += n.replace(/["]/g, '&quot;');
                });
                value += '"';
            }
        }

        var components = [];

        $.each([tagType, name, options, value], function(i, v) {
            v = $.trim(v);

            if ('' != v) {
                components.push(v);
            }
        });

        components = $.trim(components.join(' '));
        return '[' + components + ']';
    }

    function normalizeTagGenerator($input) {
        var val = $input.val();

        if ($input.is('input[name="name"]')) {
            val = val.replace(/[^0-9a-zA-Z:._-]/g, '').replace(/^[^a-zA-Z]+/, '');
        }

        if ($input.is('.numeric')) {
            val = val.replace(/[^0-9.-]/g, '');
        }

        if ($input.is('.idvalue')) {
            val = val.replace(/[^-0-9a-zA-Z_]/g, '');
        }

        if ($input.is('.classvalue')) {
            val = $.map(val.split(' '), function(n) {
                return n.replace(/[^-0-9a-zA-Z_]/g, '');
            }).join(' ');

            val = $.trim(val.replace(/\s+/g, ' '));
        }

        if ($input.is('.color')) {
            val = val.replace(/[^0-9a-fA-F]/g, '');
        }

        if ($input.is('.filesize')) {
            val = val.replace(/[^0-9kKmMbB]/g, '');
        }

        if ($input.is('.filetype')) {
            val = val.replace(/[^0-9a-zA-Z.,|\s]/g, '');
        }

        if ($input.is('.date')) {
            if (! val.match(/^\d{4}-\d{2}-\d{2}$/)) { // 'yyyy-mm-dd' ISO 8601 format
                val = '';
            }
        }

        if ($input.is(':input[name="values"]')) {
            val = $.trim(val);
        }

        $input.val(val);

        if ($input.is(':checkbox.exclusive')) {
            exclusiveCheckbox($input);
        }
    }

    function exclusiveCheckbox($cb) {
        if ($cb.is(':checked')) {
            $cb.siblings(':checkbox.exclusive').prop('checked', false);
        }
    }

    function insertTagGenerator(content) {
        $('textarea#madeit-forms-form').each(function() {
            this.focus();

            if (document.selection) { // IE
                var selection = document.selection.createRange();
                selection.text = content;
            } else if (this.selectionEnd || 0 === this.selectionEnd) {
                var val = $(this).val();
                var end = this.selectionEnd;
                $(this).val(val.substring(0, end) + content + val.substring(end, val.length));
                this.selectionStart = end + content.length;
                this.selectionEnd = end + content.length;
            } else {
                $(this).val($(this).val() + content);
            }

            this.focus();
        });
    };

})(jQuery);

//actions
jQuery(document).ready(function($) {
    $('.action-section').each(function() {
        var value = $(this).find("[name^='action_type_'] option:selected").val();
        $(this).find('tr:gt(0)').hide();
        $(this).find('tr.ACTION_' + value).show();
    });

    reorderActionId();

    $('body').on('change', '.action-section [name^=action_type_]', function(e) {
        var value = $(this).find('option:selected').val();
        $(this).parent().parent().parent().find('tr:gt(0)').hide();
        $(this).parent().parent().parent().find('tr.ACTION_' + value).show();
    });

    $('body').on('click', '.delete-section', function(e) {
        e.preventDefault();
        $(this).parent().parent().remove();
        reorderActionId();
    });

    $('.add-section').click(function(e) {
        e.preventDefault();
        reorderActionId();
        var lastId = $("#actions-panel .action-section").length + 1;
        $("#actions-panel fieldset").append($('#empty-actions-section').html());
        $("#actions-panel fieldset .action-section:last").attr('id', $("#actions-panel fieldset .action-section:last").attr('id') + lastId);
        $("#actions-panel fieldset .action-section:last").attr('data-id', lastId);

        reorderActionId();

        $('.action-section').each(function() {
            var value = $(this).find("[name^='action_type_'] option:selected").val();
            $(this).find('tr:gt(0)').hide();
            $(this).find('tr.ACTION_' + value).show();
        });
    });

    function reorderActionId() {
        var i = 1;
        $("#actions-panel .action-section").each(function() {
            $(this).attr('data-id', i);
            $(this).attr('id', $(this).attr('data-section-id') + i);
            $(this).find('[data-name=action_panel_]').attr('name', 'action_panel_' + i);
            $(this).find('[data-name=action_panel_]').attr('value', i);

            $(this).find('tr').each(function() {
                var label = $(this).find('label').attr('for', $(this).attr('data-name') + i);
                var name = $(this).find('input, select, textarea').attr('name',  $(this).attr('data-name') + i);
            });
            i++;
        });
    }
});