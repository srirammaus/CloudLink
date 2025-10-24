
// | Class      | CSS Equivalent  | Ideal Use                |
// | ---------- | --------------- | ------------------------ |
// | `h-1`      | `0.25rem` (4px) | borders, small dividers  |
// | `h-10`     | `2.5rem` (40px) | navbars, cards           |
// | `h-16`     | `4rem` (64px)   | headers, buttons, panels |
// | `h-20`     | `5rem` (80px)   | compact cards            |
// | `h-40`     | `10rem` (160px) | dashboard blocks         |
// | `h-64`     | `16rem` (256px) | larger widgets           |
// | `h-full`   | `100%`          | fill parent container    |
// | `h-screen` | `100vh`         | full viewport height     |


import { useState } from "react";
import { useModal } from "../../hooks/useModal";
import { Modal } from "../ui/modal";
import Button from "../ui/button/Button";
import Input from "../form/input/InputField";
import Label from "../form/Label";
import { useNavigate } from "react-router";
export default function ControlCentreInterface() {
  const { isOpen, openModal, closeModal } = useModal();
  const [selectedDevice, setSelectedDevice] = useState(null);
  const [editedConfig, setEditedConfig] = useState({});

  // Base template
  const baseDevice = {
    DeviceID: "BS-001",
    DeviceName: "Base Station",
    DeviceIP: "157.46.25.10",
    DeviceModel: "ESP32-WROOM-32",
    DeviceType: "gateway",
    Location: {
      latitude: 13.0827,
      longitude: 80.2707,
      address: "Chennai, India",
    },
    enumerable_configuration: {
      firmwareVersion: "v2.1.4",
      macAddress: "3C:71:BF:2A:9E:11",
      connectionStatus: "online",
      uptime: "4h 32m",
      lastHeartbeat: "2025-10-20T10:24:00Z",
      networkType: "WiFi",
      signalStrength: "-68 dBm",
      temperature: "37°C",
      voltage: "4.9V",
      memoryUsage: "62%",
      cpuLoad: "18%",
      connectedClients: 5,
    },
    writable_configuration: {
      reportingInterval: 30,
      rebootOnFailure: true,
      thresholdTemperature: 45,
      wifiSSID: "CloudLink_Network",
      wifiPassword: "********",
      enableOTA: true,
      logLevel: "INFO",
      sleepMode: false,
      timeZone: "Asia/Kolkata",
      dataRetentionDays: 7,
    },
    diagnostics: {
      lastReboot: "2025-10-20T06:40:12Z",
      rebootReason: "Manual Restart",
      errorLogs: [],
      firmwareUpdatePending: false,
    },
    cloudLink: {
      registeredAt: "2025-10-19T09:00:00Z",
      lastSync: "2025-10-20T10:25:00Z",
      apiVersion: "1.3.0",
      encryption: "AES-256",
      mqttTopic: "cloudlink/devices/BS-001/data",
    },
  };
//   use this below example for reference you will understand and ()-implicit return , return -direct return dont confused
// const d = Array.from({length:3}).map((elem,index)=>{
//     return "sriam" 
// })
  const devices = Array.from({ length: 3 }).map((_, i) => ({
    ...baseDevice,
    // DeviceID: `BS-00${i + 1}`,
  }));

  const renderSection = (title, dataObj) => (
    <div className="mt-4">
      <h4 className="text-lg font-semibold mb-2 text-gray-800 dark:text-white/90">{title}</h4>
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
        {Object.entries(dataObj).map(([key, val]) => (
          <div key={key}>
            <p className="text-xs text-gray-500 mb-1">{key}</p>
            <p className="text-sm text-gray-800 dark:text-white/90">
              {typeof val === "object" ? JSON.stringify(val) : String(val)}
            </p>
          </div>
        ))}
      </div>
    </div>
  );

  // When "Edit" is clicked
  const handleEdit = (device) => {
    setSelectedDevice(device);
    const allowedEdits = Object.assign({},device.enumerable_configuration); //add whatever you want later
    setEditedConfig(allowedEdits);
    openModal();
  };

  // Handle input changes inside modal
  const handleInputChange = (key, value) => {
    setEditedConfig((prev) => ({
      ...prev,
      [key]: value,
    }));
  };

  const handleSave = () => {
    console.log("Updated config for:", selectedDevice.DeviceID, editedConfig);
    closeModal();
  };

  return (
    <div className="space-y-8 p-6">
      {devices.map((device, index) => (
        <div
          key={index}
          className="p-6 border rounded-2xl bg-white dark:bg-gray-900 dark:border-gray-800">
            
          <h3 className="text-xl font-bold mb-3 text-gray-800 dark:text-white/90">{device.DeviceName}</h3>
          {renderSection("Basic Info", {
            ID: device.DeviceID,
            Type: device.DeviceType,
            IP: device.DeviceIP,
            Model: device.DeviceModel,
          })}
          {renderSection("Location", device.Location)}
          {renderSection("Active Configuration", device.writable_configuration)}
          {renderSection("Default Configuration", device.enumerable_configuration)}
          {renderSection("Diagnostics", device.diagnostics)}
          {renderSection("CloudLink", device.cloudLink)}

          <div className="mt-4 flex justify-end">
            <button
              onClick={() => handleEdit(device)}
              className="rounded-lg border px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
            >
              Edit
            </button>
          </div>
        </div>
      ))}

      {/* Modal */}
      <Modal isOpen={isOpen} onClose={closeModal} className="max-w-[700px] m-4">
        {selectedDevice && (
          <div className="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
            <div className="px-2 pr-14">
              <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
                Edit Configuration — {selectedDevice.DeviceName}
              </h4>
              <p className="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
                Update the device’s writable configuration parameters.
              </p>
            </div>

            <form className="flex flex-col">
              <div className="custom-scrollbar h-[450px] overflow-y-auto px-2 pb-3">
                <div className="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                  {Object.entries(editedConfig).map(([key, val]) => (
                    <div key={key}>
                      <Label>{key}</Label>
                      <Input
                        type="text"
                        value={String(val)}
                        onChange={(e) => handleInputChange(key, e.target.value)}
                      />
                    </div>
                  ))}
                </div>
              </div>

              <div className="flex items-center gap-3 px-2 mt-6 lg:justify-end">
                <Button size="sm" variant="outline" onClick={closeModal}>
                  Close
                </Button>
                <Button size="sm" onClick={handleSave}>
                  Save Changes
                </Button>
              </div>
            </form>
          </div>
        )}
      </Modal>
    </div>
  );
}

