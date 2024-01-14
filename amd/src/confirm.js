/*
 * @package    local_order
 * @copyright  2021 Andres, DQ.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const modules = ["jquery", "core/modal_factory", "core/modal_events"];
define(modules, function ($, ModalFactory, ModalEvents) {
  return {
    init: function () {
      const finaldatecontent = $("#fitem_id_finaldate");
      finaldatecontent.addClass("d-none");
      $("input#id_startdate_enabled").change(function (e) {
        finaldatecontent.toggleClass("d-none");
        const checked = $(e).is(":checked");
        $("input#id_finaldate_enabled")
          .attr("disabled", checked)
          .prop("checked", false);
      });

      $("a.action-delete").on("click", function (e) {
        e.preventDefault();
        var href = $(this).attr("href");
        var trigger = $(".create-modal");
        ModalFactory.create(
          {
            type: ModalFactory.types.SAVE_CANCEL,
            title: "Delete",
            body: "Do you really want to delete this record?",
          },
          trigger
        ).done(function (modal) {
          modal.getRoot().on(ModalEvents.save, function () {
            location.href = href;
          });
          modal.show();
        });
      });

      $("#id_submitbutton").on("click", function (e) {
        e.preventDefault();
        // Do not display leaving warning in the browser.
        window.onbeforeunload = function () {
          return undefined;
        };

        var trigger = $(".create-modal");
        ModalFactory.create(
          {
            type: ModalFactory.types.SAVE_CANCEL,
            title: "Update order status",
            body: "Do you really want to update this order? it may result in unregistering students from their courses.",
          },
          trigger
        ).done(function (modal) {
          modal.getRoot().on(ModalEvents.save, function () {
            // Do not display leaving warning in the browser.
            window.onbeforeunload = function () {
              return undefined;
            };
            // Submit the form.
            $("form").submit();
          });
          modal.show();
        });
      });
    },
  };
});
