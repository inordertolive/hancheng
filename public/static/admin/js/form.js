// +----------------------------------------------------------------------
// | Thinkstars
// +----------------------------------------------------------------------
// | 版权所有 似水星辰 [ 2630481389@qq.com ]
// +----------------------------------------------------------------------
// | 星辰工作室
// +----------------------------------------------------------------------
jQuery(document).ready(function() {
	
	// 文件上传集合
    var webuploader = [];
    // 当前上传对象
    var curr_uploader = {};
    // wangeditor编辑器集合
    var wangeditors = {};
	// 当前图标选择器
    var curr_icon_picker;
    var layer_icon;

	// 打开图标选择器
    $('.js-icon-picker').click(function(){
        curr_icon_picker = $(this);
        var icon_input = curr_icon_picker.find('.icon_input');
        if (icon_input.is(':disabled')) {
            return;
        }
        layer_icon = layer.open({
            type: 1,
            title: '图标选择器',
            area: ['90%', '90%'],
            scrollbar: false,
            content: $('#icon_tab')
        });
    });

	// 选择图标
    $('.js-icon-content div').on('click',function (event) {
		var icon = $(this).find('i').attr('class');
		curr_icon_picker.find('.input-group-addon.icon').html('<i class="'+icon+'"></i>');
		curr_icon_picker.find('.icon_input').val(icon);
		layer.close(layer_icon);
    });

	// 清空图标
    $('.delete-icon').click(function(event){
        event.stopPropagation();
        if ($(this).prev().is(':disabled')) {
            return;
        }
        $(this).prev().val('');
        $(this).prev().prev().html('<i class="fa fa-fw fa-plus-circle"></i>');
    });

    // 联动下拉框
    $('.select-linkage').change(function(){
        var self       = $(this), // 下拉框
            value      = self.val(), // 下拉框选中值
            ajax_url   = self.data('url'), // 异步请求地址
            param      = self.data('param'), // 参数名称
            next_items = self.data('next-items').split(','), // 下级下拉框的表单名数组
            next_item  = next_items[0]; // 下一级下拉框表单名

        // 下级联动菜单恢复默认
        if (next_items.length > 0) {
            for (var i = 0; i < next_items.length; i++) {
                $('select[name="'+ next_items[i] +'"]').html('<option value="">请选择：</option>');
            }
        }

        if (value != '') {
            Stars.loading();
            // 获取数据
            $.ajax({
                url: ajax_url,
                type: 'POST',
                dataType: 'json',
                data: param + "=" + value
            })
            .done(function(res) {
                Stars.loading('hide');
                if (res.code == '1') {
                    var list = res.list;
                    if (list) {
                        for (var item in list) {
                            var option = $('<option></option>');
                            option.val(list[item].key).html(list[item].value);
                            $('select[name="'+ next_item +'"]').append(option);
                        }
                    }
                } else {
                    Stars.notify(res.msg, 'danger');
                }
            })
            .fail(function(res) {
                Stars.loading('hide');
                Stars.notify($(res.responseText).find('h1').text() || '数据请求失败~', 'danger');
            });
        }
    });

	// 多级联动下拉框
    $('.select-linkages').change(function () {
        var self       = $(this), // 下拉框
            value      = self.val(), // 下拉框选中值
            token      = self.data('token'), // token
            pidkey     = self.data('pidkey') || 'pid',
            next_level = self.data('next-level'), // 下一级别
            next_level_id = self.data('next-level-id') || ''; // 下一级别的下拉框id

        // 下级联动菜单恢复默认
        if (next_level_id != '') {
            $('#' + next_level_id).html('<option value="">请选择：</option>');
            var has_next_level = $('#' + next_level_id).data('next-level-id');
            if (has_next_level) {
                $('#' + has_next_level).html('<option value="">请选择：</option>');
                has_next_level = $('#' + has_next_level).data('next-level-id');
                if (has_next_level) {
                    $('#' + has_next_level).html('<option value="">请选择：</option>');
                }
            }
        }

        if (value != '') {
            Stars.loading();
            // 获取数据
            $.ajax({
                url: lwwan.get_level_data,
                type: 'POST',
                dataType: 'json',
                data: {
                    token: token,
                    level: next_level,
                    pid: value,
                    pidkey: pidkey
                }
            })
            .done(function(res) {
                Stars.loading('hide');
                if (res.code == '1') {
                    var list = res.list;
                    if (list) {
                        for (var item in list) {
                            var option = $('<option></option>');
                            option.val(list[item].key).text(list[item].value);
                            $('#' + next_level_id).append(option);
                        }
                    }
                } else {
                    Stars.loading('hide');
                    Stars.notify(res.msg, 'danger');
                }
            })
            .fail(function(res) {
                Stars.loading('hide');
                Stars.notify($(res.responseText).find('h1').text() || '数据请求失败~', 'danger');
            });
        }
    });

	// 排序
    $('.nestable').each(function () {
        $(this).nestable({maxDepth:1}).on('change', function(){
            var $items = $(this).nestable('serialize');
            var name = $(this).data('name');
            var value = [];
            for (var item in $items) {
                value.push($items[item].id);
            }
            if (value.length) {
                $('input[name='+name+']').val(value.join(','));
            }
        });
    });

	// 日期
	$('.js-date').each(function () {
        var option = {
            elem: this,
            trigger: 'click'
        };
        var min = $(this).attr('min');
        var max = $(this).attr('max');
        if (min) {
            option.min = min;
        }
        if(max){
            option.max = max;
        }
        laydate.render(option);
	});
    $('.js-datetime').each(function () {
        var option = {
            elem:this,
            trigger:'click',
            type:'datetime'
        }
        var min = $(this).attr('min');
        var max = $(this).attr('max');
        if (min) {
            option.min = min;
        }
        if(max){
            option.max = max;
        }
        // console.log(option);return;
        laydate.render(option);
    });
	// wangeditor编辑器
    $('.js-wangeditor').each(function () {
        var wangeditor_name = $(this).attr('name');
        var imgExt = $(this).data('img-ext') || '';

        // 关闭调试信息
        wangEditor.config.printLog = false;
        // 实例化编辑器
        wangeditors[wangeditor_name] = new wangEditor(wangeditor_name);
        // 上传图片地址
        wangeditors[wangeditor_name].config.uploadImgUrl = lwwan.image_upload_url + '?from=wangeditor';
        // 允许上传图片后缀
        wangeditors[wangeditor_name].config.imgExt = imgExt;
        // 配置文件名
        wangeditors[wangeditor_name].config.uploadImgFileName = 'file';
        // 去掉地图
        wangeditors[wangeditor_name].config.menus = $.map(wangEditor.config.menus, function(item, key) {
            if (item === 'location' || item === 'emotion') {
                return null;
            }
            return item;
        });
        wangeditors[wangeditor_name].create();
    });

    // 注册WebUploader事件，实现秒传
    if (window.WebUploader) {
        WebUploader.Uploader.register({
            "before-send-file": "beforeSendFile" // 整个文件上传前
        }, {
            beforeSendFile:function(file){
                var $li = $( '#'+file.id );
                var deferred = WebUploader.Deferred();
                var owner = this.owner;

                owner.md5File(file).then(function(val){
                    $.ajax({
                        type: "POST",
                        url: lwwan.upload_check_url,
                        data: {
                            md5: val
                        },
                        cache: false,
                        timeout: 10000, // 超时的话，只能认为该文件不曾上传过
                        dataType: "json"
                    }).then(function(res, textStatus, jqXHR){
                        if(res.code){
                            // 已上传，触发上传完成事件，实现秒传
                            deferred.reject();
                            curr_uploader.trigger('uploadSuccess', file, res);
                            curr_uploader.trigger('uploadComplete', file);
                        }else{
                            // 文件不存在，触发上传
                            deferred.resolve();
                            $li.find('.file-state').html('<span class="text-info">正在上传...</span>');
                            $li.find('.img-state').html('<div class="bg-info">正在上传...</div>');
                            $li.find('.progress').show();
                        }
                    }, function(jqXHR, textStatus, errorThrown){
                        // 任何形式的验证失败，都触发重新上传
                        deferred.resolve();
                        $li.find('.file-state').html('<span class="text-info">正在上传...</span>');
                        $li.find('.img-state').html('<div class="bg-info">正在上传...</div>');
                        $li.find('.progress').show();
                    });
                });
                return deferred.promise();
            }
        });
    }
	// 图集幻灯片
	Stars.iview();

    // 文件上传
    $('.js-upload-file,.js-upload-files').each(function () {
        var $input_file       = $(this).find('input');
        var $input_file_name  = $input_file.attr('name');
        // 是否多文件上传
        var $multiple         = $input_file.data('multiple');
        // 允许上传的后缀
        var $ext              = $input_file.data('ext');
        // 文件限制大小
        var $size             = $input_file.data('size');
        // 文件列表
        var $file_list        = $('#file_list_' + $input_file_name);

        // 实例化上传
        var uploader = WebUploader.create({
            // 选完文件后，是否自动上传。
            auto: true,
            // 去重
            duplicate: true,
            // swf文件路径
            swf: lwwan.WebUploader_swf,
            // 文件接收服务端。
            server: lwwan.file_upload_url,
            // 选择文件的按钮。可选。
            // 内部根据当前运行是创建，可能是input元素，也可能是flash.
            pick: {
                id: '#picker_' + $input_file_name,
                multiple: $multiple
            },
            // 文件限制大小
            fileSingleSizeLimit: $size,
            // 只允许选择文件文件。
            accept: {
                title: 'Files',
                extensions: $ext
            }
        });

        // 当有文件添加进来的时候
        uploader.on( 'fileQueued', function( file ) {
            var $li = '<li id="' + file.id + '" class="list-group-item file-item">' +
                '<span class="pull-right file-state"><span class="text-info"><i class="fa fa-sun-o fa-spin"></i> 正在读取文件信息...</span></span>' +
                '<i class="fa fa-file"></i> ' +
                file.name +
                ' [<a href="javascript:void(0);" class="download-file">下载</a>] [<a href="javascript:void(0);" class="remove-file">删除</a>]' +
                '<div class="progress progress-mini remove-margin active" style="display: none"><div class="progress-bar progress-bar-primary progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div></div>'+
                '</li>';

            if ($multiple) {
                $file_list.append($li);
            } else {
                $file_list.html($li);
                // 清空原来的数据
                $input_file.val('');
            }

            // 设置当前上传对象
            curr_uploader = uploader;
        });

        // 文件上传过程中创建进度条实时显示。
        uploader.on( 'uploadProgress', function( file, percentage ) {
            var $percent = $( '#'+file.id ).find('.progress-bar');
            $percent.css( 'width', percentage * 100 + '%' );
        });

        // 文件上传成功
        uploader.on( 'uploadSuccess', function( file, response ) {
            var $li = $( '#'+file.id );
            if (response.code) {
                if ($multiple) {
                    if ($input_file.val()) {
                        $input_file.val($input_file.val() + ',' + response.id);
                    } else {
                        $input_file.val(response.id);
                    }
                    $li.find('.remove-file').attr('data-id', response.id);
                } else {
                    $input_file.val(response.id);
                }
            }
            // 加入提示信息
            $li.find('.file-state').html('<span class="text-'+ response.class +'">'+ response.info +'</span>');
            // 添加下载链接
            $li.find('.download-file').attr('href', response.path);

            // 文件上传成功后的自定义回调函数
            if (window['dp_file_upload_success'] !== undefined) window['dp_file_upload_success']();
            // 文件上传成功后的自定义回调函数
            if (window['dp_file_upload_success_'+$input_file_name] !== undefined) window['dp_file_upload_success_'+$input_file_name]();
        });

        // 文件上传失败，显示上传出错。
        uploader.on( 'uploadError', function( file ) {
            var $li = $( '#'+file.id );
            $li.find('.file-state').html('<span class="text-danger">服务器发生错误~</span>');

            // 文件上传出错后的自定义回调函数
            if (window['dp_file_upload_error'] !== undefined) window['dp_file_upload_error']();
            // 文件上传出错后的自定义回调函数
            if (window['dp_file_upload_error_'+$input_file_name] !== undefined) window['dp_file_upload_error_'+$input_file_name]();
        });

        // 文件验证不通过
        uploader.on('error', function (type) {
            switch (type) {
                case 'Q_TYPE_DENIED':
                    Stars.notify('文件类型不正确，只允许上传后缀名为：'+$ext+'，请重新上传！', 'danger');
                    break;
                case 'F_EXCEED_SIZE':
                    Stars.notify('文件不得超过'+ ($size/1024) +'kb，请重新上传！', 'danger');
                    break;
            }
        });

        // 完成上传完了，成功或者失败，先删除进度条。
        uploader.on( 'uploadComplete', function( file ) {
            setTimeout(function(){
                $('#'+file.id).find('.progress').remove();
            }, 500);

            // 文件上传完成后的自定义回调函数
            if (window['dp_file_upload_complete'] !== undefined) window['dp_file_upload_complete']();
            // 文件上传完成后的自定义回调函数
            if (window['dp_file_upload_complete_'+$input_file_name] !== undefined) window['dp_file_upload_complete_'+$input_file_name]();
        });

        // 删除文件
        $file_list.delegate('.remove-file', 'click', function(){
            if ($multiple) {
                var id  = $(this).data('id'),
                    ids = $input_file.val().split(',');

                if (id) {
                    for (var i = 0; i < ids.length; i++) {
                        if (ids[i] == id) {
                            ids.splice(i, 1);
                            break;
                        }
                    }
                    $input_file.val(ids.join(','));
                }
            } else {
                $input_file.val('');
            }
            $(this).closest('.file-item').remove();
        });

        // 将上传实例存起来
        webuploader.push(uploader);
    });

    // 图片上传
    $('.js-upload-image,.js-upload-images').each(function () {
        var $input_file       = $(this).find('input');
        var $input_file_name  = $input_file.attr('name');
        // 是否多图片上传
        var $multiple         = $input_file.data('multiple');
        // 允许上传的后缀
        var $ext              = $input_file.data('ext');
        // 图片限制大小
        var $size             = $input_file.data('size');
        // 缩略图参数
        var $thumb            = $input_file.data('thumb');
        // 水印参数
        var $watermark        = $input_file.data('watermark');
        // 图片列表
        var $file_list        = $('#file_list_' + $input_file_name);
        // 优化retina, 在retina下这个值是2
        var ratio             = window.devicePixelRatio || 1;
        // 缩略图大小
        var thumbnailWidth    = 100 * ratio;
        var thumbnailHeight   = 100 * ratio;
        // 实例化上传
        var uploader = WebUploader.create({
            // 选完图片后，是否自动上传。
            auto: true,
            // 去重
            duplicate: true,
            // 不压缩图片
            resize: false,
            compress: false,
            // swf图片路径
            swf: lwwan.WebUploader_swf,
            // 图片接收服务端。
            server: lwwan.image_upload_url,
            // 选择图片的按钮。可选。
            // 内部根据当前运行是创建，可能是input元素，也可能是flash.
            pick: {
                id: '#picker_' + $input_file_name,
                multiple: $multiple
            },
            // 图片限制大小
            fileSingleSizeLimit: $size,
            // 只允许选择图片文件。
            accept: {
                title: 'Images',
                extensions: $ext,
                mimeTypes: 'image/jpg,image/jpeg,image/bmp,image/png,image/gif'
            },
            // 自定义参数
            formData: {
                thumb: $thumb,
                watermark: $watermark
            }
        });

        // 当有文件添加进来的时候
        uploader.on( 'fileQueued', function( file ) {
            var $li = $(
                    '<div id="' + file.id + '" class="file-item js-gallery thumbnail">' +
                    '<a data-magnify="gallery" id="iview"><img></a>' +
                    '<div class="info">' + file.name + '</div>' +
                    '<i class="fa fa-times-circle remove-picture"></i>' +
                    ($multiple ? '<i class="fa fa-fw fa-arrows move-picture"></i>' : '') +
                    '<div class="progress progress-mini remove-margin active" style="display: none">' +
                    '<div class="progress-bar progress-bar-primary progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>' +
                    '</div>' +
                    '<div class="file-state img-state"><div class="bg-info">正在读取...</div>' +
                    '</div>'
                ),
                $img = $li.find('img');

            if ($multiple) {
                $file_list.append( $li );
            } else {
                $file_list.html( $li );
                $input_file.val('');
            }

            // 创建缩略图
            // 如果为非图片文件，可以不用调用此方法。
            // thumbnailWidth x thumbnailHeight 为 100 x 100
            uploader.makeThumb( file, function( error, src ) {
                if ( error ) {
                    $img.replaceWith('<span>不能预览</span>');
                    return;
                }
                $img.attr( 'src', src );
            }, thumbnailWidth, thumbnailHeight );

            // 设置当前上传对象
            curr_uploader = uploader;
        });

        // 文件上传过程中创建进度条实时显示。
        uploader.on( 'uploadProgress', function( file, percentage ) {
            var $percent = $( '#'+file.id ).find('.progress-bar');
            $percent.css( 'width', percentage * 100 + '%' );
        });

        // 文件上传成功
        uploader.on( 'uploadSuccess', function( file, response ) {
            var $li = $( '#'+file.id );

            if (response.code) {
                if ($multiple) {
                    if ($input_file.val()) {
                        $input_file.val($input_file.val() + ',' + response.id);
                    } else {
                        $input_file.val(response.id);
                    }
                    $li.find('.remove-picture').attr('data-id', response.id);
                } else {
                    $input_file.val(response.id);
                }
            }

            $li.find('.file-state').html('<div class="bg-'+response.class+'">'+response.info+'</div>');
            $li.find('img').attr('data-original', response.path);
			$li.find('#iview').attr('href', response.path);
			Stars.iview();
            // 文件上传成功后的自定义回调函数
            if (window['dp_image_upload_success'] !== undefined) window['dp_image_upload_success']();
            // 文件上传成功后的自定义回调函数
            if (window['dp_image_upload_success_'+$input_file_name] !== undefined) window['dp_image_upload_success_'+$input_file_name]();
        });

        // 文件上传失败，显示上传出错。
        uploader.on( 'uploadError', function( file ) {
            var $li = $( '#'+file.id );
            $li.find('.file-state').html('<div class="bg-danger">服务器错误</div>');

            // 文件上传出错后的自定义回调函数
            if (window['dp_image_upload_error'] !== undefined) window['dp_image_upload_error']();
            // 文件上传出错后的自定义回调函数
            if (window['dp_image_upload_error_'+$input_file_name] !== undefined) window['dp_image_upload_error_'+$input_file_name]();
        });

        // 文件验证不通过
        uploader.on('error', function (type) {
            switch (type) {
                case 'Q_TYPE_DENIED':
                    Stars.notify('图片类型不正确，只允许上传后缀名为：'+$ext+'，请重新上传！', 'danger');
                    break;
                case 'F_EXCEED_SIZE':
                    Stars.notify('图片不得超过'+ ($size/1024) +'kb，请重新上传！', 'danger');
                    break;
            }
        });

        // 完成上传完了，成功或者失败，先删除进度条。
        uploader.on( 'uploadComplete', function( file ) {
            setTimeout(function(){
                $( '#'+file.id ).find('.progress').remove();
            }, 500);

            // 文件上传完成后的自定义回调函数
            if (window['dp_image_upload_complete'] !== undefined) window['dp_image_upload_complete']();
            // 文件上传完成后的自定义回调函数
            if (window['dp_image_upload_complete_'+$input_file_name] !== undefined) window['dp_image_upload_complete_'+$input_file_name]();
        });

        // 删除图片
        $file_list.delegate('.remove-picture', 'click', function(){
            $(this).closest('.file-item').remove();
            if ($multiple) {
                var ids = [];
                $file_list.find('.remove-picture').each(function () {
                    ids.push($(this).data('id'));
                });
                $input_file.val(ids.join(','));
            } else {
                $input_file.val('');
            }
			Stars.iview();
        });

        // 将上传实例存起来
        webuploader.push(uploader);

        // 如果是多图上传，则实例化拖拽
        if ($multiple) {
            $file_list.sortable({
                connectWith: ".uploader-list",
                handle: '.move-picture',
                stop: function () {
                    var ids = [];
                    $file_list.find('.remove-picture').each(function () {
                        ids.push($(this).data('id'));
                    });
                    $input_file.val(ids.join(','));
                }
            }).disableSelection();
        }
    });

	// 表单项依赖触发
    if (lwwan.triggers != '') {
        /* 依赖显示 */
        // 先隐藏依赖项
        var $field_hide   = lwwan.field_hide.split(',') || [];
        var $field_values = lwwan.field_values.split(',') || [];
        for (var index in $field_hide) {
            $('#form_group_'+$field_hide[index]).addClass('form_group_hide');
        }

        var $form_builder = $('.form');

        $.each(lwwan.triggers, function (trigger, content) {
            $form_builder.on('ifChanged', "input[name='"+ trigger +"']", function (event, init) {
                var $trigger = $(this);
                var $value   = $trigger.val();

                $(content).each(function () {
                    var $self = $(this);
                    var $values  = $self[0].split(',') || [];
                    var $targets = $self[1].split(',') || [];

                    // 如果触发的元素是单选，且没有选中则设置值为空
                    if ($trigger.attr('type') == 'radio' && $trigger.is(':checked') == false) {
                        $value = '';
                    }

                    if ($.inArray($value, $values) >= 0) {
                        // 符合指定的值，显示对应的表单项
                        for (var index in $targets) {
                            // 如果不是该对象自身直接创建的属性（也就是该属//性是原型中的属性），则跳过显示
                            if (!$targets.hasOwnProperty(index)) {
                                continue;
                            }
                            $('#form_group_'+$targets[index]).removeClass('form_group_hide');
                        }
                    } else {
                        for (var item in $targets) {
                            if (!$targets.hasOwnProperty(item)) {
                                continue;
                            }

                            // 隐藏表单项
                            var $form_item = $('#form_group_'+$targets[item]).addClass('form_group_hide');

                            if (lwwan._field_clear[trigger] !== undefined && lwwan._field_clear[trigger] === 1) {
                               if ($.type(wangeditors) == 'object' && wangeditors[$targets[item]] != undefined) {
                                    // 清除wang编辑器内容
                                    wangeditors[$targets[item]].clear();
                                }

                                // 清除表单内容
                                if ($form_item.find("[name='"+$targets[item]+"']").attr('type') == 'radio') {
                                    $form_item.find("[name='"+$targets[item]+"']:checked").prop('checked', false).trigger("change");
                                } else if ($form_item.find("[name='"+$targets[item]+"[]']").attr('type') == 'checkbox') {
                                    $form_item.find("[name='"+$targets[item]+"[]']:checked").prop('checked', false).trigger("change");
                                } else if ($form_item.find("[name='"+$targets[item]+"']").attr('data-ext') != undefined) {
                                    $form_item.find("[name='"+$targets[item]+"']").val(null);
                                } else {
                                    $form_item.find("[name^='"+$targets[item]+"']").val(null).trigger("change");
                                }

                                // 清除上传文件
                                $form_item.find('#file_list_'+$targets[item]).empty();

                                // 清空标签
                                if ($form_item.find('.js-tags-input').length) {
                                    $form_item.find('.js-tags-input').importTags('');
                                }
                            }
                        }
                    }
                });
            });

            // 有默认值时触发
            var trigger_value = '';
            if ($form_builder.find('[name='+ trigger +']').attr('type') == 'radio') {
                trigger_value = $form_builder.find('[name='+ trigger +']:checked').val() || '';
                if (trigger_value != '' && $.inArray(trigger_value, $field_values) >= 0) {
                    var $radio_id = $('.form-builder [name='+ trigger +']:checked').attr('id');
                    $('.form-builder #'+$radio_id).trigger("change", ['1']);
                }
            } else {
                trigger_value = $form_builder.find('[name='+ trigger +']').val() || '';
                if (trigger_value != '' && $.inArray(trigger_value, $field_values) >= 0) {
                    $('.form-builder [name='+ trigger +']').trigger("change");
                }
            }
        });
    }
});