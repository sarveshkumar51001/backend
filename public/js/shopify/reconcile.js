// Add form opening
function reconcile(source, filePath, fileChecksum, from, to, organization_id) {
    var url = "/api/v1/reconcile";
    var data = [];
    data.push({name: 'source', value: source});
    data.push({name: 'file_path', value: filePath});
    data.push({name: 'file_checksum', value: fileChecksum});
    data.push({name: 'from', value: from});
    data.push({name: 'to', value: to});
    data.push({name: 'organization_id', value: organization_id});
    return request(url, 'POST', data);
}

function request(url, type, data) {
    var loader = Ladda.create(document.querySelector('#reconcile-button')).start();
    $("#reconcile-upload-success").addClass('d-none');
    $("#reconcile-upload-error").addClass('d-none');

    $.ajax({
        method: type,
        url: url,
        data: data,
        success: function(data) {
            loader.stop();
            $div = $("#reconcile-upload-success");
            $div.append('<ul>')
                .append("<li>Total entries in file: "+data.total_rows_count+"</li>")
                .append("<li>Match for settlement: "+data.total_settled_rows_count+"</li>")
                .append("<li>Match for returned settlement: "+data.returned_rows_count+"</li>")
                .append("<li>Already marked settled in system: "+data.already_settled_rows_count+"</li>")
                .append("<li>Found but details not matches: "+data.failed_rows_count+"</li>")
                .append("<li>Not found in system: "+data.not_found_rows_count+"</li>")
                .append("</ul>");

            $("#reconcile-upload-success").removeClass('d-none');
            $("#reconcile-upload-button").addClass('d-none');
            toastr.success('Action completed', 'Success!');
            $(window).scrollTop($('#reconcile-upload-success').offset().top);
        },
        error: function (data) {
            loader.stop();
            toastr.error('There are few errors', 'Error');
            $div = $("#reconcile-upload-error");
            $div.append("<ul>");
            var response = $.parseJSON(data.responseText);

            $.each(response.errors, function (field, error) {
                $div.append('<ul>')
                    .append("<li>"+error+"</li>")
                    .append("</ul>");
            });
            $div.append("</ul>");
            $div.removeClass('d-none');
            $("#reconcile-upload-button").addClass('d-none');
            $(window).scrollTop($div.offset().top);
        }
    });
}
