/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

(function ($) {
    $(document).ready(function () {
      const multiProblemInputElem = document.getElementById("xmum_multi_problems");

      function applyTasksJson(taskJsonString) {
        multiProblemInputElem.setAttribute("value", taskJsonString);
        problemListController.setData(taskJsonString);
      }

      const problemListController = (function () {
        const problemListElement = document.getElementById("xmum_selected_multi_problems");
        const dropzoneDescription = document.getElementById("xmum_dropzone_description");

        function getProblemNames(taskJson) {
          return taskJson.map(task => {
            return JSON.parse(task).name;
          })
        }

        function ensureDescriptionVisibility() {
          dropzoneDescription.removeAttribute("hidden");
        }

        function createProblemListEntry(name) {
          const element = document.createElement("li");
          element.innerHTML = name;
          return element
        }

        function createProblemListEntries(problemNames) {
          problemListElement.innerHTML = "";
          problemNames.forEach(name => problemListElement.appendChild(createProblemListEntry(name)));
        }

        return {
          setData: function (taskJsonString) {
            const problemNames = getProblemNames(JSON.parse(taskJsonString))
            createProblemListEntries(problemNames);
            ensureDescriptionVisibility();
          }
        }
      })();

      window.ilMumieTaskDropzone = {
        setData: applyTasksJson,
      };

      const restoredValue = multiProblemInputElem.getAttribute("value");
      if (restoredValue) {
        problemListController.setData(restoredValue);
      }
    })
  }
)(jQuery)