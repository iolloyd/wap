(function() {
	// Browser compatibility check
    if (window.addEventListener) {
		window.addEventListener("load", init, false)
	} else if (window.attachEvent) { 
		window.attachEvent("onload", init)
	}

    function init() {
        var patterns = {
            "email" : "^[a-zA-Z0-9_\\.\\-]+\\@(([a-zA-Z0-9\\-])+\\.)+([a-zA-Z0-9]{2,4})+$",
            "digits": "^\\d+$",
            "word"  : "\\w+$", 
        };

		// Loop for each form
        for(var i = 0; i < document.forms.length; i++){
            var needsValidation = false;
            var f = document.forms[i];

			// Loop for each member of form being checked
            for(j = 0; j < f.elements.length; j++) {
                var e = f.elements[j];
                if (e.type != "text") {
					continue;
				}
                var pattern  = e.getAttribute("pattern");
                var required = e.getAttribute("required") != null;

                if (required && !pattern){
                    pattern = "\\S";
                    e.setAttribute("pattern", pattern);
                }

                if (pattern == 'digits' || pattern == 'email' || pattern == 'word') {
                    pattern = patterns[pattern];
                    e.setAttribute('pattern', pattern);
                }

                if (pattern){
                    e.onchange = validateOnChange;
                    needsValidation = true;
                }
            }
        	if (needsValidation) f.onsubmit = validateOnSubmit;
		}
    }

	// This is a callback for anything that has a pattern for
	// its validation.
	function validateOnChange(){
        var textfield       = this;
        var value           = this.value;
        textfield.className = (value.search(pattern) == -1) ? "invalid" : "valid";
    }

	// This is called when whe submit the form if something needs validating
	// in the form.
    function validateOnSubmit( ){
        var invalid = false
        for(var i = 0; i < this.elements.length; i++){
            var e = this.elements[i];
            if (e.type == "text" && e.onchange == validateOnChange){
                e.onchange(); 
                if (e.className == "invalid") invalid = true;
            }
        }
        if (invalid){
            alert("The form is incorrectly filled out.\n" +
                  "Please correct the highlighted fields and try again.");
            return false;
        }
    }
 })( ) 
