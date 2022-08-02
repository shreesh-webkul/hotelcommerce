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
    if (window_width > 767) {
        $('.fancy_search_header_xs').hide();
    }

    if ($("body").length) {
        $(window).resize(function() {
            var window_width = $(window).width();
            if (window_width > 767) {
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

    function createDateRangePicker(max_order_date, dateFrom, dateTo)
    {
        if (max_order_date) {
            max_order_date = $.datepicker.parseDate('yy-mm-dd', max_order_date );
        } else {
            max_order_date = false;
        }

        if (typeof $('#daterange_value').data('dateRangePicker') != 'undefined') {
            if (max_order_date) {
                if ($.datepicker.parseDate('yy-mm-dd', $('#check_out_time').val()) < max_order_date) {
                    dateFrom = dateFrom ? dateFrom :$('#check_in_time').val();
                    dateTo = dateTo ? dateTo : $('#check_out_time').val();
                } else {
                    dateFrom = false;
                    dateTo = false;
                }
            }
            $('#daterange_value').data('dateRangePicker').clear();
            $('#daterange_value').data('dateRangePicker').destroy();
            $("#daterange_value").off("datepicker-change");
        }

        if (max_order_date) {
            max_order_date = $.datepicker.formatDate('dd-mm-yy', max_order_date);
        }
        $('#daterange_value').dateRangePicker({
            startDate: $.datepicker.formatDate('dd-mm-yy', new Date()),
            endDate: max_order_date,
        }).on('datepicker-change', function(event,obj){
            $('#check_in_time').val($.datepicker.formatDate('yy-mm-dd', obj.date1));
            $('#check_out_time').val($.datepicker.formatDate('yy-mm-dd', obj.date2));
        });

        if (dateFrom && dateTo) {
            $('#daterange_value').data('dateRangePicker').setDateRange(
                $.datepicker.formatDate('dd-mm-yy', $.datepicker.parseDate('yy-mm-dd', dateFrom)),
                $.datepicker.formatDate('dd-mm-yy', $.datepicker.parseDate('yy-mm-dd', dateTo))
            );
        }

    }
    $(document).on('click',  function() {
        console.log('test');
    });

    createDateRangePicker(false, $('#check_in_time').val(), $('#check_out_time').val());

    if (typeof max_order_date != 'undefined' && typeof booking_date_to != 'undefined') {
        createDateRangePicker(max_order_date, $('#check_in_time').val(), $('#check_out_time').val());
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
        createDateRangePicker(max_order_date, $('#check_in_time').val(), $('#check_out_time').val());
        $("#max_order_date").val(max_order_date);
        $('#id_hotel').val($(this).attr('data-id-hotel'));
        $('#hotel_cat_id').val($(this).attr('data-hotel-cat-id'));
        $('#hotel_cat_name').html($(this).html());
    });

    // If only one hotel then set max order date on date pickers
    var max_order_date = $('#max_order_date').val();
    if (max_order_date != '') {
        createDateRangePicker(max_order_date, $('#check_in_time').val(), $('#check_out_time').val());
    }

    // validations on the submit of the search fields
    $('#search_room_submit').on('click', function(e) {
        var check_in_time = $("#check_in_time").val();
        var check_out_time = $("#check_out_time").val();
        var max_order_date = $("#max_order_date").val();
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
            $("#daterange_value").addClass("error_border");
            $('#daterange_value_error_p').text(check_in_time_cond);
            error = true;
        } else if (check_in_time < $.datepicker.formatDate('yy-mm-dd', new Date())) {
            $("#daterange_value").addClass("error_border");
            $('#daterange_value_error_p').text(less_checkin_date);
            error = true;
        }
        if (check_out_time == '') {
            $("#daterange_value").addClass("error_border");
            $('#daterange_value_error_p').text(check_out_time_cond);
            error = true;
        } else if (check_out_time < check_in_time) {
            $("#daterange_value").addClass("error_border");
            $('#daterange_value_error_p').text(more_checkout_date);
            error = true;
        } else if (max_order_date < check_in_time) {
            $("#daterange_value").addClass("error_border");
            $('#daterange_value_error_p').text(max_order_date_err + ' ' + max_order_date);
            error = true;
        } else if (max_order_date < check_out_time) {
            $("#daterange_value").addClass("error_border");
            $('#daterange_value_error_p').text(max_order_date_err + ' ' + max_order_date);
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

        occupancy_block += '<div class="occupancy-room-block">';
            occupancy_block += '<div class="occupancy_info_head"><span class="room_num_wrapper">'+ room_txt + ' - ' + countRooms + '</span><a class="remove-room-link pull-right" href="#">' + remove_txt + '</a></div>';
            occupancy_block += '<div class="occupancy_info_block" occ_block_index="'+roomBlockIndex+'">';
                occupancy_block += '<div class="form-group occupancy_count_block">';
                    occupancy_block += '<label>' + adults_txt + '</label>';
                    occupancy_block += '<div>';
                        occupancy_block += '<input type="hidden" class="num_occupancy num_adults room_occupancies" name="occupancy['+roomBlockIndex+'][adult]" value="1">';
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
                occupancy_block += '<div class="form-group occupancy_count_block">';
                    occupancy_block += '<label>' + child_txt + '<span class="label-desc-txt"> (' + below_txt + ' ' + max_child_age + ' ' + years_txt + ')</span></label>';
                    occupancy_block += '<div>';
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
                occupancy_block += '<div class="form-group children_age_info_block">';
                    occupancy_block += '<label>' + all_children_txt + '</label>';
                    occupancy_block += '<div class="children_ages">';
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
        $(this).closest('#occupancy_wrapper .occupancy-room-block').remove();

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

                var childAgeSelect = '<div>';
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

                let adult = $("#occupancy_wrapper").find(".num_adults").map(function(){return $(this).val();}).get();
                let children = $("#occupancy_wrapper").find(".num_children").map(function(){return $(this).val();}).get();
                let child_ages = $("#occupancy_wrapper").find(".guest_child_age").map(function(){return $(this).val();}).get();

                // start validating above values
                if (!adult.length || (adult.length != children.length)) {
                    hasErrors = 1;
                    showErrorMessage(invalid_occupancy_txt);
                } else {
                    $("#occupancy_wrapper").find('.occupancy_count').removeClass('error_border');

                    // validate values of adult and children
                    adult.forEach(function (item, index) {
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
    var adult = 0;
    var children = 0;
    var rooms = $('#occupancy_wrapper .occupancy_info_block').length;
    $( "#occupancy_wrapper .num_adults" ).each(function(key, val) {
        adult += parseInt($(this).val());
    });
    $( "#occupancy_wrapper .num_children" ).each(function(key, val) {
        children += parseInt($(this).val());
    });
    var guestButtonVal = parseInt(adult) + ' ';
    if (parseInt(adult) > 1) {
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