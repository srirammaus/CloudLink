import PageMeta from "../../components/common/PageMeta";
import AuthLayout from "./AuthPageLayout";
import Verification from "../../components/auth/Verification";
export default function VerificationPg() {
    return (<>
      <PageMeta title="CloudLink | The Real Technology" description="The CloudLink is application developed by sriram marippan, This application maily used integrate multiple tools, currently it is having Unfied Weather Service"/>
      <AuthLayout>
        <Verification />
      </AuthLayout>
    </>);
}
