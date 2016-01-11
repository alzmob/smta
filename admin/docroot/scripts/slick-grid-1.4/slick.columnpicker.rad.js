/*
 * modified original column picker
 */
(function($) {
	function SlickColumnPicker(columns,grid)
	{
		var $menu;

		function init() {
			grid.onHeaderContextMenu = displayContextMenu;

			$menu = $("<span class='slick-columnpicker' style='display:none;position:absolute;z-index:20;max-height:500px;overflow-y:scroll;' />").appendTo(document.body);

			$menu.bind("mouseleave", function(e) { $(this).fadeOut() });
			$menu.bind("click", updateColumn);

		}

		function displayContextMenu(e)
		{
			$menu.empty();

            var visibleColumns = grid.getColumns();
			var $li, $input;
			for (var i=0; i<columns.length; i++) {
				if ( !columns[i].permanent ) {
					$li = $("<li />").appendTo($menu);

					$input = $("<input type='checkbox' />")
							.attr("id", "columnpicker_" + i)
							.attr("rel", columns[i].id)
							.data("id", columns[i].id)
							.appendTo($li);

					if (grid.getColumnIndex(columns[i].id) != null)
						$input.prop("checked","checked");

					$("<label for='columnpicker_" + i + "' />")
						.text(columns[i].name)
						.appendTo($li);
				}
			}

			$("<hr/>").appendTo($menu);
			$li = $("<li />").appendTo($menu);
			$input = $("<input type='checkbox' id='autoresize' />").appendTo($li);
			$("<label for='autoresize'>Force Fit Columns</label>").appendTo($li);
			if (grid.getOptions().forceFitColumns)
				$input.prop("checked", "checked");

			$menu
				.css("top", e.pageY - 10)
				.css("left", e.pageX - 10)
				.fadeIn();
		}

		function updateColumn(e)
		{
			if (e.target.id == 'autoresize') {
				if (e.target.checked) {
					grid.setOptions({forceFitColumns: true});
					grid.autosizeColumns();
				} else {
					grid.setOptions({forceFitColumns: false});
				}
				return;
			}

			if ($(e.target).is(":checkbox")) {
				// keep at least 1 column
				if ($menu.find(":checkbox:checked").length == 0) {
					$(e.target).attr("checked","checked");
					return;
				}

				// array to store visible columns in
				var goodColumns = [];
				
				// get current shown columns (in the order they currently are in)
				var currentVisibleColumns = grid.getColumns();
				
				// get an array of all the checkboxes in the column picker
				var $checkboxes = $menu.find(":checkbox[id^=columnpicker]");
				
				// loop over current visible columns, pushing those columns into the 'good' array if they should be visible, thus preserving order
				$.each(currentVisibleColumns, function() {
					var currCheckbox = $checkboxes.filter('[rel='+this.id+']');
					if (this.permanent || currCheckbox.is(":checked")) {
						goodColumns.push(this);
						currCheckbox.removeAttr('checked'); // uncheck it after we process it
					}
				});
				// get any remaining checked boxes (must be a single added column, but use each to be safe)
				$checkboxes.filter(':checked').each( function() {
					var $checked = $(this);
					$.each(columns, function() {
						// find the column and push it onto the end
						if ($checked.attr('rel') == this.id)
							goodColumns.push(this);
					});
				});
				// recheck the boxes so more selections can be made
				$.each(goodColumns, function() {
					$checkboxes.filter('[rel='+this.id+']').prop('checked', 'checked');
				});
				// set the columns into the grid
				grid.setColumns(goodColumns);

				if (grid.onColumnsSelected)
					grid.onColumnsSelected();
			}
		}


		init();
	}

	// Slick.Controls.ColumnPicker
	$.extend(true, window, { Slick: { Controls: { ColumnPicker: SlickColumnPicker }}});
})(jQuery);
