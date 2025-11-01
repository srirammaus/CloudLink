import validator from 'validator';
export const safeListedErrorCodes = [
    // Username
    "1000", // Invalid Username
    "1001", // Invalid characters
    "1002", // Too short
    "1003", // Limit exceeded
    "1004", // Username exists
    "1005", // Invalid Inputs

    // Password
    "1100", // Invalid Password
    "1101", // Too short
    "1102", // Too weak
    "1103", // Too long
    "1104", // Do not match

    // Email
    "1200", // Invalid Email
    "1201", // Invalid characters
    "1202", // Email too long
    "1203", // Email already registered
    "1204", // Domain not allowed
    "1206", // Email already verified
    "1207", // Invalid Token
    "1208", // Not verified

    // Phone
    "1300", // Invalid Phone
    "1301", // Invalid characters
    "1302", // Too short
    "1303", // Too long
    "1304", // Already registered
    "1306", // OTP Expired
    "1307", // Number already verified
    "1308", // Invalid OTP

    // Bio
    "1400", // Invalid Bio
    "1401", // Invalid chars
    "1402", // Too long
    "1403", // Too short

    // General Validation
    "1600", // Required missing
    "1601", // Invalid chars
    "1602", // Too short
    "1603", // Too long

    // Captcha
    "1905", // Captcha failed
    "1906", // Captcha generation failed

    // Session / account
    "2301", // account deactivated
];

export function signinURLs () {
    return {
        // captcha:"http://localhost:8000/api/user/captcha.api.php",
        signin:"http://localhost:8000/api/user/signin.api.php"
    }
}

export function isValidUsername (username) {
    if(!username) {
        username = ""
    }
    console.log(username)
    return validator.matches(
        username.trim(),
        /^[a-zA-Z0-9_.]+$/
    )
}
export async function signIn(props) {
    const filtered_resp = {
        flag:'0',
        message:"something went wrong",
    }
    const Data = {
        username: validator.escape(props.username),
        password:validator.escape(props.password),
        timezone:props.timezone,
        remember_me:props.remember_me,

    }

    const signinData = new FormData();
    Object.entries(Data).forEach(([key, value]) => {

        signinData.append(key, value);
    });
    try {
        
        const res = await post(signinData)
        console.log(res)
        if(res?.flag =="1") {
            response_filter(res,filtered_resp)
            console.log(filtered_resp);
            return filtered_resp;
        }else{
            // return  false
            console.log(res)
            response_filter(res,filtered_resp)
            console.log(filtered_resp)
            return filtered_resp;
        }
    }catch(err) {
        // return  false
        console.log(err.message)
        filtered_resp.flag = "0";
        filtered_resp.message = "something went wrong";
        return filtered_resp;
    }
}
export async function post (signinData) {

    try {
        const resp = await fetch(signinURLs().signin,{
            method:"POST",
            credentials:"include",
            body: signinData,
        });
        if(!resp) {
            return {}
        }
        // console.log(await resp.text)
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