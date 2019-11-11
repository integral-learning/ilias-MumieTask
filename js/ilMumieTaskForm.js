(function ($) {
    var addServerButton = document.getElementById("id_add_server_button");
    var nameElem = document.getElementById("id_name");
    var missingConfig = document.getElementsByName("xmum_missing_config")[0];
    var server_data;


    $(document).ready(function () {
        server_data = JSON.parse(document.getElementById('server_data').getAttribute('value'));
        console.log(server_data);
        init();

        serverController.setOnclickListeners();
        languageController.setOnclickListeners();
        courseController.setOnclickListeners();
        taskController.setOnclickListeners();
    });

    function init() {
        serverController.init();
        languageController.init();
        courseController.init();
        taskController.init();
        filterController.init();
        taskController.setTaskOptions();
        taskController.updateDefaultName();
    }

    function removeAllChildElements(elem) {
        while (elem.firstChild) {
            elem.removeChild(elem.firstChild);
        }
    }

    var serverController = (function () {
        var serverDropDown;

        return {
            init: function () {
                serverDropDown = document.getElementById("xmum_server");
            },
            setOnclickListeners: function () {
                serverDropDown.addEventListener('change', function(){
                    languageController.setLanguageOptions();
                    courseController.setCourseOptions();
                    filterController.resetFilters();
                    taskController.setTaskOptions();
                    taskController.updateDefaultName();
                })
            },
            getSelectedServerName: function () {
                return serverDropDown.options[serverDropDown.selectedIndex].text
            },
            getSelectedServer: function () {
                return server_data[serverDropDown.selectedIndex];
            }
        }
    })();

    var languageController = (function () {
        var languageDropDown;
        var selectedLang;



        function createOption(language) {
            var option = document.createElement('option');
            option.setAttribute('value', language);
            option.text = language;
            return option;
        }

        function getAvailableLanguages() {
            return serverController.getSelectedServer()["languages"]
        }

        function isSelectedLanguage(lang) {
            return lang == selectedLang;

        }
        return {
            init: function () {
                languageDropDown = document.getElementById("xmum_language");
                this.setLanguageOptions();
            },
            setOnclickListeners: function () {
                languageDropDown.addEventListener('change', function() {
                    courseController.setCourseOptions()
                    taskController.setTaskOptions();
                    taskController.updateDefaultName();
                });
            },
            getSelectedLanguage: function () {
                return languageDropDown.options[languageDropDown.selectedIndex].getAttribute('value');
            },

            setLanguageOptions: function () {
                var availableLangs = getAvailableLanguages();
                selectedLang = this.getSelectedLanguage();
                removeAllChildElements(languageDropDown);

                for (var i = 0; i < availableLangs.length; i++) {
                    lang = availableLangs[i]
                    languageDropDown.appendChild(createOption(lang));
                    if (isSelectedLanguage(lang)) {
                        languageDropDown.selectedIndex = i;
                    }

                }
            }
        }
    })();

    var courseController = (function () {
        var courseDropDown;
        var courseFileElem;
        var selectedCourse;

        function getAvailableCourses() {
            var availableCourses = [];
            var potentialCourses = serverController.getSelectedServer()['courses'];
            var selectedLang = languageController.getSelectedLanguage();
            for (var i = 0; i < potentialCourses.length; i++) {
                var potentialCourse = potentialCourses[i];
                if (potentialCourse.languages.includes(selectedLang)) {
                    availableCourses.push(potentialCourse);
                }
            }
            return availableCourses;
        }

        function getSelectedCourse() {
            var potentialCourses = serverController.getSelectedServer().courses;
            return potentialCourses[courseDropDown.selectedIndex];
        }

        function createCourseOption(course) {
            var option = document.createElement('option');
            option.setAttribute('value', course.name);
            option.text = course.name;
            return option;
        }

        function isSelectedCourse(course) {
            return selectedCourse && selectedCourse.pathToCourseFile == course.pathToCourseFile;
        }

        function selectCourseOption(course, selectedIndex) {
            courseDropDown.selectedIndex = selectedIndex;
            setCoursefile(course)
        }

        function setCoursefile(course) {
            courseFileElem.setAttribute('value', course.pathToCourseFile);
        }
        return {
            init: function () {
                courseDropDown = document.getElementById('xmum_course');
                courseFileElem = document.getElementById('xmum_coursefile');
                this.setCourseOptions();
            },
            setOnclickListeners: function () {
                courseDropDown.addEventListener('change', function () {
                    setCoursefile(courseController.getSelectedCourse());
                    taskController.setTaskOptions();
                    filterController.resetFilters();
                })
            },
            setCourseOptions: function () {
                var availableCourses = getAvailableCourses();
                selectedCourse = getSelectedCourse();
                courseDropDown.selectedIndex = 0;
                removeAllChildElements(courseDropDown);
                for (var i = 0; i < availableCourses.length; i++) {
                    var course = availableCourses[i];
                    courseDropDown.appendChild(createCourseOption(course))
                    if (isSelectedCourse(course)) {
                        selectCourseOption(course, i);
                    }
                }
                if(!selectedCourse){
                    selectCourseOption(availableCourses[0], 0);
                }
            },
            getSelectedCourse: getSelectedCourse
        }
    })();

    var taskController = (function() {
        var taskDropDown;
        var selectedTask;
        var taskCount;
        var titleElem;

        function getAvailableTasks() {
            var availableTasks = [];
            var potentialTasks = courseController.getSelectedCourse().tasks;
            var selectedLang = languageController.getSelectedLanguage();

            for(var i= 0; i < potentialTasks.length; i++) {
                var potentialTask = potentialTasks[i];
                if(potentialTask.languages.includes(selectedLang)) {
                    availableTasks.push(potentialTask);
                }
            }
            return availableTasks;
        }

        function isDefaultTaskName(name) {
            if(name == null || name == "") {
                return true;
            }
            return server_data
                .flatMap(server => server.courses)
                .flatMap(course => course.tasks)
                .flatMap(task => task.headline)
                .map(headline => headline ? headline.name : "null headline")
                .includes(name);            
        }
        function getSelectedTask() {
            var availableTasks = getAvailableTasks();

            for(var i = 0; i < availableTasks.length; i++) {
                var task = availableTasks[i];
                if(taskDropDown.options[taskDropDown.selectedIndex] && task.link == taskDropDown.options[taskDropDown.selectedIndex].getAttribute('value')) {
                    return task;
                }
            }
        }

        function createTaskOption(task) {
            var option = document.createElement('option');
            option.setAttribute('value', task.link);
            option.text = getHeadlineForLang(task, languageController.getSelectedLanguage());
            return option;
        }

        function getHeadlineForLang(task, lang) {
            var headlines = task.headline;
            for(var i = 0; i< headlines.length; i++) {
                var headline = headlines[i];
                if(headline.language == lang) {
                    return headline.name;
                }
            }
        }

        function isSelectedTask(task) {
            return selectedTask && selectedTask.link == task.link;
        }

        function setTaskCount(count) {
            taskCount.innerHTML = count;
        }

        function updateDefaultName() {
            if(isDefaultTaskName(titleElem.value)) {
                var task = getSelectedTask();
                var lang = languageController.getSelectedLanguage();
                titleElem.value = getHeadlineForLang(task, lang);
            }
        }
        return {
            init: function() {
                taskDropDown = document.getElementById('xmum_task');
                taskCount = document.getElementById('xmum_task_count');
                titleElem = document.getElementById('title');
            },

            setOnclickListeners: function() {
                taskDropDown.addEventListener('change', function() {
                    updateDefaultName();
                })
            },
            setTaskOptions: function() {
                var filteredTasks = filterController.getFilteredTasks();
                selectedTask = getSelectedTask();
                taskDropDown.selectedIndex = 0;
                removeAllChildElements(taskDropDown);

                for(var i = 0; i < filteredTasks.length; i++) {
                    
                    var task = filteredTasks[i];
                    taskDropDown.appendChild(createTaskOption(task))
                    if(isSelectedTask(task)) {
                        taskDropDown.selectedIndex = i;
                    }

                }
                setTaskCount(filteredTasks.length);

            },
            getAvailableTasks: getAvailableTasks,
            updateDefaultName: updateDefaultName
        }

    })();

    var filterController = (function() {
        var keyElements = [];
        var selectedValues;
        var parentEl;
        var valueElements = [];


        function getKeyValuePairs(){
            var course = courseController.getSelectedCourse();
            var tasks = course['tasks'];
            var keys = [];
            course['keys'].forEach( key => {
                var k = {};
                var values = [];
                tasks.forEach( task => {
                    task.tags.forEach(tag => {
                        if (tag.name === key){
                            k.key = key;
                            values = values.concat(tag.values);
                            k.values = values.filter((v,i) => values.indexOf(v) === i ); //remove duplicates
                        }
                    })
                });
                if (k.values) keys.push(k);
            });
            return keys;
        }

        function createTagOption(tag, id, checked, showCount) {
            var option = document.createElement('input');
            option.setAttribute('type', 'checkbox');
            option.setAttribute('name', "xmum_filter[]");
            option.setAttribute('id', id);
            option.setAttribute('value', tag);
            option.checked = checked;

            var label = document.createElement('label');
            label.setAttribute('for', id);
            label.textContent = tag + (checked || !showCount ? "" : ' (' + getFilteredCount(tag, selectedValues) + ')');

            var wrapper = document.createElement('div');
            wrapper.style = 'white-space:nowrap';
            wrapper.appendChild(option);
            wrapper.appendChild(label);

            return wrapper;
        }

        function getFilteredCount(tag,values) {
            var tags = values ? [tag, ...values] : [tag];
            return getFilteredTasks(tags).length;
        }

        function updateFilterCount(){
            selectedValues = getSelectedValues();
            valueElements.forEach( keyEl => {
                for(var i = 0 ; i < keyEl.children.length ; i++){
                    var container = keyEl.children[i];
                    var value = $(keyEl.children[i].firstElementChild).val();
                    var label = $(container).find("label");
                    label.html(value + ' (' + getFilteredCount(value,selectedValues) + ')');
                }
            });
        }

        function filterTask(task, values) {
            var taskValues = [];
            task.tags.forEach( tag => { /*if(keys.includes(tag.key))*/ taskValues = taskValues.concat(tag.values)});
            return values.every(function(value) {
                return taskValues.includes(value);
            });
        }

        function getFilteredTasks(values) {
            var availableTasks = taskController.getAvailableTasks();
            var filteredTasks = [];
            for(var i = 0; i < availableTasks.length; i++){
                var task = availableTasks[i];
                if(filterTask(task, values)) {
                    filteredTasks.push(task);
                }
            }
            return filteredTasks;
        }

        function getSelectedValues() {
            var values = [];
            valueElements.forEach( keyEl => {
                for(var i = 0 ; i < keyEl.children.length ; i++){
                    var input = keyEl.children[i].firstElementChild;
                    if(input.checked){
                        values.push(input.getAttribute("value"));
                    }
                }
            });
            return values;
        }

        function getInputId(value,index) {
            return 'xmum_filter_value_' + index.toString() + value;
        }

        function fillOptionsWithValues (element, values){
            values.forEach( (val,i) => {
                var option = document.createElement('option');
                option.setAttribute('value', val);
                option.text = val;
                element.appendChild(createTagOption(val,getInputId(val,i),false,true));
            }  );

            var childCount = element.children.length;
            var height = Number.parseFloat(element.style.width.replace("px",""));
            var width = Number.parseFloat(element.style.width.replace("px",""));
            element.style.width = (2 * width).toString()+'px';
            if (childCount > 4) element.style.height = (height * 1.5).toString()+'px';
            // set event listeners on children
            for(var i = 0; i < childCount ; i++){
                var child = $(element.children[i]);
                child.change(function() {
                    taskController.setTaskOptions();
                    taskController.updateDefaultName();
                    updateFilterCount();
                })
            }
        }

        function createKeyElement(keyEl,valueEl,key){
            keyEl.setAttribute("for",key);
            keyEl.innerHTML = "Filter by " + key;
            keyEl.style.cursor = "pointer";
            $(keyEl).hover(
                function() {
                    this.style.backgroundColor = "#ccc";

                }, function() {
                    this.style.backgroundColor = 'inherit';
                }
            );

            $(keyEl).click( function () {
                if (valueEl.style.display === "block") {
                    valueEl.style.display = "none";
                } else {
                    valueEl.style.display = "block";
                }
            });
        }

        function addKeyValueCheckboxes(tag){
            var clone = parentEl.cloneNode(true);
            clone.setAttribute("id","il_prop_cont_xmum_key_"+tag.key);
            parentEl.parentElement.insertBefore(clone,parentEl);
            var keyBox = clone.children[0];
            var valuesBox = clone.children[1].firstElementChild;
            createKeyElement(keyBox,valuesBox,tag.key);
            valuesBox.setAttribute("id","xmum_key_" + tag.key);
            fillOptionsWithValues(valuesBox,tag.values);
            valueElements.push(valuesBox);
            keyElements.push(clone);
        }

        function makeParentCollapsible(keys){
            var title = $(".ilFormHeader")[1];

            $(title).hover(
                function() {
                    this.style.backgroundColor = "#ccc";
                }, function() {
                    this.style.backgroundColor = '#f0f0f0'; //copied from ilias' style
                }
            );

            $(title).click( function() {
                keys.forEach( keyEl => {
                    if (keyEl.style.display === "block") {
                        keyEl.style.display = "none";
                    } else {
                        keyEl.style.display = "block";
                    }
                });

            });
        }

        return {
            init: function() {
                parentEl = document.getElementById("il_prop_cont_xmum_values");
                this.setFilterOptions();
            },
            setFilterOptions: function() {
                var tags = getKeyValuePairs();
                selectedValues = getSelectedValues();
                for(var i = 0; i < tags.length; i++) {
                    var tag  = tags[i];
                    addKeyValueCheckboxes(tag);
                }
                parentEl.style.display = "none";
                // hideEmptyFilter(tags.length < 1);
                makeParentCollapsible(keyElements);
            },
            getFilteredTasks: function() {
                return getFilteredTasks(getSelectedValues());
            },
            resetFilters: function () {
                keyElements.forEach( el => el.remove());
                keyElements = [];
                valueElements.forEach(el => el.remove());
                valueElements = [];
                parentEl.style.display = "block";
                this.setFilterOptions();
            }

        }
    })();

})(jQuery)