// import { useModal } from "../../hooks/useModal";
// import { Modal } from "../ui/modal";
// import Button from "../ui/button/Button";
// import Input from "../form/input/InputField";
// import Label from "../form/Label";

// export default function ControlCentreInterface () {
//     const devices = []  
//     const device1 = {
//         DeviceID: "BS-001",                 
//         DeviceName: "Base Station",
//         DeviceIP: "157.46.25.10",
//         DeviceModel: "ESP32-WROOM-32",
//         DeviceType: "gateway",              
//         Location: {
//             latitude: 13.0827,
//             longitude: 80.2707,
//             address: "Chennai, India"
//         },

//         enumerable_configuration: {
//             firmwareVersion: "v2.1.4",
//             macAddress: "3C:71:BF:2A:9E:11",
//             connectionStatus: "online",       
//             uptime: "4h 32m",
//             lastHeartbeat: "2025-10-20T10:24:00Z",
//             networkType: "WiFi",
//             signalStrength: "-68 dBm",
//             temperature: "37°C",
//             voltage: "4.9V",                  
//             memoryUsage: "62%",
//             cpuLoad: "18%",
//             connectedClients: 5               
//         },

//         writable_configuration: {
//             reportingInterval: 30,            
//             rebootOnFailure: true,
//             thresholdTemperature: 45,         
//             wifiSSID: "CloudLink_Network",
//             wifiPassword: "********",
//             enableOTA: true,                  
//             logLevel: "INFO",                
//             sleepMode: false,                 
//             timeZone: "Asia/Kolkata",
//             dataRetentionDays: 7,             
//         },

