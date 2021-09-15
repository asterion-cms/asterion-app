$(function() {
    sameHeight();
    activateBasicElements();
    activateSortable();
    activateTranslations();
    activateNestedForms();
    activateMultipleActions();
    activateAutocomplete();
    activateDatePicker();
    activateDragField();
    activateParameters();
    activateCK();
    activateMaps();
    activateModal();
});
$(window).on('load', function() {
    sameHeight();
});
/**
 * Same height elements
 **/
function sameHeight() {
    $('.same_height').each(function(index, ele) {
        $(ele).children().css('height', 'auto');
        var maxHeight = 0;
        $(ele).children().each(function(indexItem, item) {
            maxHeight = ($(item).height() > maxHeight) ? $(item).height() : maxHeight;
        });
        $(ele).children().css('height', maxHeight);
    });
}
/**
 * Activate the basic elements for the administration area.
 **/
function activateBasicElements() {
    /**
     * DELETE an element.
     * Function to show a message before deleting an element.
     **/
    $(document).on('click', '.icon_delete a', function(event) {
        event.stopImmediatePropagation();
        if (!$(this).data('confirm') || window.confirm($(this).data('confirm'))) {
            let parentIcon = $(this).parents('.icon_delete').first();
            if (parentIcon.hasClass('icon_delete_item_ajax')) {
                let eleContainer = $(this).parents('.line_admin').first();
                eleContainer.css({
                    'opacity': '0.2',
                    'pointer-events': 'none'
                });
                $.ajax($(this).attr('href')).done(function(response) {
                    eleContainer.css({
                        'opacity': '1',
                        'pointer-events': 'auto'
                    });
                    if (response && response.message_error) {
                        alert(response.message_error);
                    }
                    if (response.status && response.status == 'OK') {
                        eleContainer.remove();
                    }
                }).fail(function(event) {
                    eleContainer.css({
                        'opacity': '1',
                        'pointer-events': 'auto'
                    });
                    alert('Error');
                });
                return false;
            }
        } else {
            return false;
        }
    });
    /**
     * DELETE an image from an object.
     **/
    $(document).on('click', '.form_fields_image_delete', function(event) {
        event.stopImmediatePropagation();
        if (!$(this).data('confirm') || window.confirm($(this).data('confirm'))) {
            let eleContainer = $(this).parents('.form_fields_image').first();
            eleContainer.css({
                'opacity': '0.2',
                'pointer-events': 'none'
            });
            $.ajax($(this).data('url')).done(function(response) {
                eleContainer.css({
                    'opacity': '1',
                    'pointer-events': 'auto'
                });
                if (response && response.message_error) {
                    alert(response.message_error);
                }
                if (response.status && response.status == 'OK') {
                    eleContainer.parents('.drag_field_wrapper').first().removeClass('drag_field_wrapper_has_image');
                    eleContainer.remove();
                }
            }).fail(function(event) {
                eleContainer.css({
                    'opacity': '1',
                    'pointer-events': 'auto'
                });
                alert('Error');
            });
        }
    });
    /**
     * DELETE a file from an object.
     **/
    $(document).on('click', '.form_fields_file_delete', function(event) {
        event.stopImmediatePropagation();
        if (!$(this).data('confirm') || window.confirm($(this).data('confirm'))) {
            let eleContainer = $(this).parents('.form_fields_file').first();
            eleContainer.css({
                'opacity': '0.2',
                'pointer-events': 'none'
            });
            $.ajax($(this).data('url')).done(function(response) {
                eleContainer.css({
                    'opacity': '1',
                    'pointer-events': 'auto'
                });
                if (response && response.message_error) {
                    alert(response.message_error);
                }
                if (response.status && response.status == 'OK') {
                    eleContainer.remove();
                }
            }).fail(function(event) {
                eleContainer.css({
                    'opacity': '1',
                    'pointer-events': 'auto'
                });
                alert('Error');
            });
        }
    });
    /**
     * ORDER elements in a list.
     **/
    $(document).on('change', '.order_actions select', function(event) {
        window.location = $(this).parents('.order_actions').data('url') + $(this).val();
    });
    /**
     * CHECKBOX for certain select elements.
     **/
    $('.select_checkbox').each(function(index, ele) {
        var selectItem = $(ele).find('select');
        var checkboxItem = $(ele).find('input[type=checkbox]');
        $(selectItem).attr('disabled', !$(checkboxItem).is(':checked'));
        $(checkboxItem).click(function() {
            $(selectItem).attr('disabled', !$(checkboxItem).is(':checked'));
        });
    });
    /**
     * ACTIVATE or deactivate elements.
     **/
    $(document).on('click', '.active_option', function(event) {
        event.stopImmediatePropagation();
        event.preventDefault();
        let eleContainer = $(this).parents('.active_wrapper').first();
        eleContainer.css({
            'opacity': '0.2',
            'pointer-events': 'none'
        });
        $.ajax($(this).data('url')).done(function(response) {
            eleContainer.css({
                'opacity': '1',
                'pointer-events': 'auto'
            });
            if (response && response.message_error) {
                alert(response.message_error);
            }
            if (response.html) {
                eleContainer.replaceWith(response.html);
            }
        }).fail(function(event) {
            eleContainer.css({
                'opacity': '1',
                'pointer-events': 'auto'
            });
            alert('Error');
        });
    });
    /**
     * ACTIVATE checkboxes icons.
     **/
    var checkCheckboxesIcons = function() {
        $('.checkbox_icons_wrapper').removeClass('checkbox_icons_wrapper_selected');
        $('.checkbox_icons_wrapper').each(function(index, ele) {
            if ($(ele).find('input').is(':checked')) {
                $(this).addClass('checkbox_icons_wrapper_selected');
            }
        });
    }
    checkCheckboxesIcons();
    $(document).on('change', '.checkbox_icons_wrapper input', function(event) {
        checkCheckboxesIcons();
    });
}
/**
 * MULTIPLE actions in a list.
 **/
