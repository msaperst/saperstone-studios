var resultsSelected = false;

$(document).ready(function() {
    $('#delete-image-btn').click(function(){
        var img = $('.carousel div.active div');
        BootstrapDialog.show({
            draggable: true,
            title: 'Are You Sure?',
            message: 'Are you sure you want to delete the image <b>' + img.attr('alt') + '</b>',
            buttons: [{
                icon: 'glyphicon glyphicon-trash',
                label: ' Delete',
                cssClass: 'btn-danger',
                action: function(dialogInItself){
                    var $button = this; // 'this' here is a jQuery object that wrapping the <button> DOM element.
                    $button.spin();
                    dialogInItself.enableButtons(false);
                    dialogInItself.setClosable(false);
                    //send our update
                    $.post("/api/delete-gallery-image.php", {
                        gallery : img.attr('gallery-id'),
                        image : img.attr('image-id')
                    }).done(function() {
                        dialogInItself.close();
                        //go to the next image
                        $('.carousel').carousel("next");
                        //cleanup the dom
                        $('.gallery img[alt="'+img.attr('alt')+'"]').parent().remove();
                        img.parent().remove();
                    });
                }
            }, {
                label: 'Close',
                action: function(dialogInItself){
                    dialogInItself.close();
                }
            }]
        });
    });
});