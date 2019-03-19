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
		return this.optional( element ) || /^\+[0-9]{1,3}\.[0-9]{4,14}(?:x.+)?$/.test( value );
	}, "Please specify a valid phone number" );
	
	return $;
}));