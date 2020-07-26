Chart.defaults.global.defaultFontColor='#a89a42';
Vue.use(Chartkick.use(Chart));
Chartkick.options = {
    colors: ["#a89a42", "#e57f12", "#583a01", "#2d74a0", "#866f4f", "#595B00", "#c24b26", "#4b7d07", "#a6ad42"]
}

var app = new Vue({
    el: '#app',
    data: {
        sitename: 'Vue',
        charturl: 'ajax/radiation',
        chartOptions: {
            layout: {
                padding: {left: 10, right: 5, top: 5, bottom: 2}
            },
            animation: {
                easing: 'easeInOutQuart'
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
