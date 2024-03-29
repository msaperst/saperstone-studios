/*
  Jquery Validation using jqBootstrapValidation
   example is taken from jqBootstrapValidation docs 
 */
$(function () {

    $("#contactForm input,#contactForm textarea")
        .jqBootstrapValidation(
            {
                preventSubmit: true,
                submitSuccess: function ($form, event) {
                    event.preventDefault(); // prevent default submit
                                            // behaviour

                    $('#contactForm button').prop("disabled", true);
                    $('#success').html("<div class='alert alert-warning'>");
                    $('#success > .alert-warning').html("<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;").append("</button>");
                    $('#success > .alert-warning').append("<strong>Sending your message. </strong>");
                    $('#success > .alert-warning').append('</div>');

                    // get values from FORM
                    var name = $("input#name").val();
                    var phone = $("input#phone").val();
                    var email = $("input#email").val();
                    var message = $("textarea#message").val();
                    var firstName = name; // For Success/Failure
                    // Message
                    // Check for white space in name for Success/Fail
                    // message
                    if (firstName.indexOf(' ') >= 0) {
                        firstName = name.split(' ').slice(0, -1).join(' ');
                    }
                    $.ajax({
                        url: "api/contact-me.php",
                        type: "POST",
                        data: {
                            name: name,
                            phone: phone,
                            email: email,
                            resolution: $(window).width() + "x" + $(window).height(),
                            message: message,
                        },
                        cache: false,
                        success: function () {
                            // Success message
                            $('#success').html("<div class='alert alert-success'>");
                            $('#success > .alert-success').html("<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;").append("</button>");
                            $('#success > .alert-success').append("<strong>Your message has been sent. </strong>");
                            $('#success > .alert-success').append('</div>');

                            // clear all fields
                            $('#contactForm').trigger("reset");
                            $('#contactForm button').prop("disabled", false);
                        },
                        error: function () {    //TODO - check for a failure returned
                            // Fail message
                            $('#success').html("<div class='alert alert-danger'>");
                            $('#success > .alert-danger').html("<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;").append("</button>");
                            $('#success > .alert-danger')
                                .append(
                                    "<strong>Sorry " + firstName +
                                    " it seems that my mail server is not responding...</strong> Could you please email me directly to <a href='mailto:la@saperstonestudios.com?Subject=Contact Request;>la@saperstonestudios.com</a> ? Sorry for the inconvenience!");
                            $('#success > .alert-danger').append('</div>');
                            // clear all fields
                            $('#contactForm').trigger("reset");
                            $('#contactForm button').prop("disabled", false);
                        },
                    });
                },
                filter: function () {
                    return $(this).is(":visible");
                },
            });

    $("a[data-toggle=\"tab\"]").click(function (e) {
        e.preventDefault();
        $(this).tab("show");
    });
});

/* When clicking on Full hide fail/success boxes */
$('#name').focus(function () {
    $('#success').html('');
});