//         diagnostics: {
//             lastReboot: "2025-10-20T06:40:12Z",
//             rebootReason: "Manual Restart",
//             errorLogs: [],
//             firmwareUpdatePending: false,
//         },

//         cloudLink: {
//             registeredAt: "2025-10-19T09:00:00Z",
//             lastSync: "2025-10-20T10:25:00Z",
//             apiVersion: "1.3.0",
//             encryption: "AES-256",
//             mqttTopic: "cloudlink/devices/BS-001/data",
//         }
//     };

//         const device2 = {
//         DeviceID: "BS-001",                 
//         DeviceName: "Base Station",
//         DeviceIP: "157.46.25.10",
//         DeviceModel: "ESP32-WROOM-32",
//         DeviceType: "gateway",              
//         Location: {
//             latitude: 13.0827,
//             longitude: 80.2707,
//             address: "Chennai, India"
//         },

//         enumerable_configuration: {
//             firmwareVersion: "v2.1.4",
//             macAddress: "3C:71:BF:2A:9E:11",
//             connectionStatus: "online",       
//             uptime: "4h 32m",
//             lastHeartbeat: "2025-10-20T10:24:00Z",
//             networkType: "WiFi",
//             signalStrength: "-68 dBm",
//             temperature: "37°C",
//             voltage: "4.9V",                  
//             memoryUsage: "62%",
//             cpuLoad: "18%",
//             connectedClients: 5               
//         },

//         writable_configuration: {
//             reportingInterval: 30,            
//             rebootOnFailure: true,
//             thresholdTemperature: 45,         
//             wifiSSID: "CloudLink_Network",
//             wifiPassword: "********",
//             enableOTA: true,                  
//             logLevel: "INFO",                
//             sleepMode: false,                 
//             timeZone: "Asia/Kolkata",
//             dataRetentionDays: 7,             
//         },

//         diagnostics: {
//             lastReboot: "2025-10-20T06:40:12Z",
//             rebootReason: "Manual Restart",
//             errorLogs: [],
//             firmwareUpdatePending: false,
//         },

//         cloudLink: {
//             registeredAt: "2025-10-19T09:00:00Z",
//             lastSync: "2025-10-20T10:25:00Z",
//             apiVersion: "1.3.0",
//             encryption: "AES-256",
//             mqttTopic: "cloudlink/devices/BS-001/data",
//         }
//     };

//          const device3 = {
//         DeviceID: "BS-001",                 
//         DeviceName: "Base Station",
//         DeviceIP: "157.46.25.10",
//         DeviceModel: "ESP32-WROOM-32",
//         DeviceType: "gateway",              
//         Location: {
//             latitude: 13.0827,
//             longitude: 80.2707,
//             address: "Chennai, India"
//         },

//         enumerable_configuration: {
//             firmwareVersion: "v2.1.4",
//             macAddress: "3C:71:BF:2A:9E:11",
//             connectionStatus: "online",       
//             uptime: "4h 32m",
//             lastHeartbeat: "2025-10-20T10:24:00Z",
//             networkType: "WiFi",
//             signalStrength: "-68 dBm",
//             temperature: "37°C",
//             voltage: "4.9V",                  
//             memoryUsage: "62%",
//             cpuLoad: "18%",
//             connectedClients: 5               
//         },

