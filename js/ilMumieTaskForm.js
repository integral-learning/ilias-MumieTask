/**
 * MumieTask plugin
 *
 * @copyright   2019 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @author      Nicolas Zunker (nicolas.zunker@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

(function($) {
    $(document).ready(function() {
        const structure = JSON.parse(document.getElementById('server_data').getAttribute('value'));

        const serverController = (function() {
            let serverStructure;
            const serverDropDown = document.getElementById("xmum_server");

            return {
                init: function(structure) {
                    serverStructure = structure;
                },
                getSelectedServer: function() {
                    const selectedServerName = serverDropDown.options[serverDropDown.selectedIndex].text;
                    return serverStructure.find(server => server.name === selectedServerName);
                },
                disable: function() {
                    serverDropDown.disabled = true;
                    removeChildElements(serverDropDown);
                },
            };
        })();

        const courseController = (function() {
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
                init: function(isEdit) {
                    if (isEdit) {
                        updateCourseName();
                    }
                },
                getSelectedCourse: function() {
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

        const langController = (function() {
            const languageElement = document.getElementById("xmum_language");

            return {
                getSelectedLanguage: function() {
                    return languageElement.value;
                },
                setLanguage: function(lang) {
                    languageElement.value = lang;
                }
            };
        })();

        const problemSelectorController = (function() {
            const problemSelectorButton = document.getElementById('xmum_prb_sel');
            const multiProblemSelectorButton = document.getElementById('xmum_multi_prb_sel');
            let problemSelectorWindow;
            const mumieOrg = document.getElementById('mumie_org').value;
            const lmsSelectorUrl = document.getElementById('problem_selector_url').getAttribute('value');
            const user_id = document.getElementById('user_id').getAttribute('value');
            const user_token = document.getElementById('user_token').getAttribute('value');
            const user_lang = document.getElementById('user_lang').getAttribute('value');
            const contextId = document.getElementById('contextId').getAttribute('value');
            // const selectedServer = serverController.getSelectedServer().url_prefix;
            // const useSSO = shouldUseSSO(lmsSelectorUrl, selectedServer);


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
                console.log('sendSuccess: ', message);
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

                    const isGraded = importObj.isGraded !== false;
                    const worksheet = importObj.worksheet ?? null;
                    try {
                        langController.setLanguage(importObj.language);
                        courseController.setCourse(importObj.path_to_coursefile);
                        taskController.setSelection(importObj.link, importObj.name);
                        worksheetController.setWorksheet(worksheet);
                        taskController.setIsGraded(isGraded);
                        sendSuccess();
                        window.focus();
                        // displayProblemSelectedMessage();
                    } catch (error) {
                        sendFailure(error.message);
                    }
                }, false);
            }

            /**
             * Builds the URL to the Problem Selector
             * @returns {string} URL to the Problem Selector
             */
            function openProblemSelector() {
                const gradingType = taskController.getGradingType();
                const selection = taskController.getDelocalizedTaskLink();
                const selectedServer = serverController.getSelectedServer().url_prefix;
                const useSSO = shouldUseSSO(lmsSelectorUrl, selectedServer);

                if (useSSO) {
                    window.open('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/openProblemSelector.php?' +
                        'org=' +
                        mumieOrg +
                        '&serverurl=' +
                        encodeURIComponent(serverController.getSelectedServer().url_prefix) +
                        '&problemlang=' +
                        langController.getSelectedLanguage() +
                        '&origin=' + encodeURIComponent(window.location.origin) +
                        '&gradingtype=' + gradingType +
                        '&contextid=' + contextId +
                        '&lmsSelectorUrl=' + lmsSelectorUrl +
                        '&user_id=' + user_id +
                        '&user_token=' + user_token +
                        '&user_lang=' + user_lang +
                        (selection ? '&selection=' + selection : ''));
                } else {
                    const withoutSSO = lmsSelectorUrl +
                        '/lms-problem-selector?' +
                        'org=' +
                        mumieOrg +
                        '&serverUrl=' +
                        encodeURIComponent(selectedServer) +
                        '&problemLang=' +
                        langController.getSelectedLanguage() +
                        '&origin=' + encodeURIComponent(window.location.origin) +
                        '&uiLang=' + user_lang +
                        '&gradingType=' + gradingType +
                        '&multiCourse=true' +
                        '&worksheet=true' +
                        (selection ? '&selection=' + selection : '');
                    problemSelectorWindow = window.open(withoutSSO, '_blank');
                }

            }


            /**
             * Determines whether the Single Sign-On (SSO) should be used when opening the Problem Selector.
             * SSO is only supposed to be used when the Problem Selector URL has the same origin as the
             * URL of the selected MUMIE server.
             *
             * @param {string} problemSelectorUrl - The URL of the problem selector.
             * @param {string} selectedServerUrl - The URL of the selected MUMIE server
             * @returns {boolean} Whether SSO should be used for the Problem Selector or not
             */
            function shouldUseSSO(problemSelectorUrl, selectedServerUrl) {
                return new URL(problemSelectorUrl).origin === new URL(selectedServerUrl).origin;
            }

            return {
                init: function() {
                    problemSelectorButton.onclick = function(e) {
                        e.preventDefault();
                        openProblemSelector()




                        // problemSelectorWindow = window.open(
                        //     lmsSelectorUrl
                        //     + '/api/sso/problem-selector?'
                        //     + 'userId=' + user_id
                        //     + '&token=' + user_token
                        //     + '&uiLang=' + user_lang
                        //     // + '&gradingType=' + gradingtype
                        //     + '&org='
                        //     + mumieOrg
                        //     + '&serverUrl='
                        //     + encodeURIComponent(serverController.getSelectedServer().url_prefix)
                        //     + "&problemLang="
                        //     + langController.getSelectedLanguage()
                        //     + "&origin=" + encodeURIComponent(window.location.origin)
                        //     , '_blank'
                        // );
                    };

                    addMessageListener();

                    multiProblemSelectorButton.onclick = function(e) {
                        e.preventDefault();
                        problemSelectorWindow = window.open(
                            lmsSelectorUrl +
                            '/lms-problem-selector?' +
                            "serverUrl=" +
                            encodeURIComponent(serverController.getSelectedServer().urlprefix) +
                            '&gradingType=all',
                            "_blank",
                            'toolbar=0,location=0,menubar=0'
                        );
                    };

                    window.onclose = function() {
                        sendSuccess();
                    };

                    window.addEventListener("beforeunload", function() {
                        sendSuccess();
                    }, false);
                },
                disable: function() {
                    problemSelectorButton.disabled = true;
                }
            };
        })();

        const taskController = (function() {
            const taskSelectionInput = document.getElementById("xmum_task");
            const nameElem = document.getElementById("title");
            const taskDisplayElement = document.getElementById("xmum_display_task");
            const isGradedElem = document.getElementById('id_mumie_isgraded');
            const LANG_REQUEST_PARAM_PREFIX = "?lang=";
            // const task_element = document.getElementById("xmum_task");
            console.log('taskSelectionInput base', taskSelectionInput);
            // console.log('task_element base', task_element);

            console.log('updateTaskUri', document.getElementsByName("taskurl"));
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
                taskDisplayElement.value = uri;
            }

            /**
             * Update task uri
             * @param {string} link
             * @param {string} language
             */
            function updateTaskUri(link, language) {
                console.log('updateTaskUri #1', link, language);
                const localizedLink = localizeLink(link, language);
                console.log('updateTaskUri #2', localizedLink);
                console.log('updateTaskUri #2_1', taskSelectionInput);
                taskSelectionInput.value = localizedLink;
                console.log('updateTaskUri #3', link, language);
                updateTaskDisplayElement(localizedLink);
                console.log('updateTaskUri #4', link, language);
            }

            /**
             * Check whether this form is editing an existing task or creating a new one
             * @returns {boolean} True, if it's a new MUMIE Task
             */
            function isDummyTask() {
                return nameElem.value === '-- Empty MumieTask --'
            }

            /**
             * Add lang request param to link
             * @param {string} link
             * @param {string} language
             * @returns {string} Link with lang request param
             */
            function localizeLink(link, language) {
                return link + LANG_REQUEST_PARAM_PREFIX + language;
            }

            /**
             * Remove lang request param from link
             * @param {string} link Link that may have lang request param
             * @returns {string} Link without lang request param
             */
            function delocalizeLink(link) {
                if (link && link.includes(LANG_REQUEST_PARAM_PREFIX)) {
                    return link.split(LANG_REQUEST_PARAM_PREFIX)[0];
                }
                return link;
            }

            return {
                init: function() {
                    if (!isDummyTask()) {
                        updateTaskDisplayElement(taskSelectionInput.value)
                    }
                },
                setSelection: function(link, name) {
                    console.log('setSelection a', link, name);
                    updateTaskUri(link);
                    console.log('setSelection b', link, name);
                    updateTaskDisplayElement(link);
                    console.log('setSelection c', link, name);
                    updateName(name);
                    console.log('setSelection d', link, name);
                },
                setIsGraded: function(isGraded) {
                    if (isGraded === null) {
                        isGradedElem.value = null;
                    }
                    isGradedElem.value = isGraded ? '1' : '0';
                    // updateGradeEditability();
                },
                getGradingType: function() {
                    const isGraded = isGradedElem.value;
                    if (isGraded === '1') {
                        return 'graded';
                    } else if (isGraded === '0') {
                        return 'ungraded';
                    }
                    return 'all';
                },
                getDelocalizedTaskLink: function() {
                    return delocalizeLink(taskSelectionInput?.value);
                }
            };
        })();

        const worksheetController = (function() {
            const worksheetElement = document.getElementById("xmum_worksheet");
            return {
                setWorksheet: function(worksheet) {
                    console.log('will set worksheet data to worksheetElement', worksheet);
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