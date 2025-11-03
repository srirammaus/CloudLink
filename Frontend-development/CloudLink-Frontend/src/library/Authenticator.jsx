import { useNavigate } from "react-router";
import { useEffect, useState } from "react";
import { Atom } from "react-loading-indicators";
/**
 * This component is not compulsory to used by all, something like /editprofile route page any how that gonna call the /api/profile so there may be a check , if not authenticated then redirected
 * @param {*} param0 
 * @returns 
 */
export function Authenticator ({children}) {
    const navigate = useNavigate();
    const [isAuth,setAuth] = useState();
    const [isLoading,setLoading] = useState(true);

    const filtered_resp = {
        flag:'0',
        message:"something went wrong",
    }
    
    useEffect(()=>{
      (async () => {
        try {
          const res = await post();
          if(res?.flag == "1") {
            
            setAuth(true)
          }else {
            navigate("/")
          }
        }catch (err){
          console.log(err)
          navigate("/")
        }finally {
          setLoading(false)
          }
          })()
    },[])

    if(isLoading) return <div className="flex w-full h-[80vh] items-center justify-center"><Atom color="#729a5a" size="medium" text="" textColor="" /></div>
    if(isAuth){
      return <>{children}</>
    }



}
async function post() {
    try {
      const resp = await fetch(AuthURLs().authAPI,{
            method:"POST",
          credentials:"include",
      });
      if(!resp) {
          return {}
      }
      console.log(await resp.text)
      return await resp.json();
      }
      catch(err) {
          console.log(err.message)
          return {}
      }
  
}

function response_filter (resp,filtered_resp) {
    if(resp?.flag) {
        filtered_resp.flag = resp.flag
    }
    if(resp?.message && typeof(resp?.message) == "object") {
        response_filter(resp.message,filtered_resp)
    }
    if(resp?.message &&typeof(resp?.message) == "string" ) {
        filtered_resp.message = resp.message
    }
    if(resp?.ErrorCode){
        filtered_resp.flag = resp.ErrorCode
    }
}
function AuthURLs() {
    return {
        authAPI:"http://localhost:8000/api/user/Auth.api.php"
    }
}