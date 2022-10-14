<div class="flex flex-col pb-5 pt-8 px-5 sm:px-10 bg-white rounded-10 overflow-hidden shadow-xl transform transition-all sm:w-full"
    x-data="{ logoutWarningTimer: 30, countDownWidth:100, logoutCountdownInterval:null }"
>
    <div class="flex justify-between items-center">
        <h2>{{ __('modal.logout_warning_title') }}</h2>
        <x-button.close @click="$wire.emit('closeModal'); Core.startUserLogoutInterval(false)" class="relative -right-3"/>
    </div>

    <div class="divider mb-5 mt-2.5"></div>

    <div class="body1 mb-5">
        {{ __('modal.logout_warning_text') }}
    </div>

    <div class="flex justify-between items-center w-full gap-4"
        x-init="
            (() => {
                let deductionLength=countDownWidth/logoutWarningTimer;
                logoutCountdownInterval = setInterval(()=>{
                    countDownWidth = countDownWidth - deductionLength;
                    logoutWarningTimer--;
                    if (logoutWarningTimer <= 0) {
                        $wire.userLogout();
                        clearInterval(logoutCountdownInterval);
                    }; 
            }, 1000)})();
        "
    >
        <span class="logout-progress-bar">
            <span :style="{width: countDownWidth+'%'}"></span>
        </span>

        <x-button.cta @click="clearInterval(logoutCountdownInterval); $wire.emit('closeModal'); Core.startUserLogoutInterval(false)">
            <span>{{ __('modal.extend_session') }}</span>
        </x-button.cta>
    </div>
</div>