(function ($) {
    var addServerButton = document.getElementById("id_add_server_button");
    var nameElem = document.getElementById("id_name");
    var missingConfig = document.getElementsByName("xmum_missing_config")[0];
    var server_data;


    $(document).ready(function () {
        server_data = JSON.parse(document.getElementById('server_data').getAttribute('value'));
        init();

        serverController.setOnclickListeners();
        languageController.setOnclickListeners();
        courseController.setOnclickListeners();
        taskController.setOnclickListeners();
    })

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
                    filterController.setFilterOptions();
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
            for(var i = 0; i < potentialCourses.length; i++) {
                var course = potentialCourses[i]
                if(course.pathToCourseFile == courseFileElem.getAttribute('value')){
                    return course;
                }
            }
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
                    filterController.setFilterOptions();
                })
            },
            setCourseOptions: function () {
                var availableCourses = getAvailableCourses();
                selectedCourse = getSelectedCourse()
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
                .map(headline => headline.name)
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
            var headlines = task.headline
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
        var filterElem;
        var selectedTags;
        function getAvailableTags() {
            return courseController.getSelectedCourse()['tags'];
        }

        function createTagOption(tag, id, checked) {
            var option = document.createElement('input');
            option.setAttribute('type', 'checkbox');
            option.setAttribute('name', "xmum_filter[]");
            option.setAttribute('value', tag);
            option.setAttribute('id', id);
            option.checked = checked;

            var label = document.createElement('label');
            label.setAttribute('for', id);
            label.textContent = tag + (checked ? "" : ' (' + getFilteredCount(tag) + ')');

            var wrapper = document.createElement('div');
            wrapper.style = 'white-space:nowrap';
            wrapper.appendChild(option);
            wrapper.appendChild(label);

            return wrapper;
        }

        function getFilteredCount(tag) {
            var tags = [tag, ...selectedTags];
            return getFilteredTasks(tags).length;
        }

        function filterTask(task, selectedTags) {
            return selectedTags.every(function(tag) {
                return task.tags.includes(tag);
            });
        }

        function getFilteredTasks(tags) {
            var availableTasks = taskController.getAvailableTasks();
            var filteredTasks = []
            for(var i = 0; i < availableTasks.length; i++){
                var task = availableTasks[i];
                if(filterTask(task, tags)) {
                    filteredTasks.push(task);
                }
            }
            return filteredTasks;
        }


        function getSelectedTags() {
            var selectedTags = [];

            for(var i = 0; i < filterElem.children.length; i++) {
                var input =  filterElem.children[i].children[0];
                if(input.checked) {
                    selectedTags.push(input.getAttribute('value'));
                }
            }

            return selectedTags;
        }

        function getInputId(index) {
            return 'xmum_filter_'+index;
        }

        function setEventListener(id) {
            $('#' + id).change(function() {
                taskController.setTaskOptions();
                filterController.setFilterOptions();
                taskController.updateDefaultName();
            })
        }
        
        function hideEmptyFilter(hide) {
            wrapper = document.getElementById('il_prop_cont_xmum_filter');
            if(hide) {
                wrapper.style = 'display:none';
            } else {
                wrapper.style = 'display:block';
            }
        }
        return {
            init: function() {
                filterElem = document.getElementById("xmum_filter");
                optionWrapper = filterElem.children[0];
                this.setFilterOptions();
            },
            setFilterOptions: function() {
                var availableTags = getAvailableTags();
                selectedTags = getSelectedTags();
                removeAllChildElements(filterElem);

                for(var i = 0; i < availableTags.length; i++) {
                    var tag = availableTags[i]
                    var tagOption = createTagOption(tag, getInputId(i), selectedTags.includes(tag));

                    filterElem.appendChild(tagOption);
                    setEventListener(getInputId(i));
                }

                hideEmptyFilter(availableTags.length < 1);
            },
            getFilteredTasks: function() {
                return getFilteredTasks(getSelectedTags());
            }

        }
    })();

})(jQuery)