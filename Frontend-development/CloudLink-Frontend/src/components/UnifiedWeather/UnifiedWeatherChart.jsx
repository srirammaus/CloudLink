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
export default function UnifiedWeatherChart({
    displayMetrics = [], //defaults
    
}) {

    const Chartref = useRef(null)
    const series = [
        // 🌡️ Temperature-related
                { name: "temperature_2m", data: [22,21,20,19,18,20,23,25,27,29,30,31], color: "#FF5733" ,yAxisIndex:0},
        {  name: "apparent_temperature", data: [21,20,19,18,17,19,22,24,26,28,29,30], color: "#FF4500" ,yAxisIndex:0},
        {  name: "temperature_80m", data: [21,20,19,18,17,19,22,24,26,28,29,30], color: "#E74C3C" ,yAxisIndex:0},
        {  name: "temperature_120m", data: [20,19,18,17,16,18,21,23,25,27,28,29], color: "#C0392B" ,yAxisIndex:0},
        {  name: "temperature_180m", data: [19,18,17,16,15,17,20,22,24,26,27,28], color: "#A93226" ,yAxisIndex:0},
        {  name: "dew_point_2m", data: [15,14,13,12,11,12,14,16,17,18,19,20], color: "#FF8C00" ,yAxisIndex:0},

        // 💧 Humidity & Vapor
        {  name: "relative_humidity_2m", data: [65,67,70,72,68,66,64,60,58,55,57,59], color: "#1E90FF" ,yAxisIndex:1},
        {  name: "vapour_pressure_deficit", data: [1.0,0.9,0.8,0.7,0.6,0.5,0.4,0.3,0.2,0.3,0.4,0.5], color: "#1E8449" ,yAxisIndex:1},

            // ☁️ Clouds
        {  name: "cloud_cover", data: [80,75,70,65,60,55,50,45,40,35,30,25], color: "#7F8C8D",yAxisIndex:1},
        {  name: "cloud_cover_low", data: [20,25,30,35,40,45,50,55,60,65,70,75], color: "#95A5A6",yAxisIndex:1},
        {  name: "cloud_cover_mid", data: [10,15,20,25,30,35,40,45,50,55,60,65], color: "#BDC3C7",yAxisIndex:1},
        {  name: "cloud_cover_high", data: [5,10,15,20,25,30,35,40,45,50,55,60], color: "#ECF0F1",yAxisIndex:1},

        // 🌧️ Rain / Precipitation
        {  name: "precipitation", data: [2,0,1,3,0,0,5,10,15,8,4,1], color: "#0066CC" ,yAxisIndex:2},
        {  name: "precipitation_probability", data: [10,5,15,20,25,30,35,40,45,50,55,60], color: "#00BFFF" ,yAxisIndex:2},
        {  name: "rain", data: [1,0,0,2,0,0,4,8,12,6,3,1], color: "#0055AA" ,yAxisIndex:2},
        {  name: "showers", data: [0,0,1,1,0,0,2,5,7,3,2,0], color: "#004488" ,yAxisIndex:2},


         
        {  name: "evapotranspiration", data: [0.5,0.6,0.7,0.8,0.9,1.0,1.1,1.2,1.3,1.2,1.1,1.0], color: "#27AE60",yAxisIndex:2 },
        {  name: "et0_fao_evapotranspiration", data: [0.4,0.5,0.6,0.7,0.8,0.9,1.0,1.1,1.0,0.9,0.8,0.7], color: "#229954",yAxisIndex:2},

  
        // ❄️ Snow
        {  name: "snowfall", data: [0,0,0,0,0,0,0,0,1,2,3,2], color: "#A9CCE3" ,yAxisIndex:3},
        {  name: "snow_depth", data: [0,0,0,0,0,0,0,0,0,1,2,2], color: "#7FB3D5" ,yAxisIndex:3},

          // 💨 Wind
        {  name: "wind_speed_10m", data: [3,4,5,4,6,5,7,8,10,9,8,7], color: "#2ECC71",yAxisIndex:4 },
        {  name: "wind_speed_80m", data: [2,3,4,3,5,4,6,7,9,8,7,6], color: "#28B463",yAxisIndex:4 },
        {  name: "wind_speed_120m", data: [1,2,3,2,4,3,5,6,8,7,6,5], color: "#239B56",yAxisIndex:4 },
        {  name: "wind_speed_180m", data: [0.5,1,2,1,3,2,4,5,7,6,5,4], color: "#1D8348",yAxisIndex:4 },
        {  name: "wind_gusts_10m", data: [5,6,7,8,9,10,11,12,13,12,11,10], color: "#16A085",yAxisIndex:4 },

        { name: "wind_direction_10m", data: [90,100,110,120,135,140,150,160,170,180,190,200], color: "#F1C40F",yAxisIndex:5 },
        { name: "wind_direction_80m", data: [100,110,120,130,145,150,160,170,180,190,200,210], color: "#D4AC0D",yAxisIndex:5 },
        { name: "wind_direction_120m", data: [110,120,130,140,155,160,170,180,190,200,210,220], color: "#B7950B",yAxisIndex:5 },
        { name: "wind_direction_180m", data: [120,130,140,150,165,170,180,190,200,210,220,230], color: "#9A7D0A",yAxisIndex:5 },
        
    
    
        // 🧭 Pressure
        {  name: "pressure_msl", data: [1012,1010,1008,1009,1011,1013,1015,1014,1012,1011,1010,1009], color: "#8E44AD",yAxisIndex:6 },
        {  name: "surface_pressure", data: [1011,1009,1007,1008,1010,1012,1014,1013,1011,1010,1009,1008], color: "#7D3C98",yAxisIndex:6 },

            // 🌫️ Visibility & Evaporation
        {  name: "visibility", data: [8,9,10,9,8,7,6,6,7,8,9,10], color: "#3498DB",yAxisIndex:7 },
        
       

        //Air Pressure 
        {
          name: "vapour_pressure_deficit",
          yAxisIndex: 8,
          data: [1.0, 0.9, 0.8, 0.7, 0.6, 0.5, 0.4, 0.3, 0.2, 0.3, 0.4, 0.5],
          color: "#1E8449",
        },

        // 🌤️ Weather
        { name: "weather_code", data: [0,0,1,1,2,2,3,3,1,0,0,1], color: "#34495E",yAxisIndex:9}

        
        ];
        const seriesByUnit = {
        percent: [
            "relative_humidity_2m",
            "cloud_cover",
            "cloud_cover_low",
            "cloud_cover_mid",
            "cloud_cover_high"
        ],
        celsius: [
            "temperature_2m",
            "apparent_temperature",
            "temperature_80m",
            "temperature_120m",
            "temperature_180m",
            "dew_point_2m"
        ],
        mm: [
            "precipitation",
            "precipitation_probability",
            "rain",
            "showers",
            "evapotranspiration",
            "et0_fao_evapotranspiration"
        ],
        cm: [
            "snowfall",
            "snow_depth"
        ],
        "m/s": [
            "wind_speed_10m",
            "wind_speed_80m",
            "wind_speed_120m",
            "wind_speed_180m",
            "wind_gusts_10m"
        ],
        degrees: [
            "wind_direction_10m",
            "wind_direction_80m",
            "wind_direction_120m",
            "wind_direction_180m"
        ],
        hPa: [
            "pressure_msl",
            "surface_pressure"
        ],
        km: [
            "visibility"
        ],
        kPa: [
            "vapour_pressure_deficit"   // Air Pressure entry
        ],
        code: [
            "weather_code"
        ]
        };

   const yAxis = [
                // 0️⃣ Temperature
                {
                    seriesName: seriesByUnit.celsius,
                    title: { text: "Temp (°C)", style: { color: "#FF5733" } },
                    labels: { style: { colors: "#FF5733" } },
                },

                // 1️⃣ Humidity / Clouds / Vapor
                {
                    seriesName: seriesByUnit.percent,
                    title: { text: "Humidity / Cloud (%)", style: { color: "#1E90FF" } },
                    labels: { style: { colors: "#1E90FF" } },
                },

                // 2️⃣ Rain / Precipitation / Evaporation
                {
                    seriesName: seriesByUnit.mm,
                    title: { text: "Precipitation / Evaporation (mm)", style: { color: "#0066CC" } },
                    labels: { style: { colors: "#0066CC" } },
                },

                // 3️⃣ Snow
                {
                    seriesName: seriesByUnit.cm,
                    title: { text: "Snow (cm)", style: { color: "#A9CCE3" } },
                    labels: { style: { colors: "#A9CCE3" } },
                },

                // 4️⃣ Wind Speed
                {
                    seriesName: seriesByUnit["m/s"],
                    title: { text: "Wind Speed (m/s)", style: { color: "#2ECC71" } },
                    labels: { style: { colors: "#2ECC71" } },
                },

                // 5️⃣ Wind Direction
                {
                    seriesName: seriesByUnit.degrees,
                    title: { text: "Wind Direction (°)", style: { color: "#F1C40F" } },
                    labels: { style: { colors: "#F1C40F" } },
                },

                // 6️⃣ Pressure (hPa)
                {
                    seriesName: seriesByUnit.hPa,
                    title: { text: "Pressure (hPa)", style: { color: "#8E44AD" } },
                    labels: { style: { colors: "#8E44AD" } },
                },

                // 7️⃣ Visibility
                {
                    seriesName: seriesByUnit.km,
                    title: { text: "Visibility (km)", style: { color: "#3498DB" } },
                    labels: { style: { colors: "#3498DB" } },
                },

                // 8️⃣ Air Pressure (kPa) / Vapour Pressure Deficit
                {
                    seriesName: seriesByUnit.kPa,
                    title: { text: "Air Pressure (kPa)", style: { color: "#1E8449" } },
                    labels: { style: { colors: "#1E8449" } },
                },

                // 9️⃣ Weather Code
                {
                    seriesName: seriesByUnit.code,
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
                    //in future jsut gonna place the hrs only not days
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
            const item = yAxis.find(obj => obj.seriesName.includes(metric));
            
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
