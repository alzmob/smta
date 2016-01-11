/**
 * jquery.slickgrid.rad.js
 * wrapper around slick grid library
 */
(function($) {
	

	/*************************************************************************
	 * Slickgrid library wrapper public methods
	 *************************************************************************
	 */
	 
	$.fn.extend({
		// slick grid constructor
		slickGrid: function(param) {
			if ( typeof param == 'string' ) {
				if ( $.slickGrid.isPublicMethod(param) ) {
					var slickGrid = $.slickGrid.getInstance($(this), {}, {});
					return slickGrid[param].apply(slickGrid, Array.prototype.slice.call(arguments, 1));
				}
				
				$.rad.notify.error('Error', param + ' is not a valid slickgrid function!');
				return this;
			}
			else if ( typeof param === 'object' ) {
				var slick_opts = arguments[1];
				return this.each(function() {
					var slickGrid = $.slickGrid.getInstance($(this), param, slick_opts);
				});
			}
			
			$.rad.notify.error('Error', 'Unrecognized parameter type');
			return this;
		},
		
		/**************************************************************
		 *	the following are maintained for backwards compatibility
		 *	-- you are encouraged to use the function above instead,
		 *	   passing the method and its arguments as parameters
		 **************************************************************/
		
		// given data and dimensions, refactor data into appropriate form
		slickSetData: function(data, dimensions) {
			slickInstance = $.slickGrid.getInstance($(this), {}, {});
			slickInstance.setData(data, dimensions || null);
		},
		// update grid with new data
		slickUpdateGrid: function(data) {
			slickInstance = $.slickGrid.getInstance($(this), {}, {});
			slickInstance.updateGrid(data);
		},
		// display loading indicator
		slickShowLoadingIndicator: function() {
			slickInstance = $.slickGrid.getInstance($(this), {}, {});
			slickInstance.showLoadingIndicator($(this));
		},
		// hide loading indicator
		slickHideLoadingIndicator: function() {
			slickInstance = $.slickGrid.getInstance($(this), {}, {});
			slickInstance.hideLoadingIndicator();
		},
		slickUpdateItem: function(item) {
			slickInstance = $.slickGrid.getInstance($(this), {}, {});
			return slickInstance.updateItem(item);
		},
		// return the data set being used
		slickGetItems: function() {
			slickInstance = $.slickGrid.getInstance($(this), {}, {});
			return slickInstance.getItems();
		},
		// return the data set being used
		slickGetAllColumns: function() {
			slickInstance = $.slickGrid.getInstance($(this), {}, {});
			return slickInstance.getAllColumns();
		},
		slickGetCurrentColumns: function() {
			slickInstance = $.slickGrid.getInstance($(this), {}, {});
			return slickInstance.getCurrentColumns();		
		},
		slickSetColumns: function(columns) {
			slickInstance = $.slickGrid.getInstance($(this), {}, {});
			return slickInstance.setColumns(columns);
		},
		slickRemoveRow: function(row) {
			slickInstance = $.slickGrid.getInstance($(this), {}, {});
			return slickInstance.removeRow(row);
		},
		slickRender: function() {
			slickInstance = $.slickGrid.getInstance($(this), {}, {});
			return slickInstance.renderGrid();
		},
		slickGetDataView: function() {
			slickInstance = $.slickGrid.getInstance($(this), {}, {});
			return slickInstance.getDataView();
		},
		slickViewPort: function() {
			slickInstance = $.slickGrid.getInstance($(this), {}, {});
			return slickInstance.getViewPort();
		},
		slickReloadData: function(data, dimensions) {
			slickInstance = $.slickGrid.getInstance($(this), {}, {});
			slickInstance.reloadData(data, dimensions);
			return true;
		}
	});
	
	/*************************************************************************
	 * Slickgrid library wrapper
	 *************************************************************************
	 */
	
	$.extend({
		// constructor
		slickGrid: function(report_grid, rad_options, slick_options) {
			return this.init(report_grid, rad_options, slick_options);
		}
	});

	$.extend($.slickGrid, {
		// get or create a slick grid instance
		getInstance: function($element, rad_options, slick_options) {
			if ($element.data('slickGridData')) return $element.data('slickGridData');
			var slickInst = new $.slickGrid($element, rad_options, slick_options);
			$element.data('slickGridData', slickInst);
			return slickInst;
		},
		getPublicMethods: function() {
			return [ 'setData', 'updateGrid', 'showLoadingIndicator', 'hideLoadingIndicator',
					 'getAllColumns', 'getCurrentColumns', 'setCurrentColumns', 'setColumns', 
					 'updateItem', 'getItems', 'removeRow', 'renderGrid', 'getDataView', 
					 'getViewPort', 'reloadData' ];
		},
		isPublicMethod: function(method) {
			var methods = $.slickGrid.getPublicMethods();
			return ( $.inArray(method, methods) > -1 );
		}
	});	
	
	/*
	 *	rad options:
	 *
	 *		columns - array of column objects
	 *			- required:
	 *				id(unique dom id, eg. 'slick-status_id'),
	 *				name(display name, eg. 'Status Id'),
	 *				field(col id within slickgrid, eg. 'status_id'); will copy id if not specified,
	 *				db(where to look in json; can be dotted, e.g. 'Status.status_id'); will copy field if not specified
	 *			- see slick.grid.rad.js for optional column params;
	 *				note that formatter can be a function or one of the following valid strings:
	 *					tree, numeric, currency, bool, or date
	 *		pager - jQuery selector to use for bottom pager
	 *		toppager - jQuery selector to use for top pager
	 *		useFilter - true/false - enables/disables row filter (which rows to show/hide)
	 *		useTooltips - true/false - enables/disables header tooltips on hover
	 *		callbacks - object with callback name => function; see slick.grid.rad.js for valid callbacks
	 *		sortBy - string or object(column, dir)
	 *		pagingOptions - object with pageSize and pageNum parameters to set default pager
	 *		idProperty - each item must have a unique id, but this param will let you override which one
	 *		form - a form object containing data to serialize when updating the grid
	 *
	 */
	
	$.slickGrid.prototype = {
		_domObject: null,
		_grid: null,
		_dataView: null,
		_columnPicker: null,
		_pager: null,
        _toppager: null,
		_columns: null,
		_loadingIndicator: null,
		_useLoadingIndicator: false,
		_aggregationIteration: null,
		_idProperty: null,
		_data: null,
		_facts: null,
		_sumDuplicates: null,
		_filter: null,
		_currSortCol: null,
		_currSortAsc: null,
		_cookie: null,
		_form: null,
		_windowInitialOffset: null,
		// initialize slick grid
		init: function(report_grid, rad_opts, slick_opts) {
		
			var _this = this;
			
			this._domObject = report_grid;
			
			// set data view to hold data, rows, etc.
			this._dataView = new Slick.Data.DataView();
			var dataView = this._dataView;
			
			if (!slick_opts && rad_opts.slickOptions) {
				slick_opts = rad_opts.slickOptions;
			} else if (!slick_opts) {
				slick_opts = {};
			}
			
			var rad_options = $.extend({}, $.slickGrid.defaults.rad, rad_opts);
			var grid_options = $.extend({}, $.slickGrid.defaults.slick, slick_opts);
			rad_opts = null;
			slick_opts = null;

			if (rad_options.pagingOptions) {
				dataView.setPagingOptions(rad_options.pagingOptions);
			}
			
			if (rad_options.form) {
				this._form = rad_options.form;
				dataView.setForm(rad_options.form);
			}
			
			// have to define columns
			if (!rad_options.columns) {
				$.rad.notify.error('Error', 'You must include columns in the rad_options portion of the slickGrid constructor');
			}
			
			this._facts = [];
			
			// get formatter functions from string or object if given
			$.each(rad_options.columns, function() {
				
				if (rad_options.useTooltips) {
					// determine available actions
					var text = [];
					if (Slick.Controls && Slick.Controls.ColumnPicker)
						text.push('Right click for column picker');
					if (grid_options['enableColumnReorder'] != false) {
						text.push('Drag to reorder');
					}

					// get tooltip string
					var title = this.name;
					if ( title.length > 0 )
						title = '<b>' + title + '</b><br />';
					
					var left = '';
					if (this.sortable) {
						left = 'Left click for sort';
						if ( text.length > 0 )
							left += '<br />';
					}
					this.toolTip = title + left + text.join('<br />');
				}
				
				// set formatters
				if (this.formatter) {
					var formatters;
					if ( typeof this.formatter == 'object' ) {
						formatters = _this.getFormatters(this.formatter.options);
						this.formatter = formatters[this.formatter.name];
					}
					else if ( typeof this.formatter == 'string' ) {
						formatters = _this.getFormatters();
						this.formatter = formatters[this.formatter];
					}
				}
				
				// set optional properties
				if ( !this.id )
					$.rad.notify.error('Error in column ' + (i+1), 'You must provide at least an id field in the column');
				if ( !this.field )
					this.field = this.id;
				if ( !this.type )
					this.type = 'string';
				if ( !this.def_value )
					this.def_value = '';
					
				// update possible facts
				if ( this.fact )
					_this._facts.push(this);
			});
			
			// store all possible columns
			this._columns = rad_options.columns;
			
			// get the actual grid
			this._grid = new Slick.Grid($(report_grid), dataView.rows, this._columns, grid_options);
			var grid = this._grid;
			
			// create a column picker, if the js file is included
			if (Slick.Controls && Slick.Controls.ColumnPicker)
				this._columnPicker = new Slick.Controls.ColumnPicker(this._columns, grid);
			
			// set a pager, if given in the options
			if (rad_options.pager && Slick.Controls && Slick.Controls.Pager) {
				new Slick.Controls.Pager(this._dataView, this._grid, rad_options.pager);
				this._pager = rad_options.pager;
			}
            
            // set a top pager, if given in the options
			if (rad_options.toppager && Slick.Controls && Slick.Controls.Pager) {
				new Slick.Controls.Pager(this._dataView, this._grid, rad_options.toppager);
				this._toppager = rad_options.toppager;
			}

			// define cookie setter function
			var setCookie = function() {
				if (_this._cookie) {
					var sortInfo = _this._grid.getSortInfo();
					var visibleColumns = _this._grid.getColumns();
					var cols = $.map(visibleColumns, function(el, idx) {
						var val = el.id + ',' + el.width;
						if ( el.id == sortInfo.sortColumnId ) {
							val += ',' + (sortInfo.sortAsc ? 'asc' : 'desc');
						}
						return val;
					} ).join('|');
					$.cookie(_this._cookie, cols, { path: '/' });
				}
			}
			
			if (rad_options.idProperty)
				this._idProperty = rad_options.idProperty;
            
			// put tooltips on the headers if requested
			if (rad_options.useTooltips) {
				$('.slick-header-column').simpletooltip();				
			}
			
			if (rad_options.useLoadingIndicator != undefined) {
				this._useLoadingIndicator = rad_options.useLoadingIndicator;
			}

			// define filter to use if filter requested (for tree)
			if(rad_options.useFilter) {
				// use filter supplied
				if (rad_options.callbacks && rad_options.callbacks.filter) {
					this._filter = rad_options.callbacks.filter;
					delete rad_options.callbacks.filter;
				}
				else { // use default filter
					this._filter = function (item) {
						if (item.parent !== null) {
							var parent = _this._dataView.getItemByIdx(item.parent);
							while (parent !== null) {
								if (parent._collapsed)
									return false;
								if (parent.parent !== null)
									parent = _this._dataView.getItemByIdx(parent.parent);
								else
									parent = null;
							}
						}
						return true;
					}
				}
			}
			
			if (rad_options.sumDuplicates) {
				this._sumDuplicates = true;
			}
			
			// add in callbacks
			if (rad_options.callbacks) {
				$.each(rad_options.callbacks, function(i, callback) {
					grid[i] = callback;
				});
			}
			
			// display loading indicator
			var $g = $(report_grid);
			
			this._loadingIndicator = $("<span class='loading-indicator'><label>Loading...</label></span>").hide().appendTo(document.body);
			this.showLoadingIndicator($(report_grid));

			// defines initial column and direction to sort by
			this._currSortAsc = true;
			if (rad_options.sortBy) {
				var _this = this;
				var colID;
				
				if (typeof rad_options.sortBy == 'string') {
					var pieces = rad_options.sortBy.split(' ');
					colID = pieces[0];
					if (pieces[1])
						this._currSortAsc = !(pieces[1] == 'desc');
				}
				else if (typeof rad_options.sortBy == 'object') {
					colID = rad_options.sortBy['column'];
					this._currSortAsc = !(rad_options.sortBy.dir == 'desc');
				}
			
				$.each(this._columns, function() {
					if(this.id == colID)
						_this._currSortCol = this;
				});
			}
			
			// set sorting function
			grid.onSort = function(sortCol, sortAsc) {
				setCookie(sortCol, sortAsc);
				if (sortCol.sort_field != undefined) {
					dataView.fastSort(sortCol.sort_field, sortAsc);
				} else {
					dataView.fastSort(sortCol.field, sortAsc);
				}
			}
			/*
			if (grid.onSort) {
				grid._onSort = grid.onSort;
			}
			grid.onSort = function(sortCol, sortAsc) {
				_this._currSortCol = sortCol;
				_this._currSortAsc = sortAsc;
				setCookie(sortCol, sortAsc);
				
				// use user-supplied sort function if given
				if (this._onSort && typeof this._onSort == 'function') {
					this._onSort(sortCol, sortAsc);					
				}
				else {
					if (this._onSort) {
						// see if onSort is requesting a tree sort
						if ( (typeof this._onSort == 'string' && this._onSort == 'tree')
							|| (typeof this._onSort == 'object' && this._onSort.type == 'tree') ) {
							// onBeforeSort: strip off sub-data so that sort works only on top-level items
							this.onBeforeSort = function(dataView) {
								var orig_items = dataView.getItems();
								var gt;
								var items = [];
								var sub_items = {};
								$.each(orig_items, function() {
									if (this.parent == null) {
										gt = this;
									} else if (this.parent == 0) {
										items.push(this);
										this._orig_collapsed = this._collapsed;
										this._collapsed = true;
									}
									else if (this.parent > 0) {
										this._orig_collapsed = this._collapsed;
										this._collapsed = true;
										this.parent_id = orig_items[this.parent].id;
										if (!sub_items[this.parent_id])
											sub_items[this.parent_id] = [];
										sub_items[this.parent_id].push(this);
									}
								});
								dataView.setItems(items);
								
								// whatever you return gets passed back into the onAfterSort function
								return {gt:gt, sub_items:sub_items};
							};
							
							// onAfterSort: push the sub-data back into the items
							this.onAfterSort = function(dataView, saved_data) {
								var orig_items = dataView.getItems();
								var items = [saved_data.gt];
								
								var pushItems = function(curr, idx, curr_idx) {
									curr.parent = curr_idx;
									curr._collapsed = curr._orig_collapsed;
									items.push(curr);
									idx++;
									if (saved_data.sub_items[curr.id]) {
										var saved_idx = idx;
										$.each(saved_data.sub_items[curr.id], function() {
											idx = pushItems(this, idx, saved_idx);
										});
									}
									return idx;
								};
								
								var idx = 0;
								$.each(orig_items, function() {
									idx = pushItems(this, idx, 0);
								});
								dataView.setItems(items);
							}
						}
					}
				
					var data = null;
					if (this.onBeforeSort) {
						data = this.onBeforeSort(dataView);
					}
					
					if(sortCol.type == "integer" || sortCol.type == "float") //Sorting Numerically
						dataView.sort( function(a,b) { return a[sortCol.field] - b[sortCol.field] }, sortAsc );
					else if (sortCol.type == "ip") {
						dataView.sort( function(a,b) {
							var aa = a[sortCol.field].split('.');
							var bb = b[sortCol.field].split('.');
							
							if (aa && bb) {
								var d = aa[0] - bb[0];
								if (d < 0 || d > 0)
									return d;
								else {
									d = aa[1] - bb[1];
									if (d < 0 || d > 0)
										return d;
									else {
										d = aa[2] - bb[2];
										if (d < 0 || d > 0)
											return d;
										else
											return aa[3] < bb[3];
									}
								}
							}
						}, sortAsc );
					}
					else //Sorting Lexigraphically
						dataView.fastSort(sortCol.field, sortAsc);
					
					if (this.onAfterSort) {
						this.onAfterSort(dataView, data);
					}
				}
			}
			

			// define onclick behavior for tree toggling
			if (!grid.onClick) {
				grid.onClick = function(e, row, cell) {
					if ($(e.target).hasClass("toggle")) {
						var item = dataView.rows[row];
						if (item) {
							if (!item._collapsed)
								item._collapsed = true;
							else
								item._collapsed = false;
							dataView.updateItem(item.id, item);
						}
						return true;
					}
					return false;
				};
			}
			
			// update item when a cell is changed
			if (!grid.onCellChange) {
				grid.onCellChange = function(row,col,item) {
					dataView.updateItem(item.id,item);
				};
			}*/
			
			if (rad_options.width && rad_options.width !== 'auto') {
				if (rad_options.width !== 'fit') {
					$(report_grid).width(rad_options.width);
				}
				else { // fit with window
					if (!this._windowInitialOffset) {
						var initOffset = $(window).width() - ($(report_grid).offset().left + $(report_grid).width());
						this._windowInitialOffset = (initOffset > 0 ? initOffset : 25);
						
					}
					
					$(window).resize(function() {
						//$(report_grid).parent().parent().css('max-width', $(this).width() - $(report_grid).offset().left - _this._windowInitialOffset);
						//$(report_grid).parent().css('max-width', $(this).width() - $(report_grid).offset().left - _this._windowInitialOffset);
						$(report_grid).children().css('width', '99.9%');
						$(report_grid).css('overflow-x', 'hidden');
					}).resize();

					$(report_grid).trigger('resize');
				}
			}
			
			// rad_options.width = 'auto' should fall through to here
			if (!rad_options.width || rad_options.width === 'auto') {
				if (!grid.onResizeCanvas) {
					grid.onResizeCanvas = function(args) {
						args.container.width(args.width);
					};
				}
			}
			
			if (rad_options.cookie) {
				this._cookie = rad_options.cookie;
			}
			
			// set cookie when column moved
			if (!grid.onColumnsReordered) {
				grid.onColumnsReordered = setCookie;
			}
			
			// set cookie when column resized
			if (!grid.onColumnsResized) {
				grid.onColumnsResized = setCookie;
			}
			
			// set cookie when column picked
			if (!grid.onColumnsSelected) {
				grid.onColumnsSelected = setCookie;
			}
			
			// display rad notify error on validation error
			if (!grid.onValidationError) {
				grid.onValidationError = function(node, validationResults, currentRow, currentCell, column) {
					$.rad.notify.error('Error', validationResults.msg);
				};
			}

			// wire up model events to drive the grid
			dataView.onRowCountChanged.subscribe(function(args) {
				grid.updateRowCount();
				grid.render();
			});

			// remove the rows affected and re-render
			dataView.onRowsChanged.subscribe(function(rows) {
				grid.removeRows(rows);
				grid.render();
			});
			// remove the rows affected and re-render
			dataView.onDataLoading.subscribe(function(rows) {
				$(report_grid).slickShowLoadingIndicator();
			});
			
			// remove the rows affected and re-render
			dataView.onDataLoaded.subscribe(function(rows) {
				$(report_grid).slickHideLoadingIndicator();
			});
						
			// reorder/hide columns based on cookies
			
			var columns = [];
			var cookie_val = null;
			
			if (this._cookie !== null) {	
				cookie_val = $.cookie(this._cookie);
			}
			
			if ( cookie_val !== null ) {
				$.each(cookie_val.split('|'), function(i, val) {
					var parts = val.split(',');
					var colId = parts[0];
					var width = parts[1];
					var sortAsc = parts[2];
					$.each(_this._columns, function() {
						if (this.id == colId) {
							if (sortAsc !== undefined && sortAsc !== null) {
								_this._currSortCol = this;
								_this._currSortAsc = !(sortAsc == 'desc');
								if (this.sort_field) {
									dataView.sort(this.sort_field, _this._currSortAsc);
								} else {
									dataView.sort(this.id, _this._currSortAsc);
								}
							}
							this.width = parseInt(width);
							columns.push(this);
						}
					});
				});
				this._grid.setColumns(columns);
			}
			else {
				$.each(_this._columns, function() {
					if( !this.hidden )
						columns.push(this);
				});
				this._grid.setColumns(columns);
			}
			
			return this;
		},
		// define default formatters to use
		getFormatters: function(format_options) {

			var _this = this;
		
			var currency = function(row, cell, value, columnDef, dataContext) {
				return $.rad.formatters.currency(value, format_options);
			};
			
			var numeric = function(row, cell, value, columnDef, dataContext) {
				return $.rad.formatters.numeric(value, format_options);
			};
			
			var bool = function(row, cell, value, columnDef, dataContext) {
				return value >= 1 ? "<img src='/images/slick-grid/tick.png'>" : "";
			};
			
			// date formatters work for basic needs
			var date = function(row, cell, value, columnDef, dataContext) {
				return $.rad.formatters.date(value, format_options);
			};
			
			return { currency: currency, numeric: numeric, bool: bool, date: date };
		},
		// Submit the associated form for this grid and return the resultset
		submitForm: function()  {
			var _this = this;
			_this._form.trigger('submit');
		},
		reloadData: function(data, dimensions) {
			this.showLoadingIndicator(this._domObject);
			this.setData(data, dimensions);
			this.updateGrid();
			this.hideLoadingIndicator();
		},
		// take flat data from database and @returns all rows in table with correct attributes
		setData: function(data, dimensions) {
			var _this = this;
			
			if ( dimensions != null ) {
				// default collapse params
				var collapse = {
					root: false,
					children: true
				};
				// get collapsing information from query
				if (data.input && data.input.Expansion) {
					switch(data.input.Expansion) {
						case 'expand': // expand all
							collapse.root = false;
							collapse.children = false;
							break;
						case 'collapse': // collapse all
							collapse.root = true;
							collapse.children = true;
							break;
						case 'root-only':
							collapse.root = false;
							collapse.children = true;
							break;
					}
				}
			
				_this._aggregationIteration = -1;
				
				var default_facts = {};
				var default_types = {};
				$.each(_this._facts, function() {
					default_facts[this.field] = this['def_value'];
					default_types[this.field] = this['type'];
				});
				
				// create root node to hold all others
				var root = {
					leaf: false,
					name: 'grand total', // TODO: allow this to be set?
					facts: default_facts,
					types: default_types,
					_collapsed: collapse.root,
					id: 'slick-row-' + Math.floor(Math.random() * new Date())
				};
				
				// loop over data and dimensions to turn flat structure into nested
				if ( data.entries ) {
					$.each(data.entries, function(i, row) {
						var current = root;

						// loop through given dimensions
						$.each(dimensions, function(j, dimension) {
							var value = dimension.get(row);

							if(!current['children'])
								current['children'] = {};
							if(!current['children'][value]) {
								current['children'][value] = {
									id: 'slick-row-' + Math.floor(Math.random() * new Date()),
									type: dimension.id,
									name: dimension.text(row),
									leaf: false,
									facts: {},
									types: {},
									_collapsed: collapse.children
								};
								
								// set each fact recursively
								$.each(_this._facts, function() {
									var props = this;
									
									// account for possible json nesting
									//var nesting = props.db.split(/[.]/);
									var nesting = props.field.split(/[.]/); // changed to field by hobby
									var curr = row;
									$.each(nesting, function() {
										if ( curr )
											curr = curr[this];
									});
									
									// get the value in the correct format
									var fact_value = curr || props['def_value'];
									switch(props['type']) {
										case 'integer':
											fact_value = parseInt(fact_value);
											break;
										case 'float':
											fact_value = parseFloat(fact_value);
											break;
									}
									// set it
									current['children'][value]['facts'][props.field] = fact_value;
									current['children'][value]['types'][props.field] = props.type;
								});
							}
							else if (_this._sumDuplicates) {
								// set each fact recursively
								$.each(_this._facts, function() {
									var props = this;
									
									// account for possible json nesting
									//var nesting = props.db.split(/[.]/);
									var nesting = props.field.split(/[.]/); // changed to field by hobby
									var curr = row;
									$.each(nesting, function() {
										if ( curr )
											curr = curr[this];
									});
									
									// get the value in the correct format
									var fact_value = curr || props['def_value'];
									switch(props['type']) {
										case 'integer':
											fact_value = parseInt(fact_value);
											break;
										case 'float':
											fact_value = parseFloat(fact_value);
											break;
									}
									current['children'][value]['facts'][props.field] += fact_value;
								});
							}
							
							// recurse
							current = current.children[value];
						});

						if ( dimensions.length == 0 ) {
							// set each fact recursively
							$.each(_this._facts, function() {
								var props = this;
								
								// account for possible json nesting
								//var nesting = props.db.split(/[.]/);
								var nesting = props.field.split(/[.]/); // changed to field by hobby
								var curr = row;
								$.each(nesting, function() {
									if ( curr )
										curr = curr[this];
								});
								
								// get the value in the correct format
								var fact_value = curr || props['def_value'];
								switch(props['type']) {
									case 'integer':
										fact_value = parseInt(fact_value);
										break;
									case 'float':
										fact_value = parseFloat(fact_value);
										break;
								}
								current['facts'][props.field] = fact_value;
								current['types'][props.field] = props.type;
							});
						}
						
						current.leaf = true;
						delete current._collapsed; // don't need/want this on a leaf
					});
				}
				else {
					root.leaf = true;
				}
				
				// aggregate the data into the variable
				_this._data = _this.aggregate([], root, 0, [], null);
			}
			else { // flat
			
				// loop over data and restructure
				objects = [];
				if ( data.entries ) {
					$.each(data.entries, function(i, row) {
						// create object and set unique id
						var obj = {
							id: 'slick-row-' + Math.floor(Math.random() * new Date())
						};
						
						// give css class and extra data to store, if given
						if (row.css_class) {
							obj.css_class = row.css_class;
						}
						if (row.extra_data) {
							obj.extra_data = row.extra_data;
						}
						
						// loop over facts and push them into the object
						//$.each(_this._facts, function() {
						$.each(_this._columns, function() {
							//var nesting = this.db.split(/[.]/);
							var nesting = this.field.split(/[.]/); // changed to field by hobby
							var curr = row;
							$.each(nesting, function() {
								if ( curr )
									curr = curr[this];
							});
							obj[this.field] = curr || this.def_value;
						});
						objects.push(obj);
					});
				}
				else {
					this.hideLoadingIndicator();
				}
								
				// set the data
				_this._data = objects;
				objects = null;
			}
			
			return $(this._domObject);
		},
		updateGrid: function(data) {
			var _this = this;
			
			// bypass setData function and set data directly
			if (data) {
				this._data = data;
			}
			
			// start the update, set the data, possibly set a filter, and finish the update
			this._dataView.beginUpdate();
			if ( this._idProperty !== null )
				this._dataView.setItems(this._data, this._idProperty);
			else
				this._dataView.setItems(this._data);
			if ( this._filter !== null )
				this._dataView.setFilter(this._filter);
			this._dataView.endUpdate();

			this.hideLoadingIndicator();
			
			// force window update
			$(window).resize();

			// sorting the new table by the same column and direction used before the update
			if(this._currSortCol) {
				this._grid.setSortColumn(this._currSortCol.id, this._currSortAsc);
				this._grid.onSort(this._currSortCol, this._currSortAsc);
			}
			
			// set hover classes
			$(".slick-row").live( 'mouseover mouseout', function(event) {
				if ( event.type == 'mouseover' )
					$(this).addClass("ui-state-hover");
				else
					$(this).removeClass("ui-state-hover");
			});
			
			// round the pager bottom if it exists
			if(this._pager != null) {
				$(this._pager).find('.slick-pager').addClass("ui-corner-bottom").css('border-top', '1px solid #AAAAAA');
			}
			
			return $(this._domObject);
		},
		// display loading indicator over grid
		showLoadingIndicator: function($g) {
			if ($g === undefined) {
				$g = $(this._domObject);
			}
			if (this._useLoadingIndicator) {
				this._loadingIndicator
					.css("position", "absolute")
					.css("top", $g.offset().top + $g.height()/2 - this._loadingIndicator.height()/2)
					.css("left", $g.offset().left + $g.width()/2 - this._loadingIndicator.width()/2)
					.show();
					
			} 
			return $g;
		},
		// hide loading indicator
		hideLoadingIndicator: function() {
			if(this._loadingIndicator) {
				this._loadingIndicator.fadeOut();
			}
			return $(this._domObject);
		},
		updateItem: function(item) {
			return this._dataView.updateItem(item.id, item);
		},
		// return the items in the grid
		getItems: function() {
			return this._dataView.getItems();
		},
		// return all the columns in the grid, even if they aren't all shown
		getAllColumns: function() {
			return this._columns;
		},
		// return columns currently in the grid
		getCurrentColumns: function() {
			return this._grid.getColumns();
		},
		// set columns to display in the grid
		setColumns: function(columns) {
			this._grid.setColumns(columns);
			return $(this._domObject);
		},
		// remove row from grid
		removeRow: function(row) {
			this._grid.removeRow(row);
			return $(this._domObject);
		},
		// re-render grid
		renderGrid: function() {
			this._grid.render();
			return $(this._domObject);
		},
		getDataView: function() {
			return this._dataView;
		},
		// get grid's viewport object
		getViewPort: function() {
			return this._grid.getViewport();
		}
	};
	
	$.slickGrid.editor = {
		args: null,
		defaultValue: null,
		wrapper: null,
		_display: function() {				
			this.position(this.args.position);
			return this.focus();
		},
		init: function(args) {
			this.args = args;
			this.loadValue(args.item);
			this.wrapper = $('<div />');
			$input = $('<input type="text" name="' + this.args.column['field'] + '" />').appendTo(this.wrapper);
			$input.val(this.defaultValue);
			this.wrapper.appendTo(this.args.container);
			
			return this.focus();
		},
		handleKeyDown: function(e) {
			if (e.which == $.ui.keyCode.ENTER && e.ctrlKey) {
				this.save();
			}
			else if (e.which == $.ui.keyCode.ESCAPE) {
				e.preventDefault();
				this.cancel();
			}
			else if (e.which == $.ui.keyCode.TAB && e.shiftKey) {
				e.preventDefault();
				this.save();
				this.args.grid.navigatePrev();
			}
			else if (e.which == $.ui.keyCode.TAB) {
				e.preventDefault();
				this.save();
				this.args.grid.navigateNext();
			}
		},
		handleClick: function(e, row, cell) {
			this.save();
		},
		destroy: function() {
			this.wrapper.remove();
			return this;
		},
		save: function() {
			this.args.commitChanges();
			return this;
		},
		cancel: function() {
			this.args.cancelChanges();
			return this;
		},
		focus: function() {
			$(':input:visible:first', this.wrapper).focus();
			return this;
		},
		_position: function(position) {
			var height = position.bottom - position.top;
			var width = position.right - position.left;
			var wrapper_width = this.wrapper.innerWidth();
			var $triangle = $('.slickgrid-edit-form-handle', this.wrapper);
			
			if($triangle.length == 0) {
				$triangle = $('<div class="slickgrid-edit-form-handle" />')
					.css({
						'position': 'absolute',
						'top': '3px',
						'width': '0px',
						'height': '0px',
						'line-height': '0px',
						'border-style': 'solid',
						'border-width': '5px'
					})
					.hover(function() {
						$(this).css('cursor', 'move');
					}, function() {
						$(this).css('cursor', '');
					})
					.appendTo(this.wrapper);
			}
		
			this.wrapper.draggable({ handle: '.slickgrid-edit-form-handle' });
			this.wrapper.css("top", position.top + (height*0.66) );
			
			if ( position.left + (width*0.75) + this.wrapper.width() > $(window).width() ) {
				this.wrapper.css("left", position.left + (width*0.25) - wrapper_width);
				$triangle.css({right:'3px', 'border-color': '#bbb #bbb white white'});
			}
			else {
				this.wrapper.css("left", position.left + (width*0.75) );
				$triangle.css({left:'3px', 'border-color': '#bbb white white #bbb'});
			}
			
			this.wrapper.show();
			return this;
		},
		position: function(position) {
			this.wrapper.show();
			return this;
		},
		loadValue: function(item) {
			if(item[this.args.column['field']] != undefined) {
				this.defaultValue = item[this.args.column['field']];
			}
			return this;
		},
		isValueChanged: function() {
			return true;
		},
		validate: function() {
			return {valid: true, msg: null};
		},
		serializeValue: function() {
			return this.args.item;
		},
		applyValue: function(item,state) {
			item = state;
			var val = $('input[name="' + this.args.column['field'] + '"]').val();
			item[this.args.column['field']] = val;
			return this;
		}
	};
	
	// default slick grid options
	$.slickGrid.defaults = {
		slick: {
			editable: false,
			autoEdit: false,
			enableAddRow: false,
			leaveSpaceForNewRows: false,
			autoHeight: true,
			enableRowReordering: false,
			enableAutoTooltips: false
		},
		rad: {
			useTooltips: false,
			useFilter: false,
			sumDuplicates: false,
			width: 'fit',
			pagingOptions: {
				pageSize: 25,
				pageNum: 1
			},
			useLoadingIndicator: false
		}
	};
	
})(jQuery);
