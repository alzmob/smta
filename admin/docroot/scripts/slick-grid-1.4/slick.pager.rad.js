(function($) {
    function SlickGridPager(dataView, grid, $container)
    {
        var $status, $contextMenu;

        function init()
        {
            dataView.onPagingInfoChanged.subscribe(function(pagingInfo) {
                updatePager(pagingInfo);
            });

            constructPagerUI();
            updatePager(dataView.getPagingInfo());
        }

		function getNavState()
		{
			var cannotLeaveEditMode = !Slick.GlobalEditorLock.commitCurrentEdit();
			var pagingInfo = dataView.getPagingInfo();
			var lastPage = Math.floor(pagingInfo.totalRows/pagingInfo.pageSize);

            return {
                canGotoFirst:	!cannotLeaveEditMode && pagingInfo.pageSize != 0 && pagingInfo.pageNum > 0,
                canGotoLast:	!cannotLeaveEditMode && pagingInfo.pageSize != 0 && pagingInfo.pageNum != lastPage,
                canGotoPrev:	!cannotLeaveEditMode && pagingInfo.pageSize != 0 && pagingInfo.pageNum > 0,
                canGotoNext:	!cannotLeaveEditMode && pagingInfo.pageSize != 0 && pagingInfo.pageNum < lastPage,
                pagingInfo:		pagingInfo,
                lastPage:		lastPage
            }
        }

        function setPageSize(n)
        {
            dataView.setPagingOptions({pageSize:n});
        }

        function gotoFirst()
        {
            if (getNavState().canGotoFirst)
                dataView.setPagingOptions({pageNum: 0});
        }

        function gotoLast()
        {
            var state = getNavState();
            if (state.canGotoLast)
                dataView.setPagingOptions({pageNum: state.lastPage});
        }

        function gotoPrev()
        {
            var state = getNavState();
            if (state.canGotoPrev)
                dataView.setPagingOptions({pageNum: state.pagingInfo.pageNum-1});
        }

        function gotoNext()
        {
            var state = getNavState();
            if (state.canGotoNext)
                dataView.setPagingOptions({pageNum: state.pagingInfo.pageNum+1});
        }

        function constructPagerUI()
        {
            $container.empty();

            $status = $("<span class='slick-pager-status' />").appendTo($container);

            var $nav = $("<span class='slick-pager-nav btn-group' role='group' />").appendTo($container);
            var $settings = $("<span class='slick-pager-settings' />").appendTo($container);

            $settings
                    .append("<span class='slick-pager-settings-expanded' style='display:none'>Show: <a data='20'>Auto</a><a data=25>25</a><a data=50>50</a><a data=100>100</a><a data=500>500</a></span>");

            $settings.find("a[data]").click(function(e) {
                var pagesize = $(e.target).attr("data");
                if (pagesize != undefined)
                {
                    if (pagesize == -1)
                    {
                        var vp = grid.getViewport();
                        setPageSize(vp.bottom-vp.top);
                    }
                    else
                        setPageSize(parseInt(pagesize));
                }
            });

            var icon_prefix = "<button href='#' class='btn btn-sm btn-default'><span class='glyphicon ";
            var icon_suffix = "'></span></button>";
            //var icon_prefix = "<span class='ui-state-default ui-corner-all ui-icon-container'><span class='ui-icon ";
            //var icon_suffix = "' /></span>";

            $(icon_prefix + "glyphicon-list-alt" + icon_suffix)
                    .click(function() { $(this).siblings(".slick-pager-settings-expanded").toggle() })
                    .appendTo($settings);

            $(icon_prefix + "glyphicon-step-backward" + icon_suffix)
                    .click(gotoFirst)
                    .appendTo($nav);

            $(icon_prefix + "glyphicon-backward" + icon_suffix)
                    .click(gotoPrev)
                    .appendTo($nav);

            $(icon_prefix + "glyphicon-forward" + icon_suffix)
                    .click(gotoNext)
                    .appendTo($nav);

            $(icon_prefix + "glyphicon-step-forward" + icon_suffix)
                    .click(gotoLast)
                    .appendTo($nav);
/*
            $container.find(".ui-icon-container")
                    .hover(function() {
                        $(this).toggleClass("ui-state-hover");
                    });
*/
            $container.children().wrapAll("<div class='slick-pager' />");
        }


        function updatePager(pagingInfo)
        {
            var state = getNavState();

            $container.find(".slick-pager-nav span").parent().removeAttr("disabled");
            if (!state.canGotoFirst) $container.find(".glyphicon-step-backward").parent().attr("disabled", "disabled");
            if (!state.canGotoLast) $container.find(".glyphicon-step-forward").parent().attr("disabled", "disabled");
            if (!state.canGotoNext) $container.find(".glyphicon-forward").parent().attr("disabled", "disabled");
            if (!state.canGotoPrev) $container.find(".glyphicon-backward").parent().attr("disabled", "disabled");


            if (pagingInfo.pageSize == 0)
                $status.text("Viewing all " + pagingInfo.totalRows + " rows");
            else {
				var start = 0;
				var end = 0;
				if (pagingInfo.totalRows > 0) {
					start = pagingInfo.pageNum * pagingInfo.pageSize;
					end = start + pagingInfo.pageSize;
					if (end > pagingInfo.totalRows)
						end = pagingInfo.totalRows;
					start += 1;
				}
				
				$status.text('viewing rows ' + $.number(start) + ' - ' + $.number(end) + ' of ' + $.number(pagingInfo.totalRows) + ' (page ' + $.number((pagingInfo.pageNum+1)) + " of " + $.number((Math.floor(pagingInfo.totalRows/pagingInfo.pageSize)+1)) + ')');
			}
        }



        init();
    }

    // Slick.Controls.Pager
    $.extend(true, window, { Slick: { Controls: { Pager: SlickGridPager }}});
})(jQuery);
