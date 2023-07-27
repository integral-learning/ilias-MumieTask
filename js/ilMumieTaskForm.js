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
                },
                getSelectedServer: function () {
                    const selectedServerName = serverDropDown.options[serverDropDown.selectedIndex].text;
                    return serverStructure.find(server => server.name === selectedServerName);
                },
                disable: function () {
                    serverDropDown.disabled = true;
                    removeChildElements(serverDropDown);
                },
            };
        })();

        const courseController = (function () {
            const courseNameElement = document.getElementById("xmum_course");
            const courseNameDisplayElement = document.getElementById("xmum_course_display");
            const coursefileElem = document.getElementById('xmum_coursefile');


            /**
             * Update the hidden input field with the selected course's course file path
             */
            function updateCoursefilePath(courseFile) {
                coursefileElem.value = courseFile;
                updateCourseName();
            }

            /**
             * Update displayed course name.
             */
            function updateCourseName() {
                const selectedCourse = courseController.getSelectedCourse();
                const selectedLanguage = langController.getSelectedLanguage();
                if (selectedCourse && selectedLanguage) {
                    const name = selectedCourse.name
                    .find(translation => translation.language === selectedLanguage)?.value;
                    courseNameElement.value = name;
                    courseNameDisplayElement.value = name;
                }
            }

            return {
                init: function (isEdit) {
                    if (isEdit) {
                        updateCourseName();
                    }
                },
                getSelectedCourse: function () {
                    const courses = serverController.getSelectedServer().courses;
                    return courses.find(course => {
                        return course.path_to_course_file === coursefileElem.value;
                    })
                },
                setCourse: function(courseFile) {
                    updateCoursefilePath(courseFile);
                }
            };
        })();

        const langController = (function () {
            const languageElement = document.getElementById("xmum_language");

            return {
                getSelectedLanguage: function () {
                    return languageElement.value;
                },
                setLanguage: function (lang) {
                    languageElement.value = lang;
                }
            };
        })();

        const problemSelectorController = (function () {
            const problemSelectorButton = document.getElementById('xmum_prb_sel');
            const multiProblemSelectorButton = document.getElementById('xmum_multi_prb_sel');
            let problemSelectorWindow;
            const mumieOrg = document.getElementById('mumie_org').value;

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
                    const worksheet = importObj.worksheet ?? null;
                    try {
                        langController.setLanguage(importObj.language);
                        courseController.setCourse(importObj.path_to_coursefile)
                        taskController.setSelection(importObj.link, importObj.name);
                        worksheetController.setWorksheet(worksheet);
                        sendSuccess();
                        window.focus();
                    } catch (error) {
                        sendFailure(error.message);
                    }
                }, false);
            }

            return {
                init: function () {
                    problemSelectorButton.onclick = function (e) {
                        e.preventDefault();
                        problemSelectorWindow = window.open(
                            lmsSelectorUrl
                            + '/lms-problem-selector?'
                            + 'org='
                            + mumieOrg
                            + '&serverUrl='
                            + encodeURIComponent(serverController.getSelectedServer().url_prefix)
                            + "&problemLang="
                            + langController.getSelectedLanguage()
                            + "&origin=" + encodeURIComponent(window.location.origin)
                            , '_blank'
                        );
                    };

                    multiProblemSelectorButton.onclick = function(e) {
                        e.preventDefault();
                        problemSelectorWindow = window.open(
                          lmsSelectorUrl
                          + '/lms-problem-selector?'
                          + "serverUrl="
                          + encodeURIComponent(serverController.getSelectedServer().url_prefix),
                          "_blank",
                          'toolbar=0,location=0,menubar=0'
                        );
                    }

                    window.onclose = function () {
                        sendSuccess();
                    };

                    window.addEventListener("beforeunload", function () {
                        sendSuccess();
                    }, false);

                    addMessageListener();
                },
                disable: function () {
                    problemSelectorButton.disabled = true;
                }
            };
        })();

        const taskController = (function () {
            const task_element = document.getElementById("xmum_task");
            const display_task_element = document.getElementById("xmum_display_task");
            const nameElem = document.getElementById("title");

            /**
             * Update the activity's name in the input field
             */
            function updateName(name) {
                nameElem.value = name;
            }

            /**
             * @param {string} uri
             */
            function updateTaskDisplayElement(uri) {
                display_task_element.value = uri;
            }

            /**
             * Update task uri
             * @param {string} uri
             */
            function updateTaskUri(uri) {
                task_element.value = uri;
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
                    if (!isDummyTask()) {
                        updateTaskDisplayElement(task_element.value)
                    }
                },
                setSelection: function(link, name) {
                    updateTaskUri(link);
                    updateTaskDisplayElement(link);
                    updateName(name);
                },
            };
        })();

        const worksheetController = (function() {
            const worksheetElement = document.getElementById("xmum_worksheet");
            return {
                setWorksheet: function(worksheet) {
                    if (worksheet) {
                        worksheetElement.setAttribute("value", JSON.stringify(worksheet));
                    } else {
                        worksheetElement.removeAttribute("value");
                    }
                }
            };
        })();

        /**
         * Remove all child elements of a given html element
         * @param {Object} elem
         */
        function removeChildElements(elem) {
            while (elem.firstChild) {
                elem.removeChild(elem.firstChild);
            }
        }

        serverController.init(structure);
        courseController.init();
        taskController.init();
        problemSelectorController.init();
    });
})(jQuery)