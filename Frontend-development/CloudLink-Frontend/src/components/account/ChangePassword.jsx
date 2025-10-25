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
import { ChevronLeftIcon, EyeCloseIcon, EyeIcon } from "../../icons";

import { useNavigate } from "react-router";
export default function ChangePassword({isOpen,openModal,closeModal}) {


    // const [oldPassword,setOldPassword] = useState()
    // const [confirmPassword,setConfirmPassword] = useState()
    // const [newPassword,setNewPassword] = useState()

    const [showOldPassword,setShowOldPassword] = useState(false)
    const [showNewPassword, setShowNewPassword] = useState(false);
    const [showCPassword,setShowCPassword] = useState(false);

    const [disable,setDisabled] = useState(true);

    
    const navigate = useNavigate();
    const [verificationStatus,setVerificatioStatus] = useState(false) //error, success

    function handleOnChange (e) {
      isValidPwd(e);

    }
    /**
     * check for len
     */
    function isValidPwd(e) {
      if (e.target.value.length > 8 && e.target.value.length < 30){
        setDisabled(false)
          
      }else {
        setDisabled(true)
      }

    }
       /**
     * first send then disable for 60s
     * then again send 
     * 
     */
    const pageStack = ["page-1"];

   


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
      console.log(currentPage)
      console.log(pageStack)
      const NextPage = Number(currentPage.split("-")[1]) + 1
      const getNextPage = document.querySelector(".page-"+NextPage.toString());

      getCurrentPage.classList.add("hidden");
      pageStack.push("page-"+NextPage.toString())
      
      console.log(NextPage)
      console.log(getNextPage)
      getNextPage.classList.remove("hidden");

     
    }
    function goAccountSettings(e) {
      pageStack.length = 0
      navigate("/accountsettings")
    }

    function changePwd(e) {
      e.preventDefault()
        // verify logic here
      const cPwd =document.getElementById("c-pwd").value;
      const newPwd = document.getElementById("new-pwd").value;
      console.log(cPwd)
      console.log(newPwd);
      if(cPwd === newPwd) {
        goNext()
      }else {
        alert("Both password should be same")
      }

    }
    function closeAndClearStack(){
      pageStack.length = 0
      console.log(pageStack)
      closeModal()

    }

 
  
    return (

      <Modal isOpen={isOpen} onClose={closeModal} className="max-w-[700px] m-4">
        <div
          id="page-1"
          className="page-1 no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11"
        >
          <div className="px-2 pr-14">
            <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
              Change Password
            </h4>
            <p className="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
              Use your old password to set a new one. Please make sure your new password is strong.
            </p>
          </div>

          <form className="flex flex-col">
            <div className="custom-scrollbar h-[450px] overflow-y-auto px-2 pb-3 space-y-6">

              {/* Old Password */}
                <div>
                  <Label>
                    Old Password<span className="text-error-500">*</span>
                  </Label>
                  <div className="relative">
                    <Input placeholder="Enter your password" name="old-pwd" type={showOldPassword ? "text" : "password"} onChange={handleOnChange}/>
                    <span onClick={() => setShowOldPassword(!showOldPassword)} className="absolute z-30 -translate-y-1/2 cursor-pointer right-4 top-1/2">
                      {showOldPassword ? (<EyeIcon className="fill-gray-500 dark:fill-gray-400 size-5"/>) : (<EyeCloseIcon className="fill-gray-500 dark:fill-gray-400 size-5"/>)}
                    </span>
                  </div>
                </div>
              {/* <div>
                <Label htmlFor="old-password">Old Password</Label>
                <Input
                  id="old-password"
                  type="password"
                  name="oldPassword"
                  value={oldPassword}
                  onChange={handleOnChange}
                  placeholder="Enter your old password"
                />
              </div> */}

              {/* New Password */}
                  <div>
                  <Label>
                    Password<span className="text-error-500">*</span>
                  </Label>
                  <div className="relative">
                    <Input placeholder="Enter your password" id="new-pwd" name="new-pwd" type={showNewPassword ? "text" : "password"} onChange={handleOnChange}/>
                    <span onClick={() => setShowNewPassword(!showNewPassword)} className="absolute z-30 -translate-y-1/2 cursor-pointer right-4 top-1/2">
                      {showNewPassword ? (<EyeIcon className="fill-gray-500 dark:fill-gray-400 size-5"/>) : (<EyeCloseIcon className="fill-gray-500 dark:fill-gray-400 size-5"/>)}
                    </span>
                  </div>
                </div>
              {/* <div>
                <Label htmlFor="new-password">New Password</Label>
                <Input
                  id="new-password"
                  type="password"
                  name="newPassword"
                  value={newPassword}
                  onChange={handleOnChange}
                  placeholder="Enter your new password"
                />
              </div> */}

              {/* Confirm New Password */}
                <div>
                  <Label>
                    Password<span className="text-error-500">*</span>
                  </Label>
                  <div className="relative">
                    <Input placeholder="Enter your password" id="c-pwd" name="c-pwd" type={showCPassword ? "text" : "password"} onChange={handleOnChange}/>
                    <span onClick={() => setShowCPassword(!showCPassword)} className="absolute z-30 -translate-y-1/2 cursor-pointer right-4 top-1/2">
                      {showCPassword ? (<EyeIcon className="fill-gray-500 dark:fill-gray-400 size-5"/>) : (<EyeCloseIcon className="fill-gray-500 dark:fill-gray-400 size-5"/>)}
                    </span>
                  </div>
              </div>
              {/* <div>
                <Label htmlFor="confirm-password">Confirm New Password</Label>
                <Input
                  id="confirm-password"
                  type="password"
                  name="confirmPassword"
                  value={confirmPassword}
                  onChange={handleOnChange}
                  placeholder="Re-enter new password"
                />
              </div> */}
              

          

            </div>

            {/* Action buttons */}
            <div className="flex items-center gap-3 px-2 mt-6 lg:justify-end">
              <Button size="sm" type="button" variant="outline" onClick={()=>closeAndClearStack()}>
                Cancel
              </Button>
              <Button
                size="sm"
                type="button"
                onClick={(e)=>changePwd(e)}
                id="changePwd"
                disabled={disable}
              >
                Change Password
              </Button>
            </div>
          </form>
        </div>

    <div id="page-2" className="page-2 no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11 hidden">
          <div className="px-2 pr-14">
            <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
                  Setup Complete
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