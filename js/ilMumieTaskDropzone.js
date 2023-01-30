/**
 * MumieTask plugin
 *
 * @copyright   2022 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

(function ($) {
  $(document).ready(function () {
    const dropzone = document.getElementById("xmum-dropzone");
    const multiProblemInputElem = document.getElementById("xmum_multi_problems");
    const DRAG_OVER_CLASS = "ilFileDragOver";
    dropzone.addEventListener("dragover",  (event) => {
      event.preventDefault();
    })
    dropzone.ondrop = (event) => {
      dropzone.classList.remove("red");
      multiProblemInputElem.setAttribute("value", event.dataTransfer.getData("mumie/jsonArray"));

    }
    dropzone.addEventListener("dragenter",  (event) => {
      event.preventDefault();
      dropzone.classList.add(DRAG_OVER_CLASS);
    })
    dropzone.addEventListener("dragleave",  (event) => {
      event.preventDefault();
      dropzone.classList.remove(DRAG_OVER_CLASS);
    })
  })}
)(jQuery)