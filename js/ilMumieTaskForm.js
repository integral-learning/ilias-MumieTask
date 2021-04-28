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
        const structure = JSON.parse(document.getElementById('server_data').getAttribute('value'));
        const lmsSelectorUrl = 'https://pool.mumie.net';

        const serverController = (function () {
            let serverStructure;
            const serverDropDown = document.getElementById("xmum_server");

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
                    const selectedServerName = serverDropDown.options[serverDropDown.selectedIndex].text;

                    for (let i in serverStructure) {
                        const server = serverStructure[i];
                        if (server.name === selectedServerName) {
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

        const courseController = (function () {
            const courseDropDown = document.getElementById("xmum_course");
            const coursefileElem = document.getElementById('xmum_coursefile');

            /**
             * Add a new option the the 'MUMIE Course' drop down menu
             * @param {Object} course
             */
            function addOptionForCourse(course) {
                const optionCourse = document.createElement("option");
                const selectedLanguage = langController.getSelectedLanguage();
                let name;
                // If the currently selected server is not available on the server, we need to select another one.
                if (!course.languages.includes(selectedLanguage)) {
                    name = course.name[0];
                } else {
                    for (let i in course.name) {
                        if (course.name[i].language === selectedLanguage) {
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
                    courseDropDown.onchange = function () {
                        updateCoursefilePath();
                        langController.updateOptions();
                        taskController.updateOptions();
                    };
                    courseController.updateOptions(isEdit ? coursefileElem.value : false);
                },
                getSelectedCourse: function () {
                    const selectedCourseName = courseDropDown.options[courseDropDown.selectedIndex].text;
                    const courses = serverController.getSelectedServer().courses;
                    for (let i in courses) {
                        const course = courses[i];
                        for (let j in course.name) {
                            if (course.name[j].value === selectedCourseName) {
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
                    const selectedCourseFile = coursefileElem.value;
                    removeChildElems(courseDropDown);
                    courseDropDown.selectedIndex = 0;
                    const courses = serverController.getSelectedServer().courses;
                    for (const i in courses) {
                        const course = courses[i];
                        addOptionForCourse(course);
                        if (course.path_to_course_file === selectedCourseFile) {
                            courseDropDown.selectedIndex = courseDropDown.childElementCount - 1;
                        }
                    }
                    updateCoursefilePath();
                }
            };
        })();

        const langController = (function () {
            const languageElement = document.getElementById("xmum_language");

            return {
                init: function () {
                    langController.updateOptions();
                },
                updateOptions: function () {
                    const availableLanguages = courseController.getSelectedCourse().languages;
                    const currentLang = langController.getSelectedLanguage();
                    if(!availableLanguages.includes(currentLang)) {
                        langController.setLanguage(availableLanguages[0]);
                    }
                },
                getSelectedLanguage: function () {
                    return languageElement.value;
                },
                setLanguage: function(lang) {
                    if (!courseController.getSelectedCourse().languages.includes(lang)) {
                        throw new Error("Selected language not available");
                    }
                    languageElement.value = lang;
                    taskController.updateOptions();
                    courseController.updateOptions();
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
                    event.preventDefault();
                    if (event.origin !== lmsSelectorUrl) {
                        return;
                    }
                    const importObj = JSON.parse(event.data);
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
                        var selectedTask = taskController.getSelectedTask();
                        console.log("selectedTask", selectedTask);
                        problemSelectorWindow = window.open(
                            lmsSelectorUrl
                                + '/lms-problem-selector?'
                                + 'org='
                                + mumieOrg
                                + '&serverUrl='
                                + encodeURIComponent(serverController.getSelectedServer().url_prefix)
                                + "&lang="
                                + langController.getSelectedLanguage()
                                + (selectedTask  ? "&problem=" + selectedTask.link : '')
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
            var task_element = document.getElementById("xmum_task");
            var display_task_element = document.getElementById("xmum_display_task");
            var taskCount = document.getElementById('xmum_task_count');
            var nameElem = document.getElementById("title");

            /**
             * Update the activity's name in the input field
             */
            function updateName() {
                var newHeadline = getHeadline(taskController.getSelectedTask())
                if (!isCustomName()) {
                    nameElem.value = newHeadline;
                }
                display_task_element.value = newHeadline;
            }

            /**
             * Check whether the activity has a custom name
             *
             * @return {boolean} True, if there is no headline with that name in all tasks
             */
            function isCustomName() {
                if (nameElem.value.length === 0) {
                    return false;
                }
                return !isDummyTask() && !getAllHeadlines().includes(nameElem.value);
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
                    if (localHeadline.language === langController.getSelectedLanguage()) {
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
                const tasks = [];
                for (let i in serverController.getAllServers()) {
                    const server = serverController.getAllServers()[i];
                    for (const j in server.courses) {
                        const course = server.courses[j];
                        for (const m in course.tasks) {
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

            /**
             * Check whether this form is editing an existing task or creating a new one
             * @returns {boolean} True, if it's a new MUMIE Task
             */
            function isDummyTask() {
                return nameElem.value === '-- Empty MumieTask --'
            }

            return {
                init: function () {
                    task_element = document.getElementById("xmum_task");
                    taskCount = document.getElementById('xmum_task_count');
                    nameElem = document.getElementById("title");
                    taskController.updateOptions(!isDummyTask() ?
                        task_element?.value : undefined
                    );
                },
                getSelectedTask: function () {
                    var selectedLink = task_element.value;
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
                updateOptions: function (selectTaskByLink) {
                    task_element.value = selectTaskByLink;
                    var tasks = courseController.getSelectedCourse().tasks;

                    taskCount.textContent = '' + tasks.length;
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