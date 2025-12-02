/**
 * Modern Greek Datepicker Helper
 * Uses jQuery UI Datepicker with Greek localization
 */

// Greek localization for jQuery UI Datepicker
if (typeof jQuery !== 'undefined' && jQuery.ui && jQuery.ui.datepicker) {
    jQuery(function($) {
        $.datepicker.regional['el'] = {
            closeText: 'Κλείσιμο',
            prevText: 'Προηγούμενος',
            nextText: 'Επόμενος',
            currentText: 'Σήμερα',
            monthNames: ['Ιανουάριος','Φεβρουάριος','Μάρτιος','Απρίλιος','Μάιος','Ιούνιος',
                'Ιούλιος','Αύγουστος','Σεπτέμβριος','Οκτώβριος','Νοέμβριος','Δεκέμβριος'],
            monthNamesShort: ['Ιαν','Φεβ','Μαρ','Απρ','Μαι','Ιουν',
                'Ιουλ','Αυγ','Σεπ','Οκτ','Νοε','Δεκ'],
            dayNames: ['Κυριακή','Δευτέρα','Τρίτη','Τετάρτη','Πέμπτη','Παρασκευή','Σάββατο'],
            dayNamesShort: ['Κυρ','Δευ','Τρι','Τετ','Πεμ','Παρ','Σαβ'],
            dayNamesMin: ['Κυ','Δε','Τρ','Τε','Πε','Πα','Σα'],
            weekHeader: 'Εβδ',
            dateFormat: 'dd-mm-yy', // 'yy' means 4-digit year in jQuery UI
            firstDay: 1, // Monday
            isRTL: false,
            showMonthAfterYear: false,
            yearSuffix: ''
        };
        $.datepicker.setDefaults($.datepicker.regional['el']);
    });
}

/**
 * Initialize a modern datepicker
 * @param {string} inputId - The ID of the input element
 * @param {object} options - Datepicker options
 */
function initModernDatepicker(inputId, options) {
    if (typeof jQuery === 'undefined' || !jQuery.ui || !jQuery.ui.datepicker) {
        console.error('jQuery UI Datepicker is not loaded');
        return;
    }
    
    var defaults = {
        dateFormat: 'dd-mm-yy',
        changeMonth: true,
        changeYear: true,
        yearRange: '1970:2050',
        showButtonPanel: true,
        firstDay: 1, // Monday
        beforeShowDay: function(date) {
            // Disable weekends by default if disabledDays includes 'sun' or 'sat'
            if (options && options.disabledDays) {
                var day = date.getDay();
                if (options.disabledDays.indexOf('sun') !== -1 && day === 0) return [false];
                if (options.disabledDays.indexOf('sat') !== -1 && day === 6) return [false];
            }
            return [true];
        }
    };
    
    // Merge user options with defaults
    var settings = jQuery.extend({}, defaults, options || {});
    
    // Apply date restrictions
    if (options && options.minDate) {
        settings.minDate = options.minDate;
    }
    if (options && options.maxDate) {
        settings.maxDate = options.maxDate;
    }
    
    jQuery('#' + inputId).datepicker(settings);
}

