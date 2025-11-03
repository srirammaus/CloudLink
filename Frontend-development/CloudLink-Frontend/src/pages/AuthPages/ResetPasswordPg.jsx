import PageMeta from "../../components/common/PageMeta";
import AuthLayout from "./AuthPageLayout";
import ResetPassword from "../../components/auth/ResetPassword";
export default function ResetPasswordPg() {
    return (<>
      <PageMeta title="CloudLink | The Real Technology" description="The CloudLink is application developed by sriram marippan, This application maily used integrate multiple tools, currently it is having Unfied Weather Service"/>
      <AuthLayout>
        <ResetPassword />
      </AuthLayout>
    </>);
}
