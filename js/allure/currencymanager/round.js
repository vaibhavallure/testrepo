//flag - we already updated Product.Config
var extendProductConfigformatPriceTrigged = false;
//calling this function will update Product.Config to our formatPrice

function extendProductConfigformatPrice() {
    if (typeof(Product) != "undefined") {
        if (!extendProductConfigformatPriceTrigged) {
            extendProductConfigformatPriceTrigged = true;

            Product.Config.prototype = Object.extend(Product.Config.prototype, {
                formatPrice:function (price, showSign) {
                    var str = '';
                    price = parseFloat(price);
                    if (showSign) {
                        if (price < 0) {
                            str += '-';
                            price = -price;
                        }
                        else {
                            str += '+';
                        }
                    }

                    var roundedPrice = (Math.round(price * 100) / 100).toString();

                    if (this.prices && this.prices[roundedPrice]) {
                        str += this.prices[roundedPrice];
                    } else {
                        precision = 2;
                        if (typeof(AllureCurrencyManagerJsConfig) != "undefined") {
                            if (typeof(AllureCurrencyManagerJsConfig.precision) != "undefined") {
                                precision = AllureCurrencyManagerJsConfig.precision;
                            }
                        }
                        if (typeof(optionsPrice) != "undefined") {
                            if (typeof(optionsPrice.priceFormat) != "undefined") {
                                precision = optionsPrice.priceFormat.requiredPrecision;
                            }
                        }
                        if (typeof(AllureCurrencyManagerJsConfig) != "undefined") {
                            if (typeof(AllureCurrencyManagerJsConfig.cutzerodecimal) != "undefined") {
                                if (AllureCurrencyManagerJsConfig.cutzerodecimal != 0) {
                                    if (price - Math.round(price) == 0) {
                                        precision = 0;
                                    }
                                }
                            }
                        }

                        if (typeof(AllureCurrencyManagerJsConfig) != "undefined") {
                            if (typeof(AllureCurrencyManagerJsConfig.min_decimal_count) != "undefined") {
                                if (AllureCurrencyManagerJsConfig.min_decimal_count < precision) {
                                    for (var testPrecision = AllureCurrencyManagerJsConfig.min_decimal_count;
                                         testPrecision < precision; testPrecision++) {
                                        //abs for 0.00199999999 or 1.1000000000001 fix
                                        if (Math.abs(Math.round(price * Math.pow(10, testPrecision))
                                            - price * Math.pow(10, testPrecision)) < 0.0000001) {
                                            precision = testPrecision;
                                            break;
                                        }
                                    }
                                }
                            }
                        }


                        if (precision > 0) {
                            str += this.priceTemplate.evaluate({price:price.toFixed(precision)});
                        }
                        else {
                            price = price.toFixed(0);
                            if (typeof(AllureCurrencyManagerJsConfig) != "undefined") {
                                if (typeof(AllureCurrencyManagerJsConfig.cutzerodecimal) != "undefined") {
                                    if (AllureCurrencyManagerJsConfig.cutzerodecimal != 0) {
                                        if (typeof(AllureCurrencyManagerJsConfig.cutzerodecimal_suffix) != "undefined") {
                                            if (AllureCurrencyManagerJsConfig.cutzerodecimal_suffix.length > 0) {
                                                price = price + "" + AllureCurrencyManagerJsConfig.cutzerodecimal_suffix;
                                            }
                                        }
                                    }
                                }
                            }
                            str += this.priceTemplate.evaluate({price:price});
                        }
                    }
                    return str;
                }
            });

            if (AllureCurrencyManagerJsConfig.cutzerodecimal > 0) {
                Product.OptionsPrice.prototype = Object.extend(Product.OptionsPrice.prototype, {formatPrice:function (price) {
                    var tmpPriceFormat = Object.clone(this.priceFormat);
                    if (price - parseInt(price) == 0) {
                        tmpPriceFormat.precision = 0;
                        tmpPriceFormat.requiredPrecision = 0;
                    }
                    if (tmpPriceFormat.precision < 0) {
                        tmpPriceFormat.precision = 0;
                    }
                    if (tmpPriceFormat.requiredPrecision < 0) {
                        tmpPriceFormat.requiredPrecision = 0;
                    }
                    var price2return = formatCurrency(price, tmpPriceFormat);
                    return price2return;
                }});
            }
        }
    }
}

try {
    extendProductConfigformatPrice();
} catch (e) {
    extendProductConfigformatPriceTrigged = false;
}

