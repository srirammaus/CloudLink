import { useState } from "react";
import { Link } from "react-router";
import { ChevronLeftIcon, EyeCloseIcon, EyeIcon } from "../../icons";
import Label from "../form/Label";
import Input from "../form/input/InputField";
import Checkbox from "../form/input/Checkbox";
import Button from "../ui/button/Button";
import ComponentCard from "../common/ComponentCard";
import Alert from "../ui/alert/Alert";
import { useNavigate } from "react-router";
export default function ForgotPassword() {
    //paramters error 
    //something went wrong put a redirect to sigin in btn
    //success and then put a redirect to sigin btn
    const [showPassword, setShowPassword] = useState(false);
    const [showCPassword,setShowCPassword] = useState(false);
    const [password,setPassword] = useState();
    const [cpassword,setCpassword] = useState();

    const navigate = useNavigate();

    const [verificationStatus,setVerificationStatus] = useState(null)

    const [isChecked, setIsChecked] = useState(false);
    function handleCPwdOnBlur () {
        const cpwd_err =  document.getElementById("cpwd-err");
      if(password != cpassword) {
                // show
          cpwd_err.classList.remove("hidden")
          return false
      }else {        
             // hide
        if(!cpwd_err.classList.contains("hidden")) {
          cpwd_err.classList.add("hidden")
        }
        return true

        
      }
    }
    function handleReset(e) {
        e.preventDefault()  
        let c_pg = document.getElementById("page-1")
        let nxt_pg = document.getElementById("page-2")

        if(!c_pg.classList.contains("hidden")) {
            c_pg.classList.add("hidden")
            nxt_pg.classList.remove("hidden")
        }
        
    }
    return (
  <div className="flex flex-col flex-1">
    <div className="w-full max-w-md pt-10 mx-auto">
      <Link
        to="/signin"
        className="inline-flex items-center text-sm text-gray-500 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
      >
        <ChevronLeftIcon className="size-5" />
        Back to Sign In
      </Link>
    </div>
        <div id="page-1" className="page-1 flex flex-col justify-center flex-1 w-full max-w-md mx-auto ">
      <div>
        <div className="mb-5 sm:mb-8">
          <h1 className="mb-2 font-semibold text-gray-800 text-title-sm dark:text-white/90 sm:text-title-md">
            Reset Password
          </h1>
          <p className="text-sm text-gray-500 dark:text-gray-400">
            Your password request has been processed
          </p>
        </div>

             {verificationStatus == true &&  (<ComponentCard title="Password Reset Successfull
">
        <Alert
            variant="success"
            title="Password Updated"
            message="Your password has been updated successfully. You can now log in with your new password."
            showLink={false}
        />

           {/* <Alert
            variant="warning"
            title="Warning Message"
            message="Be cautious when performing this action."
            showLink={false}
          /> */}
               <form className="flex flex-col items-center">

            <div className="flex flex-col items-center gap-3 px-2 mt-10 lg:flex-row lg:justify-end w-full">
              <Button size="sm" variant="outline" type="button" onClick={(e) =>{navigate("/")} }>
                Sign In
              </Button>
            </div>
          </form>
          </ComponentCard> )} 
          {verificationStatus ==false &&  (<ComponentCard title="Password Reset Failed">
                <Alert
                variant="error"
            title="Update Error"
            message="We couldn’t reset your password. Please try again later or contact support."
            showLink={false}
          />
                 <form className="flex flex-col items-center">

            <div className="flex flex-col items-center gap-3 px-2 mt-10 lg:flex-row lg:justify-end w-full">
              <Button size="sm" variant="outline" type="button" onClick={(e) =>{navigate("/")} }>
                Sign In
              </Button>
            </div>
          </form>
          </ComponentCard> )} 
                  {verificationStatus == null &&  (<ComponentCard title="Invalid verfication Call">
                <Alert
                    variant="error"
                    title="Verification Failed"
                    message="No valid token was found. Please use the link from your email to reset your password."
            showLink={false}
          />
                 <form className="flex flex-col items-center">

            <div className="flex flex-col items-center gap-3 px-2 mt-10 lg:flex-row lg:justify-end w-full">
              <Button size="sm" variant="outline" type="button" onClick={(e) =>{navigate("/")} }>
                Sign In
              </Button>
            </div>
          </form>
          </ComponentCard> )} 
         
        <div className="mt-5">
          <p className="text-sm font-normal text-center text-gray-700 dark:text-gray-400 sm:text-start">
            Don’t have an account?{" "}
            <Link
              to="/signup"
              className="text-brand-500 hover:text-brand-600 dark:text-brand-400"
            >
              Sign Up
            </Link>
          </p>
        </div>
      </div>
    </div>
  </div>
);

}
