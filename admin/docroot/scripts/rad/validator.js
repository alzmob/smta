/**
 * validator.js
 *
 * Creates a validation with a specific scope. FormElements within that scope can be manual validated or automatically
 * validated when their values change or their form is submitted. Custom validation methods can be passed to the object along
 * with custom error handling.
 *
 * Requires jquery library, last checked against version 1.3.1
 *
 * --------------------
 * Public Functions
 * --------------------
 * var validator = new validator(jQuerySelector idOfAFormOrOtherContainerTag, JSON validatorOptions);
 * validator.prevalidateAll();
 * validator.validate(string idOfAFormElement);
 * validator.validateAll();
 * validator.validation_failed(string errorCode, string errorMsg);
 * validator.humanize_name(string idOfAFormElement);
 *
 * var validator = new validator($('#my_form'), {onSubmit:true, onSubmitFinish:submitForm});
 *
 * function submitForm(valid) {
 *   if (valid) {}
*    else {}
 * }
 *
 * --------------------
 * validatorOptions
 * --------------------
 * boolean ignoreDisabled - Ignore fields that are disabled. Defaults to true.
 * boolean onChange - Validate each form field when its value changes. Defaults to false.
 * boolean onSubmit - Validate everything in the validator's scope on a form submit. Defaults to false.
 * function onSubmitStart - Function called before form submit begins validation. Defaults to false. This can be used to clear global variables before beginning validation.
 * function onSubmitFinish - Function called after form submit ends. Defaults to false. Passes a boolean of true/false depending on success stat. This can be used to add your own custom onSubmit actions. Return false in order to cancel the form submission.
 * function onPreValidate - Function that runs automatically for every form field before it is validated. This function is called with element formElement as its argument. Defaults to a function that removes the error created by the default errorHandler.
 * function customValidation - Function that runs automatically during a form field validation to check for custom validation conditions. element formElement is passed as an argument. Defaults to false.
 * function errorHandler - Function to call in event of a validation error. element formElement and JSON {code: errorCode, message: errorMsg} are passed as arguments to the function. If none specified, then output the error in a span with class invalidStatus.
 * function customHumanizeName - Function to call when humanizing names. Overrides the default behavior of splitting by underscore. element formElement is passed. Requires a string to be used as the name to be returned.
 *
 */

function validator(scope, validatorOptions) {
	if (!validatorOptions) { 
		validatorOptions = {};
	}

	this.scope = scope;
	this.scope.data('validator', this);
	this.errors = {code: [], message: []};
	this.validation_running = false;
	this.validation_queue = [];
	this.formElements_checked = [];
	this.radioCheckElements_checked = [];
	this.formElement_passed_validation = true;
	this.data_string = [];

	this.ignoreDisabled = validatorOptions.onChange || true;
	this.onChange = validatorOptions.onChange || false;
	this.onSubmit = validatorOptions.onSubmit || false;
	this.onSubmitStart = validatorOptions.onSubmitStart || false;
	this.onSubmitFinish = validatorOptions.onSubmitFinish || false;
	this.onPreValidate = validatorOptions.onPreValidate || this.prevalidate;
	this.customValidation = validatorOptions.customValidation || false;
	this.errorHandler = validatorOptions.errorHandler || this.error_handler;
	this.customHumanizeName = validatorOptions.customHumanizeName || false;
	this.validators = new validators();

	this.enable();
};

validator.prototype.getScopeElements = function() {
	var scopeElements = this.scope.find(':input:not(:hidden,:submit,:button)');
	return scopeElements;
};

validator.prototype.enable = function() {
	if (this.onChange) {
		this.addOnChangeFunctions();
	}
	if (this.onSubmit) {
		this.addOnSubmitFunction();
	}
};

validator.prototype.reset = function() {
	$('div[id$=errorText]').remove();
}

validator.prototype.disable = function() {
	this.scope.unbind('click.clickEventFunction');
	this.scope.unbind('change.changeEventFunction');
	this.scope.unbind('submit.submitEventFunction');
};

