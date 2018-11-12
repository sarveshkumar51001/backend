/*
*This file will hold custom js function
* @module custom module
*
* */

function LoadDateRangePicker(className) {
    className = className || 'daterange';
    $('.' + className).daterangepicker({
        opens: 'left',
        ranges: {
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'Next 30 Days': [moment(), moment().add(30,'days')],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Next 3 Months': [moment().startOf('month'), moment().add(3,'month').endOf('month')],
            'Next 6 Months': [moment().startOf('month'), moment().add(6,'month').endOf('month')],
            'Next 12 Months': [moment().startOf('month'), moment().add(12,'month').endOf('month')]
        }
    });
}

LoadDateRangePicker();

/**
 * Convert start end date to formatted string
 * @param startDate
 * @param endDate
 * @returns {string}
 * @constructor
 */
function ConvertDateToString(startDate, endDate) {
    var locale = "en-us";
    startDateObject = new Date(startDate);
    var sMonth =  startDateObject.getMonth();
    var sYear =   startDateObject.getFullYear();
    var sMonthStr = startDateObject.toLocaleString(locale, {month: "short"});

    endDateObject = new Date(endDate);
    var eMonth =  endDateObject.getMonth();
    var eYear =   endDateObject.getFullYear();
    var eMonthStr = endDateObject.toLocaleString(locale, {month: "short"});

    var dateString = '';
    if(sMonth == eMonth) {
        dateString = sMonthStr;
    }
    else {
        dateString = sMonthStr+' '+eMonthStr;
    }
    if(sYear == eYear) {
        dateString += ' '+sYear;
    }
    else {
        dateString += ' '+sYear+'-'+eYear.toString().substr(-2);
    }
    return dateString

}