//         writable_configuration: {
//             reportingInterval: 30,            
//             rebootOnFailure: true,
//             thresholdTemperature: 45,         
//             wifiSSID: "CloudLink_Network",
//             wifiPassword: "********",
//             enableOTA: true,                  
//             logLevel: "INFO",                
//             sleepMode: false,                 
//             timeZone: "Asia/Kolkata",
//             dataRetentionDays: 7,             
//         },

//         diagnostics: {
//             lastReboot: "2025-10-20T06:40:12Z",
//             rebootReason: "Manual Restart",
//             errorLogs: [],
//             firmwareUpdatePending: false,
//         },

//         cloudLink: {
//             registeredAt: "2025-10-19T09:00:00Z",
//             lastSync: "2025-10-20T10:25:00Z",
//             apiVersion: "1.3.0",
//             encryption: "AES-256",
//             mqttTopic: "cloudlink/devices/BS-001/data",
//         }
//     };
//          const device4 = {
//         DeviceID: "BS-001",                 
//         DeviceName: "Base Station",
//         DeviceIP: "157.46.25.10",
//         DeviceModel: "ESP32-WROOM-32",
//         DeviceType: "gateway",              
//         Location: {
//             latitude: 13.0827,
//             longitude: 80.2707,
//             address: "Chennai, India"
//         },

//         enumerable_configuration: {
//             firmwareVersion: "v2.1.4",
//             macAddress: "3C:71:BF:2A:9E:11",
//             connectionStatus: "online",       
//             uptime: "4h 32m",
//             lastHeartbeat: "2025-10-20T10:24:00Z",
//             networkType: "WiFi",
//             signalStrength: "-68 dBm",
//             temperature: "37°C",
//             voltage: "4.9V",                  
//             memoryUsage: "62%",
//             cpuLoad: "18%",
//             connectedClients: 5               
//         },

//         writable_configuration: {
//             reportingInterval: 30,            
//             rebootOnFailure: true,
//             thresholdTemperature: 45,         
//             wifiSSID: "CloudLink_Network",
//             wifiPassword: "********",
//             enableOTA: true,                  
//             logLevel: "INFO",                
//             sleepMode: false,                 
//             timeZone: "Asia/Kolkata",
//             dataRetentionDays: 7,             
//         },

//         diagnostics: {
//             lastReboot: "2025-10-20T06:40:12Z",
//             rebootReason: "Manual Restart",
//             errorLogs: [],
//             firmwareUpdatePending: false,
//         },

//         cloudLink: {
//             registeredAt: "2025-10-19T09:00:00Z",
//             lastSync: "2025-10-20T10:25:00Z",
//             apiVersion: "1.3.0",
//             encryption: "AES-256",
//             mqttTopic: "cloudlink/devices/BS-001/data",
//         }
//     };
//     const device5 = {
//         DeviceID: "BS-001",                 
//         DeviceName: "Base Station",
//         DeviceIP: "157.46.25.10",
//         DeviceModel: "ESP32-WROOM-32",
//         DeviceType: "gateway",              
//         Location: {
//             latitude: 13.0827,
//             longitude: 80.2707,
//             address: "Chennai, India"
//         },

//         enumerable_configuration: {
//             firmwareVersion: "v2.1.4",
//             macAddress: "3C:71:BF:2A:9E:11",
//             connectionStatus: "online",       
//             uptime: "4h 32m",
//             lastHeartbeat: "2025-10-20T10:24:00Z",
//             networkType: "WiFi",
//             signalStrength: "-68 dBm",
//             temperature: "37°C",
//             voltage: "4.9V",                  
//             memoryUsage: "62%",
//             cpuLoad: "18%",
//             connectedClients: 5               
//         },

//         writable_configuration: {
//             reportingInterval: 30,            
//             rebootOnFailure: true,
//             thresholdTemperature: 45,         
//             wifiSSID: "CloudLink_Network",
//             wifiPassword: "********",
//             enableOTA: true,                  
//             logLevel: "INFO",                
//             sleepMode: false,                 
//             timeZone: "Asia/Kolkata",
//             dataRetentionDays: 7,             
//         },

