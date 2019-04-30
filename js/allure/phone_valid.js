//<![CDATA[
if(Validation) {
    Validation.addAllThese([
        ['validate-mobileno','Enter Valid Phone Number',
            function(v){
        // console.log('VALUE'+v);
                if(v.length > 5){
                            return true;

                }else {
                    return false;
                }

            }
        ]])
};

if (document.getElementsByClassName("validate-mobileno").length) {
    var dataForm = new VarienForm('form-validate', true);
}
//]]>
