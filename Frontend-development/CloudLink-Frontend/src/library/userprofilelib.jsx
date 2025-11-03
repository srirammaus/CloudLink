import { useNavigate } from "react-router";
import { useEffect, useState } from "react";
import { Atom } from "react-loading-indicators";

import React from "react";
/**
 * This component is not compulsory to used by all, something like /editprofile route page any how that gonna call the /api/profile so there may be a check , if not authenticated then redirected
 * @param {*} param0 
 * @returns 
 * ["new_name","new_username","new_email","new_secondary_email","new_phone_number","new_bio"];
 */
async function updateProfile ({data,navigate}) {
  let arr = Object.entries(data)
  
 
  let formData = new FormData()
  arr.forEach(([index,elem])=>{
    console.log(index,elem)
    if(index == "Name") {
      console.log("this happen")
      formData.append("new_name",elem)
    }
    if(index == "Username") {
      formData.append("new_username",elem)
    }
    if(index== "Email") {
      formData.appened("new_email",elem)
    }
    if(index == "Bio") {
      formData.append("new_bio",elem)
    }
    if(index == "Phone") {
      formData.append("new_phone",elem)
    }
    if(index == "Secondary Email") {
      formData.append("new_secondary_email",elem)
    }
  })
  const filtered_resp ={
      flag:'0',
      message:"something went wrong",
  }
  //2400,1702,1701,1205,1207,2501- redirect to sigin page or retry
  // 1806,1300,1200,1000,1904,2100,1805 
  //2000,2003
  if(arr.length > 0 ) {
    try{
      const res = await post(formData);
  
      if(res?.flag == "1") {
        response_filter(res,filtered_resp);
        return filtered_resp

      }else {
        response_filter(res,filtered_resp);
        const redirection_flag = ["2400","1702","1701","1205","1207","2501"]
        const safeListedErrorCodes = ["1806","1300","1200","1000","1904","2100","1805" ]
        const err_flag = filtered_resp?.flag;
        console.log(res)

        if(redirection_flag.includes(err_flag)){
          //later clear the session too
          
          navigate("/")
        }else if(safeListedErrorCodes.includes(err_flag)) {
          return filtered_resp
        }else {
          //something went wrong
          filtered_resp.flag = "0"
          filtered_resp.message = "something went wrong"
        }

      }
    }
    catch (err) {
        //sowmthing went wrong
      console.log(err.message)
      filtered_resp.flag = "0"
      filtered_resp.message = "something went wrong"

    }
  }else {
    // show modal as no changes
    filtered_resp.flag = "2"
    filtered_resp.message = "No changes Applied"
  }

  //end return signature state
  return filtered_resp
  
}
async function post(data) {
  try {
    console.log(data)
    const resp = await fetch(userprofileURLs().updateProfile,{
      method:"POST",
      body:data,
      credentials:"include",
    })
    if(!resp) {
      throw new Error("something went wrong");
    }
    let js;
    try{
      js = resp.json()
    }catch(err) {
      throw new Error("Parsing error")
    }
    return js
    
  }catch (err) { //any technical errors
    return {
      ErrorCode:"2000",
      message:"Something went wrong",
    }
  }
  return
}
export function Userprofile ({children}) {
    const navigate = useNavigate();
    const [username,setUsername] = useState();
    const [isAuth,setAuth] = useState();
    const [isLoading,setLoading] = useState(true);
    const [filtered_resp,setFilterResp] = useState({
        flag:'0',
        message:"something went wrong",
    })
    // const filtered_resp = {
    //     flag:'0',
    //     message:"something went wrong",
    // }
    useEffect(()=>{
      const cookies = document.cookie.toString();
      const cookies_ = cookies.split(";");
      let found = null;
      cookies_.forEach(cookie => {
        if(cookie.includes("username")) {
          console.log(cookie.split("="))
          found= cookie.split("=")[1];
        }
      });
      if(!found) {
        navigate("/")
      }
      setUsername(found)

   
    },[])
    console.log(username)
    useEffect(()=>{
      (async () => {
        try {
          if(!username) {
            return
          }
          
          const data = {
            username:username,
          }
          const res = await Get(data);
      
          if(res?.flag == "1") {
            setAuth(true)
            response_filter(res,filtered_resp)
            console.log(filtered_resp)
          }else {
            //We having ErrorCodes so we have to handle authetication errors and http erros here (that's why i not used 401,403,500 in below)
            //other than 2000,2003 take them to signin page and (note : use signout or clearsessionn function later)
            console.log(res)
            if(res?.ErrorCode == "2000" || res?.ErrorCode == "2003") {
              navigate("/dashboard"); //as of now redirecting him to dashboard later take him inter server error page
            }else {
              //add the clearSession later
              navigate("/")
            }
          }
        }catch (err){
          //These errs mostly front end errors
          console.log(err.message)
          navigate("/dashboard"); //now itself redirecting dashboard later take him  somehting went wrong page 
        }finally {
          if(isAuth == true) {
            setLoading(false)
          }

        }
      })()
    },[username,isAuth])
    if(isLoading) return <div className="flex w-full h-[80vh] items-center justify-center"><Atom color="#729a5a" size="medium" text="" textColor="" /></div>
    if(isAuth){

      const params = {
        name: filtered_resp?.message?.name ?? "",
        username:filtered_resp?.message?.username ?? "",
        email:filtered_resp?.message?.email ??  filtered_resp?.message?.pending_email  ?? "",
        phone:filtered_resp?.message.phone ?? iltered_resp?.message?.pending_phone ??"",
        bio:filtered_resp?.message.bio ?? "",
        onClick:{updateProfile}
      }
      return <>
      {
        React.Children.map(children,child=>{
          let cl = React.cloneElement(child,{...params})
          return cl
          
        })
      }
      </>
    }



}
async function Get(data) {
    try {
      const query = new URLSearchParams(data);
      const resp = await fetch(userprofileURLs().getUserInfo+`?${query}`,{
          method:"GET",
          credentials:"include",
       
      });
      //other than 200
      // if(!resp.ok) {
        // //unAuthorized access
        //   if(resp.status == 401) {
        //   }
        //   if(resp.status == 403) { //forbidden
        //   }
        //   if(resp.status == 500) { // redirect to Internal server error page
        //   }
      // }
      if(!resp) {
        throw new Error("something went wrong");
      }

      let contentType = resp.headers.get("content-type") || "" ;
      if(!contentType.includes("application/json")) {
        throw new Error("unexpected Content Type")
      }
      let js ;
      try {
        js = await resp.json();

      }catch(err) {
        throw new Error("Parsing Error")
      }
        return js;
    }
    //any technicall errors can be caught here, validation error shodl be checked in that above
    catch(err) {
        
        return {ErrorCode:"2000",message:"something went wrong"}
    }
  
}

function response_filter (resp,filtered_resp) {
    if(resp?.flag) {
        filtered_resp.flag = resp.flag
    }
    if(resp?.ErrorCode){
      filtered_resp.flag = resp.ErrorCode
    }
    if(resp?.message && typeof(resp?.message) == "object") {
      response_filter(resp.message,filtered_resp)
    }else if(resp?.message &&typeof(resp?.message) == "string" ) {
      filtered_resp.message = resp.message
    }else {
      filtered_resp.message = resp
    }

}
function userprofileURLs() {
    return {
        getUserInfo:"http://localhost:8000/api/user/getUserInfo.api.php",
        updateProfile:"http://localhost:8000/api/user/updateprofile.api.php"

    }
}