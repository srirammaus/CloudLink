import { useNavigate } from "react-router";

export default function HomeInterface() {
    var navigate = useNavigate();
    const paths = {
        "control-centre":"/controlcentre",
        "learn-more":"https://srirammaus.github.io"
    }
    const handleOnClick = (e) => {
        // alert(e.target.value);
        const val = e.target.value
        if(val == "learn-more") {
            window.open(paths[val])
        }else {
            navigate(paths[val])
        }
        
    }
    return (
    <div className="rounded-2xl border border-gray-200 bg-white px-5 pb-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6 h-[83vh] flex flex-col justify-center items-center">
        
        {/* Animated Cloud Icon with gradient background */}
        <div className="relative w-40 h-40 mb-6">
        <div className="absolute inset-0 animate-pulse-slow bg-gradient-to-br from-blue-300 via-blue-500 to-purple-600 rounded-full blur-3xl opacity-30"></div>
        <span className="text-7xl animate-bounce relative z-10 inline-flex">☁️</span>
        </div>

        {/* Main Heading */}
        <h1 className="text-5xl sm:text-6xl font-extrabold text-gray-800 dark:text-white/90 text-center leading-tight">
        Welcome to <span className="text-blue-600">CloudLink</span>
        </h1>

        {/* Subheading */}
        <p className="mt-4 text-lg sm:text-xl font-medium text-gray-600 dark:text-gray-400 text-center">
        Connect • Sync • Simplify your digital world ⚡
        </p>

        {/* Call to Action Buttons */}
        <div className="mt-8 flex flex-wrap justify-center gap-4">
        <button value="control-centre" onClick={handleOnClick} className="px-6 py-3 rounded-full bg-blue-600 text-white font-semibold shadow-lg hover:bg-blue-700 transition-all transform hover:scale-105">
            Get Started
        </button>
        <button value="learn-more" onClick={handleOnClick} className="px-6 py-3 rounded-full border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 font-semibold hover:bg-gray-100 dark:hover:bg-gray-800 transition-all transform hover:scale-105">
            Learn More
        </button>
        </div>

        {/* Footer subtle text */}
        <p className="mt-10 text-sm text-gray-400 dark:text-gray-500 animate-fadeInSlow">
        Your journey to seamless cloud management begins here.
        </p>
    </div>
    );
}
