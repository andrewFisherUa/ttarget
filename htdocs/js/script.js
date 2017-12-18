var fancyDefaults = {
    "transitionIn": "elastic",
    "transitionOut": "elastic",
    "speedIn": 600,
    "speedOut": 200,
    "overlayShow": true,
    "aspectRatio": true,
    "hideOnContentClick": false,
    "hideOnOverlayClick": false,
    "enableEscapeButton": false,
    "wrapCSS": "modal show",
    "autoSize": false,
    "padding": 0,
    "closeClick": false,
    "autoCenter": true,
    "centerOnScroll": true,
    "closeBtn": false,
    "height": "auto"
};
$(document).ready(function () {
    if ($.fn.dataTable) {
        $('.datatable').dataTable({
            bPaginate: false,
            "sScrollY": "auto",
            bInfo: false,
            bSort: false,
            oLanguage: {
                "sSearch": "",
                "sZeroRecords": "Не найдено подходящих записей"
            }
        });
    }
    if ($.fn.selectpicker) {
        $('.selectpicker').selectpicker();
    }
    /*if ($.fn.datepicker) {
        $('.input-date').datepicker({'weekStart': 1});
    }*/
    if ($.fn.jqBootstrapValidation) {
        $(".jqvalidate input, .jqvalidate select").not("[type=submit]").jqBootstrapValidation();
    }
    $('.enable-all-button').change(function () {
        var checkboxes = $($(this).parents('table').get(0)).find('input[type=checkbox]');
        if ($(this).is(':checked'))
            $(checkboxes).prop('checked', true);
        else
            $(checkboxes).prop('checked', false);
    });
    $('.main-search').focusin(
        function () {
            if ($(".modal-backdrop").length == 0) {
                $('body').append('<div class="modal-backdrop"></div>');
            }
        }).focusout(
        function () {
            $('.modal-backdrop').remove();
        });

    $('#add_user').live('click', function () {
        $.ajax({
            type: "POST",
            url: "/users/returnForm",
            data: {"YII_CSRF_TOKEN": CSRF_TOKEN},
            beforeSend: function () {
                $("#users-grid").addClass("ajax-sending");
            },
            complete: function () {
                $("#users-grid").removeClass("ajax-sending");
            },
            success: function (data) {
                //modal show
                $.fancybox(data,
                    {    "transitionIn": "elastic",
                        "transitionOut": "elastic",
                        "speedIn": 600,
                        "speedOut": 200,
                        "overlayShow": true,
                        "aspectRatio": true,
                        "hideOnContentClick": false,
                        "hideOnOverlayClick": false,
                        "enableEscapeButton": false,
                        "wrapCSS": "modal show",
                        //"modal": "true",
                        "overlayShow": true,
                        "width": 400,
                        "minWidth": 400,
                        "autoSize": false,
                        "padding": 0,
                        "closeClick": false,
                        "autoCenter": true,
                        "centerOnScroll": true,
                        "closeBtn": false,
                        "height": "auto",
                        "afterClose": function () {
                            window.location.href = window.location.href;
                        } //onclosed function
                    });//fancybox
            } //success
        });//ajax
        return false;
    });//bind

    $(document).on('click', '.user-story-title', editUser);
});

var editUser = function() {
    var id = parseInt($(this).attr('href'));
    data = {"YII_CSRF_TOKEN": CSRF_TOKEN };
    if(!isNaN(id)){
        data["update_id"] = id;
    }
    $.ajax({
        type: "POST",
        url: "/users/returnForm",
        data: data,
        beforeSend: function () {
            $("#users-grid").addClass("ajax-sending");
        },
        complete: function () {
            $("#users-grid").removeClass("ajax-sending");
        },
        success: function (data) {
            $.fancybox(data,
                $.extend({}, fancyDefaults, {
                    "width": 540,
                    "minWidth": 540,
                    "afterClose": function () {
                        window.location.href = window.location.href;
                    } //onclosed function
                })
            );//fancybox
            //  console.log(data);
        } //success
    });//ajax
    return false;
}

var editCampaign = function (user_id, campaign_id){
	var data = {};
	if(typeof campaign_id != "undefined"){
		data['update_id'] = campaign_id;
	}else{
		data['u'] = user_id;
	}
	$.ajax({
		type: "GET",
		//url: "/campaigns/returnForm",
		url: "/campaigns/edit/" + campaign_id,
		//data: data,
		beforeSend: function () {
			$("#users-grid").addClass("ajax-sending");
		},
		complete: function () {
			$("#users-grid").removeClass("ajax-sending");
		},
		success: function (data) {
			$.fancybox(data,
				$.extend({}, fancyDefaults, {
					"width": 558,
					"minWidth": 558,
					"afterClose": function () {
						if(
							typeof campaignEditActionId != "undefined" && typeof lastAjaxResponse != "undefined"
								&& campaignEditActionId != false && lastAjaxResponse.success == true
						){
							if(campaignEditActionId === true){
								editAction(undefined, lastAjaxResponse.id);
							}else{
								editAction(campaignEditActionId);
							}
						}else{
							window.location.href = window.location.href;
						}
					} //onclosed function
				})
			);//fancybox
			//  console.log(data);
		} //success
	});//ajax
}

var facyboxClose = function(){
    $.fancybox.close();
    return false;
}

var updateGrid = function(id, options){
    if(typeof options == "undefined"){
        options = {};
    }
    options = $.extend({},{
        complete: function(jqXHR, status) {
            $("#news-grid").removeClass("ajax-sending");
        }
    }, options);

    $("#"+id).addClass("ajax-sending");
    $.fn.yiiGridView.update(id,options);
}