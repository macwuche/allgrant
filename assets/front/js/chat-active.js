
(function($) {
'use strict';

      // Loan
      var options3 = {
        series: [{
        name: 'Loan',
        data: [9, 13, 5, 19, 7, 12, 9, 22, 19, 29, 9, 10]
      }],

      chart: {
        id: "sparkline3",
        fontFamily: "Plus Jakarta Sans', sans-serif",
        foreColor: "#adb0bb",
        height: 60,
        sparkline: {
          enabled: true,
        },
        group: "sparklines",
      },

      stroke: {
        width: 2,
        curve: 'smooth'
      },
      tooltip: {
        theme: "dark",
        fixed: {
          enabled: true,
          position: "right",
        },
        x: {
          show: false,
        },
      },

      fill: {
        type: 'gradient',
        gradient: {
          shade: 'dark',
          gradientToColors: [ '#FF2D5B'],
          shadeIntensity: 1,
          type: 'horizontal',
          opacityFrom: 1,
          opacityTo: 1,
          stops: [0, 100, 100, 100]
        },
      },
      };
      var loan = new ApexCharts(document.querySelector("#loan"), options3);
      loan.render();
      
})(jQuery);