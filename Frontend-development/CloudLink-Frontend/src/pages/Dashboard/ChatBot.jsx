import { useState } from "react";
import PageMeta from "../../components/common/PageMeta";
// import ControlCentreInteface from "../../components/ControlCentre/ControlCentreInterface";
import { RefershIcon } from "../../icons";
import { useNavigate } from "react-router";
import { Dropdown } from "../../components/ui/dropdown/Dropdown";
import { DropdownItem } from "../../components/ui/dropdown/DropdownItem";
import { Link } from "react-router";
export default function ControlCentre() {
    const navigate = useNavigate();
    const handleRefresh = () => { 
        navigate("/assistant");
    }
    const [isOpen, setIsOpen] = useState(false);
    function toggleDropdown() {
        setIsOpen(!isOpen);
    }
    function closeDropdown() {
        setIsOpen(false);
    }
    const AI_models = [
        "ChatGPT 4.0",
        "Llma-Meta 3.5",
        "DeepSeek 3.0"
    ];
    return (<>
      <PageMeta title="CloudLink | The Real Technology" description="The CloudLink is application developed by sriram marippan, This application maily used integrate multiple tools, currently it is having Unfied Weather Service"/>
      {/* <div className="grid grid-cols-12 gap-4 md:gap-6"></div> */}
        <div className="w-full min-h-[83vh] h-[83vh]">
            <div className="w-full h-[10%] flex-col relative">
                <button onClick={toggleDropdown} className="flex flex-none items-center text-gray-700 dropdown-toggle dark:text-gray-400 w-[50%] ">

                    <span className="block font-medium text-theme-sm overflow-hidden whitespace-nowrap trunacate">Model -ChatGPT 4.0</span>
                    <svg className={`stroke-gray-500 dark:stroke-gray-400 transition-transform duration-200 ${isOpen ? "rotate-180" : ""}`} width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4.3125 8.65625L9 13.3437L13.6875 8.65625" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                    </svg>
                </button>
                <Dropdown isOpen={isOpen} onClose={closeDropdown} className="flex left-0 absolute w-[22%] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark">
                    {/* <div>
                    <span className="block font-medium text-gray-700 text-theme-sm dark:text-gray-400">
                        sriram mariappan
                    </span>
                    <span className="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">
                        srirammaus@gmail.com
                    </span>
                    </div> */}
 
                    <ul className="flex flex-col gap-1 pt-4 pb-3 border-b border-gray-200 dark:border-gray-800 dark:text-gray-400  ">
                        {AI_models.map((models,index)=>(
                            <li key={index} >
                            <DropdownItem key={index} onItemClick={closeDropdown} tag="a" to="/profile" className="inline-flex items-center gap-3 px-3 py-2 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                {/* <svg className="fill-gray-500 group-hover:fill-gray-700 dark:fill-gray-400 dark:group-hover:fill-gray-300" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fillRule="evenodd" clipRule="evenodd" d="M12 3.5C7.30558 3.5 3.5 7.30558 3.5 12C3.5 14.1526 4.3002 16.1184 5.61936 17.616C6.17279 15.3096 8.24852 13.5955 10.7246 13.5955H13.2746C15.7509 13.5955 17.8268 15.31 18.38 17.6167C19.6996 16.119 20.5 14.153 20.5 12C20.5 7.30558 16.6944 3.5 12 3.5ZM17.0246 18.8566V18.8455C17.0246 16.7744 15.3457 15.0955 13.2746 15.0955H10.7246C8.65354 15.0955 6.97461 16.7744 6.97461 18.8455V18.856C8.38223 19.8895 10.1198 20.5 12 20.5C13.8798 20.5 15.6171 19.8898 17.0246 18.8566ZM2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12ZM11.9991 7.25C10.8847 7.25 9.98126 8.15342 9.98126 9.26784C9.98126 10.3823 10.8847 11.2857 11.9991 11.2857C13.1135 11.2857 14.0169 10.3823 14.0169 9.26784C14.0169 8.15342 13.1135 7.25 11.9991 7.25ZM8.48126 9.26784C8.48126 7.32499 10.0563 5.75 11.9991 5.75C13.9419 5.75 15.5169 7.32499 15.5169 9.26784C15.5169 11.2107 13.9419 12.7857 11.9991 12.7857C10.0563 12.7857 8.48126 11.2107 8.48126 9.26784Z" fill=""/>
                                </svg> */}
                                {models}
                                </DropdownItem>
                            </li>
                        ))}
                    </ul>
    
                </Dropdown>
                        
                {/* <span onClick={handleRefresh} className="menu-item-icon-size ml-[45%] cursor-pointer ">
                    <RefershIcon/>
                </span> */}
            </div>
            <div></div>
        </div>
      </>
      );
    }


    // <div className="flex  w-[70%] ">
    //                 <h4 className="text-2xl font-semibold text-gray-800 dark:text-white/90 lg:mb-6 w-full">
    //                     Model - Chatgpt
    //                 </h4>
    //             </div>




    // <div className="relative ">
    //   <button onClick={toggleDropdown} className="flex items-center text-gray-700 dropdown-toggle dark:text-gray-400">
    //     <span className="mr-3 overflow-hidden rounded-full h-11 w-11">
    //       <img src="/images/user/owner.jpeg" alt="User"/>
    //     </span>

    //     <span className="block mr-1 font-medium text-theme-sm  w-[60%] overflow-hidden whitespace-nowrap trunacate">Sriram mariappan</span>
    //     <svg className={`stroke-gray-500 dark:stroke-gray-400 transition-transform duration-200 ${isOpen ? "rotate-180" : ""}`} width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
    //       <path d="M4.3125 8.65625L9 13.3437L13.6875 8.65625" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
    //     </svg>
    //   </button>