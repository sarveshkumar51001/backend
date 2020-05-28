function addTag() {
    var passcode = $('#passcode').val();

    $.ajax({
        method: 'POST',
        url: '/details',
        data: {passcode : passcode},
        success: function(data) {
            $("#success").html("Passcode Verify Successfylly").css("color", "Green").delay(5000).fadeOut('slow');
            location.reload();
        },
        error: function (data) {
            var response = $.parseJSON(data.responseText);
            // Add error to the field and enable them
            $.each(response.errors, function (field, error) {
                if(field) {
                    $("#error-" + field).removeClass('d-none').html(error).css("color", "red").show().delay(5000).fadeOut('slow');
                }
                else {
                    $("#error_msg").removeClass('d-none').html(error).css("color", "red").show().delay(5000).fadeOut('slow');
                }
            })
        }
    });
}
