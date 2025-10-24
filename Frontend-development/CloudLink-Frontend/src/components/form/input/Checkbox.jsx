import React from "react";

const Checkbox = ({
  label,
  checked,
  id,
  onChange,
  className = "",
  disabled = false,
}) => {
  return (
    <div
      className={`flex items-center space-x-3 select-none ${
        disabled ? "cursor-not-allowed opacity-60" : "cursor-pointer"
      }`}
      onClick={() => !disabled && onChange(!checked)} // ✅ click handled at parent div
    >
      {/* Visual box */}
      <div
        className={`relative w-5 h-5 rounded-md border flex items-center justify-center transition-all duration-200 ${
          checked
            ? "bg-yellow-500 border-transparent"
            : "border-gray-300 dark:border-gray-700 bg-transparent"
        } ${className}`}
      >
        {/* Tick mark */}
        {checked && (
          <svg
            className="w-3.5 h-3.5 text-white"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 14 14"
            fill="none"
          >
            <path
              d="M11.6666 3.5L5.24992 9.91667L2.33325 7"
              stroke="white"
              strokeWidth="1.94437"
              strokeLinecap="round"
              strokeLinejoin="round"
            />
          </svg>
        )}
      </div>

      {label && (
        <span className="text-sm font-medium text-gray-800 dark:text-gray-200">
          {label}
        </span>
      )}
    </div>
  );
};

export default Checkbox;


// const Checkbox = ({ label, checked, id, onChange, className = "", disabled = false, }) => {
//   // console.log(checked)
//     return (<label className={`flex items-center space-x-3 group cursor-pointer ${disabled ? "cursor-not-allowed opacity-60" : ""}`}>
//       {/* {console.log("re rendering happening..")} */}
//       <div className="relative w-5 h-5">
//         <input id={id} type="checkbox" className={`w-5 h-5 appearance-none cursor-pointer dark:border-gray-700 border border-gray-300 checked:border-transparent rounded-md checked:bg-yellow-500 disabled:opacity-60 
//           ${className}`} checked={checked} onChange={(e) =>{ onChange(e.target.checked);console.log("Changing");} } onClick={()=>console.log("clicked",id)} disabled={disabled}/>

    
//         {checked && (<svg className="absolute transform -translate-x-1/2 -translate-y-1/2 pointer-events-none top-1/2 left-1/2" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
//             <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" strokeWidth="1.94437" strokeLinecap="round" strokeLinejoin="round"/>
//           </svg>)}
//         {disabled && (<svg className="absolute transform -translate-x-1/2 -translate-y-1/2 pointer-events-none top-1/2 left-1/2" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
//             <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="#E4E7EC" strokeWidth="2.33333" strokeLinecap="round" strokeLinejoin="round"/>
//           </svg>)}
//       </div>
//       {label && (<span className="text-sm font-medium text-gray-800 dark:text-gray-200">
//           {label}
//         </span>)}
//     </label>);
// };
// export default Checkbox;
