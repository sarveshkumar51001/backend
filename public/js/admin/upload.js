
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
            var title = _Payload.headers[key];
            if(value != '') {
                if(key == 'upload_date' || key == 'order_update_at') {
                    value = (new Date(parseInt(value * 1000)));
                }
                if(key == 'upload_date') {
                    return;
                }

                if (key == 'order_update_at') {
                    title = 'Order created at';
                } else if (key == 'installment') {
                    title = 'Installment number';
                }

                template += '<div>'+title + ': <strong>' +value+'</strong></div>';
            }
        });
        template +='<small class="text-muted mr-3"><i class="icon-calendar"></i>&nbsp; '+(new Date(parseInt(payment.order_update_at * 1000)))+'</small>\n' +
        '</div><hr class="mx-3 my-0">';

        index++;
    });

    return template;
}