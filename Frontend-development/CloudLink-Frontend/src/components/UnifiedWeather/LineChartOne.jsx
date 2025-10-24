import Chart from "react-apexcharts";
export default function LineChartOne() {
    const options = {
        legend: {
            show: false, // Hide legend
            position: "top",
            horizontalAlign: "left",
        },
        colors: ["#465FFF", "#9CB9FF"], // Define line colors
        chart: {
            fontFamily: "Outfit, sans-serif",
            height: 310,
            type: "line", // Set the chart type to 'line'
            toolbar: {
                show: false, // Hide chart toolbar
            },
        },
        stroke: {
            curve: "straight", // Define the line style (straight, smooth, or step)
            width: [2, 2], // Line width for each dataset
        },
        fill: {
            type: "gradient",
            gradient: {
                opacityFrom: 0.55,
                opacityTo: 0,
            },
        },
        markers: {
            size: 0, // Size of the marker points
            strokeColors: "#fff", // Marker border color
            strokeWidth: 2,
            hover: {
                size: 6, // Marker size on hover
            },
        },
        grid: {
            xaxis: {
                lines: {
                    show: false, // Hide grid lines on x-axis
                },
            },
            yaxis: {
                lines: {
                    show: true, // Show grid lines on y-axis
                },
            },
        },
        dataLabels: {
            enabled: false, // Disable data labels
        },
        tooltip: {
            enabled: true, // Enable tooltip
            x: {
                format: "dd MMM yyyy", // Format for x-axis tooltip
            },
        },
        xaxis: {
            type: "category", // Category-based x-axis
            categories: [
                "Jan",
                "Feb",
                "Mar",
                "Apr",
                "May",
                "Jun",
                "Jul",
                "Aug",
                "Sep",
                "Oct",
                "Nov",
                "Dec",
            ],
            axisBorder: {
                show: false, // Hide x-axis border
            },
            axisTicks: {
                show: false, // Hide x-axis ticks
            },
            tooltip: {
                enabled: false, // Disable tooltip for x-axis points
            },
        },
        yaxis: [
            {
                seriesName:"Sales",
                title: { text: "Temp (°C)", style: { color: "#FF5733" } },
                labels: { style: { colors: "#FF5733" } },
            },
            {
                seriesName: "Revenue",
                title: { text: "Humidity (%)", style: { color: "#1E90FF" } },
                labels: { style: { colors: "#1E90FF" } },
            },
          
        ]
    };
    const series = [
        {
            name: "Sales",
            data: [180, 190, 170, 160, 175, 165, 170, 205, 230, 210, 240, 235],
        },
        {
            name: "Revenue",
            data: [40, 30, 50, 40, 55, 40, 70, 100, 110, 120, 150, 140],
        },
          {
                name: "Snow Depth (cm)",
                data: [0, 0, 0, 0, 0, 0, 0, 0, 1, 2, 3, 2],
                color: "#A9CCE3", // light blue-gray
            },
    ];
    return (<div className="max-w-full overflow-x-auto custom-scrollbar">
      <div id="chartEight" className="min-w-[1000px]">
        <Chart options={options} series={series} type="area" height={310}/>
      </div>
    </div>);
}
