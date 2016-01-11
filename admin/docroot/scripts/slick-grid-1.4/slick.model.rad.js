/***
 * A simple observer pattern implementation.
 */
function EventHelper() {
    this.handlers = [];

    this.subscribe = function(fn) {
        this.handlers.push(fn);
    };

    this.notify = function(args) {
        for (var i = 0; i < this.handlers.length; i++) {
            this.handlers[i].call(this, args);
        }
    };

    return this;
}


(function($) {
    /***
     * A sample Model implementation.
     * Provides a filtered view of the underlying data.
     *
     * Relies on the data item having an "id" property uniquely identifying it.
     */
    function DataView() {
        var self = this;

        // private
        var _data = [];    // Internal data array to store retrieved data
        var idProperty = "_id";  // property holding a unique row id
        var items = {};			// data by index
        var rows = [];			// data by row
        var idxById = {};		// indexes by id
        var rowsById = null;	// rows by id; lazy-calculated
        var filter = null;		// filter function
        var updated = null; 	// updated item ids
        var suspend = false;	// suspends the recalculation
        var sortAsc = true;
        var sortComparer = null;
        var fastSortField = null;
        var grid_form = null;
        var h_request = null;
        
        var pagesize = 0;
        var pagenum = 0;
        var totalRows = 0;

        // events
        var onRowCountChanged = new EventHelper();
        var onRowsChanged = new EventHelper();
        var onPagingInfoChanged = new EventHelper();
        var onDataLoading = new EventHelper();
		var onDataLoaded = new EventHelper();
		
        function beginUpdate() {
            suspend = true;
        }

        function endUpdate() {
            suspend = false;
            refresh();
        }

        function refreshIdxById() {
            idxById = {};
            $.each(items, function(i, item) {
            	if (item != undefined) {
            		// Handle Mongo Object ids
            		if (item._id instanceof Object) {
            			var id = item._id.$id;
            		} else {
	            		var id = item._id;
            		}
	                if (id == undefined || idxById[id] != undefined) {
	                	if (window.console) { console.log(item); }
	                	if (window.console) { console.log(id); }
	                    throw "Each data element must implement a unique 'id' property or the 'id' is 0";
	                }
	                idxById[id] = i;
            	}
            });
        }

        function getForm() {
            return grid_form;
        }

        function setForm(_form) {
        	grid_form = _form;
            $(grid_form).form(function(data) {
            	server_page_num = 0;
            	server_page_size = 0;
          		if (data.pagination) {
          			server_page_num = parseInt(data.pagination.page) - 1;
          			server_page_size = parseInt(data.pagination.items_per_page);
          			if (parseInt(data.pagination.total_rows) != totalRows) {
          				items = {};
          				totalRows = parseInt(data.pagination.total_rows);
          			}
          			onPagingInfoChanged.notify(getPagingInfo());
          		}
          		offset = parseInt(server_page_size) * parseInt(server_page_num);
            	if (data.entries) {
            		$.each(data.entries, function(i, item) {
            			items[(offset + i)] = item;
            		});
          		}
            	refreshIdxById();
        		endUpdate();
            	
            	onDataLoaded.notify({from:offset, to:(offset + pagesize)});
          	}, {keep_form:true});
        }
        
        function getItems() {
            return items;
        }

        function setItems(data, objectIdProperty) {
            if (objectIdProperty !== undefined) idProperty = objectIdProperty;
            items = data;
            refreshIdxById();
            refresh();
        }

        function setPagingOptions(args) {
            if (args.pageSize != undefined) {
                pagesize = args.pageSize;
            }
            if (args.totalRows != undefined) {
            	totalRows = args.totalRows;
            }

            if (args.pageNum != undefined) {
                pagenum = Math.min(args.pageNum, Math.ceil(totalRows / pagesize));
            }

            onPagingInfoChanged.notify(getPagingInfo());
            
            refresh();
        }
		
        function getPagingInfo() {
            return {pageSize:pagesize, pageNum:pagenum, totalRows:totalRows};
        }

        function sort(comparer, ascending) {
        	_form = getForm();
        	$('input[name=sort]', _form).val(comparer);
        	if (ascending === false) {
        		$('input[name=sord]', _form).val('DESC');
        	} else {
        		$('input[name=sord]', _form).val('ASC');
        	}
        	ensureData();
        }

        /***
         * Provides a workaround for the extremely slow sorting in IE.
         * Does a [lexicographic] sort on a give column by temporarily overriding Object.prototype.toString
         * to return the value of that field and then doing a native Array.sort().
         */
        function fastSort(field, ascending) {
            return sort(field, ascending);
        }

        function reSort() {
            if (sortComparer)
                sort(sortComparer,sortAsc);
            else if (fastSortField)
                fastSort(fastSortField,sortAsc);
        }

        function setFilter(filterFn) {
            filter = filterFn;
            refresh();
        }

        function getItemByIdx(i) {
            return items[i];
        }

        function getIdxById(id) {
            return idxById[id];
        }

        // calculate the lookup table on first call
        function getRowById(id) {
            if (!rowsById) {
                rowsById = {};
                for (var i = 0, l = rows.length; i < l; ++i) {
                    rowsById[rows[i][idProperty]] = i;
                }
            }

            return rowsById[id];
        }

        function getItemById(id) {
            return items[idxById[id]];
        }

        function updateItem(id, item) {
            if (idxById[id] === undefined || id !== item[idProperty])
                throw "Invalid or non-matching id";
            items[idxById[id]] = item;
            if (!updated) updated = {};
            updated[id] = true;
            refresh();
        }

        function insertItem(insertBefore, item) {
            items.splice(insertBefore, 0, item);
            refreshIdxById();  // TODO:  optimize
            refresh();
        }

        function addItem(item) {
            items.push(item);
            refreshIdxById();  // TODO:  optimize
            refresh();
        }

		function insertItems(insertBefore, newItems) {
			$.each(newItems, function(i) {
				items.splice(insertBefore + i, 0, this);
			});
			refreshIdxById();
			refresh();
		}
		function addItems(newItems) {
			$.each(newItems, function() {
				items.push(this);
			});
			refreshIdxById();
			refresh();
		}
		
        function deleteItem(id) {
            if (idxById[id] === undefined)
                throw "Invalid id";
            items.splice(idxById[id], 1);
            refreshIdxById();  // TODO:  optimize
            refresh();
        }

        function recalc(_items, _rows, _filter, _updated) {
            var diff = [];
            var items = _items, rows = _rows, filter = _filter, updated = _updated; // cache as local vars
            
            rowsById = null;

            // go over all items remapping them to rows on the fly
            // while keeping track of the differences and updating indexes
            var rl = rows.length;
            var currentRowIndex = 0;
            var currentPageIndex = 0;
            var item,id;

            $.each(items, function(i, item) {
            	currentRowIndex = i;
                id = item.id;

                if (!filter || filter(item)) {
                    if (!pagesize || (currentRowIndex >= pagesize * pagenum && currentRowIndex < pagesize * (pagenum + 1))) {
                        if (currentPageIndex >= rl || id != rows[currentPageIndex][idProperty] || (updated && updated[id])) {
                            diff[diff.length] = currentPageIndex;
                        }

                        rows[currentPageIndex] = item;
                        currentPageIndex++;
                    }
                }
            });
            if (rl > currentPageIndex) {
                rows.splice(currentPageIndex, rl - currentPageIndex);
            }

            return diff;
        }
        
        function refresh() {
            if (suspend) return;

            if (!isDataLoaded()) {
            	beginUpdate();
            	ensureData();  
            	return false;
            }
            
            var countBefore = rows.length;
            var totalRowsBefore = totalRows;

            var diff = recalc(items, rows, filter, updated); // pass as direct refs to avoid closure perf hit

            // if the current page is no longer valid, go to last page and recalc
            // we suffer a performance penalty here, but the main loop (recalc) remains highly optimized
            if (pagesize && totalRows < pagenum * pagesize) {
                pagenum = Math.floor(totalRows / pagesize);
                diff = recalc(items, rows, filter, updated);
            }

            updated = null;

            if (totalRowsBefore != totalRows) onPagingInfoChanged.notify(getPagingInfo());
            if (countBefore != rows.length) onRowCountChanged.notify({previous:countBefore, current:rows.length});
            if (diff.length > 0) onRowsChanged.notify(diff);
        }
        
        function isDataLoaded() {
        	offset = parseInt(pagesize) * parseInt(pagenum);
        	for (i=offset;i<(offset + parseInt(pagesize));i++) {
        		if (i < totalRows) {
					if (items[i] == undefined || items[i] == null) {
						return false;
					}
        		}
			}

			return true;
		}
        
        function ensureData() {
        	// checks that we have data in the indices
        	// Figure out what page we really need to send to the server
        	_form = getForm();
        	offset = parseInt(pagesize) * parseInt(pagenum);
        	items_per_page = $('input[name=items_per_page]', _form).val();
        	server_page = parseInt(offset / parseInt(items_per_page));
        	        	
        	$('input[name=page]', _form).val((server_page + 1));
        	
        	if (h_request != null) {
				clearTimeout(h_request);
        	}

			h_request = setTimeout(function() {
				onDataLoading.notify({from:offset,to:(offset+items_per_page)});
				getForm().trigger('submit');
			}, 50);
        }


        return {
            // properties
            "rows":             rows,  // note: neither the array or the data in it should be modified directly

            // methods
            "beginUpdate":      beginUpdate,
            "endUpdate":        endUpdate,
            "setPagingOptions": setPagingOptions,
            "getPagingInfo":    getPagingInfo,
            "getItems":         getItems,
            "setItems":         setItems,
            "setFilter":        setFilter,
            "sort":             sort,
            "fastSort":         fastSort,
            "reSort":           reSort,
            "getIdxById":       getIdxById,
            "getRowById":       getRowById,
            "getItemById":      getItemById,
            "getItemByIdx":     getItemByIdx,
            "refresh":          refresh,
            "updateItem":       updateItem,
            "insertItem":       insertItem,
			"insertItems":      insertItems, 
            "addItem":          addItem,
            "deleteItem":       deleteItem,
            "setForm":	        setForm,

            // events
            "onRowCountChanged":    onRowCountChanged,
            "onRowsChanged":        onRowsChanged,
            "onPagingInfoChanged":  onPagingInfoChanged,
            "onDataLoading": 		onDataLoading,
			"onDataLoaded": 		onDataLoaded
        };
    }

    // Slick.Data.DataView
    $.extend(true, window, { Slick: { Data: { DataView: DataView }}});
})(jQuery);