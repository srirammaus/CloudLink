import { useModal } from "../../hooks/useModal";
import { Modal } from "../ui/modal";
import Button from "../ui/button/Button";
import Input from "../form/input/InputField";
import Label from "../form/Label";
import { useEffect,useState } from "react";
import { preventDefault } from "@fullcalendar/core/internal";
import { useRef } from "react";
import Alert from "../ui/alert/Alert";
import ComponentCard from "../common/ComponentCard";
import { useNavigate } from "react-router";
import { ChevronLeftIcon, EyeCloseIcon, EyeIcon } from "../../icons";

export default function DeleteAccount({isOpen,openModal,closeModal}) {
    
    const [showPassword,setShowPassword] = useState(false)
    const [disable,setDisabled] = useState(true);
    const navigate = useNavigate();
    const [verificationStatus,setVerificatioStatus] = useState(false) //error, success


    function handleOnChange (e) {
      isValidPhone(e);
      setPhoneVal(e.target.value)
    }
 

    /**
     * check for len
     */
    function isValidPhone(e) {
      if (e.target.value.length > 5){
          setDisabled(false)
      } else {
        setDisabled(true)
      }
    }
       /**
     * first send then disable for 60s
     * then again send 
     * 
     */
    const pageStack = ["page-1"];

    function handleSend(e) {
      e.preventDefault()
      goNext()
    }
    function goBack (e) {
      e.preventDefault()
      const currentPage = pageStack.pop();
      const getCurrentPage =  document.getElementById(currentPage);
      
      const lastPage = Number(currentPage.split("-")[1]) - 1
      const getLastPage = document.getElementById("page-"+lastPage.toString())


      getCurrentPage.classList.add("hidden");
      getLastPage.classList.remove("hidden")

    }
    function goNext() {
      const currentPage = pageStack.at(-1);
      const getCurrentPage = document.querySelector("."+currentPage);

      const NextPage = Number(currentPage.split("-")[1]) + 1
      const getNextPage = document.querySelector(".page-"+NextPage.toString());

      getCurrentPage.classList.add("hidden");
      pageStack.push("page-"+NextPage.toString())
      getNextPage.classList.remove("hidden");

     
    }
    function goAccountSettings(e) {
      pageStack.length = 0
      navigate("/accountsettings")
    }






  
    return (

    <Modal isOpen={isOpen} onClose={closeModal} className="max-w-[700px] m-4">
        <div id="page-1" className="page-1 no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
          <div className="px-2 pr-14">
            <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
              Delete Account 
            </h4>
            <p className="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
                This action will permanently delete your account and all associated datay performing this action your account will be permantly deleted
            </p>
          </div>
          <form className="flex flex-col">
            <div className="custom-scrollbar h-[450px] overflow-y-auto px-2 pb-3">
              <div>
                <h5 className="mb-5 text-lg font-medium text-gray-800 dark:text-white/90 lg:mb-6">
                  Confirm your identity
                </h5>

                <div className="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                  <div>
                    <Label>Current Password</Label>
                        <div className="relative">
                            <Input placeholder="Enter your password" name="old-pwd" type={showPassword ? "text" : "password"} onChange={handleOnChange}/>
                            <span onClick={() => setShowPassword(!showPassword)} className="absolute z-30 -translate-y-1/2 cursor-pointer right-4 top-1/2">
                            {showPassword ? (<EyeIcon className="fill-gray-500 dark:fill-gray-400 size-5"/>) : (<EyeCloseIcon className="fill-gray-500 dark:fill-gray-400 size-5"/>)}
                            </span>
                        </div>    
                  </div>


                </div>
              </div>
            </div>
            <div className="flex items-center gap-3 px-2 mt-6 lg:justify-end">
              <Button size="sm" type="button" variant="outline" onClick={closeModal}>
                Cancel 
              </Button>
              <Button size="sm" type="button" onClick={(e) => handleSend(e)} id="send-btn" disabled={disable}>
                Permanently Delete
              </Button>
            </div>
          </form>
        </div>

      
        
        <div id="page-2" className="page-2 no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11 hidden">
          <div className="px-2 pr-14">
            <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
                  Deletion Complete
            </h4>
          </div>
        < ComponentCard title="Success Alert">
        
          { verificationStatus == true && (<Alert
            variant="success"
            title="Success Message"
            message="Be cautious when performing this action."
            showLink={false}
          /> )} 
           {/* <Alert
            variant="warning"
            title="Warning Message"
            message="Be cautious when performing this action."
            showLink={false}
          /> */}
          {verificationStatus == false && (
          <Alert
                    variant="error"
                    title="Error Message"
                    message="Be cautious when performing this action."
                    showLink={false}
                  />
          )}
       
          
        </ComponentCard>
          <form className="flex flex-col items-center">

            <div className="flex flex-col items-center gap-3 px-2 mt-10 lg:flex-row lg:justify-end w-full">
              <Button size="sm" variant="outline" type="button" onClick={(e) => goAccountSettings(e)}>
                Back to Settings
              </Button>
            </div>
          </form>
        </div>
      </Modal>);
}