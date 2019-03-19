/*!
 * jQuery Validation Plugin v1.19.0
 *
 * https://jqueryvalidation.org/
 *
 * Copyright (c) 2018 Jörn Zaefferer
 * Released under the MIT license
 */
(function( factory ) {
	if ( typeof define === "function" && define.amd ) {
		define( ["jquery", "./jquery.validate"], factory );
	} else if (typeof module === "object" && module.exports) {
		module.exports = factory( require( "jquery" ) );
	} else {
		factory( jQuery );
	}
}(function( $ ) {

	$.validator.addMethod( "phone", function( value, element ) {
        value = value.replace(/\s+/g, '');
		return this.optional( element ) || /^((\+[1-9]{1,4}[ \-]*)|(\([0-9]{2,3}\)[ \-]*)|([0-9]{2,4})[ \-]*)*?[0-9]{3,4}?[ \-]*[0-9]{3,4}?$/.test( value );
	}, "Please specify a valid phone number" );

	$.validator.addMethod( "notEqualTo", function( value, element, param ) {

		// Bind to the blur event of the target in order to revalidate whenever the target field is updated
		var target = param;
		
		return value != param;
	}, "Please select correct value" );

	return $;
}));