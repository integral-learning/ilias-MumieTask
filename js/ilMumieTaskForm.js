/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @author      Nicolas Zunker (nicolas.zunker@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

(function ($) {
    var server_data;


    $(document).ready(function () {
        server_data = JSON.parse(document.getElementById('server_data').getAttribute('value'));
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
                    courseController.setCourseOptions();
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
            return selectedCourse && selectedCourse.path_to_course_file == course.path_to_course_file;
        }

        function selectCourseOption(course, selectedIndex) {
            courseDropDown.selectedIndex = selectedIndex;
            setCoursefile(course)
        }

        function setCoursefile(course) {
            courseFileElem.setAttribute('value', course.path_to_course_file);
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

        var DUMMY_TITLE = "-- Empty MumieTask --";

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
            if(name == null || name == ""|| name == DUMMY_TITLE) {
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
            return selectedTask && task && selectedTask.link == task.link;
        }

        function setTaskCount(count) {
            taskCount.innerHTML = count;
        }

        function updateDefaultName() {
            if(isDefaultTaskName(titleElem.value)) {
                var task = getSelectedTask();
                var lang = languageController.getSelectedLanguage();
                titleElem.value = task ? getHeadlineForLang(task, lang) : titleElem.value;
            }
        }

        function isDummyTask() {
            return titleElem.value === DUMMY_TITLE;
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
                if(isDummyTask()){
                    taskDropDown.selectedIndex = -1;
                }

                setTaskCount(filteredTasks.length);

            },
            getAvailableTasks: getAvailableTasks,
            updateDefaultName: updateDefaultName
        }

    })();

    var filterController = (function() {
        var filterElements = [];
        var filterWrapperTemplate;
        var valueBoxes = [];


        function getNameValuePairs(){
            var course = courseController.getSelectedCourse();
            var tasks = course['tasks'];
            var nameValuePairs = [];
            course['tag_names'].forEach( name => {
                var tagObj = {};
                var values = [];
                tasks.forEach( task => {
                    task.tags.forEach(tag => {
                        if (tag.name === name){
                            tagObj.name = name;
                            values = values.concat(tag.values);
                            tagObj.values = values.filter((v,i) => values.indexOf(v) === i ); //remove duplicates
                        }
                    })
                });
                if (tagObj.values) nameValuePairs.push(tagObj);
            });
            return nameValuePairs;
        }

        function createTagOption(value, id, name) {
            var option = document.createElement('input');
            option.setAttribute('type', 'checkbox');
            option.setAttribute('name', "xmum_filter[]");
            option.setAttribute('id', id);
            option.setAttribute('value', value);

            var label = document.createElement('label');
            label.style.paddingLeft = "5px";
            label.setAttribute('for', id);
            label.textContent = value + ' (' + getFilteredCount({name,value}, getSelections()) + ')';

            var wrapper = document.createElement('div');
            wrapper.style = 'white-space:nowrap';
            wrapper.appendChild(option);
            wrapper.appendChild(label);

            return wrapper;
        }

        function getFilteredCount(tag,selections) {
            if(selections[tag.name]) selections[tag.name].push(tag.value);
            else selections[tag.name] = [tag.value];
            return getFilteredTasks(selections).length;
        }

        function updateFilterCount(){
            var selections = getSelections();
            valueBoxes.forEach( valueBox => {
                for(var i = 0 ; i < valueBox.children.length ; i++){
                    var wrapper = valueBox.children[i];
                    var name = valueBox.parentElement.previousElementSibling.getAttribute("for");
                    var value = valueBox.children[i].firstElementChild.getAttribute("value");
                    var label = $(wrapper).find("label");
                    var tag = {};
                    tag.name = name;
                    tag.value = value;
                    var clone = JSON.parse(JSON.stringify(selections)); // deep clone
                    var count = getFilteredCount(tag,clone);
                    if($(wrapper).find("input")[0].checked) {
                        label.html(value);
                    } else {
                        label.html(value + ' (' + count + ')');
                    }
                    wrapper.firstElementChild.disabled = !count;
                }
            });
        }

        function filterTask(task, selections) {
            var obj = {};
            task.tags.forEach(tag => {
                obj[tag.name] = tag.values;
            });
            for (var key in selections){
               if(!obj[key]) return false;
               if(!haveCommonEntry(obj[key],selections[key])) return false;
            }
            return true;
        }

        function getFilteredTasks(selections) {
            var availableTasks = taskController.getAvailableTasks();
            var filteredTasks = [];
            for(var i = 0; i < availableTasks.length; i++){
                var task = availableTasks[i];
                if(filterTask(task, selections)) {
                    filteredTasks.push(task);
                }
            }
            return filteredTasks;
        }

        function haveCommonEntry (array1,array2) {
            if(!Array.isArray(array1) || !Array.isArray(array2)) return false;
            for(var i = 0 ; i < array1.length ; i++){
                if(array2.includes(array1[i])) return true;
            }
            return false;
        }

        function getSelections() {
            var selectedNamesAndValuesMap = {};
            valueBoxes.forEach( optionWrapper => {
                for(var i = 0 ; i < optionWrapper.children.length ; i++){
                    var input = optionWrapper.children[i].firstElementChild;
                    if(input.checked){
                        var name = optionWrapper.parentElement.previousElementSibling.getAttribute("for");
                        var val = input.getAttribute("value");
                        if(selectedNamesAndValuesMap[name]) {
                            selectedNamesAndValuesMap[name].push(val);
                        } else selectedNamesAndValuesMap[name] = [val];
                    }
                }
            });
            return selectedNamesAndValuesMap;
        }

        function getInputId(value,index) {
            return 'xmum_filter_value_' + index.toString() + value;
        }

        function addFilterOptions (valueElement, tag){
            var values = tag.values;
            values.forEach( (val,i) => {
                var option = document.createElement('option');
                option.paddingRight = "2";
                option.setAttribute('value', val);
                option.text = val;
                valueElement.appendChild(createTagOption(val,getInputId(val,i),tag.name));
            }  );

            var childCount = valueElement.children.length;
            var height = Number.parseFloat(valueElement.style.width.replace("px",""));
            var width = Number.parseFloat(valueElement.style.width.replace("px",""));
            valueElement.style.width = (2 * width).toString()+'px';
            if(childCount > 4) valueElement.style.height = (height * 1.5).toString()+'px';
            // set event listeners on children
            for(var i = 0; i < childCount ; i++){
                var child = $(valueElement.children[i]);
                child.change(function() {
                    taskController.setTaskOptions();
                    taskController.updateDefaultName();
                    updateFilterCount();
                })
            }
        }

        function setFilterLabelElement(element,filterOptionWrapper,name){
            element.setAttribute("for",name);
            element.innerHTML = "Filter by " + name;
            element.style.cursor = "pointer";
            $(element).hover(
                function() {
                    this.style.backgroundColor = "#ccc";

                }, function() {
                    this.style.backgroundColor = 'inherit';
                }
            );

            $(element).click( function () {
                if (filterOptionWrapper.style.display === "block") {
                    filterOptionWrapper.style.display = "none";
                } else {
                    filterOptionWrapper.style.display = "block";
                }
            });
        }

        function addFilterElem(tag){
            var clone = filterWrapperTemplate.cloneNode(true);
            clone.style.display = "block";
            clone.setAttribute("id","il_prop_cont_xmum_name_"+tag.name);
            filterWrapperTemplate.parentElement.insertBefore(clone,filterWrapperTemplate);
            var filterLabel = clone.children[0];
            var filterOptionWrapper = clone.children[1].firstElementChild;
            setFilterLabelElement(filterLabel,filterOptionWrapper,tag.name);
            filterOptionWrapper.setAttribute("id","xmum_name_" + tag.name);
            addFilterOptions(filterOptionWrapper,tag);
            valueBoxes.push(filterOptionWrapper);
            filterElements.push(clone);
        }

        function hideEmptyFilters(hide){
            var title = $(".ilFormHeader")[1];
            $(title).css("display", hide ? "none" : "block");
        }

        function makeParentCollapsible(names){
            var title = $(".ilFormHeader")[1];
            $(title).css("cursor","pointer");
            $(title).hover(
                function() {
                    this.style.backgroundColor = "#ccc";
                }, function() {
                    this.style.backgroundColor = '#f0f0f0'; // original color
                }
            );

            $(title).click( function() {
                names.forEach( name => {
                    if (name.style.display === "block") {
                        name.style.display = "none";
                    } else {
                        name.style.display = "block";
                    }
                });

            });
        }

        return {
            init: function() {
                filterWrapperTemplate = document.getElementById("il_prop_cont_xmum_values");
                filterWrapperTemplate.style.display = "none";
                this.setFilterOptions();
            },
            setFilterOptions: function() {
                var tags = getNameValuePairs();
                for(var i = 0; i < tags.length; i++) {
                    var tag  = tags[i];
                    addFilterElem(tag);
                }
                hideEmptyFilters(tags.length < 1);
                makeParentCollapsible(filterElements);
            },
            getFilteredTasks: function() {
                return getFilteredTasks(getSelections());
            },
            resetFilters: function () {
                filterElements.forEach( el => el.remove());
                filterElements = [];
                valueBoxes.forEach(el => el.remove());
                valueBoxes = [];
                this.setFilterOptions();
            }

        }
    })();

})(jQuery)