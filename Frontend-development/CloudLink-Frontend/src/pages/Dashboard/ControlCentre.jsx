import PageMeta from "../../components/common/PageMeta";
import ControlCentreInteface from "../../components/ControlCentre/ControlCentreInterface";
import { RefershIcon } from "../../icons";
import { useNavigate } from "react-router";
export default function ControlCentre() {
    const navigate = useNavigate();
    const handleRefresh = () => { 
        navigate("/controlcentre");
    }
    return (<>
      <PageMeta title="CloudLink | The Real Technology" description="The CloudLink is application developed by sriram marippan, This application maily used integrate multiple tools, currently it is having Unfied Weather Service"/>
      {/* <div className="grid grid-cols-12 gap-4 md:gap-6"></div> */}
        <div className="w-full min-h-[83vh] h-[83vh]">
            <div className="w-full h-[10%] flex">
                <h4 className="text-2xl font-semibold text-gray-800 dark:text-white/90 lg:mb-6 w-[70%] ">
                    Devices Control Centre
                </h4>
                <span onClick={handleRefresh} className="menu-item-icon-size ml-[70%] cursor-pointer">
                    <RefershIcon/>
                </span>
            </div>
            <ControlCentreInteface/>
        </div>
      </>
      );
    }


