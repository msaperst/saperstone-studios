$(document).ready(function () {
    $('[data-background-image]').each(function () {
        this.style.backgroundImage = 'url("' + $(this).attr('data-background-image') + '")';
    });

    $('.carousel-three-by-two').height($('.carousel-three-by-two').width() * 2 / 3);
    $('.modal-carousel').on('shown.bs.modal', function () {
        $('.carousel-three-by-two').height($('.carousel-three-by-two').width() * 2 / 3);
    });
});