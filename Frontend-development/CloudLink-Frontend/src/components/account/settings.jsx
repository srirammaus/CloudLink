import { useModal } from "../../hooks/useModal";
import { Modal } from "../ui/modal";
import Button from "../ui/button/Button";
import Input from "../form/input/InputField";
import Label from "../form/Label";
import TwoFactorAuth from "./TwoFactorAuth";
import ChangePassword from "./ChangePassword";
import { useState } from "react";
import SessionManagement from "./SessionManagement";

export default function Settings() {
    const [selectedIndex,setSelectedIndex] = useState(false);
    const { isOpen, openModal, closeModal } = useModal();

    function  handlOnClick(index) {
        //update the selected index
        closeModal() //for precaution , if there anything opened it close
        openModal()
        setSelectedIndex(index)
    }
    const settingsOptions = [
        "Two Factor Authentication",
        "Password Settings",
        "Session Management",
        "Activity History",
        "Delete Account",
        "Deactivate Account",
        "Export Data",
        "Language",
    ]
    function renderModal() {{
        switch (selectedIndex) {
            case 0:
                return <TwoFactorAuth isOpen={isOpen} openModal={openModal} closeModal={closeModal} />;
            case 1:
                return <ChangePassword isOpen={isOpen} openModal={openModal} closeModal={closeModal} />;
            case 2:
                return  <SessionManagement isOpen={isOpen} openModal={openModal} closeModal={closeModal}/>;
            case 3:
                return  <></> ;
            default:
                return <></> ;
            }
    }}
    return (
        // rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6
        <div className="w-full">
        <ul className="bg-white dark:bg-white/[0.03] rounded-2xl ">
            {settingsOptions.map((setting, index) => (
            <li
                key={index}
                className="group flex items-center justify-between px-4 py-6 
                text-gray-700 dark:text-white/90 transition-all duration-300 cursor-pointer 
                            hover:text-black dark:hover:text-white 
                            
                            hover:bg-gray-100 dark:hover:bg-white/[0.02]
                            active:scale-[1.02] active:bg-gray-200 dark:active:bg-white/[0.08] hover:scale-[1.03] hover:rounded-2xl active:scale-[1.05] active:bg-white/[0.06]"
                onClick={() => handlOnClick(index)}
                >
            <span className="text-base font-medium ">{setting}</span>
            </li>

            ))}
        </ul>
        {renderModal()}

        </div>

    );
}

// hover:scale-[1.03] active:scale-[1.05] active:bg-white/[0.06]"
                            // text-gray-700 dark:text-gray-400 