
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

var check = 0;
/**
 * Check uncheck all checkbox
 */
function toggle_all(fieldClass) {

    if(check % 2 === 0) {
        $("."+fieldClass).prop('checked', true);
    } else {
        $("."+fieldClass).prop('checked', false);
    }

    check = check + 1;
}

function mark_settled_confirm() {
    $('#settle-button').popover({
        html: true,
        title: 'Are you sure?',
        content: '<p>This will mark the transaction settled/returned.' +
            ' <p id="mark-settled-message-error" class="alert alert-danger" style="display: none;"></p>' +
            ' <p id="mark-settled-message-success" class="alert alert-success" style="display: none;">Action completed!</p>' +
            '<button id="mark-settled-btn-yes" class="btn btn-success mr-2 btn-ladda-progress" data-style="expand-right" onclick="mark_payment_settled(200);" >Settled</button>' +
            '<button id="mark-returned-btn-yes" class="btn btn-danger mr-2 btn-ladda-progress" data-style="expand-right" onclick="mark_payment_settled(400);" >Returned</button>' +
            '<button id="mark-settled-btn-no" class="btn btn-secondary" onclick="mark_settled_no()">No</button>',
        template: '<div class="popover" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'
    }).popover('show');

}

function mark_settled_no() {
    $('#settle-button').popover('hide');
}
function mark_payment_settled(type) {
    var action = '';

    if(type === 200) {
        action = 'settle';
    } else if(type === 400) {
        action = 'return';
    }
    if(action === '') {
        return;
    }
    var formData = $('#transaction-id-form').serializeArray();
    formData.push({name: 'action', value: action});

    $.ajax({
        method: 'POST',
        url: '/api/v1/manual/settle',
        data: formData,
        success: function(data) {
            $('#settle-button').popover('hide');
            toastr.success('Action completed', 'Success!');
            return;
        },
        error: function (data) {
            toastr.error('Error Encountered', 'Error');
        }
    });
}
