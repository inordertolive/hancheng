$(function () {
    // 字段定义列表
    var $field_define_list = {
		number: "int(11) UNSIGNED NOT NULL",
        text: "varchar(256) NOT NULL",
        textarea: "varchar(256) NOT NULL",
        password: "varchar(128) NOT NULL",
        checkbox: "varchar(256) NOT NULL",
        radio: "tinyint(1) NOT NULL",
        datetime: "int(11) UNSIGNED NOT NULL",
        array: "varchar(256) NOT NULL",
        select: "varchar(256) NOT NULL",
        linkage: "varchar(256) NOT NULL",
        linkages: "varchar(256) NOT NULL",
        image: "int(11) UNSIGNED NOT NULL",
        images: "varchar(256) NOT NULL",
        file: "int(11) UNSIGNED NOT NULL",
        files: "varchar(256) NOT NULL",
        wangeditor: "text NOT NULL",
		money: "decimal(10,2) NOT NULL",
		alivideo: "int(11) UNSIGNED NOT NULL",
		alivideo: "varchar(256) NOT NULL",
    };
    // 选择自动类型，自动填写字段定义
    var $field_define = $('input[name=define]');
	var $field_value = $('input[name=value]');
    $('select[name=type]').change(function () {
		var val = $(this).val();
        $field_define.val($field_define_list[$(this).val()] || '');
		if(val == 'number' || val == 'datetime' || val == 'image' || val == 'file' || val == 'money'){
			$field_value.val(0);
		}else{
			$field_value.val('');
		}
    });
});