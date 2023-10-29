module.exports = {
    content: [
        "./vendor/wire-elements/modal/resources/views/*.blade.php",
        "./resources/js/**/*.js",
        "./resources/css/**/*.css",
        "./resources/views/**/*.blade.php"
    ],
    safelist: [
        "sm:w-full",
        "sm:max-w-md",
        "md:max-w-xl",
        "lg:max-w-3xl",
        "xl:max-w-5xl",
        "2xl:max-w-6xl",
        "max-w-[600px]",
        "max-w-modal",
        "max-w-[720px]",
        "mx-8",
        "bg-teacher-primary-light",
        "w-3/4",
        "h-1/2",
        "w-5/6",
        "lg:w-4/6",
        "h-[80vh]",
        "w-[80vw]",
        "h-[45vw]"
    ],
    theme: {
        extend: {
            colors: {
                primary: "#004df5",
                teacherPrimaryLight: "#4781ff",
                secondary: "#CEDAF3",
                sysbase: "#041f74",
                bluegrey: "#c3d0ed",
                offwhite: "#f9faff",
                allred: "#cf1b04",
                cta: "#3ab753",
                ctamiddark: "#27973D",
                ctadark: "#006314",
                midgrey: "#929DAF",
                student: "#ECDB00",
                lightgreen: "#95cd3e",
                orange: "#ff9d23",
                note: "#6b7789",
                lightGrey: "#F0F2F5"
            },
            borderWidth: {
                "3": "3px",
                "6": "6px"
            },
            width: {
                "50": "12.5rem",
                "auto": "auto",
            },
            height: {
                "12.5": "3.125rem",
                "15": "3.75rem",
            },
            zIndex: {
                "1": 1
            },
            maxWidth: {
                "modal": "700px"
            },
            boxShadow: {
                "hover": "var(--hover-shadow)"
            },
            padding: {
                "15": "3.75rem"
            },
            borderRadius: {
                "10": "10px",
            }
        },
        keyframes: {
            knightrider: {
                "0%": { left: "0" },
                "50%": { left: "85%" },
                "100%": { left: "0" }
            },
            borderPulse: {
                "0%": { "border-color": "rgba(255,255,255, 1)" },
                "50%": { "border-color": "rgba(255,255,255, .2)" },
                "100%": { "border-color": "rgba(255,255,255, 1)" }
            },
            spin: {
                "from": { "transform": "rotate(0deg)" },
                "to": { "transform": "rotate(360deg)" }
            }
        },
        animation: {
            "knightrider": "knightrider 2s ease infinite",
            "borderpulse": "borderPulse 3s ease infinite",
            "spin": "spin 2s linear infinite"
        }
    },
    plugins: [],
};
