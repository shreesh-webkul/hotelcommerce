/**
* 2010-2020 Webkul.
*
* NOTICE OF LICENSE
*
* All right is reserved,
* Please go through this link for complete license : https://store.webkul.com/license.html
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to https://store.webkul.com/customisation-guidelines/ for more information.
*
*  @author    Webkul IN <support@webkul.com>
*  @copyright 2010-2020 Webkul IN
*  @license   https://store.webkul.com/license.html
*/

$(document).ready(function() {
    // for screen size changes for room search
    var window_width = $(window).width();
    if (window_width >= 767) {
        $('.fancy_search_header_xs').hide();
    }

    if ($("body").length) {
        $(window).resize(function() {
            var window_width = $(window).width();
            if (window_width >= 767) {
                $.fancybox.close();
                $('.fancy_search_header_xs').hide();
            } else {
                $('.fancy_search_header_xs').show();
            }
        });
    }
    $(function() {
        $('#xs_room_search').fancybox({
            minWidth: 200,
            autoSize: true,
            padding: 0,
            autoScale: false,
            maxWidth: '100%',
            'hideOnContentClick': false,
            'afterClose': function() {
                $('.header-rmsearch-container').show();
                $('#xs_room_search_form').show();
            },
        });
    });

    /*END*/
    var ajax_check_var = '';
    $('.location_search_results_ul').hide();

    $("#check_out_time").datepicker({
        dateFormat: 'dd-mm-yy',
        dayNamesMin: [ "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
        //for calender Css
        beforeShowDay: function (date) {
            // get check-in date
            return highlightSelectedDateRange(date, $("#check_in_time").val(), $("#check_out_time").val());
        },
        beforeShow: function (input, instance) {
            // So that on translating page date is translated to NaN-NaN-NaN
            $('.ui-datepicker').addClass('notranslate');

            var date_to = $('#check_in_time').val();
            if (typeof date_to != 'undefined' && date_to != '') {
                var date_format = date_to.split("-");
                var selectedDate = new Date($.datepicker.formatDate('yy-mm-dd', new Date(date_format[2], date_format[1] - 1, date_format[0])));
                selectedDate.setDate(selectedDate.getDate()+1);
                $("#check_out_time").datepicker("option", "minDate", selectedDate);
            } else {
                var date_format = new Date();
                var selectedDate = new Date($.datepicker.formatDate('yy-mm-dd', new Date()));
                selectedDate.setDate(selectedDate.getDate()+1);
                $("#check_out_time").datepicker("option", "minDate", selectedDate);
            }
        }
    });

    $("#check_in_time").datepicker({
        dateFormat: 'dd-mm-yy',
        minDate: 0,
        dayNamesMin: [ "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
        beforeShow: function (input, instance) {
            // So that on translating page date is translated to NaN-NaN-NaN
            $('.ui-datepicker').addClass('notranslate');
        },
        //for calender Css
        beforeShowDay: function (date) {
            // highlight dates of the selected date range
            return highlightSelectedDateRange(date, $("#check_in_time").val(), $("#check_out_time").val());
        },
        onClose: function() {
            // get checkout date before making any changes for the operations
            var checkOut = $("#check_out_time").val();
            var date = $("#check_in_time").val();
            var dateFormat = date.split("-");
            var selectedDate = new Date(
                $.datepicker.formatDate('yy-mm-dd', new Date(dateFormat[2], dateFormat[1] - 1, dateFormat[0]))
            );
            selectedDate.setDate(selectedDate.getDate() + 1);
            $("#check_out_time").datepicker("option", "minDate", selectedDate);

            /* open datepicker of chechout date only if
            checkout date is empty or checkin selected is equal or more than check out date */
            if (checkOut == '') {
                $("#check_out_time").datepicker( "show" );
            } else {
                // Lets make the date in the required format
                selectedDate.setDate(selectedDate.getDate() - 1);
                var currentDate = selectedDate.getDate();
                var currentMonth = selectedDate.getMonth() + 1;
                if (currentMonth < 10) {
                    currentMonth = '0' + currentMonth;
                }
                if (currentDate < 10) {
                    currentDate = '0' + currentDate;
                }

                dmy = selectedDate.getFullYear() + "-" + currentMonth + "-" + currentDate;
                checkOut = checkOut.split("-");
                checkOut = (checkOut[2]) + '-' + (checkOut[1]) + '-' + (checkOut[0]);

                if (checkOut <= dmy) {
                    $("#check_out_time").datepicker('show');
                }
            }
        }
    });

    if (typeof max_order_date != 'undefined' && typeof booking_date_to != 'undefined') {
        max_order_date_cal = new Date(max_order_date);
        $("#check_out_time").datepicker("option", "maxDate", new Date(max_order_date));
        max_order_date_cal.setDate(max_order_date_cal.getDate() - 1);
        $("#check_in_time").datepicker("option", "maxDate", max_order_date_cal);
    }

    function abortRunningAjax() {
        if (ajax_check_var) {
            ajax_check_var.abort();
        }
    }

    // search location with users searched characters
    $(document).on('keyup', "#hotel_location", function(e) {
        if (($('.location_search_results_ul').is(':visible')) && (e.which == 40 || e.which == 38)) {
            $(this).blur();
            if (e.which == 40) {
                $(".location_search_results_ul li:first").focus();
            } else if (e.which == 38) {
                $(".location_search_results_ul li:last").focus();
            }
        } else {
            $('.location_search_results_ul').empty().hide();
            if ($(this).val() != '') {
                abortRunningAjax();
                ajax_check_var = $.ajax({
                    url: autocomplete_search_url,
                    data: {
                        to_search_data: $(this).val(),
                    },
                    method: 'POST',
                    dataType: 'json',
                    success: function(result) {
                        if (result.status == 'success') {
                            $('.location_search_results_ul').html(result.data);
                            $('.location_search_results_ul').show();
                        }
                    }
                });
            }
        }
    });

    // if user clicks anywhere and location/hotel li is visible the select first li as selection
    $('body').on('click', function(e) {
        if ($('.location_search_results_ul').is(':visible') && e.target.className != "search_result_li" && e.target.id != "hotel_location") {
            $('.location_search_results_ul .search_result_li:first').click();
        }

        if ($('.hotel_dropdown_ul').is(':visible') && e.target.className != "search_result_li" && e.target.id != "hotel_location") {
            $('.hotel_dropdown_ul .search_result_li:first').click();
        }
    });

    // set data on clicking the searched location on dropdown
    $(document).on('click', '.location_search_results_ul li', function(e) {
        $('#hotel_location').attr('value', $(this).html());
        $('#hotel_location').attr('city_cat_id', $(this).val());

        $('.location_search_results_ul').empty().hide();

        $.ajax({
            url: autocomplete_search_url,
            data: {
                hotel_city_cat_id: $('#hotel_location').attr('city_cat_id'),
            },
            method: 'POST',
            dataType: 'json',
            success: function(result) {
                if (result.status == 'success') {
                    $('#hotel_cat_id').val('');
                    $('#hotel_cat_name').html('Select Hotel');
                    $('.hotel_dropdown_ul').empty();
                    $('.hotel_dropdown_ul').html(result.data);
                } else {
                    alert(no_results_found_cond);
                }
            }
        });
    });

    // navigate to prev and next li in the location/hotel dropdown
    $('body').on('keyup', 'li.search_result_li', function(e) {
        if (e.which == 40 || e.which == 38) {
            var ulElement = $(this).closest('ul');
            var ulLength = ulElement.find('li').length;
            $(this).blur();
            ulElement.scrollTop($(this).index() * $(this).outerHeight());
            if (e.which == 40) {
                if ($(this).index() != (ulLength - 1)) {
                    $(this).next('li.search_result_li').focus();
                } else {
                    ulElement.find("li:first").focus();
                }
            } else if (e.which == 38) {
                if ($(this).index()) {
                    $(this).prev('li.search_result_li').focus();
                } else {
                    ulElement.find("li:last").focus();
                }
            }
        }
    });

    // when focus goes from hotel button to li list of hotels
    $(document).on('keyup', "#id_hotel_button", function(e) {
        if ($('.hotel_dropdown_ul').is(':visible')) {
            if ($('.hotel_dropdown_ul .search_result_li').length) {
                $(".hotel_dropdown_ul li:first").focus();
            }
        }
    });

    // if user is selecting the location by keydown / up key
    $(document).on('keydown', 'body', function(e) {
        if ((e.which == 40 || e.which == 38) && ($('.location_search_results_ul li.search_result_li').is(':visible') || $('.hotel_dropdown_ul li.search_result_li').is(':visible'))) {
            return false;
        } else if (e.which == 13 && e.target.className == 'search_result_li') {
            e.target.click();
        } else if (e.which == 9 && $('.location_search_results_ul').is(':visible')) {
            if ($('.location_search_results_ul .search_result_li').length) {
                $('.location_search_results_ul li.search_result_li:first').click();
            }
        } else if (e.which == 9 && $('.hotel_dropdown_ul').is(':visible')) {
            if ($('.hotel_dropdown_ul .search_result_li').length) {
                $('.hotel_dropdown_ul li.search_result_li:first').click();
            }
        }
    });

    $(document).on('click', '.hotel_dropdown_ul li', function() {
        var max_order_date = $(this).attr('data-max_order_date');
        var max_date_from = new Date(max_order_date);
        max_date_from.setDate(max_date_from.getDate() - 1);
        var max_date_to = new Date(max_order_date);
        if($("#check_in_time").datepicker("getDate") > max_date_from) {
            $("#check_in_time").val('');
        }
        if($("#check_out_time").datepicker("getDate") > max_date_to) {
            $("#check_out_time").val('');
        }
        $("#check_in_time").datepicker("option", "maxDate", max_date_from);
        $("#check_out_time").datepicker("option", "maxDate", max_date_to);
        $("#max_order_date").val(max_order_date);
        $('#id_hotel').val($(this).attr('data-id-hotel'));
        $('#hotel_cat_id').val($(this).attr('data-hotel-cat-id'));
        $('#hotel_cat_name').html($(this).html());
    });

    // If only one hotel then set max order date on date pickers
    var max_order_date = $('#max_order_date').val();
    if (max_order_date != '') {
        var max_date_from = new Date(max_order_date);
        max_date_from.setDate(max_date_from.getDate() - 1);
        var max_date_to = new Date(max_order_date);
        if($("#check_in_time").datepicker("getDate") > max_date_from) {
            $("#check_in_time").val('');
        }
        if($("#check_out_time").datepicker("getDate") > max_date_to) {
            $("#check_out_time").val('');
        }
        $("#check_in_time").datepicker("option", "maxDate", max_date_from);
        $("#check_out_time").datepicker("option", "maxDate", max_date_to);
    }

    // validations on the submit of the search fields
    $('#search_room_submit').on('click', function(e) {
        var check_in_time = $("#check_in_time").val();
        var check_out_time = $("#check_out_time").val();

        var date_format_check_in = check_in_time.split("-");
        var new_chk_in = new Date($.datepicker.formatDate('yy-mm-dd', new Date(date_format_check_in[2], date_format_check_in[1] - 1, date_format_check_in[0])));
        var date_format_check_out = check_out_time.split("-");
        var new_chk_out = new Date($.datepicker.formatDate('yy-mm-dd', new Date(date_format_check_out[2], date_format_check_out[1] - 1, date_format_check_out[0])));
        var max_order_date = $("#max_order_date").val();
        var max_order_date_format = $.datepicker.formatDate('yy-mm-dd', new Date(max_order_date));
        var error = false;

        var locationCatId = $('#hotel_location').attr('city_cat_id');
        var hotelCatId = $('#hotel_cat_id').val();
        $('.header-rmsearch-input').removeClass("error_border");

        if (hotelCatId == '') {
            if (typeof(locationCatId) == 'undefined' || locationCatId == '') {
                $("#hotel_location").addClass("error_border");
                error = true;
            }
            $("#id_hotel_button").addClass("error_border");
            $('#select_htl_error_p').text(hotel_name_cond);
            error = true;
        }
        if (check_in_time == '') {
            $("#check_in_time").addClass("error_border");
            $('#check_in_time_error_p').text(check_in_time_cond);
            error = true;
        } else if (new_chk_in < $.datepicker.formatDate('yy-mm-dd', new Date())) {
            $("#check_in_time").addClass("error_border");
            $('#check_in_time_error_p').text(less_checkin_date);
            error = true;
        }
        if (check_out_time == '') {
            $("#check_out_time").addClass("error_border");
            $('#check_out_time_error_p').text(check_out_time_cond);
            error = true;
        } else if (new_chk_out < new_chk_in) {
            $("#check_out_time").addClass("error_border");
            $('#check_out_time_error_p').text(more_checkout_date);
            error = true;
        } else if (max_order_date_format < new_chk_in) {
            $("#check_in_time").addClass("error_border");
            $('#check_in_time_error_p').text(max_order_date_err + ' ' + max_order_date);
            error = true;
        } else if (max_order_date_format < new_chk_out) {
            $("#check_out_time").addClass("error_border");
            $('#check_out_time_error_p').text(max_order_date_err + ' ' + max_order_date);
            error = true;
        }
        if (error)
            return false;
        else
            return true;
    });
    // Occupancy field dropdown
    // add occupancy info block
    $('#occupancy_wrapper .add_new_occupancy_btn').on('click', function(e) {
        e.preventDefault();

        var occupancy_block = '';

        var roomBlockIndex = parseInt($("#occupancy_wrapper .occupancy_info_block").last().attr('occ_block_index'));
        roomBlockIndex += 1;

        var countRooms = parseInt($('#occupancy_wrapper .occupancy_info_block').length);
        countRooms += 1

        occupancy_block += '<div class="occupancy_info_block" occ_block_index="'+roomBlockIndex+'">';
            occupancy_block += '<div class="occupancy_info_head"><span class="room_num_wrapper">'+ room_txt + ' - ' + countRooms + '</span><a class="remove-room-link pull-right" href="#">' + remove_txt + '</a></div>';
            occupancy_block += '<div class="row">';
                occupancy_block += '<div class="form-group col-sm-5 col-xs-6 occupancy_count_block">';
                    occupancy_block += '<div class="row">';
                        occupancy_block += '<label class="col-sm-12">' + adults_txt + '</label>';
                        occupancy_block += '<div class="col-sm-12">';
                            occupancy_block += '<input type="hidden" class="num_occupancy num_adults room_occupancies" name="occupancy['+roomBlockIndex+'][adults]" value="1">';
                            occupancy_block += '<div class="occupancy_count pull-left">';
                                occupancy_block += '<span>1</span>';
                            occupancy_block += '</div>';
                            occupancy_block += '<div class="qty_direction pull-left">';
                                occupancy_block += '<a href="#" data-field-qty="qty" class="btn btn-default occupancy_quantity_up">';
                                    occupancy_block += '<span><i class="icon-plus"></i></span>';
                                occupancy_block += '</a>';
                                occupancy_block += '<a href="#" data-field-qty="qty" class="btn btn-default occupancy_quantity_down">';
                                    occupancy_block += '<span><i class="icon-minus"></i></span>';
                                occupancy_block += '</a>';
                            occupancy_block += '</div>';
                        occupancy_block += '</div>';
                    occupancy_block += '</div>';
                occupancy_block += '</div>';
                occupancy_block += '<div class="form-group col-sm-7 col-xs-6 occupancy_count_block">';
                    occupancy_block += '<div class="row">';
                        occupancy_block += '<label class="col-sm-12">' + child_txt + '<span class="label-desc-txt">(' + below_txt + ' ' + max_child_age + ' ' + years_txt + ')</span></label>';
                        occupancy_block += '<div class="col-sm-12">';
                            occupancy_block += '<input type="hidden" class="num_occupancy num_children room_occupancies" name="occupancy['+roomBlockIndex+'][children]" value="0">';
                            occupancy_block += '<div class="occupancy_count pull-left">';
                                occupancy_block += '<span>0</span>';
                            occupancy_block += '</div>';
                            occupancy_block += '<div class="qty_direction pull-left">';
                                occupancy_block += '<a href="#" data-field-qty="qty" class="btn btn-default occupancy_quantity_up">';
                                    occupancy_block += '<span><i class="icon-plus"></i></span>';
                                occupancy_block += '</a>';
                                occupancy_block += '<a href="#" data-field-qty="qty" class="btn btn-default occupancy_quantity_down">';
                                    occupancy_block += '<span><i class="icon-minus"></i></span>';
                                occupancy_block += '</a>';
                            occupancy_block += '</div>';
                        occupancy_block += '</div>';
                    occupancy_block += '</div>';
                occupancy_block += '</div>';
            occupancy_block += '</div>';
            occupancy_block += '<div class="form-group row children_age_info_block">';
                occupancy_block += '<label class="col-sm-12">' + all_children_txt + '</label>';
                occupancy_block += '<div class="col-sm-12">';
                    occupancy_block += '<div class="row children_ages">';
                    occupancy_block += '</div>';
                occupancy_block += '</div>';
            occupancy_block += '</div>';
            occupancy_block += '<hr class="occupancy-info-separator">';
        occupancy_block += '</div>';
        $('#occupancy_inner_wrapper').append(occupancy_block);

        // scroll to the latest added room
        var objDiv = document.getElementById("occupancy_wrapper");
        objDiv.scrollTop = objDiv.scrollHeight;

        setGuestOccupancy();
    });

    // remove occupancy info block
    $(document).on('click', '#occupancy_wrapper .remove-room-link', function(e) {
        e.preventDefault();
        $(this).closest('#occupancy_wrapper .occupancy_info_block').remove();

        $( "#occupancy_wrapper .room_num_wrapper" ).each(function(key, val) {
            $(this).text(room_txt + ' - '+ (key+1) );
        });

        setGuestOccupancy();
    });

    // increase the quantity of adult and child
    $(document).on('click', '#occupancy_wrapper .occupancy_quantity_up', function(e) {
        e.preventDefault();
        // set input field value
        var element = $(this).closest('.occupancy_count_block').find('.num_occupancy');
        var elementVal = parseInt(element.val()) + 1;

        var childElement = $(this).closest('.occupancy_count_block').find('.num_children').length;
        if (childElement) {
            var totalChilds = $(this).closest('.occupancy_info_block').find('.guest_child_age').length;

            if (totalChilds < max_child_in_room) {
                element.val(elementVal);
                $(this).closest('.occupancy_info_block').find('.children_age_info_block').show();

                var roomBlockIndex = parseInt($(this).closest('.occupancy_info_block').attr('occ_block_index'));

                var childAgeSelect = '<div class="col-xs-4">';
                    childAgeSelect += '<select class="guest_child_age room_occupancies" name="occupancy[' +roomBlockIndex+ '][child_ages][]">';
                        childAgeSelect += '<option value="-1">' + select_age_txt + '</option>';
                        childAgeSelect += '<option value="0">' + under_1_age + '</option>';
                        for (let age = 1; age < max_child_age; age++) {
                            childAgeSelect += '<option value="'+age+'">'+age+'</option>';
                        }
                    childAgeSelect += '</select>';
                childAgeSelect += '</div>';

                $(this).closest('.occupancy_info_block').find('.children_ages').append(childAgeSelect);

                // set input field value
                $(this).closest('.occupancy_count_block').find('.occupancy_count > span').text(elementVal);
            }
        } else {
            element.val(elementVal);

            // set input field value
            $(this).closest('.occupancy_count_block').find('.occupancy_count > span').text(elementVal);
        }

        setGuestOccupancy();
    });

    $(document).on('click', '#occupancy_wrapper .occupancy_quantity_down', function(e) {
        e.preventDefault();
        // set input field value
        var element = $(this).closest('.occupancy_count_block').find('.num_occupancy');
        var elementVal = parseInt(element.val()) - 1;
        var childElement = $(this).closest('.occupancy_count_block').find('.num_children').length;

        if (childElement) {
            if (elementVal < 0) {
                elementVal = 0;
            } else {
                $(this).closest('.occupancy_info_block').find('.children_ages select').last().closest('div').remove();
                if (elementVal <= 0) {
                    $(this).closest('.occupancy_info_block').find('.children_age_info_block').hide();
                }
            }
        } else {
            if (elementVal == 0) {
                elementVal = 1;
            }
        }

        element.val(elementVal);
        // set input field value
        $(this).closest('.occupancy_count_block').find('.occupancy_count > span').text(elementVal);

        setGuestOccupancy();
    });

    // toggle occupancy block
    $('#guest_occupancy').on('click', function(e) {
        e.stopPropagation();
        $("#occupancy_wrapper").toggle();
    });

    // close the occupancy block when clink anywhere in the body outside occupancy block
    $('body').on('click', function(e) {
        // @TODO better approach to be found
        if ($('#occupancy_wrapper').css('display') !== 'none') {
            if (!($(e.target).closest("#occupancy_wrapper").length)) {
                // Before closing the occupancy block validate the vaules inside
                let hasErrors = 0;

                let adults = $("#occupancy_wrapper").find(".num_adults").map(function(){return $(this).val();}).get();
                let children = $("#occupancy_wrapper").find(".num_children").map(function(){return $(this).val();}).get();
                let child_ages = $("#occupancy_wrapper").find(".guest_child_age").map(function(){return $(this).val();}).get();

                // start validating above values
                if (!adults.length || (adults.length != children.length)) {
                    hasErrors = 1;
                    showErrorMessage(invalid_occupancy_txt);
                } else {
                    $("#occupancy_wrapper").find('.occupancy_count').removeClass('error_border');

                    // validate values of adults and children
                    adults.forEach(function (item, index) {
                        if (isNaN(item) || parseInt(item) < 1) {
                            hasErrors = 1;
                            $("#occupancy_wrapper .num_adults").eq(index).closest('.occupancy_count_block').find('.occupancy_count').addClass('error_border');
                        }
                        if (isNaN(children[index])) {
                            hasErrors = 1;
                            $("#occupancy_wrapper .num_children").eq(index).closest('.occupancy_count_block').find('.occupancy_count').addClass('error_border');
                        }
                    });

                    // validate values of selected child ages
                    $("#occupancy_wrapper").find('.guest_child_age').removeClass('error_border');
                    child_ages.forEach(function (age, index) {
                        age = parseInt(age);
                        if (isNaN(age) || (age < 0) || (age >= parseInt(max_child_age))) {
                            hasErrors = 1;
                            $("#occupancy_wrapper .guest_child_age").eq(index).addClass('error_border');
                        }
                    });
                }

                if (hasErrors == 0) {
                    $("#occupancy_wrapper").hide();
                    $("#search_hotel_block_form #guest_occupancy").removeClass('error_border');
                } else {
                    $("#search_hotel_block_form #guest_occupancy").addClass('error_border');
                    return false;
                }
            }
        }
    });
});
// function to set occupancy infor in guest occupancy field(search form)
function setGuestOccupancy()
{
    var adults = 0;
    var children = 0;
    var rooms = $('#occupancy_wrapper .occupancy_info_block').length;
    $( "#occupancy_wrapper .num_adults" ).each(function(key, val) {
        adults += parseInt($(this).val());
    });
    $( "#occupancy_wrapper .num_children" ).each(function(key, val) {
        children += parseInt($(this).val());
    });
    var guestButtonVal = parseInt(adults) + ' ';
    if (parseInt(adults) > 1) {
        guestButtonVal += adults_txt;
    } else {
        guestButtonVal += adult_txt;
    }
    if (parseInt(children) > 0) {
        if (parseInt(children) > 1) {
            guestButtonVal += ', ' + parseInt(children) + ' ' + children_txt;
        } else {
            guestButtonVal += ', ' + parseInt(children) + ' ' + child_txt;
        }
    }
    if (parseInt(rooms) > 1) {
        guestButtonVal += ', ' + parseInt(rooms) + ' ' + rooms_txt;
    } else {
        guestButtonVal += ', ' + parseInt(rooms) + ' ' + room_txt;
    }
    $('#guest_occupancy > span').text(guestButtonVal);
}