$(function($) {
	$('#createFile').click(function(){
	    var ids = new Array();
		var objs=$("input[type=checkbox]:checked");
		if(objs.length<=0){			
			$.Toast("友情提示", '请选择接口','warning', {
							stack: true,
							sticky:true,
							position_class: "toast-center-center",
							has_icon:true,
							has_close_btn:true,
							fullscreen:false,
							timeout:1500,
							has_progress:true,
							rtl:false,
       		});
			return false;
		}
		objs.each(function(){
		     ids.push($(this).val());												 
		})
		$.ajax({
            type:"POST",
            dataType:"json",
            url:"/admin.php/admin/apilist/createFile",
            data:'ids='+ids.join(','),
            success:function(result) {
				var type='';
                if(result.code==1){
					type='success';
			    }else{
					type='warning';
			    }
				$.Toast("友情提示", result.msg,type, {
							stack: true,
							sticky:true,
							position_class: "toast-center-center",
							has_icon:true,
							has_close_btn:true,
							fullscreen:false,
							timeout:1500,
							has_progress:true,
							rtl:false,
       			});
            },
        });
	})
});
