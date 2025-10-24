// assuming const chartRef = useRef(null)
//for outside 
// const resetAll = () => {
//   const apex = chartRef.current?.chart ?? chartRef.current; // adapt if structure differs
//   apex?.resetSeries && apex.resetSeries();
//   apex?.zoomReset && apex.zoomReset();
//   apex?.updateOptions && apex.updateOptions({ xaxis: { categories: originalCategories } }, false, false);
//   apex?.updateSeries && apex.updateSeries(originalSeries, true);
// };


import { useEffect, useRef,useState} from "react";

import Chart from "react-apexcharts";

function HrsToDates() { // this what the api actually gonna provide

}

const arr = [
    "21 Oct",
    "22 Oct",
    "23 Oct",
    "24 Oct",
    "25 Oct",
    "26 Oct",
    "27 Oct",
    "28 Oct",
    "29 Oct",
    "30 Oct",
    "31 Oct",
    "1 Nov",
    ]
function DatesToHrs(arr ) {
    const newarr= []
    const n = arr.length;
    var i,j,time_,time_string;
    for(i=0;i<n;i++) {
        time_ = 0
        for(j=0;j<24;j++){
            time_string = time_.toString();
            newarr.push(time_string+":00")
            time_ += 1
        }
    }

    return newarr
}
export default function WeatherChart({
    displayMetrics = [], //defaults
    
}) {

    const Chartref = useRef(null)
      const series = [
        {
            name: "Temperature (°C)",
            data: [22, 21, 20, 19, 18, 20, 23, 25, 27, 29, 30, 31],
            color: "#FF5733", // orange-red
        },
        {
            name: "Humidity (%)",
            data: [65, 67, 70, 72, 68, 66, 64, 60, 58, 55, 57, 59],
            color: "#1E90FF", // sky blue
        },
        {
            name: "Rainfall (mm)",
            data: [2, 0, 1, 3, 0, 0, 5, 10, 15, 8, 4, 1],
            color: "#00BFFF", // blue
        },
        {
            name: "Snow Depth (cm)",
            data: [0, 0, 0, 0, 0, 0, 0, 0, 1, 2, 3, 2],
            color: "#A9CCE3", // light blue-gray
        },
        {
            name: "Wind Speed (m/s)",
            data: [3, 4, 5, 4, 6, 5, 7, 8, 10, 9, 8, 7],
            color: "#2ECC71", // green
        },
        {
            name: "Wind Direction (°)",
            data: [90, 100, 110, 120, 135, 140, 150, 160, 170, 180, 190, 200],
            color: "#F1C40F", // yellow
        },
        {
            name: "Pressure (hPa)",
            data: [1012, 1010, 1008, 1009, 1011, 1013, 1015, 1014, 1012, 1011, 1010, 1009],
            color: "#8E44AD", // purple
        },
        {
            name: "Visibility (km)",
            data: [8, 9, 10, 9, 8, 7, 6, 6, 7, 8, 9, 10],
            color: "#3498DB", // blue
        },
        {
            name: "Air Pressure (kPa)",
            data: [101.2, 101.0, 100.8, 100.9, 101.1, 101.3, 101.5, 101.4, 101.2, 101.1, 101.0, 100.9],
            color: "#9B59B6", // violet
        },
        {
            name: "Weather Code",
            data: [0, 0, 1, 1, 2, 2, 3, 3, 1, 0, 0, 1], // e.g., 0-clear, 1-cloudy, 2-rainy, 3-stormy
            color: "#34495E", // dark gray
        },
    ];
    const yAxis = [
            {
                seriesName: "Temperature (°C)",
                title: { text: "Temp (°C)", style: { color: "#FF5733" } },
                labels: { style: { colors: "#FF5733" } },
            },
            {
                seriesName: "Humidity (%)",
                title: { text: "Humidity (%)", style: { color: "#1E90FF" } },
                labels: { style: { colors: "#1E90FF" } },
            },
            {
                seriesName: "Rainfall (mm)",
                title: { text: "Rainfall (mm)", style: { color: "#00BFFF" } },
                labels: { style: { colors: "#00BFFF" } },
            },
            {
                seriesName: "Snow Depth (cm)",
                title: { text: "Snow Depth (cm)", style: { color: "#A9CCE3" } },
                labels: { style: { colors: "#A9CCE3" } },
            },
            {
                seriesName: "Wind Speed (m/s)",
                title: { text: "Wind Speed (m/s)", style: { color: "#2ECC71" } },
                labels: { style: { colors: "#2ECC71" } },
            },
            {
                seriesName: "Wind Direction (°)",
                title: { text: "Wind Direction (°)", style: { color: "#F1C40F" } },
                labels: { style: { colors: "#F1C40F" } },
            },
            {
                seriesName: "Pressure (hPa)",
                title: { text: "Pressure (hPa)", style: { color: "#8E44AD" } },
                labels: { style: { colors: "#8E44AD" } },
            },
            {
                seriesName: "Visibility (km)",
                title: { text: "Visibility (km)", style: { color: "#3498DB" } },
                labels: { style: { colors: "#3498DB" } },
            },
            {
                seriesName: "Air Pressure (kPa)",
                title: { text: "Air Pressure (kPa)", style: { color: "#9B59B6" } },
                labels: { style: { colors: "#9B59B6" } },
            },
            {
                seriesName: "Weather Code",
                title: { text: "Weather Code", style: { color: "#34495E" } },
                labels: { style: { colors: "#34495E" } },
            },
    ];


    const [options,setOptions]= useState({   
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
                show: true, // if flase - Hide chart toolbar , if true show toolbar
            },
            events:{
                zoomed: (chartContext, { xaxis, yaxis }) => {
                    console.log("Zoomed x-axis:", xaxis);
                    const newarr = DatesToHrs(arr) 
                    // setOptions((prev)=>({ //it makes everything reinitializes
                    //     ...prev,
                    //     xaxis:{
                    //         ...prev.xaxis,
                    //         categories:newarr,
                    //     }
                    // }))
                    if (chartContext && chartContext.updateOptions) {
                        chartContext.updateOptions({
                            xaxis: {
                            categories: newarr,
                            },
                        }, false, false); // <- no animation, no full redraw
                    }
                    

                },
                selection: (chartContext, { xaxis, yaxis }) => {
                    console.log("Selected range:", xaxis);
                    // if (chartContext && chartContext.updateOptions) {
                    //     chartContext.resetSeries(true,true); // <- no animation, no full redraw
                    // }
                },
                dataPointSelection: (event, chartContext, config) => {
                    console.log("Clicked point index:", config.dataPointIndex);
                    // console.log("Value:", config.w.config.series[config.seriesIndex].data[config.dataPointIndex]);
                }

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
                    show: true, // Hide grid lines on x-axis
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
            categories:arr,
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
        // °C, %, mm, cm, m/s, °, hPa, km, kPa, code  -- total 33 parameter in this humitdity and cloud has mutual unit


        });
        //always provide same no.of elem in both series , then only it poerly apear in tool tip
      
    const setSeries = () =>{
        const filteredSeries= []
        displayMetrics.map((metric,index)=>{
            const item = series.find(obj => obj.name === metric);

            if (item && !filteredSeries.some(obj => obj.name === item.name)) {
                filteredSeries.push(item);
            }

            // filteredSeries.push(series.find(obj => obj.name == metric))
        })
        
        return filteredSeries

    }
    const setYAxis = () => {
        const filteredYAxis= []
        displayMetrics.map((metric,index)=>{
            const item = yAxis.find(obj => obj.seriesName === metric);
            if (item && !filteredYAxis.some(obj => obj.seriesName === item.seriesName)) {
            filteredYAxis.push(item);
        }

        })
        console.log("Updating..")
        setOptions((prev)=> ({
            ...prev,
            yaxis:filteredYAxis,
        }))
    }
 
    useEffect(() => {
        setYAxis()
    }, [displayMetrics]); 
    // console.log(Chartref);
    useEffect(() => {
        if (Chartref.current) {
        }
    }, [])

    
    return (<div className="max-w-full overflow-x-auto custom-scrollbar">

      <div id="chartEight" className="min-w-[1000px]">
        <Chart options={options} series={setSeries()} type="area" height={310} chartRef={Chartref}/>
      </div>
    </div>);
}
