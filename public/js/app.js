$(document).ready(function(){

    // Script for grid columns show/hide

    if($('#ColumnSetting .chk:checked').length === $('#ColumnSetting .chk').length){
        $('#ColumnSetting .checkallbox').prop('checked', true);
    }
    $(document).on('click', '#ColumnSetting .chk', function () {
        if($('#ColumnSetting .chk:checked').length < 3){
            alert('You need to select at least 3 fields.');
            return false;
        }else{
            if($('#ColumnSetting .chk:checked').length === $('#ColumnSetting .chk').length){
                $('#ColumnSetting .checkallbox').prop('checked', true);
            }else{
                $('#ColumnSetting .checkallbox').prop('checked', false);
            }
        }
    });

    $(document).on('click', '#ColumnSetting .checkallbox', function () {
        if ($(this).is(':checked') === true) {
            $('#ColumnSetting .chk').prop('checked', true);
        } else {
            $('.chk').prop('checked', false);
            if($('#ColumnSetting .chk:checked').length < 3){
                //alert('You have to select at least 3 fields.');
                $('#ColumnSetting .chk').each(function(i, element){
                    if(i < 3){
                        $(element).prop('checked', true);
                    }
                });
            }
            //return false;
        }
    });



    $('#partner_selection').change(function(){
        //alert($(this).val());
        $('#partner_name').val($(this).find(':selected').text());
        $.ajax({
            type: 'POST',
            url: $(this).data('url'),
            //contentType: "application/json",
            dataType: "json",
            data: $('#partner_selection_form').serialize(),
            success: function(result){
                window.location.reload();
            }
        });
    });

    $("#toggle-menu").click(function(){
        $(".menu-sidebar").toggleClass("hide-menu");
        $(".page-container").toggleClass("fullpage");
        $(".header-desktop").toggleClass("fulldesktop");
        $(".fa-chevron-left").toggleClass("fa-chevron-right");
    });

    $('.select2-drop').select2({
        closeOnSelect: true,
        //tags: true,
        //templateSelection: searchTemplateSelection,
        tokenSeparators: [','],
    });

});

var one_min_remaining, session_expired, count_down;
function sessionExpireAlert(timeout) {

    var seconds = timeout / 1000;
    console.log('Popup will show after : '+convertHMS((timeout - 60 * 1000) / 1000));

    /*setInterval(function () {
        console.log('countdown');
    }, 1000);*/

    count_down = setInterval(function () {
        seconds--;
        if(seconds > 0){
            //console.log(convertHMS(seconds));
            $('#second-remaining').text(convertHMS(seconds));
        }else{
            clearInterval(count_down);
        }
    }, 1000);

    one_min_remaining = setTimeout(function () {
        console.log('Show Popup before '+ convertHMS(seconds) +' seconds of timeout.');
        $('#login-alert').modal('show');
    }, timeout - 60 * 1000);

    session_expired = setTimeout(function () {
        delete(seconds);
        clearInterval(count_down);
        clearTimeout(one_min_remaining);
        clearTimeout(session_expired);

        //console.log('Expired');
        window.location.href = '/index/logout';
    }, timeout);
}

function resetSession() {
    //Refresh Session.
    $.ajax({
        url: "/dashboard/refresh-session",
        success: function (response) {
            $('#login-alert').modal('hide');
            clearInterval(count_down);
            clearTimeout(one_min_remaining);
            clearTimeout(session_expired);
            sessionExpireAlert(600000);
            // sessionExpireAlert(120000);
        },
        error: function () {
            window.location.href = '/index/logout';
        }
    });
}

sessionExpireAlert(1800000);

$('#login-alert').on('hidden.bs.modal', function (e) {
    var $button = $(event.target); // The clicked button
    if($button.data('action') === 'yes'){
        resetSession();
    }/*else if($button.data('action') === 'no'){
    }*/
});

function convertHMS(value) {
    const sec = parseInt(value, 10); // convert value to number if it's string
    var hours   = Math.floor(sec / 3600); // get hours
    var minutes = Math.floor((sec - (hours * 3600)) / 60); // get minutes
    var seconds = sec - (hours * 3600) - (minutes * 60); //  get seconds
    // add 0 if value < 10; Example: 2 => 02
    if (hours   < 10) {hours   = "0"+hours;}
    if (minutes < 10) {minutes = "0"+minutes;}
    if (seconds < 10) {seconds = "0"+seconds;}
    return hours+':'+minutes+':'+seconds; // Return is HH : MM : SS
}
