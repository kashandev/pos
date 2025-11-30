/**
 * Created by Huzaifa on 9/18/15.
 */

function getTopCustomer() {

    $.ajax({
        url: $UrlGettop5customers,
        dataType: 'json',
        type: 'post',
        data: '',
        beforeSend: function () {
        },
        complete: function () {
        },
        success: function (json) {
            if (json) {

                Highcharts.chart('container1',json.data);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    });
}
//function getSales() {
//
//    $.ajax({
//        url: $UrlGetSales,
//        dataType: 'json',
//        type: 'post',
//        data: '',
//        beforeSend: function () {
//        },
//        complete: function () {
//        },
//        success: function (json) {
//            if (json) {
//                var result = json.data;
//                var chart = new CanvasJS.Chart("salesContainer", {
//                    title:{
//                        text: "Sales Of The Year"
//
//                    },
//                    axisX: {
//
//                        valueFormatString: "MMM",
//                        interval:1,
//                        intervalType: "month"
//                    },
//                    data: [
//
//                        {
//                            type: "line",
//                            dataPoints: result
//                        }
//                    ]
//                });
//
//                chart.render();
//            }
//        },
//        error: function (xhr, ajaxOptions, thrownError) {
//            console.log(xhr.responseText);
//        }
//    });
//}
function GetSaleMonthChart() {
    $.ajax({
        url: $UrlGetSaleMonthChart,
        dataType: 'json',
        type: 'post',
        data: '',
        beforeSend: function () {
        },
        complete: function () {
        },
        success: function (json) {
            if (json) {

                Highcharts.chart('container',json.data);

            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    });
}
$(document).ready(function() {
//        x = new Date(2012, 10, 1);
//        console.log(x);

    //getTopCustomer();
    //getSales();
    GetSaleMonthChart();
});