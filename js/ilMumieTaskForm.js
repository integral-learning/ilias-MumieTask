(function ($) {
    var addServerButton = document.getElementById("id_add_server_button");
    var serverDropDown = document.getElementById("xmum_server");
    var courseDropDown = document.getElementById("xmum_course");
    var languageDropDown = document.getElementById("xmum_language");
    var taskDropDown = document.getElementById("xmum_task");
    var nameElem = document.getElementById("id_name");
    var coursefileElem = document.getElementsByName("xmum_coursefile")[0];
    var missingConfig = document.getElementsByName("xmum_missing_config")[0];
    var server_data;

    
    $(document).ready(function () {
        server_data = JSON.parse(document.getElementById('server_data').getAttribute('value'));
        serverController.init();
        languageController.init();
        languageController.setLanguageOptions();
        //updateLanguageDropDownOptions();
    })


    function updateLanguageDropDownOptions() {
    }

    var serverController = (function () {
        var serverDropDown;

        return{
            init: function() {
                serverDropDown = document.getElementById("xmum_server");
            },
            getSelectedServerName : function() {
                return serverDropDown.options[serverDropDown.selectedIndex].text
            },
            getSelectedServer: function() {
                return server_data[serverDropDown.selectedIndex];
            }
        }
    })();

    var languageController = (function() {
        var languageDropDown;


        return {
            init: function() {
                languageDropDown = document.getElementById("xmum_language");
            },
            getAvailableLanguages: function() {
                //alert(JSON.stringify())
                return serverController.getSelectedServer()["languages"]
            },
            setLanguageOptions: function(){
                var availableLangs = this.getAvailableLanguages();
                for(var i =0; i<languageDropDown.options.length; i++) {
                    var option = languageDropDown.options[i];
                    
                    if(!availableLangs.includes(option.getAttribute('value'))) {
                        console.log("langs dont include " + option.getAttribute('value'));
                        option.setAttribute("disabled", true);
                    } else {
                        console.log("langs DO include " + option.getAttribute('value'));

                        option.setAttribute("disabled", false);
                        
                    }
                    
                }
            }
        }
    })();
})(jQuery)