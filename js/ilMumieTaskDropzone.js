/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

(function ($) {
    $(document).ready(function () {
      console.log('[MumieTaskDropzone DEBUG] ready fired');
      const dropzone = document.getElementById("xmum-dropzone");
      const multiProblemInputElem = document.getElementById("xmum_multi_problems");
      console.log('[MumieTaskDropzone DEBUG] elements found', {
          dropzone: dropzone,
          multiProblemInputElem: multiProblemInputElem,
      });
      const DRAG_OVER_CLASS = "mumie-drag-over";
      dropzone.addEventListener("dragover", (event) => {
        event.preventDefault();
      })
      dropzone.ondrop = (event) => {
        dropzone.classList.remove(DRAG_OVER_CLASS);
        applyTasksJson(event.dataTransfer.getData("mumie/jsonArray"));
      }

      function applyTasksJson(taskJsonString) {
        multiProblemInputElem.setAttribute("value", taskJsonString);
        problemListController.setData(taskJsonString);
      }
      dropzone.addEventListener("dragenter", (event) => {
        event.preventDefault();
        dropzone.classList.add(DRAG_OVER_CLASS);
      })
      dropzone.addEventListener("dragleave", (event) => {
        event.preventDefault();
        dropzone.classList.remove(DRAG_OVER_CLASS);
      })

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
      console.log('[MumieTaskDropzone DEBUG] window.ilMumieTaskDropzone set', window.ilMumieTaskDropzone);
    })
  }
)(jQuery)