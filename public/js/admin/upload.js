
function render_upload_details(id) {
    $('#payment-details').html('');

    $.ajax({
        method: 'GET',
        url: '/api/v1/upload/' + id,
        success: function(data) {
            $('#payment-details').html(get_render_template(data));
        },
        error: function (data) {
            var response = $.parseJSON(data.responseText);
            toastr.error('There are few errors loading data, Data:' + response, 'Error');
            console.log('Error: ' + response);
        }
    });
}

function get_render_template(data) {
    var template ='';
    var index = 1;
    $.each(data.payments, function (key, payment) {
        template +=
            '<div class="callout m-0 text-muted bg-light text-uppercase">' +
            '<small>Payment '+index + '</small><small class="pull-right"><strong><i class="fa fa-rupee"></i>&nbsp;'+payment.amount+'</strong></small></div>';

        var statusClass = (payment.processed == 'Yes') ? 'success' : 'warning';
        template += '<div class="callout callout-'+statusClass+' m-0 py-3">';
        $.each(payment, function (key, value) {
            if(value != '') {
                template += '<div>'+_Payload.headers[key]+ ': <strong>' +value+'</strong></div>';
            }
        });
        template +='<small class="text-muted mr-3"><i class="icon-calendar"></i>&nbsp; '+payment.order_update_at+'</small>\n' +
        '</div><hr class="mx-3 my-0">';

        index++;
    });

    return template;
}