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
export default function TwoFactorAuth({isOpen,openModal,closeModal}) {
    
    const [phoneVal,setPhoneVal] = useState("")
    const [disable,setDisabled] = useState(true);
    const navigate = useNavigate();
    const [verificationStatus,setVerificatioStatus] = useState(false) //error, success
    const handleSave = () => {
        // Handle save logic here
        closeModal();
    };
    function handleOnChange (e) {
      isValidPhone(e);
      setPhoneVal(e.target.value)
    }
 

    /**
     * check for len
     */
    function isValidPhone(e) {
      if (e.target.value.length > 10 && e.target.value.length < 18){
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
    const [otp, setOtp] = useState(["", "", "", "", "", ""]);

    function handleOtpChange(e, index) {
      const regex = /[^0-9]/g;
      if(regex.test(e.target.value) == true){
        e.target.value = ""
      }
      
      const value = e.target.value.replace(/[^0-9]/g, ""); // allow only digits
      const newOtp = [...otp];
      newOtp[index] = value;
      setOtp(newOtp);

      // move to next box automatically
      if (value && index < 5) {
        document.getElementById(`otp-${index + 1}`).focus();
      }
    }

    function handleOtpKeyDown(e, index) {
  
      if (e.key === "Backspace" && !otp[index] && index > 0) {
        document.getElementById(`otp-${index - 1}`).focus();
      }
    }

    function handleVerify(e) {
      e.preventDefault()
      const code = otp.join("");
      console.log("Verifying OTP:", code);
      if (code.length === 6) {
        // verify logic here
        goNext()

      } else {
        alert("Please enter all 6 digits of the OTP.");
      }
    }

    function handleResend() {
      console.log("Resending OTP...");
      // Add resend logic here
    }

  
    return (

    <Modal isOpen={isOpen} onClose={closeModal} className="max-w-[700px] m-4">
        <div id="page-1" className="page-1 no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
          <div className="px-2 pr-14">
            <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
              Two-Step Verification Setup
            </h4>
            <p className="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
              Add your phone number to enable extra protection for your account.
            </p>
          </div>
          <form className="flex flex-col">
            <div className="custom-scrollbar h-[450px] overflow-y-auto px-2 pb-3">
              <div>
                <h5 className="mb-5 text-lg font-medium text-gray-800 dark:text-white/90 lg:mb-6">
                  Verification Number
                </h5>

                <div className="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                  <div>
                    <Label>Phone number</Label>
                    <Input onChange={(e)=> handleOnChange(e)} type="number" value={phoneVal} placeholder="+91 98765 43210"/>
                  
                  </div>
                </div>
              </div>
            </div>
            <div className="flex items-center gap-3 px-2 mt-6 lg:justify-end">
              <Button size="sm" type="button" variant="outline" onClick={closeModal}>
                Close 
              </Button>
              <Button size="sm" type="button" onClick={(e) => handleSend(e)} id="send-btn" disabled={disable}>
                Send
              </Button>
            </div>
          </form>
        </div>

        <div id="page-2" className="page-2 no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11 hidden">
          <div className="px-2 pr-14">
            <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
              Verify Your Identity
            </h4>
            <p className="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
              Enter the 6-digit verification code we sent to your phone number.
            </p>
          </div>

          <form className="flex flex-col items-center">
            <div className="custom-scrollbar h-[350px] overflow-y-auto px-2 pb-3 w-full">
              <div>
                <h5 className="mb-5 text-lg font-medium text-gray-800 dark:text-white/90 lg:mb-6">
                  One-Time Password (OTP)
                </h5>

                {/* OTP INPUT BOXES */}
                <div className="flex justify-center gap-3 mt-4">
                  {[...Array(6)].map((_, i) => (
                    <input
                      key={i}
                      id={`otp-${i}`}
                      type="text"
                      maxLength={1}
                      inputMode="numeric"
                      pattern="[0-9]*"
                      className="w-12 h-12 text-center text-lg font-semibold text-gray-800 dark:text-white bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                      onChange={(e) => handleOtpChange(e, i)}
                      onKeyDown={(e) => handleOtpKeyDown(e, i)}
                    />
                  ))}
                </div>
              </div>
            </div>

            <div className="flex flex-col items-center gap-3 px-2 mt-10 lg:flex-row lg:justify-end w-full">
              <Button size="sm" variant="outline" type="button" onClick={(e) => goBack(e)}>
                Back
              </Button>
              <Button size="sm" type="button" onClick={(e) => handleVerify(e)}>
                Verify
              </Button>
              <button
                type="button"
                onClick={handleResend}
                className="text-sm text-blue-500 hover:underline mt-2 lg:mt-0"
              >
                Resend Code
              </button>
            </div>
          </form>
        </div>
        
        <div id="page-3" className="page-3 no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11 hidden">
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