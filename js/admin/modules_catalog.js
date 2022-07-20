$('document').ready( function() {
	loadSuggestion();
    // ScrollTo
    $('#module-search').on('keyup', function(){
        val = this.value;
        var interval = setTimeout(function () {
            filterModules(val, 'module-name');
        }, 200);
    }).on('keydown', function(e){
        if (e.keyCode == 13)
            return false;
		if (e.keyCode == 27) {
			this.value = '';
		}
    });

	$('#theme-search').on('keyup', function(){
        val = this.value;
        var interval = setTimeout(function () {
            filterTheme(val, 'module-name');
        }, 200);
    }).on('keydown', function(e){
        if (e.keyCode == 13)
            return false;
		if (e.keyCode == 27) {
			this.value = '';
		}
    });


	$('#theme-sort, #module-sort').on('change', function(){
		setFilter();
	});

	function filterModules(val, element_class)
    {
		// unhide all elements
		$('#suggested-modules-list .list-empty').hide();
        $('#suggested-modules-list .module-panel').show();

        if (val != '') {
            var reg = new RegExp(val, "i");
            $('#suggested-modules-list .module-panel .'+element_class).each(function(id, mod_name) {
                if (!reg.test($(mod_name).text()) && !reg.test($(mod_name).data('module'))){
                // if (!$(mod_name).text().includes(val)) {
                    $(mod_name).closest('.module-panel').hide();
                }
            });
        }
		if (!$('#suggested-modules-list .module-panel:visible').length) {
			$('#suggested-modules-list .list-empty').show();
		}
    }

	function filterTheme(val, element_class)
	{
		$('#suggested-theme-list .list-empty').hide();
        $('#suggested-theme-list .module-panel').show();
		if (val != '') {
            // unhide all elements
            var reg = new RegExp(val, "i");
            $('#suggested-theme-list .module-panel .'+element_class).each(function(id, mod_name) {
                if (!reg.test($(mod_name).text()) && !reg.test($(mod_name).data('module'))){
                // if (!$(mod_name).text().includes(val)) {
                    $(mod_name).closest('.module-panel').hide();
                }
            });
        }
		if (!$('#suggested-theme-list .module-panel:visible').length) {
			$('#suggested-theme-list .list-empty').show();
		}
	}

	function loadSuggestion()
	{
		$.ajax({
			type: 'POST',
			url: 'index.php',
			async: true,
			dataType: 'JSON',
			data: {
				action: 'getSuggestionContent',
				tab: 'AdminModulesCatalog',
				ajax: 1,
				token: token
			},
			success: function(res) {
				if (res.success) {
					$('#suggestion-wrapper-placeholder').fadeOut('slow').hide();
					$('#suggestion-wrapper').html(res.content).fadeIn('slow');
				}
			},
		});
	}

	function setFilter()
	{
		let theme_sorting = $("#theme-sort").val();
		let module_sorting = $("#module-sort").val();
		$.ajax({
			type: 'POST',
			url: 'index.php',
			async: true,
			dataType: 'JSON',
			data: {
				action: 'setSorting',
				theme_sorting: theme_sorting,
				module_sorting: module_sorting,
				tab: 'AdminModulesCatalog',
				ajax: 1,
				token: token
			},
			success: function(res) {
				location.reload();
			},
		});
	}

});