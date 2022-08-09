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
        }
    };
});
