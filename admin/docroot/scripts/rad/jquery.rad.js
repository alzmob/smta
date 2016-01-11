/**
 * jquery.rad.js
 * @author Mark Hobson
 */

(function($) {
//	var notifier = null;
	
	var httpData = $.httpData || function( xhr, type, s ) { // lifted from jq1.4.4       
		var ct = xhr.getResponseHeader("content-type"),
		xml = type == "xml" || !type && ct && ct.indexOf("xml") >= 0,
		script = type == "script" || !type && ct && ct.indexOf("script") >= 0,
		json = type == "json" || !type && ct && ct.indexOf("json") >= 0,
		data = xml ? xhr.responseXML : xhr.responseText;
	                            
		if ( xml && data.documentElement.tagName == "parsererror" )
			throw "parsererror";
	                            
		// Allow a pre-filtering function to sanitize the response
		// s != null is checked to keep backwards compatibility
		if( s && s.dataFilter )
			data = s.dataFilter( data, type );
	                            
		// If the type is "script", eval it in global context
		if ( script ) 
			jQuery.globalEval( data );

		// Get the JavaScript object, if JSON is used.
		if ( json )
			data = eval("(" + data + ")");

		return data;
	}
	
	/*****************************************************************
	 * rad plugins
	 *****************************************************************
	 */
	$.fn.extend({
		serializeObject: function()
		{
		    var o = {};
		    var a = this.serializeArray();
		    $.each(a, function() {
		        if (o[this.name] !== undefined) {
		            if (!o[this.name].push) {
		                o[this.name] = [o[this.name]];
		            }
		            o[this.name].push(this.value || '');
		        } else {
		            o[this.name] = this.value || '';
		        }
		    });
		    return o;
		},		
		form: function(callback, options) {
			if(!$.isFunction(callback)) {
				options = callback;
			} else {
				options = options || {};
				options['onsuccess'] = callback;
			}
			return this.each(function() {
				var form = $.rad.form.getInstance($(this), options || {});
			});
		},
		error: function(error) {
			return this.each(function() {
				var $input = $(this);				
				var $error = $('<div style="display:none;" />').html(error || ';nbsp').attr({
					id: $input.attr('name') + '_errorText',
					'class': 'alert alert-warning'
				});
				
				$error.insertAfter($input).fadeIn();
			});
		}
	});
	
	/*****************************************************************
	 * rad library
	 *****************************************************************
	 */
	$.extend({
		rad: {
			ajax: function(options) {
				options = $.extend(true, {}, this.ajax.options.defaults, options || {});
				options['data'] = this.ajax.data(options['data']);
				
				if(options['global'] && $.isFunction(options['success'])) {
					// move success callback so that global success callback is called first
					options['onsuccess'] = options['success'];
					options['success'] = false;
				}
				
				if(options['type'] == 'DELETE') {
					options['url'] += ('?' + options['data']);
				}

				var xhr = null;
				try {
					xhr = $.ajax(options);
				} catch(e) {	
					if(xhr) {
						if(xhr.readyState != 4) {
							$.event.trigger('ajaxComplete', [xhr, options]);
						}
					} else {
						//$.event.trigger('ajaxStop');
					}
					
					if(typeof(e) == 'object' && window.console) {
						window.console.log(e);
					}
					
					var title = typeof(e) == 'object' ? e.name : 'Error';
					var message = typeof(e) == 'object' ? e.message : e;
					$.rad.notify.error(title, message);
				}
				return xhr;
			},
			get: function(url, data, callback, dataType, options) {
				if (options) {
					this.ajax.setup(options);
					return this.ajax.get(url, data, callback, dataType);
				}
				return this.ajax.get(url, data, callback, dataType);
			},
			post: function(url, data, callback, dataType) {
				return this.ajax.post(url, data, callback, dataType);
			},
			put: function(url, data, callback, dataType) {
				return this.ajax.put(url, data, callback, dataType);
			},
			del: function(url, data, callback, dataType) {
				return this.ajax.del(url, data, callback, dataType);
			},
			notify: function(title, message, type, options) {				
				if(title == null && message == null) {
					return;
				}
				
				var notifier = this.notify.getNotifier();
				var notification = {
					title: title || '',
					message: message || '',
					type: type,
					options: options || {}
				};
				
				try {
					notifier.notify(notification);
				} catch(e) {
					// if there is a problem with the notifier, fall back on alert
					notifier = $.rad.notify.plugins['alert'];
					notifier.notify(notification);
				}
			},
			form: function(form, options) {
				this._form = $(form);
				this.setup(options);
			},
			serializeForm: function($form){
				var obj = {};
				var data = $form.serializeArray();
				for(var i in data){
					var key = data[i]['name'];
					var val = data[i]['value'];
					var is_array = key.substring(key.length-2, key.length) == '[]';
					if ( obj[key] == undefined ) {
						if ( is_array ) {
							obj[key] = [val];
						} else {
							obj[key] = val;
						}
					} else {
						if ( is_array ) {
							obj[key].push(val);
						} else {
							obj[key] = val;
						}
					}
				}
				return obj;
			}
		}
	});
		
	/*****************************************************************
	 * rad notifier
	 *****************************************************************
	 */
	$.extend($.rad.notify, {
		_notifier: null,
		setup: function(options) {
			options = $.extend({}, this.defaults, options || {});
			
			switch(options['type'].toLowerCase()) {
				case 'pnotify' :
					this._notifier = this.plugins['pnotify'];
					break;
				case 'alert' :
				default:
					this._notifier = this.plugins['alert'];
					break;
			}
			
			if(options['settings']) {
				this._notifier.setup(options['settings']);
			}
		},
		plugins: {
			_abstract: {
				_settings: {},
				ajaxIndicator: {
					start: function() {},
					stop: function() {}
				},
				setup: function(settings) { 
					this._settings = settings;
					return this;
				},
				notify: function(notification) {
					return null;
				},
				notice: function(notification) {
					notification.type = 'notice';
					return this.notify(notification);
				},
				error: function(notification) {
					notification.type = 'error';
					return this.notify(notification);
				}
			}
		},
		getNotifier: function(options) {
			if(this._notifier == null) {
				this.setup(options);
			}
			
			return this._notifier;
		},
		notice: function(title, message, options) {
			return this.getNotifier().notice({
				title: title || '',
				message: message || '',
				options: options || {}
			});
		},
		error: function(title, message, options) {			
			return this.getNotifier().error({
				title: title || '',
				message: message || '',
				options: options || {}
			});
		}
	});
		
	$.rad.notify.defaults = {
		type: 'pnotify',
		settings: {  }
	};
		
	/*****************************************************************
	 * notifier plugins
	 *****************************************************************
	 */
	
	/*
	 * javascript alert plugin
	 *
	 */	
	$.rad.notify.plugins.alert = $.extend({}, $.rad.notify.plugins._abstract, {
		notify: function(notification) {
			alert(
				(notification.title != null ? notification.title + ':\n' : '') + 
				notification.message
			); 
			return null;
		}
	});
	
	/*
	 * pines notify plugin
	 *
	 */
	$.rad.notify.plugins.pnotify = $.extend({}, $.rad.notify.plugins._abstract, {
		notify: function(notification) {
			notification.options = notification.options || {};
			
			var options = $.extend(notification.options, {
				title: notification.title, 
				text: notification.message,
				type: notification.type,
				addclass: 'stack-bottomright',
				opacity: 0.8,
				styling: 'bootstrap3',
				stack: {
					dir1: 'up', 
					dir2: 'left', 
					firstpos1: 15, 
					firstpos2: 15
				},
				animate_speed: 'slow',
				history: true
			});
			
			if(notification.options['sticky']) {
				options['hide'] = false;
			}
			if(notification.options['width']) {
				options['width'] = notification.options['width'];
			}
			
			return new PNotify(options);
		},
		notice: function(notification) {
			notification.type = 'notice';
			notification.options = $.extend({
				notice_icon: 'ui-icon ui-icon-info'
			}, notification.options || {});
			
			return this.notify(notification);
		},
		error: function(notification) {
			notification.type = 'error';
			notification.options = $.extend({
				error_icon: 'ui-icon ui-icon-alert'
			}, notification.options || {});
			
			return this.notify(notification);
		},
		setup: function(settings) { 
			//$.pnotify.defaults = $.extend(true, $.pnotify.defaults, settings);
			return this;
		},
		ajaxIndicator: {
			_indicator: null,
			start: function() {				
				this._indicator = $.rad.notify.notice(
					'Loading...', '',
					{
						hide: false,
						history: false,
						icon: 'fa fa-spinner fa-spin',
						width: '200px'
					}
				);
			},
			stop: function() {
				if(this._indicator != null) {
					this._indicator.remove();
				}
			}
		}
	});
	
	
	/*****************************************************************
	 * rad ajax handler
	 *****************************************************************
	 */
	$.extend($.rad.ajax, {
		_queue: [],
		setup: function(options) {
			$.extend(true, this.options, options || {});
		},
		init: function() {
			var _this = this;
			
			$.ajaxSetup({
				//global: false
			});
			
			$(document)
				.ajaxError(function(event, XMLHttpRequest, ajaxOptions, thrownError) {
					_this.error(event, XMLHttpRequest, ajaxOptions, thrownError);
				})
				.ajaxStart(function() {
					if($.rad.ajax.options['show_indicator'] == true) {
						_this.getIndicator().start();
					}
				})
				.ajaxStop(function() {
					_this.getIndicator().stop();
				})
				.ajaxSend(function(event, XMLHttpRequest, ajaxOptions) {
					XMLHttpRequest.position = _this._queue.length;
					_this._queue[XMLHttpRequest.position] = XMLHttpRequest;
				})
				.ajaxSuccess(function(event, XMLHttpRequest, ajaxOptions) {
					_this.success(event, XMLHttpRequest, ajaxOptions);
				})
				.ajaxComplete(function(event, XMLHttpRequest, ajaxOptions) {
					if(XMLHttpRequest 
						&& XMLHttpRequest.position != undefined 
						&& _this._queue[XMLHttpRequest.position]
					) {
						delete _this._queue[XMLHttpRequest.position];
					}
				});
				
			$(document).unload(function() {
				$(this).ajaxSuccess($.noop).ajaxError($.noop);
			});
		},
		_ajax: function(type, url, data, callback, dataType) {
			// shift arguments if data argument was omitted
			if($.isFunction(data)) {
				dataType = dataType || callback;
				callback = data;
				data = {};
				
				if($.isPlainObject(url)) {
					data = url;
					url = undefined;
				}
			}
			if (dataType == undefined) {
				dataType = 'json';
			}
			return $.rad.ajax({
				type: type,
				url: url,
				data: data,
				success: callback,
				dataType: dataType
			});
		},
		get: function(url, data, callback, dataType) {
			return this._ajax('GET', url, data, callback, dataType);
		},
		post: function(url, data, callback, dataType) {
			return this._ajax('POST', url, data, callback, dataType);
		},
		put: function(url, data, callback, dataType) {
			return this._ajax('PUT', url, data, callback, dataType);
		},
		del: function(url, data, callback, dataType) {
			return this._ajax('DELETE', url, data, callback, false);
		},
		data: function(data, url) {
			var defaults = this.options.defaults['data'];
			
			if(data == undefined) {
				data = defaults;
			}
			if(!$.isPlainObject(data)) {
				data = $.param(defaults) + '&' + data;
			} else {
				data = $.param(data);
			}	

			return data;
		},
		deserialize: function(qs) {
			var obj = {};
			
			$.each(qs.split('&'), function(i, param) {
				if(param) {
					var parts = param.split('=');
					var key = parts[0];
					var value = parts[1] || '';
					obj[key] = unescape(value);
				}
			});
			
			return obj;
		},
		abortAll: function() {			
			$.each(this._queue, function(i, xhr) {
				if(typeof(xhr) == 'object') {
					xhr.abort();
				}
			});
			
			$.event.trigger('ajaxStop');
		},
		error: function(event, XMLHttpRequest, ajaxOptions, thrownError) {
			var sticky = false;
			var title = 'Your request has failed';
			var message = thrownError;
			var responseText = XMLHttpRequest.responseText;
			var callback = false;
			switch(XMLHttpRequest.status) {
				case 200:
					message = (thrownError ? thrownError : '') + '<hr />' +
						(responseText ? responseText.substring(0, 200) : '');
					if (window.console) {
						console.log(responseText);
					}
					break;
//				case 401:
//					this.abortAll();
//					title = 'Login Required';
//					sticky = true;
//					message = '<div id="login_form_container"></div>';
//					
//					var url = this.options['login_form'];
//					if(url != false) {
//						callback = function() {
//							var $form = $('#login_form_container');
//							$form.load(url, function(responseText, textStatus, XMLHttpRequest) {
//								$('input[name="referrer"]', $form).val(location.pathname);
//							});
//						};
//					}
//					
//					break;
				case 530:
				case 403:
					title = 'Access Denied';
					break;
				default:
					message = XMLHttpRequest.status + ' ' + 
						XMLHttpRequest.statusText + '<br />' + 
						(thrownError ? thrownError : '');
			}
			
			$.rad.notify.error(title, message, {sticky: sticky});
			
			if(callback !== false) {
				callback();
			}
		},
		success: function(event, xhr, ajaxOptions) {			
			// get data from response
			var data = httpData(xhr, ajaxOptions['dataType'], ajaxOptions);
			ajaxOptions['type'] = ajaxOptions['type'] ? ajaxOptions['type'].toUpperCase() : 'GET';
			
			try {				
				// check response validity
				if($.rad.ajax.options['check_response'] && 
					(ajaxOptions['dataType'] == 'json' || ajaxOptions['dataType'] == 'xml')) {
					if(ajaxOptions['type'] == 'DELETE') {
						if(data) {
							//throw new Error('unexpected data after deleting');
						}
						data = {};
					} else if(!data) {
						return;
						throw new Error('no data in response');
					}
					
					if((data['errors'] && data['errors'].length > 0)) {
						var errors = '';
						$.each(data['errors'], function(index, value) {
							errors += (value + "\n");
						});
						throw new Error('response contains ' + data['errors'].length + ' error(s): ' + errors);
						
					} else if(data['result'] && data['result'].toUpperCase() != 'SUCCESS') {
						throw new Error(data['result']);
					}
					
					switch(ajaxOptions['type']) {
						case 'DELETE':
							break;
						case 'POST':
						case 'PUT':
							if(!$.isPlainObject(data['record']) && !$.isArray(data['record'])) {
								throw new Error('expected object "record" not found in response');
							}
							break;
						case 'GET':
						default:
							if(!data['entries'] && !data['record'] && !data['html']) {
								throw new Error('invalid response');
							}
					}
					
					/* data['entries'] = data['entries'] || [];
					data['record'] = data['record'] || [];
					data['html'] = data['html'] || ''; */
				}
				
				// execute onsuccess callback
				if($.isFunction(ajaxOptions['onsuccess'])) {
					ajaxOptions['onsuccess'](data, xhr.statusText, xhr);
				}
			} catch(e) {
				if(typeof(e) == 'object' && window.console) {
					window.console.log(e);
				}
				
				xhr.errorThrown = e;
				data = data || {};
				data['errors'] = data['errors'] || [];
				
				if (data['errors']) {
					if(data['errors'].length == 0) {	
						data['errors'].push({
							id: 'errors',
							message: typeof(e) == 'object' ? e.name + ': ' + e.message : e
						});
						if (window.console) {
							window.console.log(data['errors']);
						}
					}
				}
				
				// execute onerror callback
				if($.isFunction(ajaxOptions['onerror'])) {
					ajaxOptions['onerror'](data, 'ERROR', xhr);
				}
			}
		},
		getIndicator: function() {
			var notifier = $.rad.notify.getNotifier();
			return notifier.ajaxIndicator;
		}
	});
	
	$.rad.ajax.options = {
		show_indicator: true,
		check_response: false,
		login_form: false,
		defaults: {
			global: true,
			url: '/api',
			dataType: 'json',
			data: {
				format: 'json'
			},
			onerror: function(data, textStatus, xhr) {
				if(data && data['errors']) {
					$.each(data['errors'], function(i, error) {
						if (typeof(error) == 'object') {
							$.rad.notify.error('There was a problem with your request', error.message || '');
						} else {
							$.rad.notify.error('There was a problem with your request', error || '');
						}
					});
				}
			}
		}
	};
	
	$.rad.ajax.init();
	
	/*****************************************************************
	 * rad form plugin
	 *****************************************************************
	 */
	 
	$.extend($.rad.form, {
		getInstance: function($element, options) {
			var form = $element.data('rad-form');

			if(!(form instanceof $.rad.form)) {
				if(options == undefined) {
					throw new Error($element.context + ' is not bound to a form instance');
				} else {
					form = new $.rad.form($element, options);
					$element.data('rad-form', form);
				}
			} else if($.isPlainObject(options)) {
				form.setOptions(options);
			}
			
			return form;
		}
	});
	 
	$.rad.form.prototype = {
		_form: null,
		_options: null,
		_validator: null,
		setup: function(options) {
			var _this = this;			
			this.setOptions(options);
			if(this._form !== null) {
				this._form.bind({
					'submit': function(e) {
						return _this.submit();
					},
					'reset': function(e) {
						_this.reset();
					},
					'validate': function(e) {
						_this.validate();
					}
				});
			}
		},
		setOptions: function(options) {			
			this._options = $.extend({}, $.rad.form.defaults, this._options || {}, options || {});
			return this;
		},
		validate: function() {
			if(this._options['validate'] != true) {
				return true;
			}
			return true;
			/* Removed validation library so that validation can be done manually */
//			var validator = this.getValidator();
//			validator.reset();
//			return validator.validateAll();
		},
		reset: function() {
			//this.getValidator().reset();
			$('input[type="text"], textarea', this._form).val('');
			$('input[type="checkbox"]', this._form).removeAttr('selected').change();
			$('input[type="checkbox"].select-all', this._form).attr('selected', true).change();
			return this;
		},
		submit: function() {	
			// Check for valid input before submitting
			if(!this.validate()) {
				return false;
			}
			
			if($.isFunction(this._options['prepare'])) {
				if(this._options['prepare'](this._options) === false) {
					return false;
				}
			}

			var _this = this;
			var $form = this._form;
			var $buttons = $('input[type="submit"]:enabled, :button:enabled', $form);
			// disable form buttons
			$buttons.attr('disabled', true);
			
			// If we are uploading files, we have to process the form differently
			if ($('input:file', $form).length > 0) {
				var t = new Date().getTime();
				var iframe_id = 'jqiFrame' + t;
				var $iframe = $('<iframe />').attr({
					id: iframe_id,
					name: iframe_id,
					src: 'javascript:;',
					style: 'display:block;width:200px;height:200px;'
				});
				
				$iframe.appendTo(document.body);
				$form.attr('target', iframe_id);
				
				$iframe.load(function(){
					// enable form buttons
					$buttons.attr('disabled', false);
					//* form submission is complete,,
					var response = $iframe.contents().find('pre').html();
					var data = $.parseJSON(response);

					setTimeout(function() {
						$iframe.remove();
					}, 100);
					
					if(!data) { return true; }
					// Check for errors in the response
					if (!data['result'] || data['result'] != 'SUCCESS') {	
						_this.onerror(data);						
					} else {
						_this.onsuccess(data);
					}
				});
				return true;
			}
			
			var options = $.extend({
				url: $form.attr('action'),
				type: $form.attr('method'),
				data: $form.serialize()
			}, _this._options);
						
			$.extend(options, {
				onerror: function(data, textStatus, xhr) {
					_this.onerror(data, textStatus, xhr);
				},
				onsuccess: function(data, textStatus, xhr) {
					_this.onsuccess(data, textStatus, xhr);
				}
			});
			
			$.rad.ajax(options);
			
			// enable form buttons
			$buttons.attr('disabled', false);

			return false;
		},
		onsuccess: function(data, textStatus, xhr) {
			// Either reset the entire form or just the validation errors;
			if (this._options['keep_form'] === false) {
				this.reset();
			} else {
				//this.getValidator().reset();
			}
			
			if ($.isFunction(this._options['onsuccess'])) {
				this._options['onsuccess'](data, textStatus, xhr);
			}
		},
		onerror: function(data, textStatus, xhr) {
			data['errors'] = data['errors'] || [];

			var _this = this;
			$.each(data['errors'], function(i, error) {
				var $input = $(':input[name="' + error['id'] + '"]:visible', _this._form); 
				// If an element within this form exists, append the error to it, otherwise notify the error
				if ($input.length > 0) {
					$input.error(error['message']);
				} else {
					$.rad.notify('Error', error['message'] || error);
				}
				
			});
			
			if ($.isFunction(this._options['onerror'])) {
				this._options['onerror'](data, textStatus, xhr);
			}
		},
		getValidator: function() {
			if(this._validator == null) {
				var _this = this;
				
				this._validator = new validator(this._form, {
					onSubmit: false, 
					errorHandler: function(formElement, errors) {
						var error = this.humanize_name(formElement) + ' ' + errors.message.join(', ');
						if (formElement.length > 0 && formElement.attr('type') != 'hidden') {
							formElement.error(error);
						} else {
							$.rad.notify.error('Validation Error', error || '');
						}
					}, 
					onSubmitFinish: function(valid) {
						return !valid;
					}
				});
			}
			
			return this._validator;
		}
	};
	
	$.rad.form.defaults = {
		keep_form: false,
		validate: true,
		onsuccess: function(data, textStatus, xhr) {
			$.rad.notify('Your request was submitted successfully', 'Your request has been submitted successfully.  You may need to refresh this page to see your changes.');
		}
	};
})(jQuery);