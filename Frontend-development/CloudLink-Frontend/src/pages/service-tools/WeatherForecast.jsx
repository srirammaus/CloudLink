import PageBreadcrumb from "../../components/common/PageBreadCrumb";
import ComponentCard from "../../components/common/ComponentCard";
import WeatherChart from "../../components/WeatherForecast/WeatherChart";
import PageMeta from "../../components/common/PageMeta";
import { useEffect, useState } from "react";
import { Dropdown } from "../../components/ui/dropdown/Dropdown";
import { DropdownItem } from "../../components/ui/dropdown/DropdownItem";
import Checkbox from "../../components/form/input/Checkbox";
export default function WeatherForecast() {
      const [metricsArr,setMeticsArr] = useState([])
      const [isOpen, setIsOpen] = useState(false);
      //cloud and humidity has mutual units
const metrics = [
  "temperature_2m",
  "apparent_temperature",
  "temperature_80m",
  "temperature_120m",
  "temperature_180m",
  "dew_point_2m",
  "relative_humidity_2m",
  "vapour_pressure_deficit", // Humidity
  "cloud_cover",
  "cloud_cover_low",
  "cloud_cover_mid",
  "cloud_cover_high",
  "precipitation",
  "precipitation_probability",
  "rain",
  "showers",
  "evapotranspiration",
  "et0_fao_evapotranspiration",
  "snowfall",
  "snow_depth",
  "wind_speed_10m",
  "wind_speed_80m",
  "wind_speed_120m",
  "wind_speed_180m",
  "wind_gusts_10m",
  "wind_direction_10m",
  "wind_direction_80m",
  "wind_direction_120m",
  "wind_direction_180m",
  "pressure_msl",
  "surface_pressure",
  "visibility",
  "vapour_pressure_deficit", // Air Pressure
  "weather_code"
];

      const updateMetrics = (metrics) => {
          setDisplayMetrics(metrics)
      }
   
      const [displayMetrics,setDisplayMetrics] = useState([]) //"Temperature (°C)","Humidity (%)"
      const [isChecked,setIsChecked] = useState(metrics.map((metric,index)=>( { id:index,name:metric,checked:false} )));
      function toggleCheck(index) {
        setIsChecked(prev => {
          const updated = prev.map((item, i) => {
            if (i === index) return { ...item, checked: !item.checked };
            return item;
          });

          // compute metrics array directly from updated checkboxes
          const selected = updated
            .filter(item => item.checked)
            .map(item => item.name);

          // update both states in sync
          setMeticsArr(selected);
          setDisplayMetrics(selected); // optional: if you want live update (no closeDropdown delay)

          return updated;
        });
      }
      function toggleDropdown() {
          setIsOpen(!isOpen);
      }
      function closeDropdown() {
          console.log(metricsArr)
          // updateMetrics(metricsArr)
          setIsOpen(false);
      }
      useEffect(()=>{       
        setIsChecked(prev => {
          const updated = prev.map((item, i) => {
            if (i === 1 || i === 0) return { ...item, checked: true };
            return item;
          });

          // compute metrics array directly from updated checkboxes
          const selected = updated
            .filter(item => item.checked)
            .map(item => item.name);

          // update both states in sync
          setMeticsArr(selected);
          setDisplayMetrics(selected); // optional: if you want live update (no closeDropdown delay)

          return updated;
        })
      },[]);
//       useEffect(() => {
//   setIsChecked(prev =>
//     prev.map((item, i) =>
//       i === 0 || i === 1 ? { ...item, checked: true } : item
//     )
//   );
// }, []);

    return (<>
      <PageMeta title="CloudLink | The Real Technology" description="The CloudLink is application developed by sriram marippan, This application maily used integrate multiple tools, currently it is having Unfied Weather Service"/>
      <PageBreadcrumb pageTitle="🌤️ Weather" />
      <div className="space-y-6">
        <ComponentCard title="Graph">
          <div className="flex w-full relative">
              <button onClick={toggleDropdown} className="flex flex-none items-center text-gray-700 dropdown-toggle dark:text-gray-400 w-[50%] ">

                  <span className="block font-medium text-theme-sm overflow-hidden whitespace-nowrap trunacate">Select Metrics</span>
                  <svg className={`stroke-gray-500 dark:stroke-gray-400 transition-transform duration-200 ${isOpen ? "rotate-180" : ""}`} width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M4.3125 8.65625L9 13.3437L13.6875 8.65625" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                  </svg>
              </button>
              <Dropdown isOpen={isOpen} onClose={closeDropdown} className="flex top-5 left-0 absolute w-[25%] max-h-[45vh] overflow-x-hidden overflow-y-auto flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark custom-scrollbar">
              <ul className="flex flex-col gap-1 pt-4 pb-3 border-b border-gray-200 dark:border-gray-800 dark:text-gray-400  ">
                  {metrics.map((metric,index)=>(
                      <li key={index}>
                      <DropdownItem key={index} className="inline-flex items-center gap-3 px-3 py-2 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                          <div className="flex-col items-center justify-between">
                            <div className="flex pb-2 items-center gap-3">
                              <Checkbox checked={isChecked[index].checked} onChange={()=>toggleCheck(index)} />
                              <span className="block font-normal text-gray-700 text-theme-sm dark:text-gray-400">
                                {metric}
                              </span>
                            </div>
                          </div> 
                        </DropdownItem>
                      </li>
                  ))}
                </ul>
            </Dropdown>
          </div>
          
          <WeatherChart  displayMetrics={displayMetrics}/>
        </ComponentCard>
      </div>
    </>);
}
