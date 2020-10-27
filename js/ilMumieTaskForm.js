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
        var lmsSelectorUrl = 'http://localhost:7070';

        var serverController = (function () {
            var serverStructure;
            var serverDropDown = document.getElementById("xmum_server");;

            return {
                init: function (structure) {
                    serverStructure = structure;
                    serverDropDown.onchange = function () {
                        courseController.updateOptions();
                        langController.updateOptions();
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
                coursefileElem.value = courseController.getSelectedCourse().path_to_course_file;
            }

            return {
                init: function (isEdit) {
                    courseDropDown = document.getElementById("xmum_course");
                    coursefileElem = document.getElementById('xmum_coursefile');
                    courseDropDown.onchange = function () {
                        updateCoursefilePath();
                        langController.updateOptions();
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
                updateOptions: function () {
                    var selectedCourseFile = coursefileElem.value;
                    removeChildElems(courseDropDown);
                    courseDropDown.selectedIndex = 0;
                    var courses = serverController.getSelectedServer().courses;
                    for (var i in courses) {
                        var course = courses[i];
                        addOptionForCourse(course);
                        if (course.path_to_course_file == selectedCourseFile) {
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
                },
                setLanguage: function(lang) {
                    for (var i in languageDropDown.options) {
                        var option = languageDropDown.options[i];
                        if (option.value == lang) {
                            languageDropDown.selectedIndex = i;
                            courseController.updateOptions();
                            return;
                        }
                    }
                    throw new Error("Selected language not available");
                }
            };
        })();

        var problemSelectorController = (function() {
            var problemSelectorButton = document.getElementById('xmum_prb_sel');
            var problemSelectorWindow;
            var mumieOrg = document.getElementById('mumie_org').value;

            /**
             * Send a message to the problem selector window.
             *
             * Don't do anything, if there is no problem selector window.
             * @param {Object} response
             */
            function sendResponse(response) {
                if (!problemSelectorWindow) {
                    return;
                }
                problemSelectorWindow.postMessage(JSON.stringify(response), lmsSelectorUrl);
            }

            /**
             * Send a success message to problem selector window
             * @param {string} message
             */
            function sendSuccess(message = '') {
                sendResponse({
                    success: true,
                    message: message
                });
            }

            /**
             * Send a failure message to problem selector window
             * @param {string} message
             */
            function sendFailure(message = '') {
                sendResponse({
                    success: false,
                    message: message
                });
            }

            /**
             * Add an event listener that accepts messages from LMS-Browser and updates the selected problem.
             */
            function addMessageListener() {
                window.addEventListener('message', (event) => {
                    console.log("message received");
                    console.log(event.data);
                    if (event.origin != lmsSelectorUrl) {
                        return;
                    }
                    var importObj = JSON.parse(event.data);
                    try {
                        langController.setLanguage(importObj.language);
                        taskController.updateOptions(importObj.link);
                        sendSuccess();
                        window.focus();
                        displayProblemSelectedMessage();
                    } catch (error) {
                        sendFailure(error.message);
                    }
                  }, false);
            }

            /**
             * Display a success message in Moodle that a problem was successfully selected.
             */
            function displayProblemSelectedMessage() {
                require(['core/str', "core/notification"], function(str, notification) {
                    str.get_strings([{
                        'key': 'mumie_form_updated_selection',
                        component: 'mod_mumie'
                    }]).done(function(s) {
                        notification.addNotification({
                            message: s[0],
                            type: "info"
                        });
                    }).fail(notification.exception);
                });
            }

            return {
                init: function() {
                    problemSelectorButton.onclick = function(e) {
                        e.preventDefault();
                        problemSelectorWindow = window.open(
                            lmsSelectorUrl
                                + '/lms-problem-selector?'
                                + 'org='
                                + mumieOrg
                                + '&serverUrl='
                                + encodeURIComponent(serverController.getSelectedServer().url_prefix)
                                + "&lang="
                                + langController.getSelectedLanguage()
                                + "&problem=" + taskController.getSelectedTask().link
                                + "&origin=" + encodeURIComponent(window.location.origin)
                            , '_blank'
                        );
                    };

                    window.onclose = function() {
                        sendSuccess();
                    };

                    window.addEventListener("beforeunload", function() {
                        sendSuccess();
                     }, false);

                    addMessageListener();
                },
                disable: function() {
                    problemSelectorButton.disabled = true;
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

                    var tasks = courseController.getSelectedCourse().tasks;
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
        problemSelectorController.init();
    });
})(jQuery)