//         diagnostics: {
//             lastReboot: "2025-10-20T06:40:12Z",
//             rebootReason: "Manual Restart",
//             errorLogs: [],
//             firmwareUpdatePending: false,
//         },

//         cloudLink: {
//             registeredAt: "2025-10-19T09:00:00Z",
//             lastSync: "2025-10-20T10:25:00Z",
//             apiVersion: "1.3.0",
//             encryption: "AES-256",
//             mqttTopic: "cloudlink/devices/BS-001/data",
//         }
//     };
//     devices.push(device1);
//     devices.push(device2);
//     devices.push(device3);
//     devices.push(device4);
//     devices.push(device5);

//        const { isOpen, openModal, closeModal } = useModal();
//     const handleSave = () => {
//         // Handle save logic here
//         console.log("Saving changes...");
//         closeModal();
//     };
//     function recursiveHandler (obj,idx) {

//     }
//     function DeviceCardComponent (props) {
//         return (
//             <>
//             {devices.map((device,index)=>(
//                 console.log("fine")
//             ))}
//             </>
//         )

//     }
//     return (
//     <>
//     {devices.map((device,index)=>(


//         <div key={index} className="p-5 border border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6 dark:border-gray-800 bg-white dark:bg-white/[0.03] ">
//         <div className="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
//         <div>
//             <h4 className="text-lg font-semibold text-gray-800 dark:text-white/90 lg:mb-6">
//                 {device.DeviceID}
//             </h4>

//             <div className="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     Device Name
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {device.DeviceName}
//                 </p>
//                 </div>

//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     Deivce IP
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {device.DeviceIP}
//                 </p>
//                 </div>

//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     Device Model
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {device.DeviceModel}
//                 </p>
//                 </div>

//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     Device Type
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {device.DeviceType}
//                 </p>
//                 </div>

//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     Device Location
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90 mb-2">
//                     {device.Location.latitude}
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90 mb-2">
//                     {device.Location.longitude}
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90 mb-2">
//                     {device.Location.address}
//                 </p>
//                 </div>
                
//             </div>

//             <div className="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
//                 <h4 className="text-lg font-semibold text-gray-800 dark:text-white/90 lg:mt-3">
//                     Active config
//                 </h4>
//                 <div></div>
//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     reportingInterval
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {device.writable_configuration.reportingInterval}
//                 </p>
//                 </div>

//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     rebootOnFailure
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {JSON.stringify(device.writable_configuration.rebootOnFailure)}
//                 </p>
//                 </div>

//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     thresholdTemperature
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {JSON.stringify(device.writable_configuration.thresholdTemperature)}
//                 </p>
//                 </div>

//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     wifiSSID
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {(device.writable_configuration.wifiSSID)}
//                 </p>
//                 </div>

//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     wifiPassword
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {JSON.stringify(device.writable_configuration.wifiPassword)}
//                 </p>
//                 </div>

//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     enableOTA
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {JSON.stringify(device.writable_configuration.enableOTA)}
//                 </p>
//                 </div>

//                     <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     logLevel
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {(device.writable_configuration.logLevel)}
//                 </p>
//                 </div>

//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     sleepMode
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {JSON.stringify(device.writable_configuration.sleepMode)}
//                 </p>
//                 </div>
//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     TimeZone
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {(device.writable_configuration.timeZone)}
//                 </p>
//                 </div>

//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     dataRetentionDays
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {JSON.stringify(device.writable_configuration.dataRetentionDays)}
//                 </p>
//                 </div>

                
                
//             </div>

            
//             <div className="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
//                 <h4 className="text-lg font-semibold text-gray-800 dark:text-white/90 lg:mt-3">
//                     Default config
//                 </h4>
//                 <div></div>
//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     firmwareVersion
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {device.enumerable_configuration.firmwareVersion}
//                 </p>
//                 </div>

