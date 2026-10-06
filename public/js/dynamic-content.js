$(document).ready(function () {
    $(".collapse-header").click(function () {
        var $icon = $(this).find("i");
        var $child = $(this).parent().next();
        $child.slideToggle(500, function () {
            $child.is(":visible") ? $icon.removeClass("fa-plus-square").addClass("fa-minus-square") : $icon.removeClass("fa-minus-square").addClass("fa-plus-square");
        });
    });
});