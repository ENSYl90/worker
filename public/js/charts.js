window.lazyLoadChart = function () {
    if (window.ApexCharts) {
        return Promise.resolve(window.ApexCharts);
    }

    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = '{{ asset("vendor/apexcharts/apexcharts.min.js") }}';
        script.onload = () => resolve(window.ApexCharts);
        script.onerror = reject;
        document.head.appendChild(script);
    });
};
