/*
 * @package    local_order
 * @copyright  2021 Andres, DQ.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/modal_factory', 'core/modal_events'],
function($, ModalFactory, ModalEvents) {
    return {
        init: function() {
            $('a.action-delete').on('click', function(e) {
                e.preventDefault();
                var href = $(this).attr('href');
                var trigger = $('.create-modal');
                ModalFactory.create({
                    type: ModalFactory.types.SAVE_CANCEL,
                    title: 'Delete',
                    body: 'Do you really want to delete this record?',
                }, trigger)
                .done(function(modal) {
                    modal.getRoot().on(ModalEvents.save, function() {
                      location.href = href;
                    });
                    modal.show();
                });
            });

            $('#id_submitbutton').on('click', function(e) {
                e.preventDefault();

                var trigger = $('.create-modal');
                ModalFactory.create({
                    type: ModalFactory.types.SAVE_CANCEL,
                    title: 'Update order status',
                    body: 'Do you really want to update this order? it may result in unregistering students from their courses.',
                }, trigger)
                .done(function(modal) {
                    modal.getRoot().on(ModalEvents.save, function() {
                        // Submit the form.
                        $('form').submit();
                        // Do not display leaving warning in the browser.
                        window.onbeforeunload = function() { return undefined; };
                    });
                    modal.show();
                });
            });
        }
    };
});
