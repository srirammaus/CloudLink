import { useState } from "react";
import { Link } from "react-router";
import { ChevronLeftIcon, EyeCloseIcon, EyeIcon } from "../../icons";
import Label from "../form/Label";
import Input from "../form/input/InputField";
import Checkbox from "../form/input/Checkbox";
import Button from "../ui/button/Button";
export default function ForgotPassword() {
    const [showPassword, setShowPassword] = useState(false);
    const [isChecked, setIsChecked] = useState(false);
    return (
  <div className="flex flex-col flex-1">
    <div className="w-full max-w-md pt-10 mx-auto">
      <Link
        to="/signin"
        className="inline-flex items-center text-sm text-gray-500 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
      >
        <ChevronLeftIcon className="size-5" />
        Back to Sign In
      </Link>
    </div>

    <div className="flex flex-col justify-center flex-1 w-full max-w-md mx-auto">
      <div>
        <div className="mb-5 sm:mb-8">
          <h1 className="mb-2 font-semibold text-gray-800 text-title-sm dark:text-white/90 sm:text-title-md">
            Forgot Password?
          </h1>
          <p className="text-sm text-gray-500 dark:text-gray-400">
            Enter your registered email address or username. We'll send you a link to reset your password.
          </p>
        </div>

        <form>
          <div className="space-y-6">
            <div>
              <Label htmlFor="user-identifier">
                Email or Username <span className="text-error-500">*</span>
              </Label>
              <Input
                id="user-identifier"
                placeholder="Enter your email or username"
              />
            </div>

            <div>
              <Button className="w-full" size="sm" type="submit">
                Send Reset Link
              </Button>
            </div>
          </div>
        </form>

        <div className="mt-5">
          <p className="text-sm font-normal text-center text-gray-700 dark:text-gray-400 sm:text-start">
            Don’t have an account?{" "}
            <Link
              to="/signup"
              className="text-brand-500 hover:text-brand-600 dark:text-brand-400"
            >
              Sign Up
            </Link>
          </p>
        </div>
      </div>
    </div>
  </div>
);

}
