var imageId = 0;

$(document).ready(function() {
	$('#post-tags-select').change(function() {
		addTag($(this));
	});
	
	$('#add-text-button').click(function(){
		addTextArea();
	});
	
	$('#add-image-button').click(function(){
		addImageArea();
	});
	
	$('#post-image-holder').uploadFile({
        url : "/api/upload-blog-images.php",
        uploadStr : "<span class='bootstrap-dialog-button-icon glyphicon glyphicon-upload'></span> Upload Images",
        multiple : true,
        dragDrop : true,
        uploadButtonLocation : $('#post-button-holder'),
        uploadContainer : $('#post-image-holder'),
        uploadButtonClass : "btn btn-default btn-info",
        statusBarWidth : "auto",
        dragdropWidth : "100%",
        fileName : "myfile",
        sequential : true,
        sequentialCount : 5,
        acceptFiles : "image/*",
        uploadQueueOrder : "bottom",
        onSubmit : function() {
            $('.ajax-file-upload-container').show();
        },
        onSuccess : function(files, data, xhr, pd) {
        	data = JSON.parse(data);
        	pd.statusbar.remove();
        	if( $.isArray(data) ) {
        		$.each(files, function(key, value){
        			var img = $("<img>");
        			img.attr({
        				id: imageId++,
        				src: "/tmp/" + value,
        				width: "100px",
        			});
        			img.addClass('draggable');
        			img.draggable();
        			img.dblclick(function () {
                        removeImage($(this));
        			});
        			$('#post-image-holder').append(img);
        		});
        	} else {
        		$('#post-image-holder').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");

        	}
        },
        afterUploadAll : function() {
            setTimeout(function() {
                $('.ajax-file-upload-container').hide();
            }, 5000);
        },
    });
	
	addImageArea();
});

function newTag(ele) {
	BootstrapDialog.show({
        draggable : true,
        title : 'Add a New Category',
        message : '<input placeholder="Category Name" id="new-category-name" type="text" class="form-control"/>',
        buttons : [ {
            icon : 'glyphicon glyphicon-plus',
            label : ' Create Category',
            hotkey : 13,
            cssClass : 'btn-success',
            action : function(dialogItself) {
                var $button = this; // 'this' here is a jQuery object that
                                    // wrapping the <button> DOM element.
                var modal = $button.closest('.modal-content');
                $button.spin();
                dialogItself.enableButtons(false);
                dialogItself.setClosable(false);
                // send our update
                $.post("/api/create-blog-tag.php", {
                    tag : $('#new-category-name').val(),
                }).done(function(data) {
                    // add the option to the select field, and select it
                    if ($.isNumeric(data) && data !== '0') {
                    	modal.find('.bootstrap-dialog-body').append("<div class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Successfully added category</div>");
                    	var option = $('<option>');
                    	option.val(data);
                    	option.html($('#new-category-name').val());
                    	ele.append(option);
                    	ele.val(data);
                    	addTag(ele);
                    	dialogItself.close();
                    } else if (data === '0') {
                    	modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while creating your new blog category.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                    } else {
                    	modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
                    }
                }).fail(function(){
                	modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while creating your new blog category.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                }).always(function(){
                	$button.stopSpin();
                	dialogItself.enableButtons(true);
                	dialogItself.setClosable(true);
                });
            }
        }, {
            label : 'Close',
            action : function(dialogItself) {
                dialogItself.close();
            }
        } ],
    });
}

function addTag(ele) {
	if (ele.val() === "0") {
		newTag(ele);
		return;
	}
	var tagSpan = $('<span>');
	tagSpan.addClass('selected-tag');
	tagSpan.attr('tag-id', ele.val());
	tagSpan.html($('option:selected', ele).text());
	tagSpan.click(function() {
		removeTag($(this));
	});
	ele.parent().append(tagSpan);
	$('option:selected', ele).remove();
}

function removeTag(ele) {
	var option = $('<option>');
	option.val(ele.attr('tag-id'));
	option.html(ele.text());
	$('#post-tags-select').append(option);
	ele.remove();
	
}