validator.prototype.addOnSubmitFunction = function() {
	if (this.scope == null) {
		return false;
	}

	this.scope.bind('submit.submitEventFunction', {validatorObj:this}, function(event) {
		if (event.data.validatorObj.onSubmitStart) {
			validatorObj.onSubmitStart();
		}

		var passed_validation = event.data.validatorObj.validateAll();
		if (event.data.validatorObj.onSubmitFinish) {
			var onSubmitFinishResult = event.data.validatorObj.onSubmitFinish(passed_validation, event);
			if (onSubmitFinishResult !== false) {
				return true;
			}
		} else {
			// Restart the submission event if the form passed validation
			if (passed_validation) {
				return true;
			}
		}
		return false;
	});
};

validator.prototype.addOnChangeFunctions = function() {
	this.scope.find(':radio,:checkbox').bind('click.clickEventFunction', {validatorObj:this}, function(event) {
		var formElement = $(event.target);
		event.data.validatorObj.validate(formElement);
	});
	this.scope.find(':input:not(:radio,:checkbox)').bind('change.changeEventFunction', {validatorObj:this}, function(event) {
		var formElement = $(event.target);
		event.data.validatorObj.validate(formElement);
	});
};

validator.prototype.prevalidate = function(formElement) {
	$(formElement).removeClass("error");
	// This is the default prevalidation handler that runs if another is not specified.
	var formElementId = this.get_element_name(formElement);
	// Replace any invalid characters in the id ([])
	formElementId = formElementId.replace(/(\[|\])/g, '');
	if ($('#' + formElementId + "_errorText")) {
		$('#' + formElementId + "_errorText").remove();
	}
};

validator.prototype.prevalidateAll = function() {
	var self = this;
	if (!this.onPreValidate) {
		return;
	}
	var formElements = this.getScopeElements();
	formElements.each(function(formElement) {
		self.onPreValidate(formElement);
	});
};

validator.prototype.error_handler = function(formElement, errors) {
	$(formElement).addClass("error");
	// This is the default error handler and is used if another is not specified.
	var fieldName = this.humanize_name(formElement);
	if (errors.code[0]=="uniqueChoice") {
		if ($.inArray(errors.message[0],this.data_string)<0) {
			this.data_string.push(errors.message[0]);
		}
	} else {
		this.data_string.push(fieldName + " " + errors.message[0]);
	}
};

validator.prototype.humanize_name = function(formElement) {
	if (this.customHumanizeName) {
		return this.customHumanizeName(formElement);
	}
	var name = this.get_element_name(formElement);
	var name = name.replace(/_/g, " ");
	var name = name.charAt(0).toUpperCase() + name.substr(1, name.length).toLowerCase();
	return name;
};

validator.prototype.get_element_name = function(formElement) {
	var sameNameTypes = ['radio', 'checkbox'];
	if (formElement.prop("tagName").toLowerCase() == "input" && $.inArray(formElement.prop("type").toLowerCase(),sameNameTypes)>0) {
		var formElementId = formElement.prop("name");
	} else {
		var formElementId = (formElement.prop("id")) ? formElement.prop("id") : formElement.prop("name");
	}
	return formElementId;
};

validator.prototype.validation_failed = function(errorCode, errorMsg) {
	this.formElement_passed_validation = false;
	this.errors.code.push(errorCode);
	this.errors.message.push(errorMsg);
};

validator.prototype.clear_validation = function() {
	this.formElement_passed_validation = true;
	this.errors = {code: [], message: []};
};

validator.prototype.get_validation = function() {
	return this.formElement_passed_validation;
};

validator.prototype.get_errors = function() {
	return this.errors;
};

validator.prototype.validateAll = function() {
	// If an onChange validation is running, consider it not to be running to avoid conflicts between an onChange check and an onSubmit check.
	this.validation_running = false;
	var passed_validation = true;
	var scopeElements = this.getScopeElements();
	
	for (var i=0; i<scopeElements.length; i++) {
		var scopeElement = $(scopeElements[i]);
		
		if ($.inArray(scopeElement.prop("name"),this.radioCheckElements_checked)<0) {
			if (!this.validate(scopeElement)) {
				passed_validation = false;
			}
			if (scopeElement.is(":radio,:checkbox")) {
				this.radioCheckElements_checked.push(scopeElement.prop("name"));
			}
		}
	}
	this.radioCheckElements_checked = [];

	return passed_validation;
};