function activateMultipleActions() {
    var checkActiveActions = function() {
        $('.multiple_action').toggleClass('multiple_action_active', ($('.line_admin_checkbox input:checked').length > 0));
    }
    // Activate the select/deselect all items.
    $(document).on('click', '.multiple_action_check_all input', function() {
        $('.line_admin_checkbox input').prop('checked', $(this).prop('checked'));
        checkActiveActions();
    });
    $(document).on('click', '.line_admin_checkbox input', function(event) {
        checkActiveActions();
    });
    // Activate the action with the multiple items.
    $(document).on('click', '.multiple_option', function(event) {
        let eleContainer = $(this).parents('.multiple_actions').first();
        event.stopImmediatePropagation();
        let postValues = [];
        $('.line_admin_checkbox input').each(function(index, ele) {
            if ($(ele).prop('checked') == true) postValues.push($(ele).attr('name'));
        });
        if (postValues.length > 0) {
            eleContainer.css({
                'opacity': '0.2',
                'pointer-events': 'none'
            });
            $.post($(this).data('url'), {
                'list_ids': postValues
            }).done(function(response) {
                eleContainer.css({
                    'opacity': '1',
                    'pointer-events': 'auto'
                });
                if (response && response.message_error) {
                    alert(response.message_error);
                }
                if (response.status && response.status == 'OK') {
                    location.reload();
                }
            }).fail(function(event) {
                eleContainer.css({
                    'opacity': '1',
                    'pointer-events': 'auto'
                });
                alert('Error');
            });
        }
    });
}
/**
 * NESTED elements in a form.
 */
