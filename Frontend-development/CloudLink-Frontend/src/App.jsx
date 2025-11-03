import { BrowserRouter as Router, Routes, Route } from "react-router";
import SignIn from "./pages/AuthPages/SignIn";
import SignUp from "./pages/AuthPages/SignUp";
import NotFound from "./pages/OtherPage/NotFound";
import UserProfiles from "./pages/Account/UserProfiles";
import Videos from "./pages/UiElements/Videos";
import Images from "./pages/UiElements/Images";
import Alerts from "./pages/UiElements/Alerts";
import Badges from "./pages/UiElements/Badges";
import Avatars from "./pages/UiElements/Avatars";
import Buttons from "./pages/UiElements/Buttons";
import LineChart from "./pages/Charts/LineChart";
import BarChart from "./pages/Charts/BarChart";
import Calendar from "./pages/Calendar";
import BasicTables from "./pages/Tables/BasicTables";
import FormElements from "./pages/Forms/FormElements";
import Blank from "./pages/Blank";
import AppLayout from "./layout/AppLayout";
import { ScrollToTop } from "./components/common/ScrollToTop";
import Home from "./pages/Dashboard/Home";
import ControlCentre from "./pages/Dashboard/ControlCentre";
import ChatBot  from "./pages/Dashboard/ChatBot";
import WeatherForecast from "./pages/service-tools/WeatherForecast";
import UnifiedWeather from "./pages/service-tools/UnifiedWeather";
import AccountSettings from "./pages/Account/AccountSettings";
import ForgotPasswordPg from "./pages/AuthPages/ForgotPasswordPg";
import {Authenticator} from "./library/Authenticator";
import ResetPasswordPg from "./pages/AuthPages/ResetPasswordPg";
import VerificationPg from "./pages/AuthPages/VerificationPg";
export default function App() {
    return (<>
      <Router>
        <ScrollToTop />
        <Routes>
          {/* Dashboard Layout */}
         
            <Route element={<AppLayout />}>
              <Route index path="/dashboard" element={<Authenticator><Home/></Authenticator>}/>
              <Route path="/controlcentre" element={<ControlCentre />}/>
              <Route path="/assistant" element={<ChatBot />}/>
              {/* Account settings*/}

            
              <Route path="/accountsettings" element={<AccountSettings />}/>
              <Route path="/editprofile" element={<UserProfiles />}/>




              {/* Forms */}
              <Route path="/form-elements" element={<FormElements />}/>

              {/* Tables */}
              {/* <Route path="/basic-tables" element={<BasicTables />}/> */}

              {/* Ui Elements */}
              {/* <Route path="/alerts" element={<Alerts />}/>
              <Route path="/avatars" element={<Avatars />}/>
              <Route path="/badge" element={<Badges />}/>
              <Route path="/buttons" element={<Buttons />}/>
              <Route path="/images" element={<Images />}/>
              <Route path="/videos" element={<Videos />}/> */}

              {/* Charts */}
              {/* <Route path="/line-chart" element={<LineChart />}/>
              <Route path="/bar-chart" element={<BarChart />}/> */}

              {/* service and tools */}
              <Route path="/weatherforecast" element={<WeatherForecast/>}/>
              <Route path="/unifiedweather" element={<UnifiedWeather/>}/>
          </Route>


          {/* Auth Layout */}
          <Route path="/" element={<SignIn />}/>
          <Route path="/signin" element={<SignIn />}/>
          <Route path="/signup" element={<SignUp />}/>
          <Route path="/forgotpassword" element={<ForgotPasswordPg/>} />
          <Route path="/resetpassword" element={<ResetPasswordPg/>}/>
          <Route path="/passwordverification" element={<VerificationPg/>}/>

          {/* Fallback Route */}
          <Route path="*" element={<NotFound />}/>
        </Routes>
      </Router>
    </>);
}
