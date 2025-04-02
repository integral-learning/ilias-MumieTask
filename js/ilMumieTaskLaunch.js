(function($) {
    $(document).ready(function() {

        const launchController = (function() {
            const launchButton = document.getElementById('xmum_launch');
            return {
                init: function(element) {
                    launchButton.onclick = function(e) {
                        e.preventDefault();
                        openMumieTask()
                    };
                }
            }
        })();

        function openMumieTask() {
            const loginurl = document.getElementById('xmum_loginurl')?.value;
            const userId = document.getElementById('xmum_userId')?.value;
            const token = document.getElementById('xmum_token')?.value;
            const org = document.getElementById('xmum_org')?.value;
            const problempath = document.getElementById('xmum_problempath')?.value;
            const lang = document.getElementById('xmum_lang')?.value;
            const deadlineinmilliseconds = document.getElementById('xmum_deadlineinmilliseconds')?.value;
            const signeddata = document.getElementById('xmum_signeddata')?.value;

            window.open('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/openMumieTask.php?' +
                `loginurl=${ encodeURIComponent(loginurl)}`+
                `&userId=${userId}`+
                `&token=${token}`+
                `&org=${org}`+
                `&problempath=${problempath}`+
                `&lang=${lang}` +
                `&deadlineinmilliseconds=${deadlineinmilliseconds}` +
                `&signeddata=${signeddata}`);
        }

        launchController.init();
    })
})(jQuery)