function activateNestedForms() {
    // Disable multiple forms
    $('.nested_form_field_empty :input').attr('disabled', true);
    // Action to add an element to the form.
    var addFormField = function(container) {
        var newForm = container.find('.nested_form_field_empty');
        var formsContainer = container.find('.nested_form_field_ins');
        var newFormClone = newForm.clone();
        newFormClone.removeClass('nested_form_field_empty');
        newFormClone.addClass('nested_form_field_object');
        newFormClone.find(':input').attr('disabled', false);
        newFormClone.html(newFormClone.html().replace(/\#ID_MULTIPLE#/g, randomString()));
        newFormClone.appendTo(formsContainer);
        $('.field_ord').each(function(index, ele) {
            $(ele).val(index + 1);
        });
        return newFormClone;
    }
    $(document).on('click', '.nested_form_field_add', function(event) {
        event.stopImmediatePropagation();
        var container = $(this).parents('.nested_form_field');
        addFormField(container);
    });
    // Action to add multiple images to the form.
    $(document).on('click', '.nested_form_field_add_multiple', function(event) {
        event.stopImmediatePropagation();
        event.preventDefault();
        var self = $(this);
        var fileInput = self.parents('.nested_form_field_add_multiple_wrapper').first().find('input[type=file]').first();
        fileInput.trigger('click');
    });
    $(document).on('change', '.nested_form_field_add_multiple_wrapper input[type=file]', function(event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var container = $(this).parents('.nested_form_field');
        var field = $(this).parents('.nested_form_field_add_multiple_wrapper').first().data('field');
        if ($(this)[0]['files']) {
            for (var i=0; i < $(this)[0]['files'].length; i++) {
                var newFormClone = addFormField(container);
                var containerInside = newFormClone.find('.form_field_' + field).first();
                loadFileField($(this)[0]['files'][i], containerInside);
            }
        }
    });
    // Action to delete an element of the form.
    $(document).on('click', '.nested_form_field_delete', function(event) {
        event.stopImmediatePropagation();
        var self = $(this);
        var container = $(this).parents('.nested_form_field_object');
        var actionDelete = $(this).data('url');
        if (!actionDelete) {
            container.remove();
        } else {
            if (!$(this).data('confirm') || window.confirm($(this).data('confirm'))) {
                $.ajax(actionDelete).done(function(response) {
                    container.remove();
                });
            }
        }
    });
}
/**
 * SORT a list of elements.
 */
function activateSortable() {
    // Regular list
    $('.sortable_list .list_content').each(function(index, ele) {
        $(ele).sortable({
            handle: '.icon_handle',
            update: function() {
                var eleContainer = $(ele);
                eleContainer.css({
                    'opacity': '0.2',
                    'pointer-events': 'none'
                });
                var url = $(this).parents('.sortable_list').data('urlsort');
                $.post(url, {
                    'new_order[]': $(ele).find('.line_admin').toArray().map(item => $(item).data('id'))
                }).done(function(response) {
                    eleContainer.css({
                        'opacity': '1',
                        'pointer-events': 'auto'
                    });
                    if (response && response.message_error) {
                        alert(response.message_error);
                    }
                    if (!response.status || response.status != 'OK') {
                        $(ele).sortable('cancel');
                    }
                }).fail(function(event) {
                    eleContainer.css({
                        'opacity': '1',
                        'pointer-events': 'auto'
                    });
                    alert('Error');
                });
            }
        });
    });
    // Nested list
    $('.nested_form_field_sortable').each(function(index, ele) {
        var eleContainer = $(ele).parents('.nested_form_field').first();
        $(ele).sortable({
            handle: '.nested_form_field_order',
            update: function() {
                eleContainer.find('.field_ord').each(function(index, ele) {
                    $(ele).val(index + 1);
                });
            }
        });
    });
}
/**
 * Acivate all the actions of the translation section.
 */
function activateTranslations() {
    $(document).on('click', '.translation_statistic_reset', function(event) {
        if (!$(this).data('confirm') || window.confirm($(this).data('confirm'))) {
            var eleContainer = $(this).parents('.translation_statistic').first();
            var resultsContainer = eleContainer.find('.translation_statistic_results');
            eleContainer.css({
                'opacity': '0.2',
                'pointer-events': 'none'
            });
            eleContainer.find('.translation_statistic_results').hide();
            $.ajax($(this).data('url')).done(function(response) {
                eleContainer.css({
                    'opacity': '1',
                    'pointer-events': 'auto'
                });
                if (response && response.message_error) {
                    alert(response.message_error);
                }
                if (response.status && response.statistics) {
                    reloadListAdmin();
                    eleContainer.find('.translation_statistic_results').show();
                    eleContainer.find('.translation_statistic_result_created span').html(response.statistics.translations_created);
                    eleContainer.find('.translation_statistic_result_updated span').html(response.statistics.translations_updated);
                }
            }).fail(function(event) {
                eleContainer.css({
                    'opacity': '1',
                    'pointer-events': 'auto'
                });
                alert('Error');
            });
        }
    });
    $(document).on('submit', '.form_admin_import_translations', function(event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var url = $(this).find('input[type=url]').val();
        var eleContainer = $(this).parents('.administration_block_content').first();
        eleContainer.css({
            'opacity': '0.2',
            'pointer-events': 'none'
        });
        $.post($(this).attr('action'), $(this).serialize()).done(function(response) {
            eleContainer.css({
                'opacity': '1',
                'pointer-events': 'auto'
            });
            if (response && response.message_error) {
                alert(response.message_error);
            }
            if (response.status && response.statistics) {
                reloadListAdmin();
                eleContainer.find('input[type=url]').val('');
                eleContainer.find('.translation_statistic_results').show();
                eleContainer.find('.translation_statistic_result_created span').html(response.statistics.translations_created);
                eleContainer.find('.translation_statistic_result_updated span').html(response.statistics.translations_updated);
            }
        }).fail(function(event) {
            eleContainer.css({
                'opacity': '1',
                'pointer-events': 'auto'
            });
            alert('Error');
        });
    });
}
/**
 * AUTOCOMPLETE for certain elements in a form.
 */
function activateAutocomplete() {
    $('.autocomplete_item input').each(function(index, ele) {
        $(ele).autocomplete({
            minLength: 2,
            source: function(request, response) {
                $.getJSON($(ele).parents('.autocomplete_item').data('url'), {
                    term: split(request.term).pop()
                }, function(data) {
                    response((data && data.results) ? data.results : []);
                });
            },
            focus: function() {
                return false;
            },
            select: function(event, ui) {
                let terms = split(this.value);
                terms.pop();
                terms.push(ui.item.value);
                terms.push("");
                this.value = terms.join(", ");
                return false;
            }
        });
    });
}
/**
 * DATE PICKER for certain elements in a form.
 **/
function activateDatePicker() {
    $('.date_text input').each(function(index, ele) {
        var dateFormatView = 'yy-mm-dd';
        $(ele).datepicker({
            'firstDay': 1,
            'dateFormat': dateFormatView
        });
    });
}
/**
 * Function to activate the drag field.
 **/
function activateDragField() {
    $(document).on('change', '.drag_field_file input[type=file]', function(event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var container = $(this).parents('.form_field').first();
        var file = $(this)[0]['files'][0];
        loadFileField(file, container);
    });
    $(document).on('click', '.drag_field_drag', function(event) {
        event.stopImmediatePropagation();
        $(this).parents('.form_field').first().find('input[type=file]').trigger('click');
    });
    $(document).on('dragleave', '.drag_field_drag', function(event) {
        event.preventDefault();
        $(this).removeClass('drag_field_drag_selected');
    });
    $(document).on('dragover', '.drag_field_drag', function(event) {
        event.preventDefault();
        $(this).addClass('drag_field_drag_selected');
    });
    $(document).on('drop', '.drag_field_drag', function(event) {
        event.preventDefault();
        $(this).removeClass('drag_field_drag_selected');
        var file = event.originalEvent.dataTransfer.files[0];
        var container = $(this).parents('.form_field').first();
        loadFileField(file, container);
    });
}
function resizeBase64Img(base64, maxWidth, maxHeight) {
    return new Promise((resolve, reject) => {
        let img = document.createElement("img");
        img.src = base64;
        img.onload = function() {
            var mode = (img.width > img.height) ? 'horizontal' : 'vertical';
            var ratio = (img.width > img.height) ? img.height / img.width : img.width / img.height;
            if (img.width > img.height) {
                var newWidth = Math.ceil((img.width > maxWidth) ? maxWidth : img.width);
                var newHeight = Math.ceil(newWidth * img.height / img.width);
            } else {
                var newHeight = Math.ceil((img.height > maxHeight) ? maxHeight : img.height);
                var newWidth = Math.ceil(newHeight * img.width / img.height);
            }
            var canvas = document.createElement("canvas");
            canvas.width = newWidth;
            canvas.height = newHeight;
            let context = canvas.getContext("2d");
            context.drawImage(img, 0, 0, newWidth, newHeight);
            resolve(canvas.toDataURL());
        }
    });
}
function loadFileField(file, container) {
    var reader = new FileReader();
    reader.onloadend = function() {
        if (reader.result != '') {
            var containerData = container.find('.drag_field_wrapper');
            if (containerData.data('maxdimensions')) {
                resizeBase64Img(reader.result, containerData.data('maxwidth'), containerData.data('maxheight')).then((result)=>{
                    processFileField(result, file, container);
                });
            } else {
                processFileField(reader.result, file, container);
            }
        }
    };
    reader.readAsDataURL(file);
}
function processFileField(baseString, file, container) {
    var fileInput = container.find('input.filevalue').first();
    var fileInputName = container.find('input.filename').first();
    var fileInputUploaded = container.find('input.filename_uploaded').first();
    var fileInputFile = container.find('input.filename_input').first();
    var imageContainer = container.find('img').first();
    var fileContainer = container.find('.drag_field_file_name').first();
    var loader = container.find('.drag_field_loader').first();
    var loaderBar = container.find('.drag_field_loader_bar').first();
    var loaderMessage = container.find('.drag_field_loader_message').first();
    fileInput.val(baseString);
    fileInputName.val(file.name);
    fileInputFile.val('');
    if (imageContainer) {
        imageContainer.attr('src', baseString);
        imageContainer.parents('.drag_field_image').show();
    }
    if (fileContainer) {
        fileContainer.find('em').html(file.name);
        fileContainer.show();
    }
    // Start uploading the image
    loaderBar.removeClass('drag_field_loader_bar_loaded');
    loaderBar.removeClass('drag_field_loader_bar_error');
    loaderMessage.html(loaderMessage.data('messageloading'));
    $.post({
        url: container.data('urluploadtemp'),
        data: {
            "file": baseString,
            "filename": fileInputName.val()
        },
        xhr: function() {
            var xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener("progress", function(evt) {
                if (evt.lengthComputable) {
                    var percentage = Math.ceil((evt.loaded / evt.total) * 100);
                    loaderBar.css('width', percentage + '%');
                    loaderMessage.html(loaderMessage.data('messageloading') + ' (' + percentage + ' %)');
                    if (percentage == 100) {
                        loaderMessage.html(loaderMessage.data('messagesaving'));
                        loaderBar.addClass('drag_field_loader_bar_loaded');
                    }
                }
            }, false);
            return xhr;
        }
    }).done(function(response) {
        loaderBar.removeClass('drag_field_loader_bar_loaded');
        if (response.status == 'OK') {
            fileInput.val('');
            fileInputUploaded.val(response.file);
            loaderMessage.html(loaderMessage.data('messagesavedas') + ' : ' + response.filename);
        } else {
            loaderMessage.html(response.message_error || 'Error');
        }
    }).fail(function(event) {
        loaderMessage.html('Error');
        loaderBar.removeClass('drag_field_loader_bar_loaded');
        loaderBar.addClass('drag_field_loader_bar_error');
    });
}
/**
 * Activate the function for the parameters.
 **/
function activateParameters() {
    var changeParameters = function(type) {
        $('.form_parameter_item').removeClass('form_parameter_item_selected');
        $('.form_parameter_option').removeClass('form_parameter_option_selected');
        if (type) {
            $('.form_parameter_option[data-type="' + type + '"]').addClass('form_parameter_option_selected');
            $('.form_parameter_item_' + type).addClass('form_parameter_item_selected');
        } else {
            $('.form_parameter_option').first().addClass('form_parameter_option_selected');
            $('.form_parameter_item').first().addClass('form_parameter_item_selected');
        }
    }
    $(document).on('click', '.form_parameter_option', function(event) {
        event.preventDefault();
        changeParameters($(this).data('type'));
    });
}
/**
 * CKEditor for certain elements in a form.
 **/
function activateCK() {
    var info = $('.js_info').data('info');
    CKEDITOR.editorConfig = function(config) {
        var appUrl = info.app_url;
        var siteFile = encodeURIComponent(info.base_file + '/' + info.app_folder);
        var siteUrl = encodeURIComponent(info.base_url + '/' + info.app_folder);
        var sites = '&siteFile=' + siteFile + '&siteUrl=' + siteUrl;
        config.filebrowserBrowseUrl = appUrl + 'helpers/kcfinder/browse.php?type=files' + sites;
        config.filebrowserImageBrowseUrl = appUrl + 'helpers/kcfinder/browse.php?type=images' + sites;
        config.filebrowserFlashBrowseUrl = appUrl + 'helpers/kcfinder/browse.php?type=flash' + sites;
        config.filebrowserUploadUrl = appUrl + 'helpers/kcfinder/upload.php?type=files' + sites;
        config.filebrowserImageUploadUrl = appUrl + 'helpers/kcfinder/upload.php?type=images' + sites;
        config.filebrowserFlashUploadUrl = appUrl + 'helpers/kcfinder/upload.php?type=flash' + sites;
        config.allowedContent = true;
        config.resize_enabled = false;
        config.extraPlugins = 'widget,lineutils,codesnippet,templates';
        if (info.editorial_css) {
            config.contentsCss = [info.editorial_css];
        }
        config.font_names = info.font_families + ' Arial/Arial, Helvetica, sans-serif; Times New Roman/Times New Roman, Times, serif;';
        if (info.font_sizes) {
            config.fontSize_sizes = info.font_sizes;
        }
        if (info.colors) {
            config.colorButton_colors = info.colors;
        }
        if (info.ckeditor_templates) {
            config.templates_files = [info.ckeditor_templates];
        }
    };
    $('.ckeditorArea textarea').each(function(index, ele) {
        if ($(ele).attr('rel') != 'ckeditor') {
            $(ele).attr('rel', 'ckeditor');
            if ($(ele).attr('id') == '' || $(ele).attr('id') == undefined) {
                $(ele).attr('id', randomString());
            }
            CKEDITOR.replace($(ele).attr('id'), {
                height: '450px',
                toolbar: [{
                    name: 'basicstyles',
                    groups: ['basicstyles', 'cleanup'],
                    items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']
                }, {
                    name: 'paragraph',
                    groups: ['list', 'indent', 'blocks', 'align', 'bidi'],
                    items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl']
                }, '/', {
                    name: 'document',
                    groups: ['mode', 'document', 'doctools'],
                    items: ['Source']
                }, {
                    name: 'clipboard',
                    groups: ['clipboard', 'undo'],
                    items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']
                }, {
                    name: 'links',
                    items: ['Link', 'Unlink', 'Anchor']
                }, {
                    name: 'insert',
                    items: ['Image', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe']
                }, '/', {
                    name: 'styles',
                    items: ['Format', 'Font', 'FontSize']
                }, {
                    name: 'colors',
                    items: ['TextColor', 'BGColor']
                }, {
                    name: 'tools',
                    items: ['Maximize', 'ShowBlocks', 'CodeSnippet', '-', 'Templates']
                }, ]
            });
        }
    });
    $('.ckeditorAreaSimple textarea').each(function(index, ele) {
        if ($(ele).attr('rel') != 'ckeditor') {
            $(ele).attr('rel', 'ckeditor');
            if ($(ele).attr('id') == '' || $(ele).attr('id') == undefined) {
                $(ele).attr('id', randomString());
            }
            CKEDITOR.replace($(ele).attr('id'), {
                height: '250px',
                toolbar: [{
                    name: 'basicstyles',
                    groups: ['basicstyles', 'cleanup', 'list', 'indent', 'blocks', 'align', 'bidi'],
                    items: ['Bold', 'Italic', 'Underline', '-', 'NumberedList', 'BulletedList', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'Link', 'Unlink', 'Image']
                }, '/', {
                    name: 'styles',
                    items: ['Format', 'Font', 'FontSize', '-', 'TextColor', 'BGColor']
                }]
            });
        }
    });
}
/**
 * Activate the maps for certain elements in a form.
 **/
function activateMaps() {
    var maps = [];
    var markers = [];
    $('.point_map').each(function(index, ele) {
        var idMap = $(ele).find('.map_wrapper').attr('id');
        var map = L.map(idMap, {
            center: [$(ele).find('.map').data('latitude'), $(ele).find('.map').data('longitude')],
            zoom: $(ele).find('.map').data('zoom')
        });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(map);
        var marker = L.marker({
            lon: $(ele).find('.map').data('longitude'),
            lat: $(ele).find('.map').data('latitude')
        }).addTo(map);
        var inputLatitude = $(ele).find('.input_latitude');
        var inputLongitude = $(ele).find('.input_longitude');
        map.on('click', function(event) {
            marker.setLatLng(event.latlng);
            map.setView(event.latlng);
            inputLatitude.val(event.latlng.lat);
            inputLongitude.val(event.latlng.lng);
        });
        maps[idMap] = map;
        markers[idMap] = marker;
        if ($(ele).find('.map').first().hasClass('map_show')) {
            $(ele).find('.map_wrapper').hide();
        }
    });
    $(document).on('click', '.map_option_hide .map_option_ins', function(event) {
        event.stopImmediatePropagation();
        var eleContainer = $(this).parents('.map').first();
        eleContainer.find('.map_option_show').show();
        eleContainer.find('.map_option_hide').hide();
        eleContainer.find('.map_wrapper').hide();
        eleContainer.find('.input_latitude, .input_longitude').val('');
    });
    $(document).on('click', '.map_option_show .map_option_ins', function(event) {
        event.stopImmediatePropagation();
        var eleContainer = $(this).parents('.map').first();
        eleContainer.find('.map_option_show').hide();
        eleContainer.find('.map_option_hide').show();
        eleContainer.find('.map_wrapper').show();
        var mapIp = eleContainer.find('.map_wrapper').attr('id');;
        var defaultLatLng = {
            lat: eleContainer.data('initlatitude'),
            lng: eleContainer.data('initlongitude')
        };
        maps[mapIp].setView(defaultLatLng);
        markers[mapIp].setLatLng(defaultLatLng);
        eleContainer.find('.input_latitude').val(defaultLatLng.lat);
        eleContainer.find('.input_longitude').val(defaultLatLng.lng);
    });
}
/**
 * Reload the list in the administration page.
 **/
function reloadListAdmin() {
    if ($('.list_items').length > 0) {
        var eleContainer = $('.list_items').first();
        var url = $('.list_items').data('url');
        eleContainer.css({
            'opacity': '0.2',
            'pointer-events': 'none'
        });
        $.ajax(url, {
            contentType: "application/json"
        }).done(function(response) {
            eleContainer.css({
                'opacity': '1',
                'pointer-events': 'auto'
            });
            if (response && response.message_error) {
                alert(response.message_error);
            }
            if (response.status && response.html) {
                eleContainer.html(response.html);
            }
        }).fail(function(event) {
            eleContainer.css({
                'opacity': '1',
                'pointer-events': 'auto'
            });
            alert('Error');
        });
    }
}
/**
 * Activate modal pages.
 **/
function activateModal() {
    $(document).on('click', '#modal_background', function(event) {
        $('#modal').remove();
    });
    $(document).on('click', '[data-modal]', function(event) {
        event.stopImmediatePropagation();
        $.ajax($(this).data('modal')).done(function(response) {
            if (response.status && response.status == 'OK') {
                var modal = '<div id="modal"><div id="modal_background"></div><div id="modal_inside">' + response.html + '</div></div>';
                $('#modal').remove();
                $(modal).appendTo(document.body);
            }
        });
    });
    $(document).on('submit', '#modal form', function(event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var eleContainer = $(this);
        eleContainer.css({
            'opacity': '0.2',
            'pointer-events': 'none'
        });
        $.post($(this).attr('action'), $(this).serialize()).done(function(response) {
            eleContainer.css({
                'opacity': '1',
                'pointer-events': 'auto'
            });
            if (response && response.message_error) {
                alert(response.message_error);
            }
            if (response.status && response.status == 'OK') {
                $('#modal').remove();
                reloadListAdmin();
            }
            if (response.status && response.status == 'NOK' && response.form) {
                eleContainer.html(response.form);
            }
        }).fail(function(event) {
            eleContainer.css({
                'opacity': '1',
                'pointer-events': 'auto'
            });
            alert('Error');
        });
    });
}

function split(val) {
    return val.split(/,\s*/);
}

function randomString() {
    return Math.random().toString(36).substring(7);
}