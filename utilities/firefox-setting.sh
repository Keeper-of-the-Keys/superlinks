#!/bin/bash

if [[ "${OSTYPE}" == "linux-gnu"* ]]; then
        firefox_userdata_path=~/.mozilla/firefox

elif [[ "$OSTYPE" == "darwin"* ]]; then
        firefox_userdata_path="${HOME}/Library/Application Support/Firefox"

elif [[ "$OSTYPE" == "win32" ]]; then
	# I don't know if this if clause will even work.
	# In all likelyhood the user will have to manually set this or a cmd script written.
        #firefox_userdata_path=
	echo 'Please submit a patch for the right paths on windows if this works'
	exit 1
fi

# If firefox is running open the restart required page.
# The user can ignore this and continue working but new settings won't take effect until after the restart.
# This is cleaner than just running killall -r firefox.
function detect_firefox_and_warn {
	firefox_running=`pgrep firefox`
	if [ ! -z ${firefox_running} ]; then
		firefox about:restartrequired
	fi
}

profile_path=$(awk -F "=" '/Default/ {print $2}' "${firefox_userdata_path}/installs.ini")

# DEBUG
#firefox_userdata_path=.
#profile_path=.

if [ -d "${firefox_userdata_path}/${profile_path}" ]; then
	if [ -f "${firefox_userdata_path}/${profile_path}/prefs.js" ]; then
		go_set=`grep -c 'user_pref("browser.fixup.domainwhitelist.go", true)' "${firefox_userdata_path}/${profile_path}/prefs.js"`
		go_unset=`grep -c 'user_pref("browser.fixup.domainwhitelist.go", false)' "${firefox_userdata_path}/${profile_path}/prefs.js"`

		if (( ${go_set} == 0 )) && (( ${go_unset} == 0 )); then

			if [ -f "${firefox_userdata_path}/${profile_path}/user.js" ]; then
				go_user_set=`grep -c 'user_pref("browser.fixup.domainwhitelist.go", true)' "${firefox_userdata_path}/${profile_path}/user.js"`
				go_user_unset=`grep -c 'user_pref("browser.fixup.domainwhitelist.go", false)' "${firefox_userdata_path}/${profile_path}/user.js"`

				if (( ${go_user_set} == 0 )) && (( ${go_user_unset} == 0 )); then
					echo 'user_pref("browser.fixup.domainwhitelist.go", true);' >> "${firefox_userdata_path}/${profile_path}/user.js"
					detect_firefox_and_warn
				else
					if (( ${go_user_set} == 1 )); then
						echo 'Setting browser.fixup.domainwhitelist.go=true already exists in user.js'
					else
						echo 'Setting browser.fixup.domainwhitelist.go=false already exists in user.js, please change manually.'
					fi
				fi
			else
				echo 'user_pref("browser.fixup.domainwhitelist.go", true);' >> "${firefox_userdata_path}/${profile_path}/user.js"
				detect_firefox_and_warn
			fi
		else
			if (( ${go_set} == 1 )); then
				echo 'Setting browser.fixup.domainwhitelist.go=true already exists in prefs.js'
			else
				echo 'Setting browser.fixup.domainwhitelist.go=false already exists in prefs.js, please change manually'
			fi
		fi
	else
		echo 'prefs.js not found at the expected patch, please modify your settings manually.'
	fi
else
	echo 'The detected/guessed Firefox profile path does not exist, please modify your settings manually.'
fi
