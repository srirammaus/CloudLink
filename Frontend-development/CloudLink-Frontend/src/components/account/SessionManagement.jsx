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
import BasicTableOne from "../tables/BasicTables/BasicTableOne";

import { ChevronLeftIcon, EyeCloseIcon, EyeIcon } from "../../icons";
import { Monitor,Smartphone,HelpCircle } from "lucide-react";
import { useNavigate } from "react-router";
import { Table, TableBody, TableCell,TableHeader,TableRow} from "../ui/table";
import Badge from "../ui/badge/Badge";
export default function SessionManagement({isOpen,openModal,closeModal}) {


    // const [oldPassword,setOldPassword] = useState()
    // const [confirmPassword,setConfirmPassword] = useState()
    // const [newPassword,setNewPassword] = useState()

    const [showOldPassword,setShowOldPassword] = useState(false)
    const [showNewPassword, setShowNewPassword] = useState(false);
    const [showCPassword,setShowCPassword] = useState(false);

    const [disable,setDisabled] = useState(true);

    
    const navigate = useNavigate();
    const [verificationStatus,setVerificatioStatus] = useState(false) //error, success

    function handleOnLogOut () {
      alert("Logging out")
    }

    const pageStack = ["page-1"];

    function goBack (e) {
      e.preventDefault()
      const currentPage = pageStack.pop();
      const getCurrentPage =  document.getElementById(currentPage);
      
      const lastPage = Number(currentPage.split("-")[1]) - 1
      const getLastPage = document.getElementById("page-"+lastPage.toString());

      getCurrentPage.classList.add("hidden");
      getLastPage.classList.remove("hidden");

    }
    function goNext() {
      const currentPage = pageStack.at(-1);
      const getCurrentPage = document.querySelector("."+currentPage);

      // console.log(currentPage)
      // console.log(pageStack)

      const NextPage = Number(currentPage.split("-")[1]) + 1
      const getNextPage = document.querySelector(".page-"+NextPage.toString());

      getCurrentPage.classList.add("hidden");
      pageStack.push("page-"+NextPage.toString())
      
      // console.log(NextPage)
      // console.log(getNextPage)
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
const tableData = [
  {
    id: 1,
    device: {
      deviceName: "Windows Laptop (Chrome)",
      deviceType: "unknown",
    },
    location: "Chennai, India",
    loginTime: "25 Oct 2025, 09:10 PM",
    status: "Active",
    ipAddress: "49.207.16.92",
  },
  {
    id: 2,
    device: {
      deviceName: "Android Phone (Edge)",
      deviceType: "phone",
    },
    location: "Bangalore, India",
    loginTime: "25 Oct 2025, 07:42 PM",
    status: "Active",
    ipAddress: "103.245.12.77",
  },
  {
    id: 3,
    device: {
      deviceName: "MacBook Air (Safari)",
      deviceType: "monitor",
    },
    location: "Coimbatore, India",
    loginTime: "25 Oct 2025, 03:18 PM",
    status: "Signed Out",
    ipAddress: "115.99.214.13",
  },
  {
    id: 4,
    device: {
      deviceName: "iPhone 14 (Safari)",
      deviceType: "phone",
    },
    location: "Madurai, India",
    loginTime: "24 Oct 2025, 10:57 PM",
    status: "Active",
    ipAddress: "49.204.33.61",
  },
  {
    id: 5,
    device: {
      deviceName: "Windows Desktop (Firefox)",
      deviceType: "monitor",
    },
    location: "Pune, India",
    loginTime: "24 Oct 2025, 08:23 PM",
    status: "Expired",
    ipAddress: "106.51.142.209",
  },
    {
    id: 6,
    device: {
      deviceName: "Samsung Galaxy S23 (Chrome)",
      deviceType: "phone",
    },
    location: "Hyderabad, India",
    loginTime: "24 Oct 2025, 06:45 PM",
    status: "Active",
    ipAddress: "14.143.88.134",
  },
  {
    id: 7,
    device: {
      deviceName: "Mac Mini (Safari)",
      deviceType: "monitor",
    },
    location: "Delhi, India",
    loginTime: "23 Oct 2025, 11:29 AM",
    status: "Signed Out",
    ipAddress: "106.215.47.12",
  },
  {
    id: 8,
    device: {
      deviceName: "OnePlus 12 (Edge)",
      deviceType: "phone",
    },
    location: "Kochi, India",
    loginTime: "22 Oct 2025, 09:03 PM",
    status: "Expired",
    ipAddress: "49.32.115.78",
  },
  {
    id: 9,
    device: {
      deviceName: "Lenovo ThinkPad (Brave)",
      deviceType: "monitor",
    },
    location: "Jaipur, India",
    loginTime: "22 Oct 2025, 06:17 PM",
    status: "Active",
    ipAddress: "103.212.54.11",
  },
  {
    id: 10,
    device: {
      deviceName: "Redmi Note 13 (Chrome)",
      deviceType: "phone",
    },
    location: "Kolkata, India",
    loginTime: "21 Oct 2025, 04:02 PM",
    status: "Signed Out",
    ipAddress: "117.194.21.230",
  },

];

    return (

      <Modal isOpen={isOpen} onClose={closeModal} className="max-w-[700px] m-4">
      <ComponentCard title="Session Manager">
          <div className="flex h-[70vh]  rounded-xl border border-gray-200 bg-white dark:border-white/[0.05] dark:bg-white/[0.03]">
      <div className="max-w-full custom-scrollbar dark-custom-scrollbar overflow-auto">
        <Table>
          {/* Table Header */}
          <TableHeader className="border-b border-gray-100 dark:border-white/[0.05]">
            <TableRow>
              <TableCell isHeader className="px-10 py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400">
                Device
              </TableCell>
              <TableCell isHeader className="px-10 py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400">
                Location
              </TableCell>
              <TableCell isHeader className="px-10 py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400">
                LoginTime
              </TableCell>
              <TableCell isHeader className="px-10 py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400">
                Status
              </TableCell>
              <TableCell isHeader className="px-10 py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400">
                IpAddress
              </TableCell>
              <TableCell isHeader className="px-10 py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400">
                Action
              </TableCell>
            </TableRow>
          </TableHeader>

          {/* Table Body */}
          <TableBody className="divide-y divide-gray-100 dark:divide-white/[0.05]">
            {tableData.map((session) => (<TableRow key={session.id}>
                <TableCell className="px-5 py-4 sm:px-6 text-start">
                  <div className="flex items-center gap-3">
                      {session.device.deviceType == "phone" && <Smartphone size={40}  className="text-gray-500 text-theme-xs dark:text-gray-400"/> }
                      {session.device.deviceType == "unknown" && <HelpCircle size={40} className="text-gray-500 text-theme-xs dark:text-gray-400"/>}
                      {session.device.deviceType === "monitor" && <Monitor size={40}className="text-gray-500 text-theme-xs dark:text-gray-400"/>}
                    <div>
                      <span className="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                        {session.device.deviceName}
                      </span>
                      <span className="block text-gray-500 text-theme-xs dark:text-gray-400">
                        {session.device.deviceType}
                      </span>
                    </div>
                  </div>
                </TableCell>
                <TableCell className="px-4 py-3 text-gray-500 text-start text-theme-sm dark:text-gray-400">
                  {session.location}
                </TableCell>
                <TableCell className="px-4 py-3 text-gray-500 text-start text-theme-sm dark:text-gray-400">
                  {session.loginTime}
                </TableCell>
                <TableCell className="px-4 py-3 text-gray-500 text-start text-theme-sm dark:text-gray-400">
                  <Badge size="sm" color={session.status === "Active"
                ? "success"
                : session.status === "Expired"
                    ? "warning"
                    : session.status == "Signed Out" ? "error":"error"}>
                    {session.status}
                  </Badge>
                </TableCell>
                <TableCell className="px-4 py-3 text-gray-500 text-theme-sm dark:text-gray-400">
                  {session.ipAddress}
                </TableCell>
                    <TableCell className="px-4 py-3 text-gray-500 text-start text-theme-sm dark:text-gray-400">
                    {session.status == "Active" && <Badge size="sm" color="dark">Log Out</Badge>}
                </TableCell>
              </TableRow>))}
          </TableBody>
        </Table>
      </div>
    </div>
  </ComponentCard>
        
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