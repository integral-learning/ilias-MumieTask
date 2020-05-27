/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @author      Nicolas Zunker (nicolas.zunker@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

(function ($) {


    $(document).ready(function () {
        var structure = JSON.parse(document.getElementById('server_data').getAttribute('value'));

        var serverController = (function () {
            var serverStructure;
            var serverDropDown = document.getElementById("xmum_server");;

            return {
                init: function (structure) {
                    serverStructure = structure;
                    //serverDropDown = document.getElementById("xmum_server");
                    serverDropDown.onchange = function () {
                        courseController.updateOptions();
                        langController.updateOptions();
                        filterController.updateOptions();
                        taskController.updateOptions();
                    };
                },
                getSelectedServer: function () {
                    var selectedServerName = serverDropDown.options[serverDropDown.selectedIndex].text;

                    for (var i in serverStructure) {
                        var server = serverStructure[i];
                        if (server.name == selectedServerName) {
                            return server;
                        }
                    }
                    return null;
                },
                disable: function () {
                    serverDropDown.disabled = true;
                    removeChildElems(serverDropDown);
                },
                getAllServers: function () {
                    return serverStructure;
                }
            };
        })();

        var courseController = (function () {
            var courseDropDown = document.getElementById("xmum_course");
            var coursefileElem = document.getElementById('xmum_coursefile');

            /**
             * Add a new option the the 'MUMIE Course' drop down menu
             * @param {Object} course
             */
            function addOptionForCourse(course) {
                var optionCourse = document.createElement("option");
                var selectedLanguage = langController.getSelectedLanguage();
                var name;
                // If the currently selected server is not available on the server, we need to select another one.
                if (!course.languages.includes(selectedLanguage)) {
                    name = course.name[0];
                } else {
                    for (var i in course.name) {
                        if (course.name[i].language == selectedLanguage) {
                            name = course.name[i];
                        }
                    }
                }
                optionCourse.setAttribute("value", name.value);
                optionCourse.text = name.value;
                courseDropDown.append(optionCourse);
            }

            /**
             * Update the hidden input field with the selected course's course file path
             */
            function updateCoursefilePath() {
                coursefileElem.value = courseController.getSelectedCourse().coursefile;
            }

            return {
                init: function (isEdit) {
                    courseDropDown = document.getElementById("xmum_course");
                    coursefileElem = document.getElementById('xmum_coursefile');
                    courseDropDown.onchange = function () {
                        updateCoursefilePath();
                        langController.updateOptions();
                        filterController.updateOptions();
                        taskController.updateOptions();
                    };
                    courseController.updateOptions(isEdit ? coursefileElem.value : false);
                },
                getSelectedCourse: function () {
                    var selectedCourseName = courseDropDown.options[courseDropDown.selectedIndex].text;
                    var courses = serverController.getSelectedServer().courses;
                    for (var i in courses) {
                        var course = courses[i];
                        for (var j in course.name) {
                            if (course.name[j].value == selectedCourseName) {
                                return course;
                            }
                        }
                    }
                    return null;
                },
                disable: function () {
                    courseDropDown.disabled = true;
                    removeChildElems(courseDropDown);
                },
                updateOptions: function (selectedCourseFile) {
                    removeChildElems(courseDropDown);
                    courseDropDown.selectedIndex = 0;
                    var courses = serverController.getSelectedServer().courses;
                    for (var i in courses) {
                        var course = courses[i];
                        addOptionForCourse(course);
                        if (course.coursefile == selectedCourseFile) {
                            courseDropDown.selectedIndex = courseDropDown.childElementCount - 1;
                        }
                    }
                    updateCoursefilePath();
                }
            };
        })();

        var langController = (function () {
            var languageDropDown = document.getElementById("xmum_language");

            /**
             * Add a new option to the language drop down menu
             * @param {string} lang the language to add
             */
            function addLanguageOption(lang) {
                var optionLang = document.createElement("option");
                optionLang.setAttribute("value", lang);
                optionLang.text = lang;
                languageDropDown.append(optionLang);
            }
            return {
                init: function () {
                    languageDropDown = document.getElementById("xmum_language");
                    languageDropDown.onchange = function () {
                        taskController.updateOptions();
                        courseController.updateOptions();
                    };
                    langController.updateOptions();
                },
                getSelectedLanguage: function () {
                    return languageDropDown.options[languageDropDown.selectedIndex].text;
                },
                disable: function () {
                    languageDropDown.disabled = true;
                    removeChildElems(languageDropDown);
                },
                updateOptions: function () {
                    var currentLang = langController.getSelectedLanguage();
                    removeChildElems(languageDropDown);
                    languageDropDown.selectedIndex = 0;
                    var languages = courseController.getSelectedCourse().languages;
                    for (var i in languages) {
                        var lang = languages[i];
                        addLanguageOption(lang);
                        if (lang == currentLang) {
                            languageDropDown.selectedIndex = languageDropDown.childElementCount - 1;
                        }
                    }
                }
            };
        })();

        var taskController = (function () {
            var taskDropDown = document.getElementById("xmum_task");
            var taskCount = document.getElementById('xmum_task_count');
            var nameElem = document.getElementById("title");

            /**
             * Update the activity's name in the input field
             */
            function updateName() {
                if (!isCustomName()) {
                    nameElem.value = getHeadline(taskController.getSelectedTask());
                }
            }

            /**
             * Check whether the activity has a custom name
             *
             * @return {boolean} True, if there is no headline with that name in all tasks
             */
            function isCustomName() {
                if (nameElem.value.length == 0) {
                    return false;
                }
                return nameElem.value !== '-- Empty MumieTask --' && !getAllHeadlines().includes(nameElem.value);
            }

            /**
             * Get the task's headline for the currently selected language
             * @param {Object} task
             * @returns  {string} the headline
             */
            function getHeadline(task) {
                if (!task) {
                    return null;
                }
                for (var i in task.headline) {
                    var localHeadline = task.headline[i];
                    if (localHeadline.language == langController.getSelectedLanguage()) {
                        return localHeadline.name;
                    }
                }
                return null;
            }

            /**
             * Get all tasks that are available on all servers
             *
             * @return {Object} Array containing all available tasks
             */
            function getAllTasks() {
                var tasks = [];
                for (var i in serverController.getAllServers()) {
                    var server = serverController.getAllServers()[i];
                    for (var j in server.courses) {
                        var course = server.courses[j];
                        for (var m in course.tasks) {
                            var task = course.tasks[m];
                            tasks.push(task);
                        }
                    }
                }
                return tasks;
            }

            /**
             * Get all possible headlines in all languages
             * @returns {Object} Array containing all headlines
             */
            function getAllHeadlines() {
                var headlines = [];
                var tasks = getAllTasks();
                tasks.push(getPseudoTaskFromCourse(courseController.getSelectedCourse()));
                for (var i in tasks) {
                    var task = tasks[i];
                    for (var n in task.headline) {
                        headlines.push(task.headline[n].name);
                    }
                }
                var course = courseController.getSelectedCourse();
                for (var j in course.name) {
                    var name = course.name[j];
                    headlines.push(name.value);
                }
                return headlines;
            }

            /**
             * Add a new option to the 'Problem' drop down menu
             * @param {Object} task
             */
            function addTaskOption(task) {
                if (getHeadline(task) !== null) {
                    var optionTask = document.createElement("option");
                    optionTask.setAttribute("value", task.link);
                    optionTask.text = getHeadline(task);
                    taskDropDown.append(optionTask);
                }
            }

            /**
             * Get a task that links to a course's overview page
             * @param {Object} course
             * @returns {Object} task
             */
            function getPseudoTaskFromCourse(course) {
                var headline = [];
                for (var i in course.name) {
                    var name = course.name[i];
                    headline.push({
                        "name": name.value,
                        "language": name.language
                    });
                }
                return {
                    "link": course.link,
                    "headline": headline
                };
            }

            return {
                init: function (isEdit) {
                    taskDropDown = document.getElementById("xmum_task");
                    taskCount = document.getElementById('xmum_task_count');
                    nameElem = document.getElementById("title");
                    updateName();
                    taskDropDown.onchange = function () {
                        updateName();
                    };
                    taskController.updateOptions(isEdit ?
                        taskDropDown.options[taskDropDown.selectedIndex].getAttribute('value') : undefined
                    );
                },
                getSelectedTask: function () {
                    var selectedLink = taskDropDown.options[taskDropDown.selectedIndex] ==
                        undefined ? undefined : taskDropDown.options[taskDropDown.selectedIndex].getAttribute('value');
                    var course = courseController.getSelectedCourse();
                    var tasks = course.tasks.slice();
                    tasks.push(getPseudoTaskFromCourse(course));
                    for (var i in tasks) {
                        var task = tasks[i];
                        if (selectedLink == task.link) {
                            return task;
                        }
                    }
                    return null;
                },
                disable: function () {
                    taskDropDown.disabled = true;
                    removeChildElems(taskDropDown);
                },
                updateOptions: function (selectTaskByLink) {
                    removeChildElems(taskDropDown);
                    taskDropDown.selectedIndex = 0;

                    var tasks = filterController.filterTasks(courseController.getSelectedCourse().tasks);
                    for (var i in tasks) {
                        var task = tasks[i];
                        addTaskOption(task);
                        if (selectTaskByLink === task.link) {
                            taskDropDown.selectedIndex = taskDropDown.childElementCount - 1;
                        }
                    }

                    taskCount.textContent = tasks.length;
                    updateName();
                }
            };
        })();

        var filterController = (function () {

            var filterWrapper;
            var filterSectionHeader = $(".ilFormHeader")[1];

            var filterWrapperTemplate = document.getElementById("il_prop_cont_xmum_values");
            var filterLabelTemplate = filterWrapperTemplate.children[0];
            var selectionBoxTemplate = filterWrapperTemplate.children[1];


            var selectedTags = [];

            /**
             * Add a new filter category to the form for a given tag
             * @param {Object} tag
             */
            function addFilter(tag) {
                var filter = document.createElement('div');
                filter.classList.add(filterWrapperTemplate.classList);
                var selectionBox = createSelectionBox(tag);

                var label = document.createElement('label');
                label.innerHTML = tag.name +  ' <span class="caret"></span>';
                label.classList.add(...filterLabelTemplate.classList, 'mumie-collapsible');
                label.onclick = function () {
                    toggleVisibility(selectionBox);
                };
                filter.appendChild(label);
                filter.appendChild(selectionBox);
                filterWrapper.appendChild(filter);
            }

            /**
             * Create an element that contains checkboxes for all tag values
             * @param {Object} tag
             * @returns {Object} A div containing mulitple checkboxes
             */
            function createSelectionBox(tag) {
                var selectionBox = document.createElement('div');
                selectionBox.classList.add(selectionBoxTemplate.classList);
                var wrapper = document.createElement('div');
                wrapper.classList.add('input');

                for (var i in tag.values) {
                    selectedTags[tag.name] = [];
                    var inputWrapper = document.createElement('div');
                    inputWrapper.classList.add('mumie_input_wrapper');

                    var value = tag.values[i];
                    var checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.value = value;
                    setCheckboxListener(tag, checkbox);

                    var label = document.createElement('label');
                    label.innerText = value;
                    label.style = "padding-left: 5px";
                    inputWrapper.appendChild(checkbox);
                    inputWrapper.appendChild(label);
                    wrapper.insertBefore(inputWrapper, selectionBox.firstChild);
                }
                selectionBox.append(wrapper)
                return selectionBox;
            }

            /**
             * Selecting a tag value should filter the drop down menu for MUMIE problems for the chosen values
             * @param {*} tag The tag we created the checkbox for
             * @param {*} checkbox The checkbox containing the filter input checkboxes
             */
            function setCheckboxListener(tag, checkbox) {
                checkbox.onclick = function () {
                    if (!checkbox.checked) {
                        var update = [];
                        for (var i in selectedTags[tag.name]) {
                            var value = selectedTags[tag.name][i];
                            if (value != checkbox.value) {
                                update.push(value);
                            }
                        }
                        selectedTags[tag.name] = update;
                    } else {
                        selectedTags[tag.name].push(checkbox.value);
                    }
                    taskController.updateOptions();
                };
            }

            /**
             * Toggle visibility of the given object
             * @param {Object} elem
             */
            function toggleVisibility(elem) {
                elem.toggleAttribute('hidden');
            }

            /**
             * Filter a list of tasks
             * @param {Array} tasks the tasks to filter
             * @param {Array} filterSelection the selection to filter with
             * @returns {Array} the filtered tasks
             */
            function filterTasks(tasks, filterSelection) {
                var filteredTasks = [];
                for (var i in tasks) {
                    var task = tasks[i];
                    if (filterTask(task, filterSelection)) {
                        filteredTasks.push(task);
                    }
                }
                return filteredTasks;
            }

            /**
             * Check if the task fullfills the requirements set by the filter selection
             * @param {Object} task
             * @param {Array} filterSelection
             * @returns {boolean}
             */
            function filterTask(task, filterSelection) {
                var obj = [];
                for (var i in task.tags) {
                    var tag = task.tags[i];
                    obj[tag.name] = tag.values;
                }

                for (var j in Object.keys(filterSelection)) {
                    var tagName = Object.keys(filterSelection)[j];
                    if (filterSelection[tagName].length == 0) {
                        continue;
                    }
                    if (!obj[tagName]) {
                        return false;
                    }
                    if (!haveCommonEntry(filterSelection[tagName], obj[tagName])) {
                        return false;
                    }
                }
                return true;
            }

            /**
             * Return true, of the two arrays have at least one entry in common
             * @param {Array} array1
             * @param {Array} array2
             * @returns {boolean}
             */
            function haveCommonEntry(array1, array2) {
                if (!Array.isArray(array1) || !Array.isArray(array2)) {
                    return false;
                }
                for (var i = 0; i < array1.length; i++) {
                    if (array2.includes(array1[i])) {
                        return true;
                    }
                }
                return false;
            }

            function addFilterWrapper() {
                filterWrapper = document.createElement('div');
                filterWrapperTemplate.parentElement.insertBefore(filterWrapper, filterWrapperTemplate);
            }

            return {
                init: function () {
                    addFilterWrapper();
                    filterWrapperTemplate.style = "display: none";
                    filterSectionHeader.classList.add('mumie-collapsible')
                    this.updateOptions();
                    filterSectionHeader.onclick = function () {
                        toggleVisibility(filterWrapper);
                    };
                },
                updateOptions: function () {
                    var tags = courseController.getSelectedCourse().tags;
                    selectedTags = [];
                    if (tags.length > 0) {
                        filterWrapper.hidden = false;
                    } else {
                        filterWrapper.hidden = true;
                    }
                    removeChildElems(filterWrapper);
                    for (var i in tags) {
                        var tag = tags[i];
                        addFilter(tag);
                    }
                },
                filterTasks: function (tasks) {
                    return filterTasks(tasks, selectedTags);
                }
            };
        })();

        /**
         * Remove all child elements of a given html element
         * @param {Object} elem
         */
        function removeChildElems(elem) {
            while (elem.firstChild) {
                elem.removeChild(elem.firstChild);
            }
        }

        serverController.init(structure);
        courseController.init();
        taskController.init();
        langController.init();
        filterController.init();

    });
})(jQuery)