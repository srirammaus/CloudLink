import { useState } from "react";
import { useModal } from "../../hooks/useModal";
import { Modal } from "../ui/modal";
import { Link, redirect, useNavigate } from "react-router";
import { ChevronLeftIcon, EyeCloseIcon, EyeIcon, RefershIcon } from "../../icons";
import Label from "../form/Label";
import Input from "../form/input/InputField";
import Checkbox from "../form/input/Checkbox";
import TextAreaInput from "../form/form-elements/TextAreaInput";
import TextArea from "../form/input/TextArea";
import { LoaderCircle } from "lucide-react";
import { signuplib,signupURLs,isValidUsername,safeListedErrorCodes} from "../../library/signuplib";
import ComponentCard from "../common/ComponentCard";
import Alert from "../ui/alert/Alert";
import Button from "../ui/button/Button";
// import { preventDefault } from "@fullcalendar/core/internal";
import validator from 'validator';

/**
 * 
 * @returns 
 * signup duties :
 * Invalid input field username or phone or email or anything
 * 
 * This  should capcable for handling unexpected content-type
 * handle exact err
 * 
 */
export default function SignUpForm() {
    const navigate = useNavigate()
    const { isOpen, openModal, closeModal } = useModal();
    const limitCount = 50;
    const [message, setMessage] = useState("");
    const [phone,setPhone] = useState();
    const [name,setName] = useState();
    const [username,setUsername] = useState();
    const [captcha,setCaptcha] = useState(signupURLs().captcha)
    const [captchaVal,setCaptchaval] = useState();
    const [showPassword, setShowPassword] = useState(false);
    const [showCPassword,setShowCPassword] = useState(false);
    const [password,setPassword] = useState();
    const [cpassword,setCpassword] = useState();
    const [email,setEmail] = useState();
    const [secondary_email,setSecondary_email] = useState();
    const [SignupStatus,setSignupStatus] = useState(false)
    const [isChecked, setIsChecked] = useState(false);
    const [sent,setSent] = useState(false);
    const [AllTest,SetAllTest] = useState(false);
    const [signupMsg,setSignupMsg] = useState("Something went wrong");

    async function signup(e) {
      e.preventDefault()
      const Data = {
          username:username,
          name:name,
          password:password,
          phone:phone,
          email:email,
          secondary_email:secondary_email,
          bio:message,
          captcha:captchaVal,
      }
      const signup_msg = document.getElementById("signup-msg");

      if(handleCPwdOnBlur() && handleEmailonBlur() && handleCaptchaValonBlur() &&handleSecondEmailonBlur() && handleNumberonBlur() && handleUsernameOnBlur() ){
        if(!signup_msg.classList.contains("hidden")) {
          signup_msg.classList.add("hidden")
        } 
        await loadModal(Data,signup_msg)


      }

    }
    async function loadModal (Data,signup_msg) {
        setSent(true);

        const result =  await signuplib({...Data})
        if(result?.flag == "1") { //redirect after modal
          setSignupStatus(true)
          setSignupMsg(result.message)
          openModal()
          setSent(false);
        }else{
          setSent(false)
          setSignupStatus(false)
          const flag = Number(result?.flag);
          const usr_err = document.getElementById("username-err");
          const pwd_err = document.getElementById("cpwd-err"); 
          const em_err =  document.getElementById("email-err");
          const num_err = document.getElementById("number-err");
          const bio_er = document.getElementById("bio-err");
              const cap_err = document.getElementById("captcha-err");

          switch (true) {
            case flag >= 1000 && flag < 1100: //username err
              usr_err.innerHTML = result?.message ?? "Invalid Username"
                if(usr_err.classList.contains("hidden")) {
                    usr_err.classList.remove("hidden")
                  } 
                break;
            case flag >= 1100 && flag < 1200:    //password err   
              pwd_err.innerHTML = result?.message ?? "Invalid Password"
              if(pwd_err.classList.contains("hidden")) {
                  pwd_err.classList.remove("hidden")
                }
              break;          
            case flag >= 1200 && flag < 1300: //email err
               em_err.innerHTML = result?.message ?? "Invalid Email"
                 if(em_err.classList.contains("hidden")) {
                    em_err.classList.remove("hidden")
                  }
              break;            
            case flag >= 1300 && flag < 1400: // phone number err
              num_err.innerHTML = result?.message ?? "Invalid phone number"
                if(num_err.classList.contains("hidden")) {
                    num_err.classList.remove("hidden")
                  }
              break;            
            case flag >= 1400 && flag < 1500:  //bio
              bio_er.innerHTML = result?.message ?? "Invalid Bio"
                if(bio_er.classList.contains("hidden")) {
                    bio_er.classList.remove("hidden")
                  }
              break;
            case flag >= 1900 && flag < 2000: //captcha
              cap_err.innerHTML = result?.message ?? "Invalid captcha"
                if(cap_err.classList.contains("hidden")) {
                    cap_err.classList.remove("hidden")
                  }
              break;
            default: //something went wrong pop it
              
              setSignupMsg(result.message ?? "something went wrong");
              signup_msg.classList.remove("hidden");
              break;
          }
        }
    }
    function handleUsernameonChange (e) {
       if(e.target.value.length < 15) {
        setUsername(e.target.value)
      }
    
    }
    function handleNameonChange (e) {
      if(e.target.value.length < 15) {
        setName(e.target.value)
      }
      if(e.target.value.includes("-")){
        setName(" ");
      }

    }
    function handleNumberonChange (e) {
      
      if(e.target.value.length < 15) {
        setPhone(e.target.value)
      }

    }

    function handleNumberonBlur () {
      const number_id =document.getElementById("number-err");
      if(!validator.isNumeric(phone+ "")) {
        //show err 
        number_id.classList.remove("hidden");
        return false
      }else {
        //hide err
        if(!number_id.classList.contains("hidden")) {
          number_id.classList.add("hidden")
        }
        return true
      }
    }
    function handleEmailonBlur () {
      const email_id =document.getElementById("email-id");
      if(!validator.isEmail(email+ "")) {
        //show err 
        email_id.classList.remove("hidden");
        return false
      }else {
        //hide err
        if(!email_id.classList.contains("hidden")) {
          email_id.classList.add("hidden")
        }
        return true
      }
    }
        
    function handleSecondEmailonBlur () {
      const second_email_err =document.getElementById("second-email-err");
      if(!validator.isEmail(secondary_email+ "")) {
        //show err 
        second_email_err.classList.remove("hidden");
        return false
      }else {
        //hide err
        if(!second_email_err.classList.contains("hidden")) {
          second_email_err.classList.add("hidden")
        }
        return true
      }
    }
  
  
    function handleUsernameOnBlur() {
        const username_err =  document.getElementById("username-err");
        if(!isValidUsername(username)) {
                  // show
            console.log(isValidUsername(username))
            username_err.classList.remove("hidden")
            return false
        }else {        
              // hide
          if(!username_err.classList.contains("hidden")) {
            username_err.classList.add("hidden")

          }
          return true
        }
    }
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
    function handleCaptchaValonBlur () {
        const cap_err = document.getElementById("captcha-err");
        if(typeof(cap_err).innerHTML != "string") {
                // show
          console.log("does this happening")
          cap_err.classList.remove("hidden")
          return false
      }else {        
             // hide
          console.log("does this happening2")
        if(!cap_err.classList.contains("hidden")) {
          cap_err.classList.add("hidden")
        }
        return true

        
      }
       
    }
    /**
     * name - should not be emtpy , no hyphens,maxlen50
     * Invalid email, password and cpassword mismatch
     */
    function handleInputErrors() {

    }
    function resetCaptcha () {
      setCaptcha("");
      setTimeout(()=>{
        setCaptcha(signupURLs().captcha)
      },1)
    }

    return (<div className="flex flex-col flex-1 w-full overflow-y-auto lg:w-1/2 no-scrollbar">
      <div className="w-full max-w-md mx-auto mb-5 sm:pt-10">
        <Link to="/" className="inline-flex items-center text-sm text-gray-500 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
          <ChevronLeftIcon className="size-5"/>
          Back to dashboard
        </Link>
      </div>
      <div className="page-1 flex flex-col justify-center flex-1 w-full max-w-md mx-auto">
        <div>
          <div className="mb-5 sm:mb-8">
            <h1 className="mb-2 font-semibold text-gray-800 text-title-sm dark:text-white/90 sm:text-title-md">
              Sign Up
            </h1>
            <p className="text-sm text-gray-500 dark:text-gray-400">
              Enter your email and password to sign up!
            </p>
          </div>
          <div>
            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-5">
              <button className="inline-flex items-center justify-center gap-3 py-3 text-sm font-normal text-gray-700 transition-colors bg-gray-100 rounded-lg px-7 hover:bg-gray-200 hover:text-gray-800 dark:bg-white/5 dark:text-white/90 dark:hover:bg-white/10">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M18.7511 10.1944C18.7511 9.47495 18.6915 8.94995 18.5626 8.40552H10.1797V11.6527H15.1003C15.0011 12.4597 14.4654 13.675 13.2749 14.4916L13.2582 14.6003L15.9087 16.6126L16.0924 16.6305C17.7788 15.1041 18.7511 12.8583 18.7511 10.1944Z" fill="#4285F4"/>
                  <path d="M10.1788 18.75C12.5895 18.75 14.6133 17.9722 16.0915 16.6305L13.274 14.4916C12.5201 15.0068 11.5081 15.3666 10.1788 15.3666C7.81773 15.3666 5.81379 13.8402 5.09944 11.7305L4.99473 11.7392L2.23868 13.8295L2.20264 13.9277C3.67087 16.786 6.68674 18.75 10.1788 18.75Z" fill="#34A853"/>
                  <path d="M5.10014 11.7305C4.91165 11.186 4.80257 10.6027 4.80257 9.99992C4.80257 9.3971 4.91165 8.81379 5.09022 8.26935L5.08523 8.1534L2.29464 6.02954L2.20333 6.0721C1.5982 7.25823 1.25098 8.5902 1.25098 9.99992C1.25098 11.4096 1.5982 12.7415 2.20333 13.9277L5.10014 11.7305Z" fill="#FBBC05"/>
                  <path d="M10.1789 4.63331C11.8554 4.63331 12.9864 5.34303 13.6312 5.93612L16.1511 3.525C14.6035 2.11528 12.5895 1.25 10.1789 1.25C6.68676 1.25 3.67088 3.21387 2.20264 6.07218L5.08953 8.26943C5.81381 6.15972 7.81776 4.63331 10.1789 4.63331Z" fill="#EB4335"/>
                </svg>
                Sign up with Google
              </button>
              <button className="inline-flex items-center justify-center gap-3 py-3 text-sm font-normal text-gray-700 transition-colors bg-gray-100 rounded-lg px-7 hover:bg-gray-200 hover:text-gray-800 dark:bg-white/5 dark:text-white/90 dark:hover:bg-white/10">
                <svg width="21" className="fill-current" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M15.6705 1.875H18.4272L12.4047 8.75833L19.4897 18.125H13.9422L9.59717 12.4442L4.62554 18.125H1.86721L8.30887 10.7625L1.51221 1.875H7.20054L11.128 7.0675L15.6705 1.875ZM14.703 16.475H16.2305L6.37054 3.43833H4.73137L14.703 16.475Z"/>
                </svg>
                Sign up with X
              </button>
            </div>
            <div className="relative py-3 sm:py-5">
              <div className="absolute inset-0 flex items-center">
                <div className="w-full border-t border-gray-200 dark:border-gray-800"></div>
              </div>
              <div className="relative flex justify-center text-sm">
                <span className="p-2 text-gray-400 bg-white dark:bg-gray-900 sm:px-5 sm:py-2">
                  Or
                </span>
              </div>
            </div>
            <form>
              <div className="space-y-5">
                <div className="grid grid-cols-1 gap-5 sm:grid-cols-2">
                  {/* <!-- First Name --> */}
                     <div className="sm:col-span-1">
                    <Label>
                      Name<span className="text-error-500">*</span>
                    </Label>
                    <Input type="text" id="name" name="name" value={name} placeholder="Enter your first name" onChange={(e) =>handleNameonChange(e)}/>
                    <span className="text-error-400 text-xs hidden">Name should not includes this chars</span>
                  </div>
                  <div className="sm:col-span-1">
                    <Label>
                      Email<span className="text-error-500">*</span>
                    </Label>
                    <Input onBlur={(e)=>{handleEmailonBlur()}}type="text" id="Email" name="Email" placeholder="Enter your Email" value={email} onChange={(e)=>setEmail(e.target.value)}/>
                    <span id="email-id" className="text-error-400 text-xs hidden">Invalid Email</span>
                  
                  </div>
                  {/* <!-- Last Name --> */}
                  <div className="sm:col-span-1">
                    <Label>
                      Phone<span className="text-error-500">*</span>
                    </Label>
                    <Input onBlur={(e) => handleNumberonBlur()} type="number" id="phone" name="phone" value={phone} placeholder="Enter your Phone number" onChange={(e) =>handleNumberonChange(e)}/>
                    <span id="number-err" className="text-error-400 text-xs hidden">Invalid Number</span>
                  </div>
                  <div className="sm:col-span-1">
                    <Label>
                      secondary Email<span className="text-error-500">*</span>
                    </Label>
                    <Input onBlur={(e)=>handleSecondEmailonBlur()}type="text" id="secondary-email" name="secondary-email" placeholder="Enter your seconday email" value={secondary_email} onChange={(e)=>setSecondary_email(e.target.value)}/>
                    <span id="second-email-err" className="text-error-400 text-xs hidden">Invalid Email</span>
                    
                  </div>
                  
                </div>
                {/* <!-- Email --> */}
                <div >
                  <Label>
                    Username<span className="text-error-500">*</span>
                  </Label>
                  <Input  onBlur={(e)=>handleUsernameOnBlur()}type="text" id="username" name="username" value={username} placeholder="Enter your username" onChange={(e) => handleUsernameonChange(e)}/>
                    <span id="username-err"className="text-error-400 text-xs hidden">Invalid Username</span>
                </div>
                {/* <!-- Password --> */}
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
                  <Label>
                    Bio<span className="text-error-500">*</span>
                  </Label>
                    <TextArea value={message} onChange={(value) =>{ if(value.length < limitCount) return setMessage(value)}} rows={6}/>
                    <span id="bio-err" className="text-error-400 text-xs hidden">Invalid Bio</span>
                </div>
                  <div >
                  <Label>
                    Captcha<span className="text-error-500">*</span>
                  </Label>
                    <div className="flex">
                      <span className="block mr-3 mb-3 overflow-hidden  h-10 w-25">
                        <img src={captcha} alt="User"/>
                      </span>
                      <span className="mr-3 mt-3 cursor-pointer" onClick={()=>resetCaptcha()}>
                        <RefershIcon/>
                      </span>
                    </div>
                   
                    <Input onBlur={handleCaptchaValonBlur} type="text" id="captcha" name="captcha" placeholder="Enter Captcha" value={captchaVal} onChange={(e)=>setCaptchaval(e.target.value)}/>
                    <span id="captcha-err" className="text-error-400 text-xs hidden"></span>

                </div>
                {/* <!-- Checkbox --> */}
                <div className="flex items-center gap-3">
                  <Checkbox className="w-5 h-5" checked={isChecked} onChange={setIsChecked}/>
                  <p className="inline-block font-normal text-gray-500 dark:text-gray-400">
                    By creating an account means you agree to the{" "}
                    <span className="text-gray-800 dark:text-white/90">
                      Terms and Conditions,
                    </span>{" "}
                    and our{" "}
                    <span className="text-gray-800 dark:text-white">
                      Privacy Policy
                    </span>
                  </p>
                </div>
                {/* <!-- Button --> */}
                <div>
                  <button onClick={(e)=>{signup(e)}} className={`flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 `} >
                    
                    <LoaderCircle className={ ` ${!sent?"hidden":"loader"}`}/>
                    {!sent &&  "Sign Up"}

                  </button>
                    <span id="signup-msg" className="text-error-400 text-xs hidden">{signupMsg}</span>
                </div>
              </div>
            </form>

            <div className="mt-5">
              <p className="text-sm font-normal text-center text-gray-700 dark:text-gray-400 sm:text-start">
                Already have an account? {""}
                <Link to="/signin" className="text-brand-500 hover:text-brand-600 dark:text-brand-400">
                  Sign In
                </Link>
              </p>
            </div>
          </div>
        </div>
      </div>
        <Modal isOpen={isOpen} onClose={closeModal} className="max-w-[700px] m-4">
        <div id="page-2" className="page-2 no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
          <div className="px-2 pr-14">
            <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
                  Signup Status
            </h4>
          </div>
        { SignupStatus == true &&  (<ComponentCard title="Signup Successful
">
        <Alert
          variant="success"
          title="Account Created"
          message="Your account has been created successfully."
          showLink={false}
        />

           {/* <Alert
            variant="warning"
            title="Warning Message"
            message="Be cautious when performing this action."
            showLink={false}
          /> */}
          </ComponentCard> )} 
          { SignupStatus ==false &&  (<ComponentCard title="Signup Failed">
                <Alert
            variant="error"
            title="Registration Failed"
            message="We couldn’t create your account. Please try again."
            showLink={false}
          />

          </ComponentCard> )} 
         
       
          <form className="flex flex-col items-center">

            <div className="flex flex-col items-center gap-3 px-2 mt-10 lg:flex-row lg:justify-end w-full">
              <Button size="sm" variant="outline" type="button" onClick={(e) =>{navigate("/signin")} }>
                SignIn
              </Button>
            </div>
          </form>
        </div>
        </Modal>
    </div>);
}
