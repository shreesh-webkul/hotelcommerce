$(document).ready(function() {

    toggleSearchFields();
    $('#booking_product').on('change', function() {
        toggleSearchFields();
        console.log('dasda');
    });

    // add action for render kpis
    $(document).on('click', '#box-room-in-cart', function(e){
        e.preventDefault();
        $($(this).attr('href')).modal('toggle');
    });

    $('body').on('click', '.service_product_add_to_cart', function() {
        var current_btn = $(this);
        current_btn.attr('disabled', 'disabled');
        var search_id_prod = $("#search_id_prod").val();

        var id_product = $(this).attr('data-id-product');
        var qty = current_btn.closest('.product-container').find('.product_quantity').val();
        if (typeof(qty) == 'undefined') {
            qty = 1;
        }

        $.ajax({
            url: rooms_booking_url,
            type: 'POST',
            dataType: 'json',
            data: {
                ajax: true,
                action: 'updateProductInCart',
                id_product: id_product,
                qty: qty,
                search_id_prod: search_id_prod,
                opt: 1,
            },
            success: function(result) {
                if (result.status) {
                    refresh_kpis(true);
                    refreshCartData();
                    showSuccessMessage(product_added_cart_txt)
                } else if (result.errors) {
                    showErrorMessage(result.errors);
                }
            },
            complete: function() {
                current_btn.attr('disabled', false);
            }
        });
    });

    $('body').on('click', '.service_product_delete', function() {
        var current_btn = $(this);
        current_btn.attr('disabled', 'disabled');
        var id_product = $(this).attr('data-id-product');

        $.ajax({
            url: rooms_booking_url,
            type: 'POST',
            dataType: 'json',
            data: {
                ajax: true,
                action: 'updateProductInCart',
                id_product: id_product,
                opt: 0,
            },
            success: function(result) {
                if (result) {
                    current_btn.closest('tr').remove();
                    $('#cart_total_amt').html(currency_prefix + result.total_amount + currency_suffix);
                    refresh_kpis(true);
                }
            }
        });
    });

    $("#date_from").datepicker({
        showOtherMonths: true,
        dateFormat: 'dd-mm-yy',
        altFormat: 'yy-mm-dd',
        altField: '#from_date',
        beforeShowDay: function (date) {
            return highlightDateBorder($("#date_from").val(), date);
        },
        onSelect: function(selectedDate) {
            var date_format = selectedDate.split("-");
            var selectedDate = new Date($.datepicker.formatDate('yy-mm-dd', new Date(date_format[2], date_format[1] - 1, date_format[0])));
            var date_from = $.datepicker.formatDate('yy-mm-dd', selectedDate);

            var date_to_format = $('#date_to').val().split("-");
            var selectedDateTo = new Date($.datepicker.formatDate('yy-mm-dd', new Date(date_to_format[2], date_to_format[1] - 1, date_to_format[0])));
            var date_to = $.datepicker.formatDate('yy-mm-dd', selectedDateTo);

            if (date_from >= date_to) {
                selectedDate.setDate(selectedDate.getDate() + 1);
                $("#date_to").datepicker("option", "minDate", selectedDate);
            }
        },
    });

    $("#date_to").datepicker({
        showOtherMonths: true,
        dateFormat: 'dd-mm-yy',
        altFormat: 'yy-mm-dd',
        altField: '#to_date',
        beforeShowDay: function (date) {
            return highlightDateBorder($("#date_to").val(), date);
        },
    });

    if (typeof(booking_calendar_data) != 'undefined') {
        var calendar_data = JSON.parse(booking_calendar_data);
        $(".hotel_date").datepicker({
            defaultDate: new Date(),
            dateFormat: 'dd-mm-yy',
            minDate: 0,
            onChangeMonthYear: function(year, month) {
                if (check_calender_var)
                    $.ajax({
                        url: rooms_booking_url,
                        data: {
                            ajax: true,
                            action: 'getDataOnMonthChange',
                            month: month,
                            year: year,
                        },
                        method: 'POST',
                        async: false,
                        success: function(result) {
                            calendar_data = JSON.parse(result);
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            alert(textStatus);
                        }
                    });
            },
            beforeShowDay: function(date) {
                var currentMonth = date.getMonth() + 1;
                var currentDate = date.getDate();
                if (currentMonth < 10) {
                    currentMonth = '0' + currentMonth;
                }
                if (currentDate < 10) {
                    currentDate = '0' + currentDate;
                }

                dmy = date.getFullYear() + "-" + currentMonth + "-" + currentDate;
                var flag = 0;

                $.each(calendar_data, function(key, value) {
                    if (key === dmy) {
                        if (value && typeof value.stats != 'undefined') {
                            msg = 'Total Available Rooms: ' + value.stats.num_avail + '&#10;Total Rooms In cart : ' + value.stats.num_cart + '&#10;Total Booked Rooms: ' + value.stats.num_booked + '&#10;Total Unvailable Rooms : ' + value.stats.num_part_avai;
                            flag = 1;
                        }
                        return 1;
                    }
                });
                if (flag) {
                    return [true, check_css_condition_var, msg];
                } else
                    return [true];
            }
        });
    } else {
        $(".hotel_date").datepicker({
            dateFormat: 'dd-mm-yy',
        });
    }

    $("#hotel_id").on('change', function() {
        var hotel_id = $(this).val();
        if (!isNaN(hotel_id)) {
            if (hotel_id > 0) {
                $.ajax({
                    url: rooms_booking_url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        ajax: true,
                        action: 'getRoomType',
                        hotel_id: hotel_id,
                    },
                    success: function(result) {
                        $("#hotel_id option[value='0']").remove(); // to remove Select hotel option
                        $('#room_type').empty();
                        html = "<option value='0'>" + opt_select_all + "</option>";
                        if (result.length) {
                            $.each(result, function(key, value) {
                                html += "<option value='" + value.id_product + "'>" + value.room_type + "</option>";
                            });
                            $('#room_type').append(html);
                        } else {
                            showErrorMessage(noRoomTypeAvlTxt);
                            $('#room_type').append(html);
                        }
                    }
                });
            }
        }
    });

    /*For swaping rooms in the modal*/
    $("#realloc_allocated_rooms").on('click', function(e) {
        $(".error_text").text('');
        if ($('#realloc_avail_rooms').val() == 0) {
            $("#realloc_sel_rm_err_p").text(slct_rm_err);
            return false;
        }
    });
    $("#swap_allocated_rooms").on('click', function(e) {
        $(".error_text").text('');
        if ($('#swap_avail_rooms').val() == 0) {
            $("#swap_sel_rm_err_p").text(slct_rm_err);
            return false;
        }
    });

    $('#mySwappigModal').on('hidden.bs.modal', function(e) {
        $(".modal_date_from").val('');
        $(".modal_date_to").val('');
        $(".modal_id_room").val('');
        $(".modal_curr_room_num").val('');
        $(".cust_name").text('');
        $(".cust_email").text('');
        $(".swp_rm_opts").remove();
        $(".realloc_rm_opts").remove();
    });

    $('#mySwappigModal').on('shown.bs.modal', function(e) {
        $(".modal_date_from").val(e.relatedTarget.dataset.date_from);
        $(".modal_date_to").val(e.relatedTarget.dataset.date_to);
        $(".modal_id_room").val(e.relatedTarget.dataset.id_room);
        $(".modal_curr_room_num").val(e.relatedTarget.dataset.room_num);
        $(".cust_name").text(e.relatedTarget.dataset.cust_name);
        $(".cust_email").text(e.relatedTarget.dataset.cust_email);
        html = '';
        if (e.relatedTarget.dataset.avail_rm_realloc) {
            var json_arr_rm_swp = JSON.parse(e.relatedTarget.dataset.avail_rm_swap);
            $.each(json_arr_rm_swp, function(key, val) {
                html += '<option class="swp_rm_opts" value="' + val.id_room + '" >' + val.room_num + '</option>';
            });
        }
        if (html != '') {
            $("#swap_avail_rooms").append(html);
        }

        html = '';
        if (e.relatedTarget.dataset.avail_rm_realloc) {
            var json_arr_rm_realloc = JSON.parse(e.relatedTarget.dataset.avail_rm_realloc);
            $.each(json_arr_rm_realloc, function(key, val) {
                html += '<option class="realloc_rm_opts" value="' + val.id_room + '" >' + val.room_num + '</option>';
            });
        }
        if (html != '') {
            $("#realloc_avail_rooms").append(html);
        }
    });

    $('body').on('click', '.avai_add_cart', function() {
        $current_btn = $(this);
        $current_btn.attr('disabled', 'disabled');
        var search_id_prod = $("#search_id_prod").val();
        var search_date_from = $("#search_date_from").val();
        var search_date_to = $("#search_date_to").val();

        var id_prod = $(this).attr('data-id-product');
        var id_room = $(this).attr('data-id-room');
        var id_hotel = $(this).attr('data-id-hotel');
        var date_from = $(this).attr('data-date-from');
        var date_to = $(this).attr('data-date-to');
        var booking_type = $("input[name='bk_type_" + id_room + "']:checked").val();
        var comment = $("#comment_" + id_room).val();
        var btn = $(this);

        $.ajax({
            url: rooms_booking_url,
            type: 'POST',
            dataType: 'json',
            data: {
                ajax: true,
                action: 'addRoomToCart',
                id_prod: id_prod,
                id_room: id_room,
                id_hotel: id_hotel,
                date_from: date_from,
                date_to: date_to,
                booking_type: booking_type,
                comment: comment,
                search_id_prod: search_id_prod,
                search_date_from: search_date_from,
                search_date_to: search_date_to,
                opt: 1,
            },
            success: function(result) {
                if (result) {
                    if (result.rms_in_cart) {
                        $(".cart_booking_btn").removeAttr('disabled');
                        $current_btn.removeAttr('disabled');
                    }

                    btn.removeClass('btn-primary').removeClass('avai_add_cart').addClass('btn-danger').addClass('avai_delete_cart_data').html(remove);

                    btn.attr('data-id-cart', result.id_cart);
                    btn.attr('data-id-cart-book-data', result.id_cart_book_data);
                    refreshCartData();
                    refresh_kpis(true);
                    // html = "<tr>";
                    // html += "<td>" + result.room_num + "</td>";
                    // html += "<td>" + result.room_type + "</td>";
                    // html += "<td>" + result.date_from + " To " + result.date_to + "</td>";
                    // html += "<td>" + currency_prefix + result.amount + currency_suffix + "</td>";
                    // html += "<td><button class='btn btn-default ajax_cart_delete_data' data-id-product='" + id_prod + "' data-id-hotel='" + id_hotel + "' data-id-cart='" + result.id_cart + "' data-id-cart-book-data='" + result.id_cart_book_data + "' data-date-from='" + date_from + "' data-date-to='" + date_to + "'><i class='icon-trash'></i></button></td>";
                    // html += "</tr>";

                    // $('.cart_tbody').append(html);

                    // $('#cart_total_amt').html(currency_prefix + result.total_amount + currency_suffix);
                    // $('#cart_record').html(result.rms_in_cart);

                    // For Stats
                    $('#cart_record').html(result.booking_stats.stats.num_cart);
                    $("#num_avail").html(result.booking_stats.stats.num_avail);
                    $('#cart_stats').html(result.booking_stats.stats.num_cart);
                }
            }
        });
    });

    $('body').on('click', '.par_add_cart', function() {
        $current_btn = $(this);
        $current_btn.attr('disabled', 'disabled');
        var search_id_prod = $("#search_id_prod").val();
        var search_date_from = $("#search_date_from").val();
        var search_date_to = $("#search_date_to").val();

        var id_prod = $(this).attr('data-id-product');
        var id_room = $(this).attr('data-id-room');
        var id_hotel = $(this).attr('data-id-hotel');
        var date_from = $(this).attr('data-date-from');
        var date_to = $(this).attr('data-date-to');

        var sub_key = $(this).attr('data-sub-key');
        var booking_type = $("input[name='bk_type_" + id_room + "_" + sub_key + "']:checked").val();
        var comment = $("#comment_" + id_room + "_" + sub_key).val();
        var btn = $(this);

        $.ajax({
            url: rooms_booking_url,
            type: 'POST',
            dataType: 'json',
            data: {
                ajax: true,
                action: 'addRoomToCart',
                id_prod: id_prod,
                id_room: id_room,
                id_hotel: id_hotel,
                date_from: date_from,
                date_to: date_to,
                booking_type: booking_type,
                comment: comment,
                search_id_prod: search_id_prod,
                search_date_from: search_date_from,
                search_date_to: search_date_to,
                opt: 1,
            },
            success: function(result) {
                if (result) {
                    if (result.rms_in_cart) {
                        $(".cart_booking_btn").removeAttr('disabled');
                        $current_btn.removeAttr('disabled');
                    }

                    btn.removeClass('btn-primary').removeClass('par_add_cart').addClass('btn-danger').addClass('part_delete_cart_data').html(remove);

                    btn.attr('data-id-cart', result.id_cart);
                    btn.attr('data-id-cart-book-data', result.id_cart_book_data);
                    refreshCartData();
                    refresh_kpis(true);
                    // html = "<tr>";
                    // html += "<td>" + result.room_num + "</td>";
                    // html += "<td>" + result.room_type + "</td>";
                    // html += "<td>" + result.date_from + " To " + result.date_to + "</td>";
                    // html += "<td>" + currency_prefix + result.amount + currency_suffix + "</td>";
                    // html += "<td><button class='btn btn-default ajax_cart_delete_data' data-id-product='" + id_prod + "' data-id-hotel='" + id_hotel + "' data-id-cart='" + result.id_cart + "' data-id-cart-book-data='" + result.id_cart_book_data + "' data-date-from='" + date_from + "' data-date-to='" + date_to + "'><i class='icon-trash'></i></button></td>";
                    // html += "</tr>";

                    // $('.cart_tbody').append(html);


                    // $('#cart_total_amt').html(currency_prefix + result.total_amount + currency_suffix);
                    // $('#cart_record').html(result.rms_in_cart);

                    // For Stats
                    $('#cart_record').html(result.booking_stats.stats.num_cart);
                    $('#cart_stats').html(result.booking_stats.stats.num_cart);
                    $("#num_part").html(result.booking_stats.stats.num_part_avai);
                }
            }
        });
    });

    $('body').on('click', '.ajax_cart_delete_data', function() {
        //for booking_data
        var search_id_prod = $("#search_id_prod").val();
        var search_date_from = $("#search_date_from").val();
        var search_date_to = $("#search_date_to").val();

        var ajax_delete = 1;
        var id_product = $(this).attr('data-id-product');
        var id_cart = $(this).attr('data-id-cart');
        var id_cart_book_data = $(this).attr('data-id-cart-book-data');
        var date_from = $(this).attr('data-date-from');
        var date_to = $(this).attr('data-date-to');
        var id_hotel = $(this).attr('data-id-hotel');
        var btn = $(this);

        $.ajax({
            url: rooms_booking_url,
            type: 'POST',
            dataType: 'json',
            async: false,
            data: {
                ajax: true,
                action: 'addRoomToCart',
                id_prod: id_product,
                id_cart: id_cart,
                id_cart_book_data: id_cart_book_data,
                date_from: date_from,
                date_to: date_to,
                id_hotel: id_hotel,
                search_id_prod: search_id_prod,
                search_date_from: search_date_from,
                search_date_to: search_date_to,
                ajax_delete: ajax_delete,
                opt: 0,
            },
            success: function(result) {
                if (result) {
                    if (!(result.rms_in_cart)) {
                        $(".cart_booking_btn").attr('disabled', 'true');
                    }

                    btn.parent().parent().remove();
                    $('#cart_total_amt').html(currency_prefix + result.total_amount + currency_suffix);
                    refresh_kpis(true);
                    // $('#cart_record').html(result.rms_in_cart);

                    // For Stats
                    $('#cart_record').html(result.booking_data.stats.num_cart);
                    $('#cart_stats').html(result.booking_data.stats.num_cart);
                    $("#num_avail").html(result.booking_data.stats.num_avail);
                    $("#num_part").html(result.booking_data.stats.num_part_avai);

                    var panel_btn = $(".tab-pane tr td button[data-id-cart-book-data='" + id_cart_book_data + "']");

                    panel_btn.attr('data-id-cart', '');
                    panel_btn.attr('data-id-cart-book-data', '');

                    if (panel_btn.hasClass('avai_delete_cart_data'))
                        panel_btn.removeClass('avai_delete_cart_data').addClass('avai_add_cart');
                    else if (panel_btn.hasClass('part_delete_cart_data'))
                        panel_btn.removeClass('part_delete_cart_data').addClass('par_add_cart');

                    panel_btn.removeClass('btn-danger').addClass('btn-primary').html(add_to_cart);

                    $("#htl_rooms_list").empty().append(result.room_tpl);
                }
            }
        });
    });

    $('body').on('click', '.avai_delete_cart_data, .part_delete_cart_data', function() {
        var search_id_prod = $("#search_id_prod").val();
        var search_date_from = $("#search_date_from").val();
        var search_date_to = $("#search_date_to").val();

        var id_product = $(this).attr('data-id-product');
        var id_cart = $(this).attr('data-id-cart');
        var id_cart_book_data = $(this).attr('data-id-cart-book-data');
        var date_from = $(this).attr('data-date-from');
        var date_to = $(this).attr('data-date-to');
        var id_hotel = $(this).attr('data-id-hotel');
        var btn = $(this);

        $.ajax({
            url: rooms_booking_url,
            type: 'POST',
            dataType: 'json',
            data: {
                ajax: true,
                action: 'addRoomToCart',
                id_prod: id_product,
                id_cart: id_cart,
                id_cart_book_data: id_cart_book_data,
                date_from: date_from,
                date_to: date_to,
                search_id_prod: search_id_prod,
                search_date_from: search_date_from,
                search_date_to: search_date_to,
                id_hotel: id_hotel,
                opt: 0,
            },
            success: function(result) {
                if (result) {
                    if (!(result.rms_in_cart)) {
                        $(".cart_booking_btn").attr('disabled', 'true');
                    }

                    $(".cart_tbody tr td button[data-id-cart-book-data='" + id_cart_book_data + "']").parent().parent().remove();
                    $('#cart_total_amt').html(currency_prefix + result.total_amount + currency_suffix);
                    refresh_kpis(true);
                    // $('#cart_record').html(result.rms_in_cart);

                    //For Stats
                    $('#cart_record').html(result.booking_stats.stats.num_cart);
                    $('#cart_stats').html(result.booking_stats.stats.num_cart);
                    $("#num_avail").html(result.booking_stats.stats.num_avail);
                    $("#num_part").html(result.booking_stats.stats.num_part_avai);

                    btn.attr('data-id-cart', '');
                    btn.attr('data-id-cart-book-data', '');

                    if (btn.hasClass('avai_delete_cart_data'))
                        btn.removeClass('avai_delete_cart_data').addClass('avai_add_cart');
                    else if (btn.hasClass('part_delete_cart_data'))
                        btn.removeClass('part_delete_cart_data').addClass('par_add_cart');

                    btn.removeClass('btn-danger').addClass('btn-primary').html(add_to_cart);
                }
            }
        });
    });

    $('#search_hotel_list').on('click', function(e) {
        if ($('#booking_product').val() == '') {
            alert(product_type_cond);
            return false;
        } else if ($('#from_date').val() == '') {
            alert(from_date_cond);
            return false;
        } else if ($('#to_date').val() == '') {
            alert(to_date_cond);
            return false;
        } else if ($('#hotel_id').val() == '') {
            alert(hotel_name_cond);
            return false;
        } else if ($('#num-rooms').val() == '') {
            alert(num_rooms_cond);
            return false;
        }
    });
});