validator.prototype.validate = function(formElement) {
	var formElement = $(formElement);
	if (this.ignoreDisabled && formElement.is(':disabled')) {
		// If this element is disabled, do not validate it
		return true;
	}
	// If there is a validation running, queue the next formElement requesting validation
	if (this.validation_running) {
		// Only queue the next formElement requesting validation if it hasn't already been validated
		if ($.inArray(formElement,this.formElements_checked)>0 && $.inArray(formElement,this.validation_queue)>0) {
			this.validation_queue.push(formElement);
		}
		return true;
	}
	this.validation_running = true;
	var passed_validation = this.run_validation(formElement);
	// Validate everything in the queue directly
	while (this.validation_queue.length > 0) {
		this.run_validation($(this.validation_queue.shift()));
	}
	// Clear validatoin running flag and the list of elements checked this run through
	this.validation_running = false;
	this.formElements_checked = [];
	return passed_validation;
};

validator.prototype.run_validation = function(formElement) {
	formElement = $(formElement);
	// Mark this formElement as checked
	this.formElements_checked.push(formElement);
	this.clear_validation();
	var classNames = formElement.prop("class").split(" ");
	if (this.onPreValidate) {
		this.onPreValidate(formElement);
	}
	if (formElement.hasClass("required")) {
		if (!this.validators.is_required(formElement)) {
			this.validation_failed("required", "is required.");
		}
	}
	if (formElement.hasClass("blankValue")) {
		if (!this.validators.is_blank(formElement)) {
			this.validation_failed("blank", "must be blank.");
		}
	}
	if (formElement.hasClass("numericValue")) {
		if (!this.validators.is_numeric(formElement)) {
			this.validation_failed("numeric", "must be a number.");
		}
	}
	if (formElement.hasClass("numericPositiveValue")) {
		if (!this.validators.is_numeric_positive(formElement)) {
			this.validation_failed("numeric_positive", "must be a positive number.");
		}
	}
	if (formElement.hasClass("numericZeroPositiveValue")) {
		if (!this.validators.is_numeric_zero_positive(formElement)) {
			this.validation_failed("numeric_zero_positive", "must be zero or a positive number.");
		}
	}
	if (formElement.hasClass("numericNegativeValue")) {
		if (!this.validators.is_numeric_negative(formElement)) {
			this.validation_failed("numeric_negative", "must be a negative number.");
		}
	}
	if (formElement.hasClass("numericZeroNegativeValue")) {
		if (!this.validators.is_numeric_zero_negative(formElement)) {
			this.validation_failed("numeric_zero_negative", "must be zero or a negative number.");
		}
	}
	if (formElement.hasClass("integerValue")) {
		if (!this.validators.is_integer(formElement)) {
			this.validation_failed("integer", "must be a whole number.");
		}
	}
	if (formElement.hasClass("integerPositiveValue")) {
		if (!this.validators.is_integer_positive(formElement)) {
			this.validation_failed("integer_positive", "must be a positive whole number.");
		}
	}
	if (formElement.hasClass("integerZeroPositiveValue")) {
		if (!this.validators.is_integer_zero_positive(formElement)) {
			this.validation_failed("integer_zero_positive", "must be zero or a positive whole number.");
		}
	}
	if (formElement.hasClass("integerNegativeValue")) {
		if (!this.validators.is_integer_negative(formElement)) {
			this.validation_failed("integer_negative", "must be a negative whole number.");
		}
	}
	if (formElement.hasClass("integerZeroNegativeValue")) {
		if (!this.validators.is_integer_zero_negative(formElement)) {
			this.validation_failed("integer_zero_negative", "must be zero or a negative whole number.");
		}
	}
	if (formElement.hasClass("alphaValue")) {
		if (!this.validators.is_alpha(formElement)) {
			this.validation_failed("alpha", "must only contain alphabetic characters.");
		}
	}
	if (formElement.hasClass("alphanumericValue")) {
		if (!this.validators.is_alphanumeric(formElement)) {
			this.validation_failed("alphanumeric", "must only contain alphanumeric characters.");
		}
	}
	if (formElement.hasClass("dateValue")) {
		if (!this.validators.is_date(formElement)) {
			this.validation_failed("date", "must be a date in the yyyy-mm-dd format.");
		}
	}
	if (formElement.hasClass("timeValue")) {
		if (!this.validators.is_time(formElement)) {
			this.validation_failed("time", "must be a time in the hh:mm:ss tt format. ss and tt are optional.");
		}
	}
	if (formElement.hasClass("datetimeValue")) {
		if (!this.validators.is_datetime(formElement)) {
			this.validation_failed("datetime", "must be a date in the yyyy-mm-dd hh:mm:ss tt format. ss and tt are optional.");
		}
	}
	if (formElement.hasClass("datetimeOptionalValue")) {
		if (!this.validators.is_datetime(formElement) && !this.validators.is_date(formElement)) {
			this.validation_failed("datetime", "must be a date in the yyyy-mm-dd and may optionally include hh:mm:ss tt.");
		}
	}
    if (formElement.hasClass("ipValue")) {
		if (!this.validators.is_ip(formElement)) {
			this.validation_failed("ip", "must be a valid IP address.");
		}
	}
	if (formElement.hasClass("urlValue")) {
		if (!this.validators.is_url(formElement)) {
			this.validation_failed("url", "must be a valid Internet address.");
		}
	}
	if (formElement.hasClass("emailValue")) {
		if (!this.validators.is_email(formElement)) {
			this.validation_failed("email", "must be a valid email address.");
		}
	}
	if (formElement.hasClass("emailListValue")) {
		if (!this.validators.is_email_list(formElement)) {
			this.validation_failed("emailList", "must be a list of valid email address.");
		}
	}
	if (formElement.hasClass("zipValue")) {
		if (!this.validators.is_zip(formElement)) {
			this.validation_failed("zip", "must be a valid US zip code.");
		}
	}
	if (formElement.hasClass("canadianPostalValue")) {
		if (!this.validators.is_canadian_postal(formElement)) {
			this.validation_failed("canadian_postal", "must be a valid Canadian postal code.");
		}
	}
	if (formElement.hasClass("ukPostalValue")) {
		if (!this.validators.is_uk_postal(formElement)) {
			this.validation_failed("uk_postal", "must be a valid UK postal code.");
		}
	}
	if (formElement.hasClass("postalZipValue")) {
		if (!this.validators.is_postal_zip(formElement)) {
			this.validation_failed("postal_zip", "must be a valid US Zip Code, Canadian postal code, or UK postal code.");
		}
	}
	if (formElement.hasClass("phoneValue")) {
		if (!this.validators.is_us_phone(formElement) && !this.validators.is_foreign_phone(formElement)) {
			this.validation_failed("phone", "must be a valid phone number.");
		}
	}
	if (formElement.hasClass("usPhoneValue")) {
		if (!this.validators.is_us_phone(formElement)) {
			this.validation_failed("phone_us", "must be a valid US phone number.");
		}
	}
	if (formElement.hasClass("foreignPhoneValue")) {
		if (!this.validators.is_foreign_phone(formElement)) {
			this.validation_failed("phone_foregin", "must be a valid foreign phone number.");
		}
	}
	if (formElement.hasClass("moneyValue")) {
		if (!this.validators.is_money(formElement)) {
			this.validation_failed("money", "must be a valid dollar value.");
		}
	}
	if (formElement.hasClass("moneyPositiveValue")) {
		if (!this.validators.is_money_positive(formElement)) {
			this.validation_failed("money_positive", "must be a valid positive dollar value.");
		}
	}
	if (formElement.hasClass("moneyZeroPositiveValue")) {
		if (!this.validators.is_money_zero_positive(formElement)) {
			this.validation_failed("money_zero_positive", "must be zero or a valid positive dollar value.");
		}
	}
	if (formElement.hasClass("moneyNegativeValue")) {
		if (!this.validators.is_money_negative(formElement)) {
			this.validation_failed("money_negative", "must be a valid negative dollar value.");
		}
	}
	if (formElement.hasClass("moneyZeroNegativeValue")) {
		if (!this.validators.is_money_zero_negative(formElement)) {
			this.validation_failed("money_zero_negative", "must be zero or a valid negative dollar value.");
		}
	}
	if (formElement.hasClass("uniqueChoice")) {
		if (!this.validators.is_uniqueChoice(formElement)) {
			this.validation_failed("uniqueChoice", formElement.find(':selected').text() + " can only be selected once.");
		}
	}
	if (formElement.prop("class").indexOf("isLength") == 0 || formElement.prop("class").indexOf(" isLength") > 0) {
		if (!this.validators.is_isLength(formElement)) {
			var isLength = this.validators.parse_number_from_className(formElement, "isLength");
			this.validation_failed("isLength", "must be exactly " + isLength + " characters long.");
		}
	}
	if (formElement.prop("class").indexOf("maxLength") == 0 || formElement.prop("class").indexOf(" maxLength") > 0) {
		if (!this.validators.is_maxLength(formElement)) {
			var maxLength = this.validators.parse_number_from_className(formElement, "maxLength");
			if (maxLength == -1) {maxLength = formElement.maxLength;}
			this.validation_failed("maxLength", "must be less than " + maxLength + " characters long.");
		}
	}
	if (formElement.prop("class").indexOf("minLength") == 0 || formElement.prop("class").indexOf(" minLength") > 0) {
		if (!this.validators.is_minLength(formElement)) {
			var minLength = this.validators.parse_number_from_className(formElement, "minLength");
			this.validation_failed("minLength", "must be at least " + minLength + " characters long.");
		}
	}
	if (formElement.prop("class").indexOf("minOptionChoice") == 0 || formElement.prop("class").indexOf(" minOptionChoice") > 0) {
		if (!this.validators.is_minOptionChoice(formElement)) {
			var minOptionChoice = this.validators.parse_number_from_className(formElement, "minOptionChoice");
			this.validation_failed("minOptionChoice", "must have at least " + minOptionChoice + " choice(s) selected.");
		}
	}
	if (formElement.prop("class").indexOf("maxOptionChoice") == 0 || formElement.prop("class").indexOf(" maxOptionChoice") > 0) {
		if (!this.validators.is_maxOptionChoice(formElement)) {
			var maxOptionChoice = this.validators.parse_number_from_className(formElement, "maxOptionChoice");
			this.validation_failed("maxOptionChoice", "cannot have more than " + maxOptionChoice + " choice(s) selected.");
		}
	}
	if (formElement.prop("class").indexOf("maxFloat") == 0 || formElement.prop("class").indexOf(" maxFloat") > 0) {
		if (!this.validators.is_maxFloat(formElement)) {
			var maxFloat = this.validators.parse_number_from_className(formElement, "maxFloat");
			this.validation_failed("maxFloat", "must be numberic and cannot have more than " + maxFloat + " decimal place(s).");
		}
	}
	if (this.customValidation) {
		this.customValidation(formElement);
	}
	if (this.get_validation() == false && this.errorHandler) {
		this.errorHandler(formElement, this.get_errors());
	}
	return this.get_validation();
};

