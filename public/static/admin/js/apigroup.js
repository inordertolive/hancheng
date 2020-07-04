$(function($) {
	$("#module").change( function() {
		var module = $(this).val();
		$.get("/admin.php/admin/apilist/get_group?module=" + module, function(res){
			$('#group').html('');
			$.each(res, function(i) {
				var option = new Option(res[i], i, true, true);
				$('#group').append(option);
		    });
		    $('#group').trigger("change");
		});
	});
});
