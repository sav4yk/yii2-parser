Chart.defaults.global.defaultFontColor='#a89a42';
Vue.use(Chartkick.use(Chart));
Chartkick.options = {
    colors: ["#a89a42", "#e57f12", "#583a01", "#332200", "#866f4f", "#595B00", "#342200", "#592700", "#a6ad42"]
}

var app = new Vue({
    el: '#app',
    data: {
        sitename: 'Vue',
        charturl: 'radiation',
        chartOptions: {
            layout: {
                padding: {left: 10, right: 5, top: 5, bottom: 2}
            },
            title: {
                display: true,
                text: 'Радиационная обстановка',
                fontColor: '#a89a42',
                position:'top'
            },
            animation: {
                easing: 'easeOutQuint'
            },
            tooltips: {
                callbacks: {
                    labelTextColor: function(tooltipItem, chart) {
                        return '#866f4f';
                    }
                },
                titleFontColor: '#a89a42',
                borderColor: '#866f4f',
                borderWidth: 1
            }
        }
    }
});