function toggleSearchFields()
{
    if ($('#booking_product').val() == 1) {
        $('#date_from').closest('.form-group').show();
        $('#date_to').closest('.form-group').show();
        $('#room_type').closest('.form-group').show();
    } else {
        $('#date_from').closest('.form-group').hide();
        $('#date_to').closest('.form-group').hide();
        $('#room_type').closest('.form-group').hide();
    }
}

function refreshCartData()
{
    $.ajax({
        url: rooms_booking_url,
        type: 'POST',
        dataType: 'json',
        data: {
            ajax: true,
            action: 'updateCartData',
        },
        success: function(result) {
            if (result) {
                $("#cartModal").remove()
                $("#content").append(result.cart_content);
            }
        }
    });
}

function highlightDateBorder(elementVal, date)
{
    if (elementVal) {
        var currentDate = date.getDate();
        var currentMonth = date.getMonth()+1;
        if (currentMonth < 10) {
            currentMonth = '0' + currentMonth;
        }
        if (currentDate < 10) {
            currentDate = '0' + currentDate;
        }
        dmy = date.getFullYear() + "-" + currentMonth + "-" + currentDate;
        var date_format = elementVal.split("-");
        var check_in_time = (date_format[2]) + '-' + (date_format[1]) + '-' + (date_format[0]);
        if (dmy == check_in_time) {
            return [true, "selectedCheckedDate", "Check-In date"];
        } else {
            return [true, ""];
        }
    } else {
        return [true, ""];
    }
}