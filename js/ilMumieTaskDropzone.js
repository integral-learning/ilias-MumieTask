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
    const DRAG_OVER_CLASS = "ilFileDragOver";
    dropzone.addEventListener("dragover",  (event) => {
      event.preventDefault();
      // console.log("i was dragged over")})
    })
    dropzone.ondrop = (event) => {
      console.log("on drop")
      console.log(event.dataTransfer.getData("identifier"));
      console.log(event.dataTransfer.getData("mumie/jsonArray"));
      dropzone.classList.remove("red");

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