//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     macAddress
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {(device.enumerable_configuration.macAddress)}
//                 </p>
//                 </div>

//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     connectionStatus
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {(device.enumerable_configuration.connectionStatus)}
//                 </p>
//                 </div>

//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     uptime
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {(device.enumerable_configuration.uptime)}
//                 </p>
//                 </div>

//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     lastHeartbeat
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {(device.enumerable_configuration.lastHeartbeat)}
//                 </p>
//                 </div>

//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     networkType
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {(device.enumerable_configuration.networkType)}
//                 </p>
//                 </div>

//                     <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     signalStrength
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {(device.enumerable_configuration.signalStrength)}
//                 </p>
//                 </div>

//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     temperature
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {(device.enumerable_configuration.temperature)}
//                 </p>
//                 </div>
//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     voltage
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {(device.enumerable_configuration.voltage)}
//                 </p>
//                 </div>

//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     cpuLoad
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {(device.enumerable_configuration.cpuLoad)}
//                 </p>
//                 </div>
                
//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     connectedClients
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {JSON.stringify(device.enumerable_configuration.connectedClients)}
//                 </p>
//                 </div>
                
//             </div>


//             <div className="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
//                 <h4 className="text-lg font-semibold text-gray-800 dark:text-white/90 lg:mt-3">
//                     Diagnostics
//                 </h4>
//                 <div></div>
//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     lastReboot
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {device.diagnostics.lastReboot}
//                 </p>
//                 </div>

//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     rebootReason
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {(device.diagnostics.rebootReason)}
//                 </p>
//                 </div>

//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     firmwareUpdatePending
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {(device.diagnostics.firmwareUpdatePending)}
//                 </p>
//                 </div>
                
//             </div>

//             <div className="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
//                 <h4 className="text-lg font-semibold text-gray-800 dark:text-white/90 lg:mt-3">
//                     CloudLink Details
//                 </h4>
//                 <div></div>
//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     registeredAt
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {device.cloudLink.registeredAt}
//                 </p>
//                 </div>

//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     lastSync
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {(device.cloudLink.lastSync)}
//                 </p>
//                 </div>

//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     API Version
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {(device.cloudLink.apiVersion)}
//                 </p>
//                 </div>

//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     Encryption
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {(device.cloudLink.encryption)}
//                 </p>
//                 </div>

//                 <div>
//                 <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
//                     MQTT Topic
//                 </p>
//                 <p className="text-sm font-medium text-gray-800 dark:text-white/90">
//                     {(device.cloudLink.mqttTopic)}
//                 </p>
//                 </div>
                
//             </div>
//         </div>
        
        
//             <button onClick={openModal} className="flex w-full items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 lg:inline-flex lg:w-auto">
//             <svg className="fill-current" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
//                 <path fillRule="evenodd" clipRule="evenodd" d="M15.0911 2.78206C14.2125 1.90338 12.7878 1.90338 11.9092 2.78206L4.57524 10.116C4.26682 10.4244 4.0547 10.8158 3.96468 11.2426L3.31231 14.3352C3.25997 14.5833 3.33653 14.841 3.51583 15.0203C3.69512 15.1996 3.95286 15.2761 4.20096 15.2238L7.29355 14.5714C7.72031 14.4814 8.11172 14.2693 8.42013 13.9609L15.7541 6.62695C16.6327 5.74827 16.6327 4.32365 15.7541 3.44497L15.0911 2.78206ZM12.9698 3.84272C13.2627 3.54982 13.7376 3.54982 14.0305 3.84272L14.6934 4.50563C14.9863 4.79852 14.9863 5.2734 14.6934 5.56629L14.044 6.21573L12.3204 4.49215L12.9698 3.84272ZM11.2597 5.55281L5.6359 11.1766C5.53309 11.2794 5.46238 11.4099 5.43238 11.5522L5.01758 13.5185L6.98394 13.1037C7.1262 13.0737 7.25666 13.003 7.35947 12.9002L12.9833 7.27639L11.2597 5.55281Z" fill=""/>
//             </svg>
//             Edit
//             </button>
//         </div>

