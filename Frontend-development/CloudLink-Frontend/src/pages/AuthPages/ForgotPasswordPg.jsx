import PageMeta from "../../components/common/PageMeta";
import AuthLayout from "./AuthPageLayout";
import ForgotPassword from "../../components/auth/ForgotPassword";
export default function ForgotPasswordPg() {
    return (<>
      <PageMeta title="CloudLink | The Real Technology" description="The CloudLink is application developed by sriram marippan, This application maily used integrate multiple tools, currently it is having Unfied Weather Service"/>
      <AuthLayout>
        <ForgotPassword />
      </AuthLayout>
    </>);
}
