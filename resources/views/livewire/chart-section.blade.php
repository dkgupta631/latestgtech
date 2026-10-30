
<div
    x-data="{
        chart: null,
        init() {
            this.renderChart(@js($chartData));
        },
        renderChart(dataPoints) {
            if (this.chart) this.chart.destroy?.();

            this.chart = new CanvasJS.Chart('chartContainer', {
                animationEnabled: true,
                theme: 'light1',
                backgroundColor: 'transparent',
                title: {
                    text: 'Deposit Chart - Last {{ $days }} Days',
                    fontFamily: 'Inter, Helvetica, Arial, sans-serif',
                    fontSize: 16,
                    fontWeight: 'bold',
                    fontColor: '#1c1c1f',
                    horizontalAlign: 'left',
                    margin: 16
                },
                axisX: {
                    labelFontFamily: 'Inter, Helvetica, Arial, sans-serif',
                    labelFontColor: '#6b7a8c',
                    labelFontSize: 12,
                    lineColor: '#e6edf5',
                    tickColor: '#e6edf5'
                },
                axisY: {
                    title: '{{ __('messages.Deposits Currency') }}',
                    titleFontFamily: 'Inter, Helvetica, Arial, sans-serif',
                    titleFontSize: 12,
                    titleFontColor: '#6b7a8c',
                    labelFontFamily: 'Inter, Helvetica, Arial, sans-serif',
                    labelFontColor: '#6b7a8c',
                    labelFontSize: 12,
                    gridColor: '#eef2f7',
                    lineThickness: 0,
                    tickLength: 0
                },
                legend: {
                    fontFamily: 'Inter, Helvetica, Arial, sans-serif',
                    fontColor: '#4a5a6c',
                    fontSize: 13
                },
                toolTip: {
                    cornerRadius: 8,
                    borderColor: '#c39226',
                    fontFamily: 'Inter, Helvetica, Arial, sans-serif'
                },
                dataPointWidth: 28,
                data: [{
                    type: 'column',
                    color: '#c39226',
                    showInLegend: true,
                    legendText: '{{ __('messages.Deposit Amount') }}',
                    dataPoints: dataPoints
                }]
            });

            this.chart.render();
        },
        reload(newData) {
            this.renderChart(newData);
        }
    }"
    x-init="init()"
    x-on:chart-updated.window="reload($event.detail)"
>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">{{ __('messages.Deposit Overview') }}</h4>
            <div class="btn-group">
                {{-- <button class="btn btn-secondary" wire:click="loadChartData(7)">Last 7 Days</button> --}}
                <button class="btn btn-secondary" wire:click="loadChartData(30)">{{__('messages.last 30 days')}}</button>
                <button class="btn btn-secondary" wire:click="loadChartData(90)">{{__('messages.last 90 days')}}</button>
                <button class="btn btn-secondary" wire:click="loadChartData(365)">{{__('messages.last 365 days')}}</button>
            </div>
        </div>
        <div class="card-body">
            <div id="chartContainer" style="height: 430px; width: 100%;"></div>
        </div>
    </div>
    <style>
        a[title="JavaScript Charts"] {
    display: none !important;
}

    </style>
</div>

<script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
