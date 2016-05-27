$(document).ready(function() {
    $('.carousel-three-by-two').height($('.carousel-three-by-two').width()*2/3);
    $('.modal-carousel').on('shown.bs.modal', function() {
        console.log( 'shown' );
        $('.carousel-three-by-two').height($('.carousel-three-by-two').width()*2/3);
    })
});

