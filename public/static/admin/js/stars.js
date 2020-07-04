/*公共js处理*/
var Stars = function () {
	/**
     * ajax提交表单
     * @author 似水星辰 <2630481389@qq.com>
     */
	jQuery('#form').on('valid.form', function(e) {
		pageLoader();
		var form_data = $(this).serialize();
		var btn = $(this).find('button[type="submit"]');
		jQuery.post($(this).attr('action'), form_data, function(res) {
			pageLoader('hide');
			msg = res.msg;

			if (res.code) {
				if (res.url && !$(this).hasClass("no-refresh")) {
					msg += "， 即将返回指定页面~";
				}
				tips(msg, 'success');
				setTimeout(function() {
                    if ($('#form').attr('parent_reload')) {
                        parent.location.reload();
                        var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
                        parent.layer.close(index);
                        return  false;
                    }
					btn.attr("autocomplete", "on").prop("disabled", false);
					return $(this).hasClass("no-refresh") ? false : void(res.url && !$(this).hasClass("no-forward") ? location.href = res.url : location.reload());
				}, 1000);
			} else {
				tips(msg, 'danger');
				btn.attr("autocomplete", "on").prop("disabled", false);
			}
		}, "json").fail(function(res) {
			Stars.loading('hide');
			btn.attr("autocomplete", "on").prop("disabled", false);
			Stars.notify($(res.responseText).find('h1').text() || '服务器内部错误~', 'danger');
		});
	});
    /**
     * 处理ajax方式的post提交
     * @author 似水星辰 <2630481389@qq.com>
     */
    var ajaxPost = function () {
        jQuery(document).on('click', '.ajax-post', function () {
            var msg, self   = jQuery(this), ajax_url = self.attr("href") || self.data("url");
            var target_form = self.attr("target-form");
            var text        = self.data('tips');
            var title       = self.data('title') || '确定要执行该操作吗？';
            var confirm_btn = self.data('confirm') || '确定';
            var cancel_btn  = self.data('cancel') || '取消';
            var form        = jQuery('form[name=' + target_form + ']');
            if (form.length === 0) {
                form = jQuery('.' + target_form);
            }
            var form_data   = form.serialize();

            if ("submit" === self.attr("type") || ajax_url) {
                if (undefined === form.get(0)) return false;
                if ("FORM" === form.get(0).nodeName) {
                    ajax_url = ajax_url || form.get(0).action;

                    if (self.hasClass('confirm')) {
                        showConfirm({
                            title: title,
                            text: text || '',
                            type: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d26a5c',
                            confirmButtonText: confirm_btn,
                            cancelButtonText: cancel_btn,
                            closeOnConfirm: true,
                            html: false
                        }, function () {
                            pageLoader();
                            self.attr("autocomplete", "off").prop("disabled", true);
							
							jQuery.post(ajax_url, form_data,function(res){
							  pageLoader('hide');
                                msg = res.msg;
                                if (res.code) {
                                    if (res.url && !self.hasClass("no-refresh")) {
                                        msg += " 页面即将自动跳转~";
                                    }
                                    tips(msg, 'success');
                                    setTimeout(function () {
                                        self.attr("autocomplete", "on").prop("disabled", false);
                                        // 关闭弹出框
                                        if (res.data && (res.data === '_close_pop' || res.data._close_pop)) {
                                            var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
                                            parent.layer.close(index);return false;
                                        }
                                        // 刷新父窗口
                                        if (res.data && (res.data === '_parent_reload' || res.data._parent_reload)) {
                                            parent.location.reload();return false;
                                        }
                                        return self.hasClass("no-refresh") ? false : void(res.url && !self.hasClass("no-forward") ? location.href = res.url : location.reload());
                                    }, 1500);
                                } else {
                                    jQuery(".reload-verify").length > 0 && jQuery(".reload-verify").click();
                                    tips(msg, 'danger');
                                    setTimeout(function () {
                                        // 关闭弹出框
                                        if (res.data && (res.data === '_close_pop' || res.data._close_pop)) {
                                            var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
                                            parent.layer.close(index);return false;
                                        }
                                        // 刷新父窗口
                                        if (res.data && (res.data === '_parent_reload' || res.data._parent_reload)) {
                                            parent.location.reload();return false;
                                        }
                                        self.attr("autocomplete", "on").prop("disabled", false);
                                    }, 2000);
                                }
							}, "json")
							.fail(function () {
								Stars.loading('hide');
								Stars.notify('服务器错误~', 'danger');
							});
                        });
                        return false;
                    } else {
                        self.attr("autocomplete", "off").prop("disabled", true);
                    }
                } else if ("INPUT" === form.get(0).nodeName || "SELECT" === form.get(0).nodeName || "TEXTAREA" === form.get(0).nodeName) {

                    // 如果是多选，则检查是否选择
                    if (form.get(0).type === 'checkbox' && form_data === '') {
                        Stars.notify('请选择要操作的数据', 'warning');
                        return false;
                    }

                    // 提交确认
                    if (self.hasClass('confirm')) {
						BootstrapDialog.confirm({
							title : '友情提醒',
							message : title || '',
							type : BootstrapDialog.TYPE_DANGER,
							closable : true, // <-- Default value is false，点击对话框以外的页面内容可关闭
							draggable : true, // <-- Default value is false，可拖拽
							btnCancelLabel : cancel_btn, // <-- Default value is 'Cancel',
							btnOKLabel : confirm_btn, // <-- Default value is 'OK',
							btnOKClass : 'btn-warning btn-flat',
							btnCancelClass : 'btn-default btn-flat',// <-- If you didn't specify it, dialog type
							size : BootstrapDialog.SIZE_SMALL,
							cssClass : 'confirm-dialog',
							callback : function(result) {
								if (result) {
									pageLoader();
									self.attr("autocomplete", "off").prop("disabled", true);

									// 发送ajax请求
									jQuery.post(ajax_url, form_data,function(res){
										pageLoader('hide');
										msg = res.msg;
										if (res.code) {
											if (res.url && !self.hasClass("no-refresh")) {
												msg += " 页面即将自动跳转~";
											}
											tips(msg, 'success');
											setTimeout(function () {
												self.attr("autocomplete", "on").prop("disabled", false);
												// 关闭弹出框
												if (res.data && (res.data === '_close_pop' || res.data._close_pop)) {
													var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
													parent.layer.close(index);return false;
												}
												// 刷新父窗口
												if (res.data && (res.data === '_parent_reload' || res.data._parent_reload)) {
													parent.location.reload();return false;
												}
												return self.hasClass("no-refresh") ? false : void(res.url && !self.hasClass("no-forward") ? location.href = res.url : location.reload());
											}, 1000);
										} else {
											jQuery(".reload-verify").length > 0 && jQuery(".reload-verify").click();
											tips(msg, 'danger');
											setTimeout(function () {
												// 关闭弹出框
												if (res.data && (res.data === '_close_pop' || res.data._close_pop)) {
													var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
													parent.layer.close(index);return false;
												}
												// 刷新父窗口
												if (res.data && (res.data === '_parent_reload' || res.data._parent_reload)) {
													parent.location.reload();return false;
												}
												self.attr("autocomplete", "on").prop("disabled", false);
											}, 1000);
										}
									}, "json")
									.fail(function (res) {
										pageLoader('hide');
										tips($(res.responseText).find('h1').text() || '服务器内部错误~', 'danger');
										self.attr("autocomplete", "on").prop("disabled", false);
									});
								}
							}
						});
                        return false;
                    } else {
                        self.attr("autocomplete", "off").prop("disabled", true);
                    }
                } else {
                    if (self.hasClass("confirm")) {
                        swal({
                            title: title,
                            text: text || '',
                            type: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d26a5c',
                            confirmButtonText: confirm_btn,
                            cancelButtonText: cancel_btn,
                            closeOnConfirm: true,
                            html: false
                        }, function () {
                            pageLoader();
                            self.attr("autocomplete", "off").prop("disabled", true);
                            form_data = form.find("input,select,textarea").serialize();

                            // 发送ajax请求
                            jQuery.post(ajax_url, form_data, {}, 'json').success(function(res) {
                                pageLoader('hide');
                                msg = res.msg;
                                if (res.code) {
                                    if (res.url && !self.hasClass("no-refresh")) {
                                        msg += " 页面即将自动跳转~";
                                    }
                                    tips(msg, 'success');
                                    setTimeout(function () {
                                        self.attr("autocomplete", "on").prop("disabled", false);
                                        // 关闭弹出框
                                        if (res.data && (res.data === '_close_pop' || res.data._close_pop)) {
                                            var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
                                            parent.layer.close(index);return false;
                                        }
                                        // 刷新父窗口
                                        if (res.data && (res.data === '_parent_reload' || res.data._parent_reload)) {
                                            parent.location.reload();return false;
                                        }
                                        return self.hasClass("no-refresh") ? false : void(res.url && !self.hasClass("no-forward") ? location.href = res.url : location.reload());
                                    }, 1500);
                                } else {
                                    jQuery(".reload-verify").length > 0 && jQuery(".reload-verify").click();
                                    tips(msg, 'danger');
                                    setTimeout(function () {
                                        // 关闭弹出框
                                        if (res.data && (res.data === '_close_pop' || res.data._close_pop)) {
                                            var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
                                            parent.layer.close(index);return false;
                                        }
                                        // 刷新父窗口
                                        if (res.data && (res.data === '_parent_reload' || res.data._parent_reload)) {
                                            parent.location.reload();return false;
                                        }
                                        self.attr("autocomplete", "on").prop("disabled", false);
                                    }, 2000);
                                }
                            }).fail(function (res) {
                                pageLoader('hide');
                                tips($(res.responseText).find('h1').text() || '服务器内部错误~', 'danger');
                                self.attr("autocomplete", "on").prop("disabled", false);
                            });
                        });
                        return false;
                    } else {
                        form_data = form.find("input,select,textarea").serialize();
                        self.attr("autocomplete", "off").prop("disabled", true);
                    }
                }
            }

            return false;
        });
    };

    /**
     * 处理ajax方式的get提交
     * @author 似水星辰 <2630481389@qq.com>
     */
    var ajaxGet = function () {
        jQuery(document).on('click', '.ajax-get', function () {
            var msg, self = $(this), text = self.data('tips'), ajax_url = self.attr("href") || self.data("url");
            var title       = self.data('title') || '确定要执行该操作吗？';
            var confirm_btn = self.data('confirm') || '确定';
            var cancel_btn  = self.data('cancel') || '取消';
            // 执行确认
            if (self.hasClass('confirm')) {
				BootstrapDialog.confirm({
					title : '友情提醒',
					message : title || '',
					type : BootstrapDialog.TYPE_DANGER,
					closable : true, // <-- Default value is false，点击对话框以外的页面内容可关闭
					draggable : true, // <-- Default value is false，可拖拽
					btnCancelLabel : cancel_btn, // <-- Default value is 'Cancel',
					btnOKLabel : confirm_btn, // <-- Default value is 'OK',
					btnOKClass : 'btn-danger btn-flat',
					btnCancelClass : 'btn-default btn-flat',// <-- If you didn't specify it, dialog type
					size : BootstrapDialog.SIZE_SMALL,
					cssClass : 'confirm-dialog',
					callback : function(result) {
						if (result) {
							pageLoader();
							self.attr("autocomplete", "off").prop("disabled", true);

							// 发送ajax请求
							jQuery.get(ajax_url, function(res){
								pageLoader('hide');
								msg = res.msg;
								if (res.code) {
									if (res.url && !self.hasClass("no-refresh")) {
										msg += " 页面即将自动跳转~";
									}
									tips(msg, 'success');
									setTimeout(function () {
										self.attr("autocomplete", "on").prop("disabled", false);
										return self.hasClass("no-refresh") ? false : void(res.url && !self.hasClass("no-forward") ? location.href = res.url : location.reload());
									}, 1000);
								} else {
									tips(msg, 'danger');
									setTimeout(function () {
										self.attr("autocomplete", "on").prop("disabled", false);
									}, 1000);
								}
							}).fail(function (res) {
								pageLoader('hide');
								tips($(res.responseText).find('h1').text() || '服务器内部错误~', 'danger');
								self.attr("autocomplete", "on").prop("disabled", false);
							});
						}
					}
				});
			}else{
				pageLoader();
				self.attr("autocomplete", "off").prop("disabled", true);

				// 发送ajax请求
				jQuery.get(ajax_url, function(res){
					pageLoader('hide');
					msg = res.msg;
					if (res.code) {
						if (res.url && !self.hasClass("no-refresh")) {
							msg += " 页面即将自动跳转~";
						}
						tips(msg, 'success');
						setTimeout(function () {
							self.attr("autocomplete", "on").prop("disabled", false);
							return self.hasClass("no-refresh") ? false : void(res.url && !self.hasClass("no-forward") ? location.href = res.url : location.reload());
						}, 1000);
					} else {
						tips(msg, 'danger');
						setTimeout(function () {
							self.attr("autocomplete", "on").prop("disabled", false);
						}, 1000);
					}
				}).fail(function (res) {
					pageLoader('hide');
					tips($(res.responseText).find('h1').text() || '服务器内部错误~', 'danger');
					self.attr("autocomplete", "on").prop("disabled", false);
				});
			}

            return false;
        });
    };

 
    /**
     * 顶部菜单
     * @author 似水星辰 <2630481389@qq.com>
     */
    var topMenu = function () {
        $('.top-menu').click(function () {
            var $target = $(this).attr('target');
            var data = {
                module_id: $(this).data('module-id') || '',
                module: $(this).data('module') || '',
                controller: $(this).data('controller') || ''
            };

            if ($('#nav-' + data.module_id).length) {
                location.href = $('#nav-' + data.module_id).find('a').not('.nav-submenu').first().attr('href');
            } else {
                $.post(lwwan.top_menu_url, data, function (res) {
                    if (res !== '') {
                        if ($target === '_self') {
                            location.href = res;
                        } else {
                            window.open(res);
                        }
                    } else {
						Stars.notify('无任何节点权限', 'danger');
                    }
                }).fail(function (res) {
                    tips($(res.responseText).find('h1').text() || '服务器内部错误~', 'danger');
                });
            }
            return false;
        });
    };

    /**
     * 页面小提示
     * @param $msg 提示信息
     * @param $type 提示类型:'info', 'success', 'warning', 'danger'
     * @param $from 'top', 'bottom', 'center'
     * @param $align 'left', 'right', 'center'
     * @author 似水星辰 <2630481389@qq.com>
     */
    var tips = function ($msg, $type, $from, $align) {
        $type  = $type || 'success';
        $from  = $from || 'center';
        $align = $align || 'center';
		
		$.Toast("友情提示", $msg, $type, {
			stack: true,
			sticky:true,
			position_class: "toast-" + $from + "-" + $align,
			has_icon:true,
			has_close_btn:true,
			fullscreen:false,
			timeout:1500,
			has_progress:true,
			rtl:false,
        });
    };



    /**
     * 页面加载提示
     * @param $mode 'show', 'hide'
     * @author 似水星辰 <2630481389@qq.com>
     */
    var pageLoader = function ($mode) {
        var $loadingEl = jQuery('#loading');
        $mode          = $mode || 'show';

        if ($mode === 'show') {
            if ($loadingEl.length) {
                $loadingEl.fadeIn(250);
            } else {
                jQuery('body').prepend('<div id="loading"><div class="loading-box"><i class="fa fa-2x fa-fw fa-spinner fa-spin"></i> <br/> <span class="loding-text">请稍后...</span></div></div>');
            }
        } else if ($mode === 'hide') {
            if ($loadingEl.length) {
                $loadingEl.fadeOut(250);
            }
        }

        return false;
    };

    /**
     * 刷新页面
     * @author 似水星辰 <2630481389@qq.com>
     */
    var pageReloadLoader = function () {
        // 刷新页面
        $('.page-reload').click(function () {
            location.reload();
        });
    };
	
	/**
     * 图片预览
     * @author 似水星辰 <2630481389@qq.com>
     */
	var imgView = function () {
		//图片预览
		$('[data-magnify]').magnify({
			headToolbar: [
			  'close'
			],
			footToolbar: [
			  'zoomIn',
			  'zoomOut',
			  'prev',
			  'fullscreen',
			  'next',
			  'actualSize',
			  'rotateRight'
			],
			title: false
		});
	};

    return {
        // 初始化
        init: function () {
            ajaxPost();
            ajaxGet();
            topMenu();
            pageReloadLoader();
        },
        // 页面加载提示
        loading: function ($mode) {
            pageLoader($mode);
        },
			// 初始化图片查看
        iview: function () {
            imgView();
        },
        // 页面小提示
        notify: function ($msg, $type, $from, $align) {
            tips($msg, $type, $from, $align);
        },
    };
}();

