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
      window.addEventListener("beforeunload", (evt) =>
        evt.stopImmediatePropagation()
      );

      // Select the form with the name 'updatestatus' using jQuery
      const form = $("form[name='updatestatus']");
      // Find the cancel and submit buttons within the form
      const cancelButton = form.find("input#id_cancel");
      const submitButton = form.find("input#id_submitbutton");
      const deleteButton = form.find("a.action-delete");

      // Add a click event listener to delete action links
      deleteButton.on("click", function (e) {
        e.preventDefault();
        var href = $(this).attr("href");

        // Create a confirmation modal for deletion
        ModalFactory.create(
          {
            type: ModalFactory.types.SAVE_CANCEL,
            title: "Delete",
            body: "Do you really want to delete this record?",
          },
          $(".create-modal")
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

      // Add a click event listener to the cancel button
      cancelButton.on("click", function (evt) {
        evt.preventDefault(); // Prevent the default form submission behavior

        // Get the 'action-cancel' attribute from the form and navigate to that URL
        const formAction = form.attr("action-cancel");
        location.href = formAction;
      });

      // Add a click event listener to the submit button
      submitButton.on("click", function (evt) {
        evt.preventDefault(); // Prevent the default form submission behavior

        // Get the 'action' attribute from the form
        const formAction = form.attr("action");

        // Create a confirmation modal for updating order status
        ModalFactory.create(
          {
            type: ModalFactory.types.SAVE_CANCEL,
            title: "Update order status",
            body: "Do you really want to update this order? It may result in unregistering students from their courses.",
          },
          $(".create-modal")
        ).done(function (modal) {
          // Handle the save event of the modal
          modal.getRoot().on(ModalEvents.save, function () {
            form.prop("action", formAction).submit(); // Set the form action and submit the form
          });

          // Show the modal
          modal.show();
        });
      });
    },
  };
});