//         <Modal isOpen={isOpen} onClose={closeModal} className="max-w-[700px] m-4">
//             <div className="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
//             <div className="px-2 pr-14">
//                 <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
//                 Edit Personal Information
//                 </h4>
//                 <p className="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
//                 Update your details to keep your profile up-to-date.
//                 </p>
//             </div>
//             <form className="flex flex-col">
//                 <div className="custom-scrollbar h-[450px] overflow-y-auto px-2 pb-3">
//                 <div>
//                     <h5 className="mb-5 text-lg font-medium text-gray-800 dark:text-white/90 lg:mb-6">
//                     Social Links
//                     </h5>

//                     <div className="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
//                     <div>
//                         <Label>Facebook</Label>
//                         <Input type="text" value="https://www.facebook.com/PimjoHQ"/>
//                     </div>

//                     <div>
//                         <Label>X.com</Label>
//                         <Input type="text" value="https://x.com/PimjoHQ"/>
//                     </div>

//                     <div>
//                         <Label>Linkedin</Label>
//                         <Input type="text" value="https://www.linkedin.com/company/pimjo"/>
//                     </div>

//                     <div>
//                         <Label>Instagram</Label>
//                         <Input type="text" value="https://instagram.com/PimjoHQ"/>
//                     </div>
//                     </div>
//                 </div>
//                 <div className="mt-7">
//                     <h5 className="mb-5 text-lg font-medium text-gray-800 dark:text-white/90 lg:mb-6">
//                     Personal Information
//                     </h5>

//                     <div className="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
//                     <div className="col-span-2 lg:col-span-1">
//                         <Label>First Name</Label>
//                         <Input type="text" value="Musharof"/>
//                     </div>

//                     <div className="col-span-2 lg:col-span-1">
//                         <Label>Last Name</Label>
//                         <Input type="text" value="Chowdhury"/>
//                     </div>

//                     <div className="col-span-2 lg:col-span-1">
//                         <Label>Email Address</Label>
//                         <Input type="text" value="randomuser@pimjo.com"/>
//                     </div>

//                     <div className="col-span-2 lg:col-span-1">
//                         <Label>Phone</Label>
//                         <Input type="text" value="+09 363 398 46"/>
//                     </div>

//                     <div className="col-span-2">
//                         <Label>Bio</Label>
//                         <Input type="text" value="Team Manager"/>
//                     </div>
//                     </div>
//                 </div>
//                 </div>
//                 <div className="flex items-center gap-3 px-2 mt-6 lg:justify-end">
//                 <Button size="sm" variant="outline" onClick={closeModal}>
//                     Close
//                 </Button>
//                 <Button size="sm" onClick={handleSave}>
//                     Save Changes
//                 </Button>
//                 </div>
//             </form>
//             </div>
//         </Modal>
//         </div>    ))}
//     </>
//     );
// }


    // return (
    
    //     <div className="w-full min-h-[83vh] grid grid-cols-12 gap-4 md:gap-6">
    //         {devices.map((device,index)=>(
    //             // below getting the half of the width a
    //                 <div key={index} className="col-span-4 h-[50vh] max-h-[80vh] rounded-2xl border border-gray-200 bg-white px-5 pb-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03]">
    //                     {Object.entries(device).forEach(([key,value]) => {
    //                         if(typeof(value) == "string") {
    //                             console.log(`Key: ${key}, Value: ${typeof(value)}`);
    //                             <div className=""></div>
    //                         }
    //                     })}
    //                 {/* {device.DeviceName} */}
                        
    //                 </div>
    //         ))}
    //     </div>

        




    // )

// }