function addDetails() {
    var order_id = $('#order_id').val();
    var data = $('#add-details-form').serializeArray();
    $.ajax({
        method: 'PUT',
        url: '/frontend/update/details/'+order_id,
        data: data,
        success: function(result) {
            if(result == 1)
            {
                $("#success").html("Details Updated Successfully !").css("color", "Green").delay(5000).fadeOut('slow');

            }

        },
        error: function (data) {
            var response = $.parseJSON(data.responseText);
            // Add error to the field and enable them
            $.each(response.errors, function (field, error) {
                if(field) {
                    $("#error-" + field).removeClass('d-none').html(error).css("color", "red").show().delay(8000).fadeOut('slow');
                }
                else {
                    $("#error_msg").removeClass('d-none').html(error).css("color", "red").show().delay(8000).fadeOut('slow');
                }
            })
        }
    });
}