try {

    originalFormatCurrency = window.formatCurrency;

    window.formatCurrency = function (price, originalFormat, showPlus) {
        var format = Object.clone(originalFormat);

        //zeroSymbol
        //JS round fix
        price = Math.round(price * Math.pow(10, format.precision)) / Math.pow(10, format.precision);
        if (price - Math.round(price) != 0) {
            if (Math.abs(price - Math.round(price)) < 0.00000001) {
                price = 0;
            }
        }
        if (typeof(AllureCurrencyManagerJsConfig) != "undefined") {
            if (price == 0) {
                if (typeof(AllureCurrencyManagerJsConfig.zerotext) != "undefined") {
                    return AllureCurrencyManagerJsConfig.zerotext;
                }
            }
        }
        //cut zero decimal
        if (price - Math.round(price) == 0) {
            if (typeof(AllureCurrencyManagerJsConfig) != "undefined") {
                if (typeof(AllureCurrencyManagerJsConfig.cutzerodecimal) != "undefined") {
                    if (AllureCurrencyManagerJsConfig.cutzerodecimal != 0) {
                        format.precision = 0;
                        format.requiredPrecision = 0;

                        var for_replace = originalFormatCurrency(price, format, showPlus);
                        format.pattern = "%s";
                        var replaced_part = originalFormatCurrency(price, format, showPlus);
                        format.pattern = originalFormat.pattern;
                        if (typeof(AllureCurrencyManagerJsConfig.cutzerodecimal_suffix) != "undefined") {
                            if (AllureCurrencyManagerJsConfig.cutzerodecimal_suffix.length > 0) {
                                return for_replace.replace(replaced_part, replaced_part + ""
                                    + AllureCurrencyManagerJsConfig.cutzerodecimal_suffix);
                            }
                        }
                    }
                }
            }
        }

        if (typeof(AllureCurrencyManagerJsConfig) != "undefined") {
            if (typeof(AllureCurrencyManagerJsConfig.min_decimal_count) != "undefined") {
                AllureCurrencyManagerJsConfig.min_decimal_count = parseInt(AllureCurrencyManagerJsConfig.min_decimal_count);
                if (AllureCurrencyManagerJsConfig.min_decimal_count < format.precision) {
                    for (var testPrecision = AllureCurrencyManagerJsConfig.min_decimal_count;
                         testPrecision < format.precision; testPrecision++) {
                        //abs for 0.00199999999 or 1.1000000000001 fix
                        if (Math.abs(Math.round(price * Math.pow(10, testPrecision))
                            - price * Math.pow(10, testPrecision)) < 0.0000001) {
                            format.precision = testPrecision;
                            format.requiredPrecision = testPrecision;
                            break;
                        }
                    }
                }
            }
        }


        return formatCurrencyAllure(price, format, showPlus);

    };


    function formatCurrencyAllure(price, format, showPlus) {
        var precision = isNaN(format.precision = (format.precision)) ? 2 : format.precision;
        var requiredPrecision = isNaN(format.requiredPrecision = (format.requiredPrecision)) ? 2 : format.requiredPrecision;

        //precision = (precision > requiredPrecision) ? precision : requiredPrecision;
        //for now we don't need this difference so precision is requiredPrecision
        precision = requiredPrecision;

        var integerRequired = isNaN(format.integerRequired = Math.abs(format.integerRequired)) ? 1 : format.integerRequired;

        var decimalSymbol = format.decimalSymbol == undefined ? "," : format.decimalSymbol;
        var groupSymbol = format.groupSymbol == undefined ? "." : format.groupSymbol;
        var groupLength = format.groupLength == undefined ? 3 : format.groupLength;

        var s = '';

        if (showPlus == undefined || showPlus == true) {
            s = price < 0 ? "-" : ( showPlus ? "+" : "");
        } else if (showPlus == false) {
            s = '';
        }

        /**
         * replace(/-/, 0) is only for fixing Safari, Chrome, IE bug which appears
         * when Math.abs(0).toFixed() executed on "0" number.
         * Result is "0.-0" :(
         */
        if (precision < 0) {
            precision = 0;
        }

        var i = parseInt(price = Math.abs(+price || 0).toFixed(precision)) + "";
        var pad = (i.length < integerRequired) ? (integerRequired - i.length) : 0;
        while (pad) {
            i = '0' + i;
            pad--;
        }
        j = (j = i.length) > groupLength ? j % groupLength : 0;
        re = new RegExp("(\\d{" + groupLength + "})(?=\\d)", "g");

        var r = (j ? i.substr(0, j) + groupSymbol : "") + i.substr(j).replace(re, "$1" + groupSymbol) + (precision ? decimalSymbol + Math.abs(price - i).toFixed(precision).replace(/-/, 0).slice(2) : "")
        var pattern = '';
        if (format.pattern.indexOf('{sign}') == -1) {
            pattern = s + format.pattern;
        } else {
            pattern = format.pattern.replace('{sign}', s);
        }

        return pattern.replace('%s', r).replace(/^\s\s*/, '').replace(/\s\s*$/, '');
    }


}
catch (e) {
    //do nothing
}


