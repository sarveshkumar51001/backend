
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
                    value = ((new Date(parseInt(value * 1000))).toLocaleString());
                }
                if(key == 'upload_date') {
                    return;
                }
                if (key == 'transaction_id') {
                    title = 'Shopify Transaction ID';
                }

                if (key == 'order_update_at') {
                    title = 'Order created at';
                } else if (key == 'installment') {
                    title = 'Installment number';
                } else if (key == 'errors') {
                    title = 'Errors';
                }

                template += '<div>'+title + ': <strong>' +value+'</strong></div>';
            }
        });
        template += '</div>'
        index++;
    });

    return template;
}

function download_transactions(reco_status = '') {
    var link = "/get/transactions?daterange="+$('#txn_range').val();

    if(reco_status) {
        link += "&reco_status="+reco_status;
    }
    window.open(link, '_blank');
    return false;
}


