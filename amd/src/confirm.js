/*
 * @package    local_order
 * @copyright  2021 Andres, DQ.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const modules = ["jquery", "core/modal_factory", "core/modal_events"];
define(modules, function ($, ModalFactory, ModalEvents) {
  return {
    init: function () {
      /**
       * Adds an event listener for the 'beforeunload' event, stopping immediate propagation.
       * @param {Event} event - The event object.
       */
      window.addEventListener("beforeunload", function (event) {
        event.stopImmediatePropagation();
      });

      /**
       * Shows or hides the final date based on the state of the start date checkbox.
       */
      function showFinalDate() {
        // Check if the start date checkbox is checked
        const checked = $("input#id_startdate_enabled").is(":checked");

        // Disable or enable the final date checkbox based on the start date checkbox state
        $("input#id_finaldate_enabled")
          .prop("disabled", !checked)
          .prop("checked", false);
      }

      // Initial call to showFinalDate
      // showFinalDate();

      // Add a change event listener to the start date checkbox to trigger showFinalDate
      // $("input#id_startdate_enabled").change(showFinalDate);

      // Add a click event listener to delete action links
      $("a.action-delete").on("click", function (e) {
        e.preventDefault();
        var href = $(this).attr("href");
        var trigger = $(".create-modal");

        // Create a confirmation modal for deletion
        ModalFactory.create(
          {
            type: ModalFactory.types.SAVE_CANCEL,
            title: "Delete",
            body: "Do you really want to delete this record?",
          },
          trigger
        ).done(function (modal) {
          // Handle the save event of the modal
          modal.getRoot().on(ModalEvents.save, function () {
            // Redirect to the provided href on save
            location.href = href;
          });

          // Show the modal
          modal.show();
        });
      });

      // Add a click event listener to the submit button of the update status form
      $("form[name='updatestatus'] #id_submitbutton").on("click", function (e) {
        e.preventDefault();
        var trigger = $(".create-modal");

        // Create a confirmation modal for updating order status
        ModalFactory.create(
          {
            type: ModalFactory.types.SAVE_CANCEL,
            title: "Update order status",
            body: "Do you really want to update this order? It may result in unregistering students from their courses.",
          },
          trigger
        ).done(function (modal) {
          // Handle the save event of the modal
          modal.getRoot().on(ModalEvents.save, function () {
            // Submit the form
            $("form").submit();
          });

          // Show the modal
          modal.show();
        });
      });
    },
  };
});
