/**
* 2010-2018 Webkul.
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
*  @copyright 2010-2018 Webkul IN
*  @license   https://store.webkul.com/license.html
*/

$(document).ready(function()
{
	$(".clear_filter").on("click", function()
	{
		var filter_wrapper = $(this).parents(".layered_filter_heading").siblings(".lf_sub_cont");
		var make_diff = filter_wrapper.children("div"); //use to make difference between checkbox and price slider
		if (make_diff.hasClass("layered_filt")) // for checkbox
		{
			var selected_filter = filter_wrapper.find("div.layered_filt input.filter:checked").prop("checked", false);
			selected_filter.parent("span.checked").removeClass("checked");

			if (selected_filter.length)
			{
				triggerFilter();
			}
		}
		else if (make_diff.hasClass("price_filter_subcont")) //for price bar
		{
			ini_price = $("#filter_price_silder").slider("values");
			if ((ini_price[0] - ini_price[1]) != (max_price - min_price))
			{
				$("#filter_price_silder").slider("values", [min_price, max_price]);

				var slider_price_cont = filter_wrapper.find("div.price_filter_subcont");
				slider_price_cont.find("span#filter_price_from").html(min_price);
				slider_price_cont.find("span#filter_price_to").html(max_price);

				/* -------NOTICE------- */
				//triggerFilter();
				//on need of triggerFilter as it is triggered from base function(from which slider is created)
			}
		}
	});



	var slider_diff = 0;

	$("#filter_price_silder").slider(
	{
		range: true,
		min: min_price,
		max: max_price,
		values: [min_price, max_price],
		slide: function(event, ui)
		{
			$("#filter_price_from").html(ui.values[0]);
			$("#filter_price_to").html(ui.values[1]);
		},
		change: function(event, ui)
		{
			if (slider_diff != (parseInt(ui.values[0]) - parseInt(ui.values[1])))
			{
				slider_diff = parseInt(ui.values[0]) - parseInt(ui.values[1]);

				triggerFilter();
			}
		}
	});

	$(".sort_result").on("click", function(e)
	{
		e.preventDefault();

		$('.sort_btn_span').attr('data-sort-by', 0);
		$('.sort_btn_span').attr('data-sort-value', 0);

		$('#gst_rating .sort_btn_span').html($('#gst_rating .sort_btn_span').attr('data-sort-for'));
		$('#price_ftr .sort_btn_span').html($('#price_ftr .sort_btn_span').attr('data-sort-for'));

		// select btn data enter
		var sort_text = $(this).html();
		var dp_btn_span = $(this).parents('div.filter_dw_cont').find('button span.sort_btn_span');
		dp_btn_span.html(sort_text);
		dp_btn_span.attr('data-sort-by', $(this).attr('data-sort-by'));
		dp_btn_span.attr('data-sort-value', $(this).attr('data-value'));

		triggerFilter();
	});

	$('.filter').on('click', function()
	{
		triggerFilter();
	});

	function triggerFilter()
	{
		refreshSearchResult();
	}

	function createFilterObj()
	{
		var filter = {};
		filter['filter_data'] = {};
		$('.filter').each(function()
		{
			if ($(this).is(':checked'))
			{
				var temp_type = $(this).attr('data-type');
				if (typeof filter['filter_data'][temp_type] != 'undefined')
				{
					filter['filter_data'][temp_type].push($(this).val());
				}
				else
				{
					filter['filter_data'][temp_type] = [];
					filter['filter_data'][temp_type].push($(this).val());
				}
			}
		});

		var slider_val = $("#filter_price_silder").slider("values");
		filter['filter_data']['price'] = [];
		filter['filter_data']['price'].push(slider_val[0]);
		filter['filter_data']['price'].push(slider_val[1]);

		var sort_filter = $(".sort_btn_span[data-sort-value!='0']");
		if (sort_filter.length)
		{
			filter['sort_by'] = sort_filter.attr("data-sort-by");
			filter['sort_value'] = sort_filter.attr("data-sort-value");
		}
		else
		{
			filter['sort_by'] = 0;
			filter['sort_value'] = 0;
		}
		return filter;
	}

	$(document).on('refreshSearchResult', function(evt, params) {
		params = $.extend(
			true, params, createFilterObj()
		);
	})
});