// Initialize app when page loads
jQuery(function () {
    Stars.init();
	$.validator.setTheme('bootstrap', {
        validClass: 'has-success',
        invalidClass: 'has-error',
        bindClassTo: '.form-group',
        formClass: 'n-default n-bootstrap',
        msgClass: 'n-right'
    });
	jQuery('.select2').each(function () {
        var $select2 = jQuery(this);
        var $width = $select2.data('width') || '100%';
        $select2.select2({
			placeholder: "请选择一项",
            width: $width, //设置下拉框的宽度
            language: "zh-CN",
			allowClear: true,
			maximumSelectionLength: 3  //最多能够选择的个数
        });
    });

	//弹出层
	$(document).on('click', '[data-toggle="dialog"]', function(e) {
		var $this  = $(this)
		var options = $this.data()
		if (!options.url) options.url = $this.attr('href')
		if (!options.width) options.width = 640
		if (!options.height) options.height = 480
        var title = $this.attr("title") || $this.text();
		layer.open({
		  type: 2,
		  title: title,
		  area: [options.width + 'px', options.height + 'px'],
		  shade: 0.8,
		  closeBtn: 1,
		  isOutAnim:false,
		  anim:-1,
		  shadeClose: true,
		  content: options.url,
		  success: function(layero, index){
		    layer.iframeAuto(index);
		  }
		});
        event.preventDefault();
        return false;
	})
	//弹出输入层
	$(document).on('click', '[data-toggle="prompt"]',function(e) {
		var $this = $(this);
		var options = $this.data();
		if (!options.url) options.url = $this.attr('href') 
		var title = $this.attr("title") || $this.text();
		layer.prompt({
			title: '填写拒绝原因',
			formType: 2
		},
		function(text, index) {
			layer.close(index);
			// 发送ajax请求
			jQuery.post(options.url, {
				msg: text
			},
			function(res) {
				msg = res.msg;
				if (res.code) {
					Stars.notify(res.msg, 'success');
					setTimeout(function() {
						// 关闭弹出框
						if (res.data && (res.data === '_close_pop' || res.data._close_pop)) {
							var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
							parent.layer.close(index);
							return false;
						}
						// 刷新父窗口
						if (res.data && (res.data === '_parent_reload' || res.data._parent_reload)) {
							parent.location.reload();
							return false;
						}
						location.reload();
					},
					1000);
				}else{
					Stars.notify(res.msg, 'danger');
				}
			},
			"json")
		});
        event.preventDefault();
        return false;
	})
});