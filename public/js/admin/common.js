function get_today_txn_data() {
    $('#sk-wave-loader').show(); // Start the loader
    $('#today-txn-data').html(''); // Empty the div

    // Call the ajax
    $.ajax({
        method: 'GET',
        url: '/api/v1/metrics?name=daily_transactions',
        data: $('#create-advance-form').serializeArray(),
        success: function(data) {
            // Set the data in div
            $('#sk-wave-loader').hide();
            $('#today-txn-data').html(get_today_txn_template(data));
            return;
        },
        error: function (data) {
            var response = $.parseJSON(data.responseText);
            console.log('Error: ' + response);
            $('#today-txn-data').html(response);
        }
    });
}

/**
 * Return the txn view
 * @param data
 */
function get_today_txn_template(data) {
    var template = '';
    $.each(data, function (i, metrics) {
        template += '<a href="#" class="dropdown-item">' +
            '                <div class="small mb-1">' + metrics.name +
            '                    <span class="float-right"><strong>'+metrics.value+'</strong></span>' +
            '                </div>' +
            '            </a>';
    });

    return template;
}

function get_url(path) {
    path = path || "";
    return (window.location.origin ? window.location.origin + '/' : window.location.protocol + '/' + window.location.host + '/') + path;
}