import { useModal } from "../../hooks/useModal";
import { Modal } from "../ui/modal";
import Button from "../ui/button/Button";
import Input from "../form/input/InputField";
import Label from "../form/Label";
import { useState } from "react";
import { data, useNavigate } from "react-router";
import Alert from "../ui/alert/Alert";
import ComponentCard from "../common/ComponentCard";
export default function UserInfoCard({name,username,email,phone,bio,onClick}) {
    const [nameVal,setName] = useState(name)
    const [usernameVal,setUsername] = useState(username)
    const [emailVal,setEmail] = useState(email)
    const [phoneVal,setPhone] = useState(phone)
    const [bioVal,setBio] = useState(bio)
    const [changed,setChange] = useState({})
    const [updateStatus,setupdateStatus] = useState(false)
    const [editErr,setEditErr] = useState()
    const [refresh,setRefresh] = useState(0)
    const navigate = useNavigate()
    function handlOnChange(e,index) {
      setEditable_personal_info((prev)=>{
          return prev.map((item,idx) => {
          if(index == idx) {
            console.log(e.target.value,static_personal_info[index].value)
            let id = document.getElementById(item.errID)
            if(e.target.value != static_personal_info[index].value) {
              if(!id.classList.contains("hidden")) {
                id.classList.add("hidden")
              }
              setChange(old=>({
                ...old,
                [item.label]:e.target.value
              }))
            }else {
              id.classList.remove("hidden")
              setChange(old=>{
                delete old[item.label];
                return old
              })
            }
      
         
            return {...item,value:e.target.value}
          }
          return item
          })
       })
    }
    function handleOnBlur(index) {
      console.log(editable_personal_info[index].errID)
      let id = document.getElementById(editable_personal_info[index].errID);
        if(!id.classList.contains("hidden")) {
          id.classList.add("hidden")
        }
    }
    const [static_personal_info,setStatic_personal_info] = useState(
          [
          {label:"Name",value:name,},
          {label:"Username",value:username,},
          {label:"Email",value:email,},
          {label:"Phone",value:phone,},
          {label:"Bio",value:bio,},
        ]
    )
      const [editable_personal_info, setEditable_personal_info] = useState([
        {
          label: "Name",
          value: name,
          errID: "name-id",
          errVal: "Name cannot be same as previous value",
        },
        {
          label: "Username",
          value: username,
          errID: "usr-id",
          errVal: "Please choose a different username",
        },
        {
          label: "Email",
          value: email,
          errID: "email-id",
          errVal: "Please provide a valid, updated email address",
        },
        {
          label: "Phone",
          value: phone,
          errID: "phone-id",
          errVal: "Please update to a new phone number",
        },
        {
          label: "Bio",
          value: bio,
          errID: "bio-id",
          errVal: "Bio must contain updated information",
        },
]);

   
    const { isOpen, openModal, closeModal } = useModal();
    const handleSave = async (e) => {
        e.preventDefault()
        const filtered_resp = await onClick.updateProfile({data:changed,navigate:navigate});
        if(filtered_resp?.flag =="1") {
          //success modal seprate //for back up add a verification bade if that was default then make it show that
          setupdateStatus(true);
          let nextPg = document.getElementById("page-2");
          let currentPg = document.getElementById("page-1");

          currentPg.classList.add("hidden");
          nextPg.classList.remove("hidden");
          
        }else {
          let edit_err = document.getElementById("edit-err");
          edit_err.classList.remove("hidden");
          setEditErr(filtered_resp?.message ?? "Something went wrong!")

        }


        // else if(filtered_resp?.flag == "2") { //no changes applied // same modal pop that near save changes
        // }else if(filtered_resp?.flag == "0") { //somehting went wrong modal // same modal pop that near save changes
        // }else { // same modal pop that near save changes
        // }
        // closeModal();
    };
    return (<div className="p-5 border border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6">
      <div className="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
          <h4 className="text-lg font-semibold text-gray-800 dark:text-white/90 lg:mb-6">
            Personal Information
          </h4>

          <div className="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
            <div>
              <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                Username
              </p>
              <p className="text-sm font-medium text-gray-800 dark:text-white/90">
                {usernameVal ?? ""}
              </p>
            </div>

            <div>
              <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                Email
              </p>
              <p className="text-sm font-medium text-gray-800 dark:text-white/90">
                {emailVal ?? ""}
              </p>
            </div>

            <div>
              <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                Phone
              </p>
              <p className="text-sm font-medium text-gray-800 dark:text-white/90">
                {phoneVal ?? ""}
              </p>
            </div>

            {/* <div>
              <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                Phone
              </p>
              <p className="text-sm font-medium text-gray-800 dark:text-white/90">
                +09 363 398 46
              </p>
            </div> */}

            <div>
              <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                Bio
              </p>
              <p className="text-sm font-medium text-gray-800 dark:text-white/90">
                {/* This Cloudlink Application is designed and developed by Sriram mariappan
                 */}
                {bioVal ?? ""}
              </p>
            </div>
          </div>
        </div>

        <button onClick={openModal} className="flex w-full items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 lg:inline-flex lg:w-auto">
          <svg className="fill-current" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fillRule="evenodd" clipRule="evenodd" d="M15.0911 2.78206C14.2125 1.90338 12.7878 1.90338 11.9092 2.78206L4.57524 10.116C4.26682 10.4244 4.0547 10.8158 3.96468 11.2426L3.31231 14.3352C3.25997 14.5833 3.33653 14.841 3.51583 15.0203C3.69512 15.1996 3.95286 15.2761 4.20096 15.2238L7.29355 14.5714C7.72031 14.4814 8.11172 14.2693 8.42013 13.9609L15.7541 6.62695C16.6327 5.74827 16.6327 4.32365 15.7541 3.44497L15.0911 2.78206ZM12.9698 3.84272C13.2627 3.54982 13.7376 3.54982 14.0305 3.84272L14.6934 4.50563C14.9863 4.79852 14.9863 5.2734 14.6934 5.56629L14.044 6.21573L12.3204 4.49215L12.9698 3.84272ZM11.2597 5.55281L5.6359 11.1766C5.53309 11.2794 5.46238 11.4099 5.43238 11.5522L5.01758 13.5185L6.98394 13.1037C7.1262 13.0737 7.25666 13.003 7.35947 12.9002L12.9833 7.27639L11.2597 5.55281Z" fill=""/>
          </svg>
          Edit
        </button>
      </div>

      <Modal isOpen={isOpen} onClose={()=>{closeModal();if(updateStatus == true) navigate(0)}} className="max-w-[700px] m-4">
        <div id="page-1" className="page-1 no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
          <div className="px-2 pr-14">
            <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
              Edit Personal Information
            </h4>
            <p className="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
              Update your details to keep your profile up-to-date.
            </p>
          </div>
          <form className="flex flex-col">
            <div className="custom-scrollbar h-[450px] overflow-y-auto px-2 pb-3">
              <div>
                <h5 className="mb-5 text-lg font-medium text-gray-800 dark:text-white/90 lg:mb-6">
                  Social Links
                </h5>

                <div className="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                  <div>
                    <Label>Facebook</Label>
                    <Input type="text" value="https://www.facebook.com/PimjoHQ"/>
                  </div>

                  <div>
                    <Label>X.com</Label>
                    <Input type="text" value="https://x.com/PimjoHQ"/>
                  </div>

                  <div>
                    <Label>Linkedin</Label>
                    <Input type="text" value="https://www.linkedin.com/company/pimjo"/>
                  </div>

                  <div>
                    <Label>Instagram</Label>
                    <Input type="text" value="https://instagram.com/PimjoHQ"/>
                  </div>
                </div>
              </div>
              <div className="mt-7">
                <h5 className="mb-5 text-lg font-medium text-gray-800 dark:text-white/90 lg:mb-6">
                  Personal Information
                </h5>

                <div className="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                  {editable_personal_info.map((elem,index)=>{
                   return (<div key={index} className="col-span-2 lg:col-span-1">
                      <Label>{elem.label}</Label>
                      <Input type="text" value={elem.value} onChange={(e)=>handlOnChange(e,index)} onBlur={()=>handleOnBlur(index)}/>
                      <span id={elem.errID} className="text-error-400 text-xs hidden">{elem.errVal}</span>

                  </div>)

                  })}
           
                </div>
              </div>
            </div>
            <div className="flex items-center gap-3 px-2 mt-6 lg:justify-end">
              <Button size="sm" variant="outline" onClick={closeModal}>
                Close
              </Button>
              <Button size="sm" onClick={(e)=>{handleSave(e); console.log(changed)}}>
                Save Changes
              </Button>
            </div>
            <span id="edit-err" className="text-error-400 text-xs hidden">{editErr}</span>
          </form>
        </div>

         <div id="page-2" className="page-2 no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11 hidden">
          <div className="px-2 pr-14">
            <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
                  Signup Status
            </h4>
          </div>
        { updateStatus == true &&  (<ComponentCard title="Signup Successful
">
        <Alert
          variant="success"
          title="Update Successful"
          message="Your profile has been updated successfully."
          showLink={false}
        />

           {/* <Alert
            variant="warning"
            title="Warning Message"
            message="Be cautious when performing this action."
            showLink={false}
          /> */}
          </ComponentCard> )} 
          { updateStatus ==false &&  (<ComponentCard title="Signup Failed">
                <Alert
            variant="error"
            title="Update Error"
            message="We couldn’t update your profile. Please try again later."
            showLink={false}
          />

          </ComponentCard> )} 
         
       
          <form className="flex flex-col items-center">

            <div className="flex flex-col items-center gap-3 px-2 mt-10 lg:flex-row lg:justify-end w-full">
              <Button size="sm" variant="outline" type="button" onClick={(e) =>{closeModal(); navigate(0)} }>
                Profile
              </Button>
            </div>
          </form>
        </div>
      </Modal>
    </div>);
}