function validators() {};

validators.prototype.is_required = function(formElement) {
	// Value cannot be null
	return $(formElement).val() != '' && $(formElement).val() != null;
};

validators.prototype.is_blank = function(formElement) {
	// Value must be null
	return $(formElement).val() == '';
};

validators.prototype.is_numeric = function(formElement) {
	// Can be negative and have a decimal value
	// Do not accept commas in value as the DB does not accept them
	if ($(formElement).val() == '') {
		return true;
	};
	return $(formElement).val().match(/^-?((\d+(\.\d+)?)|(\.\d+))$/);
};

validators.prototype.is_numeric_positive = function(formElement) {
	// Must be positive and have a decimal value
	if ($(formElement).val() == '') {
		return true;
	};
	var isNumeric = this.is_numeric(formElement);
	return isNumeric && parseFloat($(formElement).val()) > 0;
};

validators.prototype.is_numeric_zero_positive = function(formElement) {
	// Must be positive and have a decimal value
	if ($(formElement).val() == "") {return true;};
	var isNumeric = this.is_numeric(formElement);
	return isNumeric && parseFloat($(formElement).val()) >= 0;
};
validators.prototype.is_numeric_negative = function(formElement) {
	// Must be negative and have a decimal value
	if ($(formElement).val() == "") {return true;};
	var isNumeric = this.is_numeric(formElement);
	return isNumeric && parseFloat($(formElement).val()) < 0;
};
validators.prototype.is_numeric_zero_negative = function(formElement) {
	// Must be negative and have a decimal value
	if ($(formElement).val() == "") {return true;};
	var isNumeric = this.is_numeric(formElement);
	return isNumeric && parseFloat($(formElement).val()) <= 0;
};
validators.prototype.is_integer = function(formElement) {
	// Positive or negative whole number
	// Do not accept commas in value as the DB does not accept them
	if ($(formElement).val() == "") {return true;};
	return $(formElement).val().match(/^-?\d+$/);
};
validators.prototype.is_integer_positive = function(formElement) {
	// Positive whole number
	if ($(formElement).val() == "") { return true; };
	var isInteger = this.is_integer(formElement);
	return isInteger && parseInt($(formElement).val()) > 0;
};
validators.prototype.is_integer_zero_positive = function(formElement) {
	// Positive whole number
	if ($(formElement).val() == "") {return true;};
	var isInteger = this.is_integer(formElement);
	return isInteger && parseInt($(formElement).val()) >= 0;
};
validators.prototype.is_integer_negative = function(formElement) {
	// Negative whole number
	if ($(formElement).val() == "") {return true;};
	var isInteger = this.is_integer(formElement);
	return isInteger && parseInt($(formElement).val()) < 0;
};
validators.prototype.is_integer_zero_negative = function(formElement) {
	// Negative whole number
	if ($(formElement).val() == "") {return true;};
	var isInteger = this.is_integer(formElement);
	return isInteger && parseInt($(formElement).val()) <= 0;
};
validators.prototype.is_alpha = function(formElement) {
	// Only alpha characters and underscore
	if ($(formElement).val() == "") {return true;};
	return $(formElement).val().match(/^[a-z_]+$/i);
};
validators.prototype.is_alphanumeric = function(formElement) {
	// Match any alpha, number, or underscore characters
	if ($(formElement).val() == "") {return true;};
	return $(formElement).val().match(/^\w+$/);
};
validators.prototype.is_date = function(formElement) {
	// Match a date in yyyy-mm-dd format
	if ($(formElement).val() == "") {return true;};
	return $(formElement).val().match(/^(19|20)?[0-9]{2}[- \/.](0?[1-9]|1[012])[- \/.](0?[1-9]|[12][0-9]|3[01])$/);
};
validators.prototype.is_time = function(formElement) {
	// Match a date in hh:mm[:ss][ ][tt] format
	if ($(formElement).val() == "") {return true;};
	return $(formElement).val().match(/^[0-2]?\d:[0-5]\d(:[0-5]\d)?( ?(a|p)m)?$/i);
};
validators.prototype.is_datetime = function(formElement) {
	// Match a datetime in yyyy-mm-dd hh:mm[:ss][ ][tt] format
	if ($(formElement).val() == "") {return true;};
	return $(formElement).val().match(/^(19|20)?[0-9]{2}[- \/.](0?[1-9]|[12][0-9]|3[01])[- \/.](0?[1-9]|1[012]) [0-2]?\d:[0-5]\d(:[0-5]\d)?( ?(a|p)m)?$/i);
};
validators.prototype.is_url = function(formElement) {
	// Match a url
	if ($(formElement).val() == '') {
		return true;
	}
	return $(formElement).val().match(/^(https?|ftp):\/\/([-A-Z0-9.]+)\.([A-Z]{2,4})(\/[-A-Z0-9+&@#\/%=~_|!:,.;]*)?(\?[-A-Z0-9+&@#\/%=~_|!:,.;]*)?$/i);
};

validators.prototype.is_ip = function(formElement) {
    // Match a url
	if ($(formElement).val() == '') {
		return true;
	}
	return $(formElement).val().match(/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/);
};

validators.prototype.is_email = function(formElement) {
	// Match a email address
	if ($(formElement).val() == "") {return true;};
	return $(formElement).val().match(/^[A-Z0-9!#$%&'*+-\/=?^_`{|}~]+@(?:[A-Z0-9-]+\.)+[A-Z]{2,}$/i);
};
validators.prototype.is_email_list = function(formElement) {
	var emails, email, i, self = this, validList = true;
	// Match an email list
	if ($(formElement).val() == '') {
		return true;
	}
	emails = $(formElement).val().split(',');
	$.each(emails, function(i, email) {
		if (!self.is_email({'value' : $.trim(email)})) {
			validList = false;
		}
	});
	return validList;
}
validators.prototype.is_zip = function(formElement) {
	// Match a US zip code
	if ($(formElement).val() == "") {return true;};
	return $(formElement).val().match(/^[0-9]{5}(?:-[0-9]{4})?$/);
};
validators.prototype.is_canadian_postal = function(formElement) {
	// Match a Canada postal code
	if ($(formElement).val() == "") {return true;};
	return $(formElement).val().match(/^[ABCEGHJKLMNPRSTVXY][0-9][A-Z][ -][0-9][A-Z][0-9]$/i);
};
validators.prototype.is_uk_postal = function(formElement) {
	// Match a UK postal code
	if ($(formElement).val() == "") {return true;};
	return $(formElement).val().match(/^[A-Z]{1,2}[0-9][A-Z0-9]? [0-9][ABD-HJLNP-UW-Z]{2}$/);
};
validators.prototype.is_postal_zip = function(formElement) {
	// Match a us zip, canadian postal code, or uk postal code
	if ($(formElement).val() == "") {return true;};
	if (this.is_zip(formElement)) {return true;};
	if (this.is_canadian_postal(formElement)) {return true;};
	if (this.is_uk_postal(formElement)) {return true;};
	return false;
};
validators.prototype.is_us_phone = function(formElement) {
	// Match a North American Phone number with required area code
	if ($(formElement).val() == "") {
		return true;
	};
	return $(formElement).val().match(/^(1[-. ]?)?\(?[0-9]{3}\)?[-. ]?[0-9]{3}[-. ]?[0-9]{4}$/);
};
validators.prototype.is_foreign_phone = function(formElement) {
	// Match a non-North American Phone number
	if ($(formElement).val() == "") {
		return true;
	};
	var numOnly = $(formElement).val().replace(/[-. +()\/]/g, '');
	if (numOnly.match(/^\d{10,}$/)) {
		return true;
	};
	return false;
};
validators.prototype.is_money = function(formElement) {
	// Match a dollar value
	// Do not accept commas in value as the DB does not accept them
	if ($(formElement).val() == '') {
		return true;
	};
	return $(formElement).val().match(/^((-?\$)|(\$-?)|(-))?((\d+(\.\d{2})?)|(\.\d{2}))$/);
};
validators.prototype.is_money_positive = function(formElement) {
	//Match a dollar value
	if ($(formElement).val() == '') {
		return true;
	};
	var isMoney = this.is_money(formElement);
	return isMoney && parseFloat($(formElement).val().replace("$", '')) > 0;
};
validators.prototype.is_money_zero_positive = function(formElement) {
	//Match a dollar value
	if($(formElement).val() == "") {return true;};
	var isMoney = this.is_money(formElement);
	return isMoney && parseFloat($(formElement).val().replace("$", "")) >= 0;
};
validators.prototype.is_money_negative = function(formElement) {
	//Match a dollar value
	if($(formElement).val() == "") {return true;};
	var isMoney = this.is_money(formElement);
	return isMoney && parseFloat($(formElement).val().replace("$", "")) < 0;
};
validators.prototype.is_money_zero_negative = function(formElement) {
	//Match a dollar value
	if($(formElement).val() == "") {return true;};
	var isMoney = this.is_money(formElement);
	return isMoney && parseFloat($(formElement).val().replace("$", "")) <= 0;
};
validators.prototype.is_isLength = function(formElement) {
	//Value must be the exact max length
	if($(formElement).val() == "") {return true;};
	return $(formElement).val().length == formElement.maxLength;
};
validators.prototype.is_maxLength = function(formElement) {
	//Value must be the less than the max length
	if($(formElement).val() == "") {return true;};
	var maxLength = this.parse_number_from_className(formElement, "maxLength");
	if(maxLength == -1) maxLength = formElement.maxLength;
	if(!maxLength) return true;
	return $(formElement).val().length <= maxLength;
};
validators.prototype.is_minLength = function(formElement) {
	//Value must be at least the min length
	if($(formElement).val() == "") {return true;};
	var minLength = this.parse_number_from_className(formElement, "minLength");
	if (minLength == -1) {
		return true;
	};
	return $(formElement).val().length >= minLength;
};

validators.prototype.is_minOptionChoice = function(formElement) {
	//total number of selected choices must be more than the minimum
	var minOptionChoice = this.parse_number_from_className(formElement, "minOptionChoice");
	if (minOptionChoice == -1) {
		return true;
	};
	var totalOptionSelected = 0;
	//TODO: does this work properly in IE 6?
	var sameOptionName = document.getElementsByName(formElement.prop("name"));
	for (var i=0;i<sameOptionName.length;i++) {
		if (sameOptionName[i].checked==true) {
			totalOptionSelected++;
		}
	}
	return totalOptionSelected >= minOptionChoice;
};

validators.prototype.is_maxOptionChoice = function(formElement) {
	//total number of selected choices must be less than the maximum
	var maxOptionChoice = this.parse_number_from_className(formElement, "maxOptionChoice");
	if (maxOptionChoice == -1) {return true;};
	var totalOptionSelected = 0;
	//TODO: does this work properly in IE 6?
	var sameOptionName = document.getElementsByName(formElement.prop("name"));
	for (var i=0;i<sameOptionName.length;i++) {
		if (sameOptionName[i].checked==true) {
			totalOptionSelected++;
		}
	}
	return totalOptionSelected <= maxOptionChoice;
};

validators.prototype.is_uniqueChoice = function(formElement) {
	// TODO: does this work properly in IE 6?
	var choiceValues = [];
	var sameOptionName = $("[name='" + formElement.prop("name") + "']:enabled");
	var eValue = formElement.val();
	for (var i=0;i<sameOptionName.length;i++) {
		if (formElement.get(0)!=sameOptionName[i]) {
			choiceValues.push($(sameOptionName[i]).val());
		}
	}
	if ($.inArray(eValue,choiceValues)<0) {
		return true;
	}
	return false;
};

validators.prototype.is_maxFloat = function(formElement) {
	// Value cannot have more digits then specified in maxFloat
	if ($(formElement).val() == "") {return true;};
	var maxFloat = this.parse_number_from_className(formElement, "maxFloat");
	if (maxFloat == -1) {return true;};
	var maxFloatPattern = new RegExp("^-?((\\d+(\\.\\d{0,"+maxFloat+"})?)|(\\.\\d{0,"+maxFloat+"}))$");
	return $(formElement).val().match(maxFloatPattern);
};

validators.prototype.parse_number_from_className = function(formElement, className) {
	// This function is expecting classNames followed by numbers such as minLength4, maxFloat10, etc. We need to parse out the number after that className to use it for testing against the formElement.length
	var number = 0;
	var indexStart = formElement.prop("class").indexOf(" " + className);
	// className wasn't found as an additional class. It must then be the first class. Verify that's true just to be safe.
	if (indexStart == -1) {
		indexStart = formElement.prop("class").indexOf(className);
		// If we couldn't find a valid className position then we got here by error and should return true
		if (indexStart != 0) {
			return -1;
		}
	} else {
		// We add one to indexStart to account for the space we found in front of the className
		indexStart++;
	}
	var indexEnd = formElement.prop("class").indexOf(" ", indexStart);
	if (indexEnd == -1) {indexEnd = formElement.prop("class").length;};
	number = formElement.prop("class").substring(indexStart + className.length, indexEnd);
	if (isNaN(number)) {
		return -1;
	};
	return number;
};

function __validatorOnSubmitFinish(validationStatus) {
	if (validationStatus) {
		this.data_string = [];
		return true;
	} else {
		alert(this.data_string.join("\n"));
		this.data_string = [];
		return false;
	}
};

function createValidationForms() {
	var validatorArray = [];
	$("form.validator").each(function() {
		var formElement = $(this);
		var validatorObj = new validator(formElement, {
			onSubmit: true,
			onSubmitFinish: __validatorOnSubmitFinish
		});
		validatorArray.push(validatorObj);
	});
	return validatorArray;
};
