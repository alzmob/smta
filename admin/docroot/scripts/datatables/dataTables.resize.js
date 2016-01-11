$(document).ready(function() {
	$(window).smartresize(function () {
		var tables = $.fn.dataTable.tables(true);
		for (var i=0;i<tables.length;i++) {
			$(tables[i]).DataTable().settings()[0].oScroll.sY = calcDataTablesHeight($(tables[i]));
			$(tables[i]).DataTable().columns.adjust();
			$('#' + $(tables[i]).prop('id') + '_wrapper div.dataTables_scrollBody').css('height', $(tables[i]).DataTable().settings()[0].oScroll.sY + 'px');
		}
	});
});

function calcDataTablesHeight(obj) {
	if ($(obj).height() > 500) {
		return $(window).height() - ($(obj).offset().top) - 100;
	} else {
		return 525;
	}
};