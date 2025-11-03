import { useState } from "react";
import { Link, useNavigate } from "react-router";
import { ChevronLeftIcon, EyeCloseIcon, EyeIcon } from "../../icons";
import Label from "../form/Label";
import Input from "../form/input/InputField";
import Checkbox from "../form/input/Checkbox";
import Button from "../ui/button/Button";
import ComponentCard from "../common/ComponentCard";
import Alert from "../ui/alert/Alert";
export default function ForgotPassword() {
    //paramters error 
    //something went wrong put a redirect to sigin in btn
    //success and then put a redirect to sigin btn
    const [showPassword, setShowPassword] = useState(false);
    const [showCPassword,setShowCPassword] = useState(false);
    const [password,setPassword] = useState();
    const [cpassword,setCpassword] = useState();
    const navigate = useNavigate();

    const [verificationStatus,setVerificationStatus] = useState(false)

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

    <div id="page-1" className="page-1 flex flex-col justify-center flex-1 w-full max-w-md mx-auto">
      <div>
        <div className="mb-5 sm:mb-8">
          <h1 className="mb-2 font-semibold text-gray-800 text-title-sm dark:text-white/90 sm:text-title-md">
            Reset Password
          </h1>
          <p className="text-sm text-gray-500 dark:text-gray-400">
            Create a strong new password and confirm to proceed.
          </p>
        </div>

        <form>
          <div className="space-y-6">
            <div>
                  <Label>
                    Password<span className="text-error-500">*</span>
                  </Label>
                  <div className="relative">
                    <Input onBlur={(e)=>handleCPwdOnBlur()} placeholder="Enter your password" value={password} type={showPassword ? "text" : "password"} onChange={(e)=>setPassword(e.target.value)}/>
                    <span onClick={() => setShowPassword(!showPassword)} className="absolute z-30 -translate-y-1/2 cursor-pointer right-4 top-1/2">
                      {showPassword ? (<EyeIcon className="fill-gray-500 dark:fill-gray-400 size-5"/>) : (<EyeCloseIcon className="fill-gray-500 dark:fill-gray-400 size-5"/>)}
                    </span>
                  </div>
                  <span className="text-error-400 text-xs hidden">Password Mismatch</span>
                </div>
                {/* confirm password */}
                <div>
                  <Label>
                    Confirm Password<span className="text-error-500">*</span>
                  </Label>
                  <div className="relative">
                    <Input placeholder="Enter your password" value={cpassword} type={showCPassword ? "text" : "password"} onChange={(e)=>setCpassword(e.target.value)} onBlur={(e) =>handleCPwdOnBlur()}/>
                    <span onClick={() => setShowCPassword(!showCPassword)} className="absolute z-30 -translate-y-1/2 cursor-pointer right-4 top-1/2">
                      {showCPassword ? (<EyeIcon className="fill-gray-500 dark:fill-gray-400 size-5"/>) : (<EyeCloseIcon className="fill-gray-500 dark:fill-gray-400 size-5"/>)}
                    </span>
                    
                  </div>
                    <span id="cpwd-err" className="text-error-400 text-xs hidden">Password Mismatch</span>
                </div>

            <div>
              <Button className="w-full" size="sm" type="submit" onClick={(e)=>handleReset(e)}>
                Reset 
              </Button>
            </div>
          </div>
        </form>

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


        <div id="page-2" className="page-2 flex flex-col justify-center flex-1 w-full max-w-md mx-auto hidden">
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
              <Button size="sm" variant="outline" type="button" onClick={(e) =>{} }>
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
